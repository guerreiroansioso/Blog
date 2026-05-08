<?php

declare(strict_types=1);

namespace App\Site;

final class HomeRoute implements RouteHandler
{
    public function supports(string $path): bool
    {
        return $path === '/' || $path === '';
    }

    public function handle(string $slug, Responder $responder): void
    {
        if ($slug !== '') {
            $responder->renderPage($slug);
            return;
        }

        $responder->renderHome();
    }
}
