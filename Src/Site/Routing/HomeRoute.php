<?php

declare(strict_types=1);

namespace App\Site\Routing;

use App\Site\Rendering\ResponseHandler;

final class HomeRoute implements RouteHandler {
    private array $supportedPathsMap = [
        '/' => true,
        '' => true,
    ];

    public function supports(string $path): bool {
        return isset($this->supportedPathsMap[$path]);
    }

    public function handle(
        string $slug,
        ResponseHandler $responseHandler,
        int $pageNumber
    ): void {
        if ($slug !== '') {
            $responseHandler->renderPage($slug, $pageNumber);
            return;
        }

        $responseHandler->renderHome($pageNumber);
    }
}
