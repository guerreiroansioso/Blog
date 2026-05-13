<?php

declare(strict_types=1);

use App\Site\Content\Repository;
use App\Site\Core\App;
use App\Site\Parsing\Parser;
use App\Site\Rendering\ViewRenderer;

require_once __DIR__ . '/Src/Bootstrap/Autoloader.php';

function buildApplication(): App {
    return new App(
        new Repository(__DIR__ . '/Content'),
        new Parser(),
        new ViewRenderer(__DIR__ . '/Views')
    );
}

function runApplication(
    string $requestUri,
    string $slug = '',
    int $pageNumber = 1
): void {
    buildApplication()->handle($requestUri, $slug, $pageNumber);
}
