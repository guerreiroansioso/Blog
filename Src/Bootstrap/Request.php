<?php

declare(strict_types=1);

function normalizeRequest(array|string $value): array|string {
    if (is_array($value)) {
        return array_map('strtolower', $value);
    }

    return strtolower($value);
}

function parseRequest(): array {
    $requestUri = normalizeRequest($_SERVER['REQUEST_URI'] ?? '/');
    $slug = isset($_GET['slug']) ? normalizeRequest($_GET['slug']) : '';
    $pageNumber = isset($_GET['page']) ? $_GET['page'] : 1;

    return [$requestUri, $slug, $pageNumber];
}
