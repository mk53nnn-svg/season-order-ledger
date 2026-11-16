<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

function out_bo($data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$seasonId = (int)($input['season_id'] ?? 0);
$productId = (int)($input['product_id'] ?? 0);

if ($seasonId <= 0 || $productId <= 0) {
    out_bo(['ok' => false, 'error' => 'パラメータが不足しています。']);
}

$pdo = get_pdo();

try {
    if ($action === 'set') {
        $date = (string)($input['expected_date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            out_bo(['ok' => false, 'error' => '出荷予定日が正しくありません。']);
        }
        $stmt = $pdo->prepare("
            INSERT INTO product_backorders (season_id, product_id, expected_date)
            VALUES (:season_id, :product_id, :d1)
            ON DUPLICATE KEY UPDATE expected_date = :d2
        ");
        $stmt->execute(['season_id' => $seasonId, 'product_id' => $productId, 'd1' => $date, 'd2' => $date]);
        out_bo(['ok' => true]);
    } elseif ($action === 'clear') {
        $stmt = $pdo->prepare("DELETE FROM product_backorders WHERE season_id = :season_id AND product_id = :product_id");
        $stmt->execute(['season_id' => $seasonId, 'product_id' => $productId]);
        out_bo(['ok' => true]);
    } else {
        out_bo(['ok' => false, 'error' => '不明なアクションです。']);
    }
} catch (Throwable $e) {
    out_bo(['ok' => false, 'error' => $e->getMessage()]);
}