<?php

declare(strict_types=1);

namespace App\Site\Routing;

use App\Site\Rendering\ResponseHandler;

final class NotFoundRoute implements RouteHandler {
    public function supports(string $path): bool { return true; }

    public function handle(
        string $slug,
        ResponseHandler $responseHandler,
        int $pageNumber
    ): void {
        $responseHandler->renderNotFoundRoute();
    }
}
