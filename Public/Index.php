<?php

declare(strict_types=1);

function normalizeRequestUriPathLowercase(string $requestUri): string {
    $parts = parse_url($requestUri);
    if ($parts === false) { return strtolower($requestUri); }

    $path = strtolower($parts['path'] ?? '');
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    return $path . $query;
}

function tryServePublicAsset(string $requestUri): bool {
    $path = parse_url($requestUri, PHP_URL_PATH) ?? '';
    $path = mapAssetAlias(strtolower($path));

    if (!isAssetRequest($path)) { return false; }

    $resolvedPath = resolvePathCaseInsensitive(__DIR__, $path);
    if (!isAllowedAsset($resolvedPath)) { return false; }

    sendAsset($resolvedPath);
    return true;
}

function isAssetRequest(string $path): bool {
    if ($path === '' || $path === '/') { return false; }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === '' || $extension === 'php') { return false; }

    return true;
}

function mapAssetAlias(string $path): string {
    return match ($path) {
        '/style.css', '/styles.css' => '/styles.css',
        default => $path,
    };
}

function isAllowedAsset(string $absolutePath): bool {
    if (!is_file($absolutePath)) { return false; }

    $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
    if ($extension === 'php') { return false; }

    return true;
}

function resolvePathCaseInsensitive(
    string $rootDirectory,
    string $requestPath
): string {
    $segments = array_values(
        array_filter(explode('/', ltrim($requestPath, '/')), 'strlen')
    );
    if ($segments === []) { return ''; }

    $currentPath = $rootDirectory;
    $lastIndex = count($segments) - 1;

    foreach ($segments as $index => $segment) {
        $expectFile = $index === $lastIndex;
        $nextPath = findMatchingEntry($currentPath, $segment, $expectFile);
        if ($nextPath === '') { return ''; }
        $currentPath = $nextPath;
    }

    return $currentPath;
}

function findMatchingEntry(
    string $directory,
    string $segment,
    bool $expectFile
): string {
    $entries = @scandir($directory);
    if ($entries === false) { return ''; }

    $target = strtolower($segment);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') { continue; }
        if (strtolower($entry) !== $target) { continue; }

        $candidate = $directory . '/' . $entry;
        if ($expectFile && is_file($candidate)) { return $candidate; }
        if (!$expectFile && is_dir($candidate)) { return $candidate; }
    }

    return '';
}

function sendAsset(string $absolutePath): void
{
    $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
    $mimeByExtension = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'txt' => 'text/plain',
        'xml' => 'application/xml',
    ];

    $mimeType = $mimeByExtension[$extension] ?? '';
    if ($mimeType === '') {
        $detectedMimeType = mime_content_type($absolutePath);
        if ($detectedMimeType !== false) { $mimeType = $detectedMimeType; }
    }

    if ($mimeType !== '') { header('Content-Type: ' . $mimeType); }

    header('Content-Length: ' . filesize($absolutePath));
    readfile($absolutePath);
}

require_once dirname(__DIR__) . '/Index.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = normalizeRequestUriPathLowercase($requestUri);
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$pageNumber = isset($_GET['page'])
    ? (filter_var($_GET['page'], FILTER_VALIDATE_INT) ?: 1)
    : 1;

match (tryServePublicAsset($requestUri)) {
    true => exit,
    false => runApplication($requestUri, $slug, $pageNumber),
};
