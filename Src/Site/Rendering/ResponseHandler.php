<?php

declare(strict_types=1);

namespace App\Site\Rendering;

use App\Site\Content\Repository;
use App\Site\Parsing\Parser;

final class ResponseHandler {
    public function __construct(
        private Repository $repository,
        private Parser $parser,
        private ViewRenderer $viewRenderer
    ) {}

    public function renderHome(int $pageNumber = 1): void {
        $menuItems = $this->repository->listMenuItems();
        $siteConfig = $this->repository->loadSiteConfig();
        $footerSections = $this->parseFooterContent(
            $this->repository->loadFooter()
        );

        if (!$siteConfig->hidePageList()) {
            $pages = $this->repository->listPages();
            echo $this->viewRenderer->render('Home', [
                'pageTitle' => $siteConfig->siteName(),
                'items' => $pages,
                'menuItems' => $menuItems,
                'siteConfig' => $siteConfig,
                'footerSections' => $footerSections,
            ]);
            return;
        }

        $homePage = $this->repository->loadHomePage();
        $content = $this->parsePageContent(
            $homePage->body(),
            $pageNumber,
            '/'
        );
        echo $this->viewRenderer->render('Page', [
            'pageTitle' => $siteConfig->siteName(),
            'contentHtml' => $content['main'],
            'authorName' => $content['author'],
            'sidebarHtml' => $content['sidebar'],
            'sidebarItems' => $content['sidebars'],
            'pagination' => $content['pagination'],
            'menuItems' => $menuItems,
            'siteConfig' => $siteConfig,
            'showBackLink' => false,
            'footerSections' => $footerSections,
        ]);
    }

    public function renderPage(string $slug, int $pageNumber = 1): void {
        $normalizedSlug = $this->normalizeSlug($slug);
        $page = $this->repository->findBySlug($normalizedSlug);

        if ($page->isNotFound()) { http_response_code(404); }

        $content = $this->parsePageContent(
            $page->body(),
            $pageNumber,
            '/page?slug=' . rawurlencode($page->slug())
        );
        $menuItems = $this->repository->listMenuItems();
        $siteConfig = $this->repository->loadSiteConfig();
        $footerSections = $this->parseFooterContent(
            $this->repository->loadFooter()
        );
        echo $this->viewRenderer->render('Page', [
            'pageTitle' => $page->title() . ' | ' . $siteConfig->siteName(),
            'contentHtml' => $content['main'],
            'authorName' => $content['author'],
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
        $slug = normalizeRequest(trim($value));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? 'untitled';
        $slug = trim($slug, '-');

        return match ($slug === '') {
            true => 'untitled',
            false => $slug,
        };
    }

    private function parsePageContent(
        string $body,
        int $pageNumber,
        string $baseUrl
    ): array {
        ['body' => $bodyWithoutAuthor, 'author' => $authorName]
            = $this->extractAuthorSection($body);

        $sections = preg_split('/^\s*#\s+Sidebar\s*$/mi', $bodyWithoutAuthor);
        if ($sections === false || count($sections) < 2) {
            $pages = $this->paginateBody($bodyWithoutAuthor, $pageNumber, $baseUrl);
            return [
                'main' => $this->parser->parse($pages['body']),
                'author' => $authorName,
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
            'author' => $authorName,
            'sidebar' => implode("\n", $sidebars),
            'sidebars' => $sidebars,
            'pagination' => $pages['pagination'],
        ];
    }

    private function extractAuthorSection(string $body): array {
        $matches = [];
        $hasAuthorSection = preg_match(
            '/^\s*#\s+Author\s*$\R([\s\S]*?)(?=^\s*#{1,6}\s+|\z)/mi',
            $body,
            $matches
        ) === 1;
        if (!$hasAuthorSection) {
            return ['body' => $body, 'author' => ''];
        }

        $authorMatch = $matches[1] ?? '';
        $author = is_string($authorMatch) ? trim($authorMatch) : '';
        $author = preg_replace('/\R+/', ' ', $author) ?? '';
        $author = trim($author);

        $bodyWithoutAuthor = preg_replace(
            '/^\s*#\s+Author\s*$\R([\s\S]*?)(?=^\s*#{1,6}\s+|\z)/mi',
            '',
            $body,
            1
        );
        if ($bodyWithoutAuthor === null) {
            $bodyWithoutAuthor = $body;
        }

        return [
            'body' => trim($bodyWithoutAuthor),
            'author' => $author,
        ];
    }

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
                'links' => $this->buildPaginationLinks(
                    $baseUrl,
                    $current,
                    $total
                ),
            ],
        ];
    }

    private function buildPaginationLinks(
        string $baseUrl,
        int $current,
        int $total
    ): array {
        if ($total <= 1) { return []; }

        $links = [];
        for ($page = 1; $page <= $total; $page++) {
            $links[] = [
                'label' => number_format($page, 0, '.', ''),
                'href' => $this->paginationUrl($baseUrl, $page),
                'isCurrent' => $page === $current,
            ];
        }

        return $links;
    }

    private function paginationUrl(string $baseUrl, int $page): string {
        if ($page === 1) { return $baseUrl; }

        $separator = match (str_contains($baseUrl, '?')) {
            true => '&',
            false => '?',
        };
        return $baseUrl . $separator . 'page=' . $page;
    }

    private function parseFooterContent(string $body): array {
        $sections = [];
        $currentTitle = '';
        $currentBody = [];
        $lines = preg_split('/\R/', $body) ?: [];

        foreach ($lines as $line) {
            $matches = [];
            if (preg_match('/^\s*#{1,3}\s+(.+)$/', $line, $matches) === 1) {
                $this->appendFooterSection(
                    $sections,
                    $currentTitle,
                    $currentBody
                );
                $currentTitle = trim($matches[1]);
                $currentBody = [];
                continue;
            }

            $currentBody[] = $line;
        }

        $this->appendFooterSection($sections, $currentTitle, $currentBody);
        return $sections;
    }

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
