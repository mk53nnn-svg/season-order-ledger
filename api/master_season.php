<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

function out2($data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY start_date DESC");
    out2(['ok' => true, 'seasons' => $stmt->fetchAll()]);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'add_season':
            $name = trim((string)($input['name'] ?? ''));
            $start = (string)($input['start_date'] ?? '');
            $end = (string)($input['end_date'] ?? '');
            if ($name === '' || $start === '' || $end === '') {
                out2(['ok' => false, 'error' => '入力内容が正しくありません。']);
            }
            $stmt = $pdo->prepare("INSERT INTO seasons (name, start_date, end_date, is_active) VALUES (:name, :start, :end, 0)");
            $stmt->execute(['name' => $name, 'start' => $start, 'end' => $end]);
            out2(['ok' => true]);
            break;

        case 'activate_season':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) out2(['ok' => false, 'error' => 'IDが不正です。']);
            $pdo->beginTransaction();
            $pdo->exec("UPDATE seasons SET is_active = 0");
            $stmt = $pdo->prepare("UPDATE seasons SET is_active = 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $pdo->commit();
            out2(['ok' => true]);
            break;

        case 'update_season':
            $id = (int)($input['id'] ?? 0);
            $name = trim((string)($input['name'] ?? ''));
            $start = (string)($input['start_date'] ?? '');
            $end = (string)($input['end_date'] ?? '');
            if ($id <= 0 || $name === '' || $start === '' || $end === '') {
                out2(['ok' => false, 'error' => '入力内容が正しくありません。']);
            }
            $stmt = $pdo->prepare("UPDATE seasons SET name = :name, start_date = :start, end_date = :end WHERE id = :id");
            $stmt->execute(['name' => $name, 'start' => $start, 'end' => $end, 'id' => $id]);
            out2(['ok' => true]);
            break;

        case 'delete_season':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) out2(['ok' => false, 'error' => 'IDが不正です。']);
            // 使用中のシーズンは削除不可
            $stmt = $pdo->prepare("SELECT is_active FROM seasons WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $season = $stmt->fetch();
            if (!$season) out2(['ok' => false, 'error' => 'シーズンが見つかりません。']);
            if ($season['is_active'] == 1) out2(['ok' => false, 'error' => '使用中のシーズンは削除できません。']);
            $stmt = $pdo->prepare("DELETE FROM seasons WHERE id = :id");
            $stmt->execute(['id' => $id]);
            out2(['ok' => true]);
            break;

        default:
            out2(['ok' => false, 'error' => '不明なアクションです。']);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    out2(['ok' => false, 'error' => $e->getMessage()]);
}