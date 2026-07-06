<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

function fail6(string $message): void
{
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $input['action'] ?? '';
$pdo = get_pdo();

try {
    switch ($action) {
        case 'update_purchase_order':
            $id = (int)($input['id'] ?? 0);
            $orderDate = (string)($input['order_date'] ?? '');
            $quantity = (int)($input['quantity'] ?? 0);
            if ($id <= 0 || $orderDate === '' || $quantity <= 0) {
                fail6('入力内容が正しくありません。');
            }
            $stmt = $pdo->prepare("UPDATE purchase_orders SET order_date = :order_date, quantity = :quantity WHERE id = :id");
            $stmt->execute(['order_date' => $orderDate, 'quantity' => $quantity, 'id' => $id]);
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
            break;

        case 'delete_purchase_order':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) fail6('IDが不正です。');
            $stmt = $pdo->prepare("DELETE FROM purchase_orders WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
            break;

        default:
            fail6('不明なアクションです。');
    }
} catch (Throwable $e) {
    fail6('エラーが発生しました：' . $e->getMessage());
}