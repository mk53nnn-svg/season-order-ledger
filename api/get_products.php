<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$genreId = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;
if ($genreId <= 0) {
    echo json_encode([]);
    exit;
}

$products = get_products_by_genre($genreId);
echo json_encode($products, JSON_UNESCAPED_UNICODE);
