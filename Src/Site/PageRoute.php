<?php

declare(strict_types=1);

namespace App\Site;

final class PageRoute implements RouteHandler {
    private array $supportedPathsMap = [
        '/page' => true,
        '/page/' => true,
        '/page/index.php' => true,
    ];

    public function supports(string $path): bool {
        return isset($this->supportedPathsMap[$path]);
    }

    public function handle(
        string $slug,
        ResponseHandler $responseHandler,
        int $pageNumber
    ): void {
        if ($slug === '') { $responseHandler->renderMissingSlug(); return; }

        $responseHandler->renderPage($slug, $pageNumber);
    }
}
