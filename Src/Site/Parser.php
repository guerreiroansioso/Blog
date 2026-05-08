<?php

declare(strict_types=1);

namespace App\Site;

final class Parser
{
    private const HEADING_MAP = [
        '### ' => 'h3',
        '## ' => 'h2',
        '# ' => 'h1',
    ];

    public function parse(string $content): string
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $html = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $this->appendParsedLine($html, $trimmed);
        }

        return implode("\n", $html);
    }

    private function inline(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escaped = preg_replace(
            '/\*\*(.+?)\*\*/',
            '<strong>$1</strong>',
            $escaped
        ) ?? $escaped;
        $escaped = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $escaped)
            ?? $escaped;

        return $escaped;
    }

    private function buildHeading(string $line): string
    {
        foreach (self::HEADING_MAP as $prefix => $tag) {
            if (!str_starts_with($line, $prefix)) {
                continue;
            }

            return $this->buildHeadingHtml($line, $prefix, $tag);
        }

        return '';
    }

    private function appendParsedLine(array &$html, string $line): void
    {
        $headingHtml = $this->buildHeading($line);
        $html[] = match ($headingHtml !== '') {
            true => $headingHtml,
            false => '<p>' . $this->inline($line) . '</p>',
        };
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
