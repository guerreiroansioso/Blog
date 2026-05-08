<?php

declare(strict_types=1);

namespace App\Site;

final class Repository
{
    private PageFactory $pageFactory;

    public function __construct(private string $contentDirectory)
    {
        $this->pageFactory = new PageFactory();
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
            $pages[] = $this->pageFactory->fromFile($filePath);
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

        return $this->pageFactory->notFound($slug);
    }
}
