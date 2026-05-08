<?php

declare(strict_types=1);

namespace App\Site;

final class PageRoute implements RouteHandler
{
    public function supports(string $path): bool
    {
        return $path === '/page'
            || $path === '/page/'
            || $path === '/page/Index.php';
    }

    public function handle(string $slug, Responder $responder): void
    {
        if ($slug === '') {
            $responder->renderMissingSlug();
            return;
        }

        $responder->renderPage($slug);
    }
}
