<?php

declare(strict_types=1);

namespace App\Site\Content;

use App\Site\Model\MenuItem;
use App\Site\Model\Page;
use App\Site\Model\SiteConfig;

final class Repository {
    private array $internalFilesMap = [
        'Menu.md' => true,
        'Config.md' => true,
        'Home.md' => true,
        'Footer.md' => true,
    ];
    private array $internalSlugsMap = [
        'menu' => true,
        'config' => true,
        'home' => true,
        'footer' => true,
    ];
    private array $trueValuesMap = [
        '1' => true,
        'true' => true,
        'yes' => true,
        'sim' => true,
    ];

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
            $href = $this->normalizeHrefPathLowercase(trim($matches[2]));
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
            'showLogo' => 'no',
            'blogColors' => '',
        ];
        $configFile = rtrim($this->contentDirectory, '/') . '/Config.md';
        $content = file_get_contents($configFile);
        if ($content === false) {
            return new SiteConfig(
                $defaults['siteName'],
                $defaults['displayName'],
                $defaults['description'],
                $this->toBool($defaults['hidePageList']),
                $this->toBool($defaults['showLogo']),
                $this->parseBlogColors($defaults['blogColors'])
            );
        }

        $lines = preg_split('/\R/', $content) ?: [];
        $config = $defaults;

        for ($index = 0; $index < count($lines); $index++) {
            $line = $lines[$index];
            $matches = [];
            $isMatch = preg_match(
                '/^\s*-\s*(siteName|displayName|description|hidePageList|'
                . 'showLogo|blogColors)\s*:\s*(.*)$/',
                $line,
                $matches
            ) === 1;
            if (!$isMatch) { continue; }

            $key = trim($matches[1]);
            $value = trim($matches[2]);

            if ($key === 'blogColors' && $value === '') {
                $pairs = [];
                $nextIndex = $index + 1;

                while ($nextIndex < count($lines)) {
                    $subMatches = [];
                    $isSubItem = preg_match(
                        '/^\s{2,}-\s*([a-zA-Z_]+)\s*:\s*(.+)\s*$/',
                        $lines[$nextIndex],
                        $subMatches
                    ) === 1;
                    if (!$isSubItem) { break; }

                    $pairs[] = trim($subMatches[1]) . '=' . trim($subMatches[2]);
                    $nextIndex++;
                }

                $value = implode(', ', $pairs);
                $index = $nextIndex - 1;
            }

            if ($value === '') { continue; }

            $config[$key] = $value;
        }

        return new SiteConfig(
            $config['siteName'],
            $config['displayName'],
            $config['description'],
            $this->toBool($config['hidePageList']),
            $this->toBool($config['showLogo']),
            $this->parseBlogColors($config['blogColors'])
        );
    }

    public function loadHomePage(): Page {
        $homeFile = rtrim($this->contentDirectory, '/') . '/Home.md';
        if (!is_file($homeFile)) {
            return $this->pageFactory->notFound('home');
        }

        return $this->pageFactory->fromFile($homeFile);
    }

    public function loadFooter(): string {
        $footerFile = rtrim($this->contentDirectory, '/') . '/Footer.md';
        $content = file_get_contents($footerFile);
        if ($content === false) { return ''; }

        return $content;
    }

    private function isInternalFile(string $filePath): bool {
        $fileName = pathinfo($filePath, PATHINFO_BASENAME);
        return isset($this->internalFilesMap[$fileName]);
    }

    private function isInternalSlug(string $slug): bool {
        return isset($this->internalSlugsMap[$slug]);
    }

    private function toBool(string $value): bool {
        $normalized = normalizeRequest(trim($value));
        return isset($this->trueValuesMap[$normalized]);
    }

    private function normalizeHrefPathLowercase(string $href): string {
        $lower = strtolower($href);
        if (
            str_starts_with($lower, 'http://')
            || str_starts_with($lower, 'https://')
        ) {
            return $href;
        }

        if ($href === '' || $href[0] !== '/') { return $href; }

        $parts = parse_url($href);
        if ($parts === false) { return strtolower($href); }

        $path = strtolower($parts['path'] ?? '');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }

    private function parseBlogColors(string $value): array {
        $raw = trim($value);
        if ($raw === '') { return []; }

        $map = [
            'bg' => '--bg',
            'card' => '--card',
            'text' => '--text',
            'muted' => '--muted',
            'primary' => '--primary',
            'primarystrong' => '--primary-strong',
            'border' => '--border',
        ];
        $colors = [];
        $pairs = preg_split('/\s*[|,]\s*/', $raw) ?: [];

        foreach ($pairs as $pair) {
            if ($pair === '' || !str_contains($pair, '=')) { continue; }

            [$name, $hex] = array_map('trim', explode('=', $pair, 2));
            $normalizedName = normalizeRequest($name);
            $cssVar = $map[$normalizedName] ?? null;
            if ($cssVar === null) { continue; }

            if (preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) { continue; }

            $colors[$cssVar] = strtolower($hex);
        }

        return $colors;
    }
}
