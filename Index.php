<?php

declare(strict_types=1);

use App\Site\App;
use App\Site\Parser;
use App\Site\Repository;
use App\Site\ViewRenderer;

require_once __DIR__ . '/Src/Bootstrap/Autoloader.php';

function buildApplication(): App
{
    return new App(
        new Repository(__DIR__ . '/Content'),
        new Parser(),
        new ViewRenderer(__DIR__ . '/Views')
    );
}

function runApplication(string $requestUri, string $slug = ''): void
{
    buildApplication()->handle($requestUri, $slug);
}
