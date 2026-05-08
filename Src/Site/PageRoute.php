<?php

declare(strict_types=1);

namespace App\Site;

final class PageRoute implements RouteHandler
{
    private const SUPPORTED_PATHS = [
        '/page',
        '/page/',
        '/page/Index.php',
    ];

    public function supports(string $path): bool
    {
        return in_array($path, self::SUPPORTED_PATHS, true);
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
