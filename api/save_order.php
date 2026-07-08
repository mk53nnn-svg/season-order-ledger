<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

function fail(string $message): void
{
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$input) {
    fail('リクエストの形式が正しくありません。');
}

$seasonId = (int)($input['season_id'] ?? 0);
$clientName = trim((string)($input['client_name'] ?? ''));
$orderDate = (string)($input['order_date'] ?? '');
$deliveryType = $input['delivery_type'] ?? null;
$deliveryDate = $input['delivery_date'] ?? null;
$items = $input['items'] ?? [];

if ($seasonId <= 0 || $clientName === '' || $orderDate === '') {
    fail('必須項目が不足しています。');
}
if (!is_array($items) || count($items) === 0) {
    fail('商品が指定されていません。');
}

$pdo = get_pdo();

try {
    $pdo->beginTransaction();

    // 取引先の自動登録はしない（マスタ管理から手動登録する運用）

    $stmtOrder = $pdo->prepare(
        "INSERT INTO orders (season_id, product_id, client_name, order_date, delivery_type, delivery_date, quantity)
         VALUES (:season_id, :product_id, :client_name, :order_date, :delivery_type, :delivery_date, :quantity)"
    );

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        if ($productId <= 0 || $quantity <= 0) continue;
        $stmtOrder->execute([
            'season_id' => $seasonId,
            'product_id' => $productId,
            'client_name' => $clientName,
            'order_date' => $orderDate,
            'delivery_type' => $deliveryType,
            'delivery_date' => ($deliveryType === 'date') ? $deliveryDate : null,
            'quantity' => $quantity,
        ]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    $pdo->rollBack();
    fail('保存中にエラーが発生しました：' . $e->getMessage());
}