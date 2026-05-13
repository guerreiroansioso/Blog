<?php

declare(strict_types=1);

namespace App\Site\Routing;

use App\Site\Rendering\ResponseHandler;

interface RouteHandler {
    public function supports(string $path): bool;
    public function handle(
        string $slug,
        ResponseHandler $responseHandler,
        int $pageNumber
    ): void;
}
