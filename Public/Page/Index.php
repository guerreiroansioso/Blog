<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Src/Bootstrap/Request.php';
require_once dirname(__DIR__, 2) . '/Index.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = normalizeRequest($requestUri);
$slug = isset($_GET['slug']) ? normalizeRequest($_GET['slug']) : '';
$pageNumber = isset($_GET['page'])
    ? (filter_var($_GET['page'], FILTER_VALIDATE_INT) ?: 1)
    : 1;

runApplication($requestUri, $slug, $pageNumber);
