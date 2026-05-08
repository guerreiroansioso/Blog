<?php

declare(strict_types=1);

namespace App\Site;

final class App
{
    public function __construct(
        private Repository $repository,
        private Parser $parser,
        private ViewRenderer $viewRenderer
    ) {
    }

    public function handle(string $requestUri, string $slug = ''): void
    {
        $path = (string) parse_url($requestUri, PHP_URL_PATH);

        match ($path) {
            '/', '' => $this->renderHome($slug),
            '/page',
            '/page/',
            '/page/Index.php' => $this->renderPageRoute($slug),
            default => $this->renderNotFound(),
        };
    }

    private function renderHome(string $slug): void
    {
        if ($slug !== '') {
            $this->renderPage($this->normalizeSlug($slug));
            return;
        }

        $pages = $this->repository->listPages();
        echo $this->viewRenderer->render('Home', [
            'pageTitle' => 'Lista de Páginas',
            'items' => $pages,
        ]);
    }

    private function renderPageRoute(string $slug): void
    {
        if ($slug === '') {
            http_response_code(400);
            echo 'Parâmetro slug é obrigatório.';
            return;
        }

        $this->renderPage($this->normalizeSlug($slug));
    }

    private function renderNotFound(): void
    {
        http_response_code(404);
        echo '404 - Página não encontrada';
    }

    private function renderPage(string $slug): void
    {
        $page = $this->repository->findBySlug($slug);

        if ($page->title() === 'Página não encontrada') {
            http_response_code(404);
        }

        $contentHtml = $this->parser->parse($page->body());

        echo $this->viewRenderer->render('Page', [
            'pageTitle' => $page->title(),
            'contentHtml' => $contentHtml,
        ]);
    }

    private function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? 'untitled';
        return trim($slug, '-') ?: 'untitled';
    }
}
