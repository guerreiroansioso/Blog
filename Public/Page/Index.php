<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Src/Bootstrap/Request.php';
require_once dirname(__DIR__, 2) . '/Index.php';

[$requestUri, $slug, $pageNumber] = parseRequest();

runApplication($requestUri, $slug, $pageNumber);
