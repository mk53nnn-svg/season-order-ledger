<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

function fail5(string $message): void
{
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $input['action'] ?? '';
$pdo = get_pdo();

try {
    switch ($action) {
        case 'update_client':
            $id = (int)($input['id'] ?? 0);
            $name = trim((string)($input['name'] ?? ''));
            if ($id <= 0 || $name === '') fail5('入力内容が正しくありません。');
            // clientsテーブルの名前を更新
            $stmt = $pdo->prepare("UPDATE clients SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            // ordersテーブルのclient_nameも合わせて更新
            $oldName = trim((string)($input['old_name'] ?? ''));
            if ($oldName !== '') {
                $stmt2 = $pdo->prepare("UPDATE orders SET client_name = :new WHERE client_name = :old");
                $stmt2->execute(['new' => $name, 'old' => $oldName]);
            }
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
            break;

        case 'delete_client':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) fail5('IDが不正です。');
            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
            break;

        default:
            fail5('不明なアクションです。');
    }
} catch (Throwable $e) {
    fail5('エラーが発生しました：' . $e->getMessage());
}