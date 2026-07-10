<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

$seasonId = (int)($input['season_id'] ?? 0);
$items = $input['items'] ?? [];
$staffName = trim((string)($input['staff_name'] ?? ''));

if ($seasonId <= 0 || !is_array($items) || count($items) === 0) {
    echo json_encode(['ok' => false, 'error' => 'パラメータが不足しています。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = get_pdo();

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("
        INSERT INTO purchase_orders (season_id, product_id, order_date, quantity, staff_name)
        VALUES (:season_id, :product_id, :order_date, :quantity, :staff_name)
    ");
    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        $orderDate = (string)($item['order_date'] ?? '');
        if ($productId <= 0 || $quantity <= 0 || $orderDate === '') continue;
        $stmt->execute([
            'season_id' => $seasonId,
            'product_id' => $productId,
            'order_date' => $orderDate,
            'quantity' => $quantity,
            'staff_name' => $staffName,
        ]);
    }
    $pdo->commit();
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}