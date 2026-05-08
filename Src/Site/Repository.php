<?php

declare(strict_types=1);

namespace App\Site;

final class Repository
{
    public function __construct(private string $contentDirectory)
    {
    }

    /** @return Page[] */
    public function ListPages(): array
    {
        $files = glob(rtrim($this->contentDirectory, '/') . '/*.md');

        if ($files === false) {
            return [];
        }

        sort($files);

        $pages = [];

        foreach ($files as $filePath) {
            $pages[] = $this->BuildPage($filePath);
        }

        return $pages;
    }

    public function FindBySlug(string $slug): ?Page
    {
        $normalizedSlug = $this->NormalizeSlug($slug);

        foreach ($this->ListPages() as $page) {
            if ($page->Slug() === $normalizedSlug) {
                return $page;
            }
        }

        return null;
    }

    private function BuildPage(string $filePath): Page
    {
        $body = file_get_contents($filePath);

        if ($body === false) {
            throw new \RuntimeException('Falha ao ler arquivo: ' . $filePath);
        }

        $fileName = (string) pathinfo($filePath, PATHINFO_FILENAME);
        $slug = $this->NormalizeSlug($fileName);
        $title = ucwords(str_replace('-', ' ', $slug));

        return new Page($slug, $title, $body);
    }

    private function NormalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? 'untitled';
        return trim($slug, '-') ?: 'untitled';
    }
}
