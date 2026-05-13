<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/Src/Bootstrap/Request.php';

final class AssetResponder {
    private const allowedExtensions = [
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

    public function tryServe(string $requestUri): bool {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (!$this->isAssetRequest($path)) { return false; }

        $publicRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Public';
        $hasImageSubfolder = strpos($path, '/images/') === 0;

        if ($hasImageSubfolder) {
            $publicRoot .= DIRECTORY_SEPARATOR . 'Images';
            $path = substr($path, strlen('/images'));
        }

        $absolutePath = null;
        $requestedName = basename($path);
        foreach (scandir($publicRoot) as $entry) {
            if (strtolower($entry) === strtolower($requestedName)) {
                $absolutePath = realpath(
                    $publicRoot . DIRECTORY_SEPARATOR . $entry
                );
                break;
            }
        }

        if (!$absolutePath) { return false; }
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return false;
        }

        $this->sendAsset($absolutePath);
        return true;
    }

    private function isAssetRequest(string $path): bool {
        if ($path === '' || $path === '/') { return false; }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return $extension !== '' && $extension !== 'php';
    }

    private function sendAsset(string $absolutePath): void {
        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);

        $selected = self::allowedExtensions[$extension] ?? null;
        if ($selected) { header('Content-Type: ' . $selected); }

        header('Content-Length: ' . filesize($absolutePath));
        readfile($absolutePath);
    }
}

require_once dirname(__DIR__) . '/Index.php';

[$requestUri, $slug, $pageNumber] = parseRequest();
$assetResponder = new AssetResponder();

match ($assetResponder->tryServe($requestUri)) {
    true => exit,
    false => runApplication($requestUri, $slug, $pageNumber),
};
