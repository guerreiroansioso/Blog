<?php

declare(strict_types=1);

namespace App\Site;

final class HomeRoute implements RouteHandler {
    private const SUPPORTED_PATHS = ['/', ''];

    public function supports(string $path): bool {
        return in_array($path, self::SUPPORTED_PATHS, true);
    }

    public function handle(string $slug, ResponseHandler $responseHandler): void {
        if ($slug !== '') { $responseHandler->renderPage($slug); return; }

        $responseHandler->renderHome();
    }
}
