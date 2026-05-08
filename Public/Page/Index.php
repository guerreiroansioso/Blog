<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Index.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/page';
$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';

runApplication($requestUri, $slug);
