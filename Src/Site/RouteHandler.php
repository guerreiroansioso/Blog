<?php

declare(strict_types=1);

namespace App\Site;

interface RouteHandler {
    public function supports(string $path): bool;
    public function handle(string $slug, ResponseHandler $responseHandler): void;
}
