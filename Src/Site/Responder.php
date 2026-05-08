<?php

declare(strict_types=1);

namespace App\Site;

final class Responder {
    public function __construct(
        private Repository $repository,
        private Parser $parser,
        private ViewRenderer $viewRenderer
    ) {}

    public function renderHome(): void {
        $pages = $this->repository->listPages();
        echo $this->viewRenderer->render('Home', [
            'pageTitle' => 'Lista de Páginas',
            'items' => $pages,
        ]);
    }

    public function renderPage(string $slug): void {
        $normalizedSlug = $this->normalizeSlug($slug);
        $page = $this->repository->findBySlug($normalizedSlug);

        if ($page->title() === 'Página não encontrada') {
            http_response_code(404);
        }

        $contentHtml = $this->parser->parse($page->body());
        echo $this->viewRenderer->render('Page', [
            'pageTitle' => $page->title(),
            'contentHtml' => $contentHtml,
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
}
