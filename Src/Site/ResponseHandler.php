<?php

declare(strict_types=1);

namespace App\Site;

final class ResponseHandler {
    public function __construct(
        private Repository $repository,
        private Parser $parser,
        private ViewRenderer $viewRenderer
    ) {}

    public function renderHome(): void {
        $menuItems = $this->repository->listMenuItems();
        $siteConfig = $this->repository->loadSiteConfig();
        $footerSections = $this->parseFooterContent($this->repository->loadFooter());

        if ($siteConfig->hidePageList()) {
            $homePage = $this->repository->loadHomePage();
            $content = $this->parsePageContent($homePage->body());
            echo $this->viewRenderer->render('Page', [
                'pageTitle' => $siteConfig->siteName(),
                'contentHtml' => $content['main'],
                'sidebarHtml' => $content['sidebar'],
                'sidebarItems' => $content['sidebars'],
                'menuItems' => $menuItems,
                'siteConfig' => $siteConfig,
                'showBackLink' => false,
                'footerSections' => $footerSections,
            ]);
            return;
        }

        $pages = $this->repository->listPages();
        echo $this->viewRenderer->render('Home', [
            'pageTitle' => $siteConfig->siteName(),
            'items' => $pages,
            'menuItems' => $menuItems,
            'siteConfig' => $siteConfig,
            'footerSections' => $footerSections,
        ]);
    }

    public function renderPage(string $slug): void {
        $normalizedSlug = $this->normalizeSlug($slug);
        $page = $this->repository->findBySlug($normalizedSlug);

        if ($page->title() === 'Página não encontrada') {
            http_response_code(404);
        }

        $content = $this->parsePageContent($page->body());
        $menuItems = $this->repository->listMenuItems();
        $siteConfig = $this->repository->loadSiteConfig();
        $footerSections = $this->parseFooterContent($this->repository->loadFooter());
        echo $this->viewRenderer->render('Page', [
            'pageTitle' => $page->title() . ' | ' . $siteConfig->siteName(),
            'contentHtml' => $content['main'],
            'sidebarHtml' => $content['sidebar'],
            'sidebarItems' => $content['sidebars'],
            'menuItems' => $menuItems,
            'siteConfig' => $siteConfig,
            'showBackLink' => true,
            'footerSections' => $footerSections,
        ]);
    }

    public function renderMissingSlug(): void {
        http_response_code(400);
        echo 'Parâmetro slug é obrigatório.';
    }

    public function renderNotFoundRoute(): void {
        http_response_code(404);
        echo '404 - Página não encontrada';
    }

    private function normalizeSlug(string $value): string {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? 'untitled';
        $slug = trim($slug, '-');
        
        if ($slug === '') { return 'untitled'; }

        return $slug;
    }

    /**
     * @return array{main: string, sidebar: string, sidebars: list<string>}
     */
    private function parsePageContent(string $body): array {
        $sections = preg_split('/^\s*#\s+Sidebar\s*$/mi', $body);
        if ($sections === false || count($sections) < 2) {
            return [
                'main' => $this->parser->parse($body),
                'sidebar' => '',
                'sidebars' => [],
            ];
        }

        $sidebars = [];
        foreach (array_slice($sections, 1) as $section) {
            $sidebarHtml = $this->parser->parse($section);
            if ($sidebarHtml === '') { continue; }

            $sidebars[] = $sidebarHtml;
        }

        return [
            'main' => $this->parser->parse($sections[0]),
            'sidebar' => implode("\n", $sidebars),
            'sidebars' => $sidebars,
        ];
    }

    /**
     * @return list<array{title: string, content: string}>
     */
    private function parseFooterContent(string $body): array {
        $sections = [];
        $currentTitle = '';
        $currentBody = [];
        $lines = preg_split('/\R/', $body) ?: [];

        foreach ($lines as $line) {
            $matches = [];
            if (preg_match('/^\s*#{1,3}\s+(.+)$/', $line, $matches) === 1) {
                $this->appendFooterSection($sections, $currentTitle, $currentBody);
                $currentTitle = trim($matches[1]);
                $currentBody = [];
                continue;
            }

            $currentBody[] = $line;
        }

        $this->appendFooterSection($sections, $currentTitle, $currentBody);
        return $sections;
    }

    /**
     * @param list<array{title: string, content: string}> $sections
     * @param list<string> $body
     */
    private function appendFooterSection(
        array &$sections,
        string $title,
        array $body
    ): void {
        $content = $this->parser->parse(trim(implode("\n", $body)));
        if ($title === '' && $content === '') { return; }

        $sections[] = [
            'title' => $title,
            'content' => $content,
        ];
    }
}
