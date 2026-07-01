<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

function fail4(string $message): void
{
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$seasonId = (int)($input['season_id'] ?? 0);
$productId = (int)($input['product_id'] ?? 0);
$orderDate = (string)($input['order_date'] ?? '');
$quantity = (int)($input['quantity'] ?? 0);

if ($seasonId <= 0 || $productId <= 0 || $orderDate === '' || $quantity <= 0) {
    fail4('入力内容が正しくありません。');
}

$pdo = get_pdo();
$stmt = $pdo->prepare("
  INSERT INTO purchase_orders (season_id, product_id, order_date, quantity)
  VALUES (:season_id, :product_id, :order_date, :quantity)
");
$stmt->execute([
    'season_id' => $seasonId,
    'product_id' => $productId,
    'order_date' => $orderDate,
    'quantity' => $quantity,
]);

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
