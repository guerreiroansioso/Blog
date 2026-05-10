<?php

declare(strict_types=1);

namespace App\Site;

final class Repository {
    private const INTERNAL_FILES = ['Menu.md', 'Config.md', 'Home.md'];
    private const INTERNAL_SLUGS = ['menu', 'config', 'home'];

    private PageFactory $pageFactory;

    public function __construct(private string $contentDirectory) {
        $this->pageFactory = new PageFactory();
    }

    public function listPages(): array {
        $files = glob(rtrim($this->contentDirectory, '/') . '/*.md');
        if ($files === false) { return []; }

        $files = array_values(
            array_filter(
                $files,
                fn(string $filePath): bool => !$this->isInternalFile($filePath)
            )
        );
        sort($files);

        $pages = [];
        foreach ($files as $filePath) {
            $pages[] = $this->pageFactory->fromFile($filePath);
        }

        return $pages;
    }

    public function findBySlug(string $slug): Page {
        if ($this->isInternalSlug($slug)) {
            return $this->pageFactory->notFound($slug);
        }

        foreach ($this->listPages() as $page) {
            if ($page->slug() === $slug) { return $page; }
        }

        return $this->pageFactory->notFound($slug);
    }

    public function listMenuItems(): array {
        $menuFile = rtrim($this->contentDirectory, '/') . '/Menu.md';
        $content = file_get_contents($menuFile);
        if ($content === false) { return []; }

        $lines = preg_split('/\R/', $content) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $matches = [];
            $isMatch = preg_match(
                '/^-\s+\[(.+)\]\((.+)\)$/',
                trim($line),
                $matches
            ) === 1;
            if (!$isMatch) { continue; }

            $label = trim($matches[1]);
            $href = trim($matches[2]);
            if ($label === '' || $href === '') { continue; }

            $items[] = new MenuItem($label, $href);
        }

        return $items;
    }

    public function loadSiteConfig(): SiteConfig {
        $defaults = [
            'siteName' => 'Site',
            'displayName' => 'Lista de Páginas',
            'description' => 'Escolha um conteúdo para abrir.',
            'hidePageList' => 'no',
        ];
        $configKeys = [
            'sitename' => 'siteName',
            'displayname' => 'displayName',
            'description' => 'description',
            'hidepagelist' => 'hidePageList',
            'site_name' => 'siteName',
            'display_name' => 'displayName',
            'hide_page_list' => 'hidePageList',
        ];

        $configFile = rtrim($this->contentDirectory, '/') . '/Config.md';
        $content = file_get_contents($configFile);
        if ($content === false) {
            return new SiteConfig(
                $defaults['siteName'],
                $defaults['displayName'],
                $defaults['description'],
                $this->toBool($defaults['hidePageList'])
            );
        }

        $lines = preg_split('/\R/', $content) ?: [];
        $config = $defaults;

        foreach ($lines as $line) {
            $matches = [];
            $isMatch = preg_match(
                '/^-\s*(siteName|displayName|description|hidePageList|site_name|display_name|hide_page_list)\s*:\s*(.+)$/i',
                trim($line),
                $matches
            ) === 1;
            if (!$isMatch) { continue; }

            $key = $configKeys[strtolower(trim($matches[1]))] ?? trim($matches[1]);
            $value = trim($matches[2]);
            if ($value === '') { continue; }

            $config[$key] = $value;
        }

        return new SiteConfig(
            $config['siteName'],
            $config['displayName'],
            $config['description'],
            $this->toBool($config['hidePageList'])
        );
    }

    public function loadHomePage(): Page {
        $homeFile = rtrim($this->contentDirectory, '/') . '/Home.md';
        if (!is_file($homeFile)) {
            return $this->pageFactory->notFound('home');
        }

        return $this->pageFactory->fromFile($homeFile);
    }

    private function isInternalFile(string $filePath): bool {
        $fileName = pathinfo($filePath, PATHINFO_BASENAME);
        return in_array($fileName, self::INTERNAL_FILES, true);
    }

    private function isInternalSlug(string $slug): bool {
        return in_array($slug, self::INTERNAL_SLUGS, true);
    }

    private function toBool(string $value): bool {
        $normalized = strtolower(trim($value));
        return in_array($normalized, ['1', 'true', 'yes', 'sim'], true);
    }
}
