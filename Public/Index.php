<?php

declare(strict_types=1);

function tryServePublicAsset(string $requestUri): bool
{
    $path = parse_url($requestUri, PHP_URL_PATH) ?? '';
    $path = mapAssetAlias($path);

    if (!isAssetRequest($path)) {
        return false;
    }

    $resolvedPath = resolvePathCaseInsensitive(__DIR__, $path);
    if (!isAllowedAsset($resolvedPath)) {
        return false;
    }

    sendAsset($resolvedPath);
    return true;
}

function isAssetRequest(string $path): bool
{
    if ($path === '' || $path === '/') {
        return false;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return $extension !== '' && $extension !== 'php';
}

function mapAssetAlias(string $path): string
{
    $lowerPath = strtolower($path);
    return match ($lowerPath) {
        '/style.css', '/styles.css' => '/Styles.css',
        default => $path,
    };
}

function isAllowedAsset(string $absolutePath): bool
{
    if (!is_file($absolutePath)) {
        return false;
    }

    $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
    return $extension !== 'php';
}

function resolvePathCaseInsensitive(
    string $rootDirectory,
    string $requestPath
): string {
    $segments = array_values(
        array_filter(explode('/', ltrim($requestPath, '/')), 'strlen')
    );
    if ($segments === []) {
        return '';
    }

    $currentPath = $rootDirectory;
    $lastIndex = count($segments) - 1;

    foreach ($segments as $index => $segment) {
        $expectFile = $index === $lastIndex;
        $nextPath = findMatchingEntry($currentPath, $segment, $expectFile);
        if ($nextPath === '') {
            return '';
        }
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
    if ($entries === false) {
        return '';
    }

    $target = strtolower($segment);
    foreach ($entries as $entry) {
        if (
            $entry === '.'
            || $entry === '..'
            || strtolower($entry) !== $target
        ) {
            continue;
        }

        $candidate = $directory . '/' . $entry;
        if ($expectFile && is_file($candidate)) {
            return $candidate;
        }

        if (!$expectFile && is_dir($candidate)) {
            return $candidate;
        }
    }

    return '';
}

function sendAsset(string $absolutePath): void
{
    $mimeType = mime_content_type($absolutePath);
    if ($mimeType !== false) {
        header('Content-Type: ' . $mimeType);
    }

    header('Content-Length: ' . filesize($absolutePath));
    readfile($absolutePath);
}

require_once dirname(__DIR__) . '/Index.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

match (tryServePublicAsset($requestUri)) {
    true => exit,
    false => runApplication($requestUri, $slug),
};
