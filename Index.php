<?php

declare(strict_types=1);

use App\Site\App;
use App\Site\Parser;
use App\Site\Repository;
use App\Site\ViewRenderer;

require_once __DIR__ . '/Src/Bootstrap/Autoloader.php';

function BuildApplication(): App
{
    return new App(
        new Repository(__DIR__ . '/Content'),
        new Parser(),
        new ViewRenderer(__DIR__ . '/Views')
    );
}

function RunApplication(string $requestUri, string $slug = ''): void
{
    BuildApplication()->Handle($requestUri, $slug);
}
