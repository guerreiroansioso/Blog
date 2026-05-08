<?php

declare(strict_types=1);

namespace App\Site;

final class Repository
{
    public function __construct(private string $contentDirectory)
    {
    }

    public function listPages(): array
    {
        $files = glob(rtrim($this->contentDirectory, '/') . '/*.md');

        if ($files === false) {
            return [];
        }

        sort($files);

        $pages = [];

        foreach ($files as $filePath) {
            $pages[] = $this->buildPage($filePath);
        }

        return $pages;
    }

    public function findBySlug(string $slug): Page
    {
        foreach ($this->listPages() as $page) {
            if ($page->slug() === $slug) {
                return $page;
            }
        }

        return $this->buildNotFoundPage($slug);
    }

    private function buildPage(string $filePath): Page
    {
        $body = file_get_contents($filePath);

        if ($body === false) {
            throw new \RuntimeException('Falha ao ler arquivo: ' . $filePath);
        }

        $fileName = (string) pathinfo($filePath, PATHINFO_FILENAME);
        $slug = $this->normalizeSlug($fileName);
        $extractedTitle = $this->extractTitle($body);
        $title = $extractedTitle !== ''
            ? $extractedTitle
            : ucwords(str_replace('-', ' ', $slug));

        return new Page($slug, $title, $body);
    }

    private function extractTitle(string $content): string
    {
        if (!preg_match('/^\s*#\s+(.+)$/m', $content, $matches)) {
            return '';
        }

        return trim($matches[1]);
    }

    private function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? 'untitled';
        return trim($slug, '-') ?: 'untitled';
    }

    private function buildNotFoundPage(string $slug): Page
    {
        return new Page(
            $slug,
            'Página não encontrada',
            "# Página não encontrada\n\nO conteúdo solicitado não existe."
        );
    }
}
