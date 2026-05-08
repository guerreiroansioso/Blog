<?php

declare(strict_types=1);

namespace App\Site;

final class Parser
{
    public function parse(string $content): string
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $html = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, '### ')) {
                $html[] = '<h3>' . $this->inline(substr($trimmed, 4)) . '</h3>';
                continue;
            }

            if (str_starts_with($trimmed, '## ')) {
                $html[] = '<h2>' . $this->inline(substr($trimmed, 3)) . '</h2>';
                continue;
            }

            if (str_starts_with($trimmed, '# ')) {
                $html[] = '<h1>' . $this->inline(substr($trimmed, 2)) . '</h1>';
                continue;
            }

            $html[] = '<p>' . $this->inline($trimmed) . '</p>';
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
}
