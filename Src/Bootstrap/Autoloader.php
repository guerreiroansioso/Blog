<?php

declare(strict_types=1);

spl_autoload_register(static function (string $className): void {
    $prefix = 'App\\';
    $baseDirectory = dirname(__DIR__) . '/';

    $hasPrefix = strncmp($prefix, $className, strlen($prefix)) === 0;
    if (!$hasPrefix) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $file = $baseDirectory . str_replace('\\', '/', $relativeClass) . '.php';

    if (!is_file($file)) {
        return;
    }

    require_once $file;
});
