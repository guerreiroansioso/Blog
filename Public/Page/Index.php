<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Index.php';

function normalizeRequest(string $requestUri): string {
    return strtolower($requestUri);
}

$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = normalizeRequest($requestUri);
$slug = $_GET['slug'];
$pageNumber = filter_var($_GET['page'], FILTER_VALIDATE_INT);

runApplication($requestUri, $slug, $pageNumber);
