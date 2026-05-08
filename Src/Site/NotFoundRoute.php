<?php

declare(strict_types=1);

namespace App\Site;

final class NotFoundRoute implements RouteHandler
{
    public function supports(string $path): bool
    {
        return true;
    }

    public function handle(string $slug, Responder $responder): void
    {
        $responder->renderNotFoundRoute();
    }
}
