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

    public function Handle(string $requestUri, string $slug = ''): void
    {
        $path = (string) parse_url($requestUri, PHP_URL_PATH);

        match ($path) {
            '/', '' => $this->RenderHome($slug),
            '/page', '/page/', '/page/Index.php' => $this->RenderPageRoute($slug),
            default => $this->RenderNotFound(),
        };
    }

    private function RenderHome(string $slug): void
    {
        if ($slug !== '') {
            $this->RenderPage($slug);
            return;
        }

        $pages = $this->repository->ListPages();
        echo $this->viewRenderer->Render('Home', [
            'pageTitle' => 'Lista de Páginas',
            'items' => $pages,
        ]);
    }

    private function RenderPageRoute(string $slug): void
    {
        if ($slug === '') {
            http_response_code(400);
            echo 'Parâmetro slug é obrigatório.';
            return;
        }

        $this->RenderPage($slug);
    }

    private function RenderNotFound(): void
    {
        http_response_code(404);
        echo '404 - Página não encontrada';
    }

    private function RenderPage(string $slug): void
    {
        $page = $this->repository->FindBySlug($slug);

        if ($page === null) {
            http_response_code(404);
            echo 'Página não encontrada.';
            return;
        }

        $contentHtml = $this->parser->Parse($page->Body());

        echo $this->viewRenderer->Render('Page', [
            'pageTitle' => $page->Title(),
            'contentHtml' => $contentHtml,
        ]);
    }
}
