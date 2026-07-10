<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($productId <= 0 || $seasonId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'パラメータが不足しています。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = get_pdo();

// 商品基本情報
$stmt = $pdo->prepare("
  SELECT p.id AS product_id, p.product_code, p.unit_quantity, p.product_name, g.name AS genre_name
  FROM products p INNER JOIN genres g ON g.id = p.genre_id
  WHERE p.id = :product_id
");
$stmt->execute(['product_id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['ok' => false, 'error' => '商品が見つかりません。'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 受注内訳
$stmt = $pdo->prepare("
  SELECT id, client_name, order_date, delivery_type, delivery_date, quantity
  FROM orders
  WHERE product_id = :product_id AND season_id = :season_id
  ORDER BY order_date ASC, id ASC
");
$stmt->execute(['product_id' => $productId, 'season_id' => $seasonId]);
$orders = $stmt->fetchAll();

$orderTotal = 0;
$ordersFormatted = array_map(function ($o) use (&$orderTotal) {
    $orderTotal += (int)$o['quantity'];
    return [
        'id' => (int)$o['id'],
        'client_name' => $o['client_name'],
        'order_date' => $o['order_date'],
        'delivery_type' => $o['delivery_type'],
        'delivery_date' => $o['delivery_date'],
        'delivery_label' => format_delivery($o['delivery_type'], $o['delivery_date']),
        'quantity' => (int)$o['quantity'],
    ];
}, $orders);

// 発注履歴（棚卸を一番上、それ以外は日付順）
$stmt = $pdo->prepare("
  SELECT id, order_date, quantity
  FROM purchase_orders
  WHERE product_id = :product_id AND season_id = :season_id
  ORDER BY CASE WHEN order_date = '棚卸' THEN 0 ELSE 1 END ASC, id ASC
");
$stmt->execute(['product_id' => $productId, 'season_id' => $seasonId]);
$poRows = $stmt->fetchAll();

$poTotal = 0;
$poFormatted = array_map(function ($po) use (&$poTotal) {
    $poTotal += (int)$po['quantity'];
    return [
        'id' => (int)$po['id'],
        'order_date' => $po['order_date'],
        'quantity' => (int)$po['quantity'],
        'is_tanoroshi' => $po['order_date'] === '棚卸',
    ];
}, $poRows);

// 在庫 = 発注合計 - 受注合計（initial_stocksは使わない）
$stock = $poTotal - $orderTotal;

echo json_encode([
    'ok' => true,
    'product' => $product,
    'orders' => $ordersFormatted,
    'order_total' => $orderTotal,
    'purchase_orders' => $poFormatted,
    'po_total' => $poTotal,
    'stock' => $stock,
], JSON_UNESCAPED_UNICODE);