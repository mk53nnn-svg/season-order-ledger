<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

function fail3(string $message): void
{
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$orderId = (int)($input['id'] ?? 0);
$clientName = trim((string)($input['client_name'] ?? ''));
$orderDate = (string)($input['order_date'] ?? '');
$deliveryType = (string)($input['delivery_type'] ?? '');
$deliveryDate = $input['delivery_date'] ?? null;
$quantity = (int)($input['quantity'] ?? 0);

// 納期タイプは空・date・即納・月+時期の形式を許可
$validDeliveryTypes = null; // バリデーションはPHP側では行わない

if ($orderId <= 0 || $clientName === '' || $orderDate === '' || $quantity <= 0) {
    fail3('入力内容が正しくありません。');
}

$pdo = get_pdo();
$stmt = $pdo->prepare("
  UPDATE orders
  SET client_name = :client_name,
      order_date = :order_date,
      delivery_type = :delivery_type,
      delivery_date = :delivery_date,
      quantity = :quantity
  WHERE id = :id
");
$stmt->execute([
    'client_name' => $clientName,
    'order_date' => $orderDate,
    'delivery_type' => $deliveryType,
    'delivery_date' => $deliveryType === 'date' ? $deliveryDate : null,
    'quantity' => $quantity,
    'id' => $orderId,
]);

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
