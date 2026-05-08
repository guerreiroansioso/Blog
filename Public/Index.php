<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Index.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';

runApplication($requestUri, $slug);
