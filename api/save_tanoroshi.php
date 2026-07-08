<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);
$seasonId = (int)($input['season_id'] ?? 0);
$stocks = $input['stocks'] ?? [];

if ($seasonId <= 0 || !is_array($stocks)) {
    echo json_encode(['ok' => false, 'error' => 'パラメータが不足しています。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = get_pdo();

try {
    $pdo->beginTransaction();

    foreach ($stocks as $s) {
        $productId = (int)($s['product_id'] ?? 0);
        $quantity = (int)($s['quantity'] ?? 0);
        if ($productId <= 0) continue;

        // 既存の棚卸データを削除してから登録（1商品につき1件のみ）
        $del = $pdo->prepare("DELETE FROM purchase_orders WHERE season_id = :season_id AND product_id = :product_id AND order_date = '棚卸'");
        $del->execute(['season_id' => $seasonId, 'product_id' => $productId]);

        if ($quantity > 0) {
            $ins = $pdo->prepare("INSERT INTO purchase_orders (season_id, product_id, order_date, quantity) VALUES (:season_id, :product_id, '棚卸', :quantity)");
            $ins->execute(['season_id' => $seasonId, 'product_id' => $productId, 'quantity' => $quantity]);
        }
    }

    $pdo->commit();
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}