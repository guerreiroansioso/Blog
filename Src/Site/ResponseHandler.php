<?php

declare(strict_types=1);

namespace App\Site;

final class ResponseHandler {
    public function __construct(
        private Repository $repository,
        private Parser $parser,
        private ViewRenderer $viewRenderer
    ) {}

    public function renderHome(int $pageNumber = 1): void {
        $menuItems = $this->repository->listMenuItems();
        $siteConfig = $this->repository->loadSiteConfig();
        $footerSections = $this->parseFooterContent($this->repository->loadFooter());

        if ($siteConfig->hidePageList()) {
            $homePage = $this->repository->loadHomePage();
            $content = $this->parsePageContent(
                $homePage->body(),
                $pageNumber,
                '/'
            );
            echo $this->viewRenderer->render('Page', [
                'pageTitle' => $siteConfig->siteName(),
                'contentHtml' => $content['main'],
                'sidebarHtml' => $content['sidebar'],
                'sidebarItems' => $content['sidebars'],
                'pagination' => $content['pagination'],
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

    public function renderPage(string $slug, int $pageNumber = 1): void {
        $normalizedSlug = $this->normalizeSlug($slug);
        $page = $this->repository->findBySlug($normalizedSlug);

        if ($page->title() === 'Página não encontrada') {
            http_response_code(404);
        }

        $content = $this->parsePageContent(
            $page->body(),
            $pageNumber,
            '/page?slug=' . rawurlencode($page->slug())
        );
        $menuItems = $this->repository->listMenuItems();
        $siteConfig = $this->repository->loadSiteConfig();
        $footerSections = $this->parseFooterContent($this->repository->loadFooter());
        echo $this->viewRenderer->render('Page', [
            'pageTitle' => $page->title() . ' | ' . $siteConfig->siteName(),
            'contentHtml' => $content['main'],
            'sidebarHtml' => $content['sidebar'],
            'sidebarItems' => $content['sidebars'],
            'pagination' => $content['pagination'],
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
     * @return array{
     *     main: string,
     *     sidebar: string,
     *     sidebars: list<string>,
     *     pagination: array{current: int, total: int, links: list<array{
     *         label: string,
     *         href: string,
     *         isCurrent: bool
     *     }>}
     * }
     */
    private function parsePageContent(
        string $body,
        int $pageNumber,
        string $baseUrl
    ): array {
        $sections = preg_split('/^\s*#\s+Sidebar\s*$/mi', $body);
        if ($sections === false || count($sections) < 2) {
            $pages = $this->paginateBody($body, $pageNumber, $baseUrl);
            return [
                'main' => $this->parser->parse($pages['body']),
                'sidebar' => '',
                'sidebars' => [],
                'pagination' => $pages['pagination'],
            ];
        }

        $sidebars = [];
        foreach (array_slice($sections, 1) as $section) {
            $sidebarHtml = $this->parser->parse($section);
            if ($sidebarHtml === '') { continue; }

            $sidebars[] = $sidebarHtml;
        }

        $pages = $this->paginateBody($sections[0], $pageNumber, $baseUrl);
        return [
            'main' => $this->parser->parse($pages['body']),
            'sidebar' => implode("\n", $sidebars),
            'sidebars' => $sidebars,
            'pagination' => $pages['pagination'],
        ];
    }

    /**
     * @return array{
     *     body: string,
     *     pagination: array{current: int, total: int, links: list<array{
     *         label: string,
     *         href: string,
     *         isCurrent: bool
     *     }>}
     * }
     */
    private function paginateBody(
        string $body,
        int $pageNumber,
        string $baseUrl
    ): array {
        $sections = preg_split('/^\s*#\s+Pagination\s*$/mi', $body);
        if ($sections === false) { $sections = [$body]; }

        $sections = array_values(
            array_filter(
                $sections,
                fn(string $section): bool => trim($section) !== ''
            )
        );
        if ($sections === []) { $sections = ['']; }

        $total = count($sections);
        $current = max(1, min($pageNumber, $total));

        return [
            'body' => $sections[$current - 1],
            'pagination' => [
                'current' => $current,
                'total' => $total,
                'links' => $this->buildPaginationLinks($baseUrl, $current, $total),
            ],
        ];
    }

    /**
     * @return list<array{label: string, href: string, isCurrent: bool}>
     */
    private function buildPaginationLinks(
        string $baseUrl,
        int $current,
        int $total
    ): array {
        if ($total <= 1) { return []; }

        $links = [];
        for ($page = 1; $page <= $total; $page++) {
            $links[] = [
                'label' => (string) $page,
                'href' => $this->paginationUrl($baseUrl, $page),
                'isCurrent' => $page === $current,
            ];
        }

        return $links;
    }

    private function paginationUrl(string $baseUrl, int $page): string {
        if ($page === 1) { return $baseUrl; }

        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        return $baseUrl . $separator . 'page=' . $page;
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
