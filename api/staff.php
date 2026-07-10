<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

function out_staff($data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM purchase_order_staff ORDER BY display_order ASC, id ASC");
    out_staff(['ok' => true, 'staff' => $stmt->fetchAll()]);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'add_staff':
            $name = trim((string)($input['name'] ?? ''));
            if ($name === '') out_staff(['ok' => false, 'error' => '名前が空です。']);
            $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(display_order), 0) FROM purchase_order_staff")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO purchase_order_staff (name, display_order, is_default) VALUES (:name, :order, 0)");
            $stmt->execute(['name' => $name, 'order' => $maxOrder + 1]);
            out_staff(['ok' => true]);
            break;

        case 'update_staff':
            $id = (int)($input['id'] ?? 0);
            $name = trim((string)($input['name'] ?? ''));
            if ($id <= 0 || $name === '') out_staff(['ok' => false, 'error' => '入力内容が正しくありません。']);
            $stmt = $pdo->prepare("UPDATE purchase_order_staff SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            // purchase_ordersの既存データも更新
            $oldName = trim((string)($input['old_name'] ?? ''));
            if ($oldName !== '') {
                $stmt2 = $pdo->prepare("UPDATE purchase_orders SET staff_name = :new WHERE staff_name = :old");
                $stmt2->execute(['new' => $name, 'old' => $oldName]);
            }
            out_staff(['ok' => true]);
            break;

        case 'delete_staff':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) out_staff(['ok' => false, 'error' => 'IDが不正です。']);
            $stmt = $pdo->prepare("DELETE FROM purchase_order_staff WHERE id = :id");
            $stmt->execute(['id' => $id]);
            out_staff(['ok' => true]);
            break;

        case 'set_default':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) out_staff(['ok' => false, 'error' => 'IDが不正です。']);
            $pdo->beginTransaction();
            $pdo->exec("UPDATE purchase_order_staff SET is_default = 0");
            $stmt = $pdo->prepare("UPDATE purchase_order_staff SET is_default = 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $pdo->commit();
            out_staff(['ok' => true]);
            break;

        case 'add_from_input':
            // 自由入力時に新しい発注者を追加（重複は無視）
            $name = trim((string)($input['name'] ?? ''));
            if ($name === '') out_staff(['ok' => true]);
            $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(display_order), 0) FROM purchase_order_staff")->fetchColumn();
            $stmt = $pdo->prepare("INSERT IGNORE INTO purchase_order_staff (name, display_order, is_default) VALUES (:name, :order, 0)");
            $stmt->execute(['name' => $name, 'order' => $maxOrder + 1]);
            out_staff(['ok' => true]);
            break;

        default:
            out_staff(['ok' => false, 'error' => '不明なアクションです。']);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    out_staff(['ok' => false, 'error' => $e->getMessage()]);
}