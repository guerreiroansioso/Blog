<?php

declare(strict_types=1);

namespace App\Site;

final class Parser {
    private const HEADING_MAP = [
        '### ' => 'h3',
        '## ' => 'h2',
        '# ' => 'h1',
    ];
    /** @var list<LineParseStrategy> */
    private array $lineStrategies;

    public function __construct() {
        $this->lineStrategies = (new LineParseStrategyFactory())->createAll();
    }

    public function parse(string $content): string {
        $lines = preg_split('/\R/', $content) ?: [];
        $html = [];
        $listStack = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') { continue; }

            $item = $this->parseListItem($line);
            if (($item['isList'] ?? false) === true) {
                $this->appendListItem($html, $listStack, $item);
                continue;
            }

            $this->closeAllLists($html, $listStack);
            $this->appendParsedLine($html, $trimmed);
        }

        $this->closeAllLists($html, $listStack);
        return implode("\n", $html);
    }

    public function inline(string $text): string {
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escaped = preg_replace_callback(
            '/\[(.+?)\]\((.+?)\)/',
            function (array $matches): string {
                $label = $matches[1] ?? '';
                $href = $this->sanitizeLinkSource($matches[2] ?? '');
                if ($href === '') { return htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8'); }

                return '<a href="' . $href . '">' . $label . '</a>';
            },
            $escaped
        ) ?? $escaped;
        $escaped = preg_replace(
            '/\*\*(.+?)\*\*/',
            '<strong>$1</strong>',
            $escaped
        ) ?? $escaped;
        $escaped = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $escaped)
            ?? $escaped;

        return $escaped;
    }

    public function buildHeading(string $line): string {
        foreach (self::HEADING_MAP as $prefix => $tag) {
            if (!str_starts_with($line, $prefix)) { continue; }

            return $this->buildHeadingHtml($line, $prefix, $tag);
        }

        return '';
    }

    private function parseListItem(string $line): array {
        $matches = [];
        $isUnordered = preg_match('/^(\s*)[-*+]\s+(.+)$/', $line, $matches) === 1;
        if ($isUnordered) {
            return [
                'isList' => true,
                'level' => $this->indentationLevel($matches[1] ?? ''),
                'type' => 'ul',
                'text' => trim($matches[2] ?? ''),
            ];
        }

        $isOrdered = preg_match('/^(\s*)\d+\.\s+(.+)$/', $line, $matches) === 1;
        if ($isOrdered) {
            return [
                'isList' => true,
                'level' => $this->indentationLevel($matches[1] ?? ''),
                'type' => 'ol',
                'text' => trim($matches[2] ?? ''),
            ];
        }

        return [
            'isList' => false,
            'level' => 0,
            'type' => '',
            'text' => '',
        ];
    }

    private function indentationLevel(string $indent): int {
        $normalized = str_replace("\t", '  ', $indent);
        return intdiv(strlen($normalized), 2);
    }

    private function appendListItem(array &$html, array &$listStack, array $item): void {
        $targetLevel = (int) $item['level'];
        $targetType = (string) $item['type'];
        $targetText = (string) $item['text'];

        if ($targetText === '') { return; }

        while ($listStack !== [] && $listStack[count($listStack) - 1]['level'] > $targetLevel) {
            $this->closeCurrentList($html, $listStack);
        }

        if ($listStack === []) {
            $this->openList($html, $listStack, $targetType, $targetLevel);
        }

        while ($listStack[count($listStack) - 1]['level'] < $targetLevel) {
            $this->openList($html, $listStack, $targetType, $listStack[count($listStack) - 1]['level'] + 1);
        }

        if ($listStack[count($listStack) - 1]['type'] !== $targetType) {
            $this->closeCurrentList($html, $listStack);
            if ($listStack === [] || $listStack[count($listStack) - 1]['level'] !== $targetLevel) {
                $this->openList($html, $listStack, $targetType, $targetLevel);
            } else {
                $this->openList($html, $listStack, $targetType, $targetLevel);
            }
        }

        $index = count($listStack) - 1;
        if ($listStack[$index]['liOpen']) {
            $html[] = '</li>';
            $listStack[$index]['liOpen'] = false;
        }

        $html[] = '<li>' . $this->inline($targetText);
        $listStack[$index]['liOpen'] = true;
    }

    private function openList(
        array &$html,
        array &$listStack,
        string $type,
        int $level
    ): void {
        $index = count($listStack) - 1;
        if ($index >= 0 && !$listStack[$index]['liOpen']) {
            $html[] = '<li>';
            $listStack[$index]['liOpen'] = true;
        }

        $html[] = '<' . $type . '>';
        $listStack[] = ['type' => $type, 'level' => $level, 'liOpen' => false];
    }

    private function closeCurrentList(array &$html, array &$listStack): void {
        if ($listStack === []) { return; }

        $current = array_pop($listStack);
        if (($current['liOpen'] ?? false) === true) {
            $html[] = '</li>';
        }
        $html[] = '</' . ($current['type'] ?? 'ul') . '>';

        $index = count($listStack) - 1;
        if ($index >= 0 && $listStack[$index]['liOpen']) {
            $html[] = '</li>';
            $listStack[$index]['liOpen'] = false;
        }
    }

    private function closeAllLists(array &$html, array &$listStack): void {
        while ($listStack !== []) {
            $this->closeCurrentList($html, $listStack);
        }
    }

    private function appendParsedLine(array &$html, string $line): void {
        foreach ($this->lineStrategies as $lineStrategy) {
            if (!$lineStrategy->supports($line)) { continue; }

            $parsedLine = $lineStrategy->parse($line, $this);
            if ($parsedLine === '') { continue; }

            $html[] = $parsedLine;
            return;
        }
    }

    public function buildImage(string $line): string {
        $matches = [];
        $isImage = preg_match('/^!\[(.*?)\]\((.+)\)$/', $line, $matches) === 1;
        if (!$isImage) { return ''; }

        $alt = trim($matches[1]);
        $src = trim($matches[2]);
        if ($src === '') { return ''; }

        $safeSrc = $this->sanitizeImageSource($src);
        if ($safeSrc === '') { return ''; }

        return '<p><img src="' . $safeSrc . '" alt="'
            . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8')
            . '" loading="lazy"></p>';
    }

    private function sanitizeImageSource(string $source): string {
        $lower = strtolower($source);
        if (str_starts_with($lower, 'javascript:')) { return ''; }
        $normalized = preg_replace('#^/Images/#', '/images/', $source) ?? $source;
        return htmlspecialchars($normalized, ENT_QUOTES, 'UTF-8');
    }

    private function sanitizeLinkSource(string $source): string {
        $trimmed = trim(htmlspecialchars_decode($source, ENT_QUOTES));
        if ($trimmed === '') { return ''; }

        $lower = strtolower($trimmed);
        if (str_starts_with($lower, 'javascript:')) { return ''; }

        return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
    }

    private function buildHeadingHtml(
        string $line,
        string $prefix,
        string $tag
    ): string {
        $offset = strlen($prefix);
        $content = $this->inline(substr($line, $offset));
        return '<' . $tag . '>' . $content . '</' . $tag . '>';
    }
}
