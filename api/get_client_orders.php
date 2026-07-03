<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$clientName = isset($_GET['client_name']) ? trim($_GET['client_name']) : '';
$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($clientName === '' || $seasonId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'パラメータが不足しています。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = get_pdo();
$stmt = $pdo->prepare("
    SELECT o.id, o.order_date, o.delivery_type, o.delivery_date, o.quantity,
           p.product_name, p.product_code, g.name AS genre_name
    FROM orders o
    INNER JOIN products p ON p.id = o.product_id
    INNER JOIN genres g ON g.id = p.genre_id
    WHERE o.client_name = :client_name AND o.season_id = :season_id
    ORDER BY o.order_date ASC, o.id ASC
");
$stmt->execute(['client_name' => $clientName, 'season_id' => $seasonId]);
$orders = $stmt->fetchAll();

$total = array_sum(array_column($orders, 'quantity'));

echo json_encode([
    'ok' => true,
    'client_name' => $clientName,
    'orders' => $orders,
    'total' => $total,
], JSON_UNESCAPED_UNICODE);