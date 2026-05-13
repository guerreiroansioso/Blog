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

    $pageRaw = $_GET['page'] ?? 1;
    $pageNumber = filter_var($pageRaw, FILTER_VALIDATE_INT);
    if ($pageNumber === false) { $pageNumber = 1; }

    return [$requestUri, $slug, $pageNumber];
}
