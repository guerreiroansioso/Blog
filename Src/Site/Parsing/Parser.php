<?php

declare(strict_types=1);

namespace App\Site\Parsing;

final class Parser {
    private const headingMap = [
        '### ' => 'h3',
        '## ' => 'h2',
        '# ' => 'h1',
    ];
    
    private const lineSplitRegex = '/\R/';
    private const inlineLinkRegex = '/\[(.+?)\]\((.+?)\)/';
    private const inlineBoldRegex = '/\*\*(.+?)\*\*/';
    private const inlineItalicRegex = '/\*(.+?)\*/';
    private const unorderedListRegex = '/^(\s*)[-*+]\s+(.+)$/';
    private const orderedListRegex = '/^(\s*)\d+\.\s+(.+)$/';
    private const imageRegex = '/^!\[(.*?)\]\((.+)\)$/';

    private array $lineParsers;

    public function __construct() {
        $this->lineParsers = (new LineParserFactory())->createAllParsers();
    }

    public function parse(string $content): string {
        $lines = preg_split(self::lineSplitRegex, $content);
        $html = [];
        $listStack = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') { continue; }

            $item = $this->parseListItem($line);
            if ($item['isList'] === true) {
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
        $withLinks = preg_replace_callback(
            self::inlineLinkRegex,
            function (array $matches): string {
                $label = $matches[1];
                $href = $matches[2];

                return '<a href="'
                    . htmlspecialchars($href, ENT_QUOTES, 'UTF-8')
                    . '">' . $label . '</a>';
            },
            $escaped
        );

        $withBold = preg_replace(
            self::inlineBoldRegex,
            '<strong>$1</strong>',
            $withLinks
        );

        $withItalic = preg_replace(
            self::inlineItalicRegex,
            '<em>$1</em>',
            $withBold
        );

        return $withItalic;
    }

    public function buildHeading(string $line): string {
        foreach (self::headingMap as $prefix => $tag) {
            if (!str_starts_with($line, $prefix)) { continue; }

            return $this->buildHeadingHtml($line, $prefix, $tag);
        }
    }

    private function parseListItem(string $line): array {
        $matches = [];
        $isUnordered = preg_match(
            self::unorderedListRegex,
            $line,
            $matches
        ) === 1;
        if ($isUnordered) {
            return [
                'isList' => true,
                'level' => $this->indentationLevel($matches[1]),
                'type' => 'ul',
                'text' => trim($matches[2]),
            ];
        }

        $isOrdered = preg_match(self::orderedListRegex, $line, $matches) === 1;
        if ($isOrdered) {
            return [
                'isList' => true,
                'level' => $this->indentationLevel($matches[1]),
                'type' => 'ol',
                'text' => trim($matches[2]),
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

    private function appendListItem(
        array &$html,
        array &$listStack,
        array $item
    ): void {
        while (true) {
            if ($listStack === []) {
                $this->openList(
                    $html,
                    $listStack,
                    $item['type'],
                    $item['level']
                );
                break;
            }

            $lastLevel = $listStack[count($listStack) - 1]['level'];
            if ($lastLevel > $item['level']) {
                $this->closeCurrentList($html, $listStack);
                continue;
            }

            if ($lastLevel < $item['level']) {
                $this->openList(
                    $html,
                    $listStack,
                    $item['type'],
                    $lastLevel + 1
                );
                continue;
            }

            break;
        }

        $lastIndex = count($listStack) - 1;
        if ($listStack[$lastIndex]['type'] !== $item['type']) {
            $this->closeCurrentList($html, $listStack);
            $this->openList($html, $listStack, $item['type'], $item['level']);
            $lastIndex = count($listStack) - 1;
        }

        if ($listStack[$lastIndex]['liOpen']) {
            $html[] = '</li>';
            $listStack[$lastIndex]['liOpen'] = false;
        }

        $html[] = '<li>' . $this->inline($item['text']);
        $listStack[$lastIndex]['liOpen'] = true;
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
        $current = array_pop($listStack);
        if ($current['liOpen'] === true) {
            $html[] = '</li>';
        }
        $html[] = '</' . $current['type'] . '>';

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
        foreach ($this->lineParsers as $lineParser) {
            if (!$lineParser->supports($line)) { continue; }

            $parsedLine = $lineParser->parse($line, $this);
            $html[] = $parsedLine;
            return;
        }
    }

    public function buildImage(string $line): string {
        $matches = [];
        $isImage = preg_match(self::imageRegex, $line, $matches) === 1;

        $alt = trim($matches[1]);
        $src = trim($matches[2]);

        return '<p><img src="'
            . htmlspecialchars(normalizeRequest($src), ENT_QUOTES, 'UTF-8')
            . '" alt="'
            . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8')
            . '" loading="lazy"></p>';
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
