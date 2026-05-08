<?php

declare(strict_types=1);

namespace App\Site;

final class PageFactory
{
    public function fromFile(string $filePath): Page
    {
        $body = file_get_contents($filePath);
        if ($body === false) {
            throw new \RuntimeException('Falha ao ler arquivo: ' . $filePath);
        }

        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $slug = $this->normalizeSlug($fileName);
        $title = $this->extractTitle($body);
        if ($title === '') {
            $title = ucwords(str_replace('-', ' ', $slug));
        }

        return new Page($slug, $title, $body);
    }

    public function notFound(string $slug): Page
    {
        return new Page(
            $slug,
            'Página não encontrada',
            "# Página não encontrada\n\nO conteúdo solicitado não existe."
        );
    }

    private function extractTitle(string $content): string
    {
        $matches = [];
        if (preg_match('/^\s*#\s+(.+)$/m', $content, $matches) !== 1) {
            return '';
        }

        return trim($matches[1]);
    }

    private function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? 'untitled';
        $slug = trim($slug, '-');
        if ($slug === '') {
            return 'untitled';
        }

        return $slug;
    }
}
