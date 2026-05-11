<?php

declare(strict_types=1);

namespace App\Site;

final class Parser {
    private const HEADING_MAP = [
        '### ' => 'h3',
        '## ' => 'h2',
        '# ' => 'h1',
    ];

    public function parse(string $content): string {
        $lines = preg_split('/\R/', $content) ?: [];
        $html = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') { continue; }

            $this->appendParsedLine($html, $trimmed);
        }

        return implode("\n", $html);
    }

    private function inline(string $text): string {
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

    private function buildHeading(string $line): string {
        foreach (self::HEADING_MAP as $prefix => $tag) {
            if (!str_starts_with($line, $prefix)) { continue; }

            return $this->buildHeadingHtml($line, $prefix, $tag);
        }

        return '';
    }

    private function appendParsedLine(array &$html, string $line): void {
        $imageHtml = $this->buildImage($line);
        if ($imageHtml !== '') {
            $html[] = $imageHtml;
            return;
        }

        $headingHtml = $this->buildHeading($line);
        $html[] = match ($headingHtml !== '') {
            true => $headingHtml,
            false => '<p>' . $this->inline($line) . '</p>',
        };
    }

    private function buildImage(string $line): string {
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
