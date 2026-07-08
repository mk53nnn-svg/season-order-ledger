<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

function out($data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'GET') {
    // 一覧取得（ジャンル＋商品）
    $genres = $pdo->query("SELECT * FROM genres ORDER BY display_order ASC, id ASC")->fetchAll();
    $products = $pdo->query("
        SELECT p.*, g.name AS genre_name FROM products p
        INNER JOIN genres g ON g.id = p.genre_id
        ORDER BY g.display_order ASC, p.display_order ASC, p.id ASC
    ")->fetchAll();
    out(['ok' => true, 'genres' => $genres, 'products' => $products]);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'add_genre':
            $name = trim((string)($input['name'] ?? ''));
            if ($name === '') out(['ok' => false, 'error' => 'ジャンル名が空です。']);
            $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(display_order), 0) FROM genres")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO genres (name, display_order) VALUES (:name, :order)");
            $stmt->execute(['name' => $name, 'order' => $maxOrder + 1]);
            out(['ok' => true]);
            break;

        case 'update_genre':
            $id = (int)($input['id'] ?? 0);
            $name = trim((string)($input['name'] ?? ''));
            if ($id <= 0 || $name === '') out(['ok' => false, 'error' => '入力内容が正しくありません。']);
            $stmt = $pdo->prepare("UPDATE genres SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            out(['ok' => true]);
            break;

        case 'delete_genre':
            $id = (int)($input['id'] ?? 0);
            // 論理削除（is_active=0）。紐づく商品があるため物理削除はしない
            $stmt = $pdo->prepare("UPDATE genres SET is_active = 0 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            out(['ok' => true]);
            break;

        case 'reorder_genres':
            // ids: [3,1,2] のように新しい並び順でIDの配列を受け取る
            $ids = $input['ids'] ?? [];
            $stmt = $pdo->prepare("UPDATE genres SET display_order = :order WHERE id = :id");
            foreach ($ids as $i => $id) {
                $stmt->execute(['order' => $i + 1, 'id' => (int)$id]);
            }
            out(['ok' => true]);
            break;

        case 'add_product':
            $genreId = (int)($input['genre_id'] ?? 0);
            $code = trim((string)($input['product_code'] ?? ''));
            $name = trim((string)($input['product_name'] ?? ''));
            if ($genreId <= 0 || $name === '') {
                out(['ok' => false, 'error' => 'ジャンルと商品名は必須です。']);
            }
            $stmt = $pdo->prepare("
                SELECT COALESCE(MAX(display_order), 0) AS m FROM products WHERE genre_id = :gid
            ");
            $stmt->execute(['gid' => $genreId]);
            $maxOrder = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO products (genre_id, product_code, product_name, display_order)
                VALUES (:genre_id, :code, :name, :order)
            ");
            $stmt->execute(['genre_id' => $genreId, 'code' => $code, 'name' => $name, 'order' => $maxOrder + 1]);
            out(['ok' => true]);
            break;

        case 'update_product':
            $id = (int)($input['id'] ?? 0);
            $genreId = (int)($input['genre_id'] ?? 0);
            $code = trim((string)($input['product_code'] ?? ''));
            $name = trim((string)($input['product_name'] ?? ''));
            if ($id <= 0 || $genreId <= 0 || $code === '' || $name === '') {
                out(['ok' => false, 'error' => '入力内容が正しくありません。']);
            }
            $stmt = $pdo->prepare("
                UPDATE products SET genre_id = :genre_id, product_code = :code, product_name = :name
                WHERE id = :id
            ");
            $stmt->execute(['genre_id' => $genreId, 'code' => $code, 'name' => $name, 'id' => $id]);
            out(['ok' => true]);
            break;

        case 'delete_product':
            $id = (int)($input['id'] ?? 0);
            // 論理削除（過去の受注データとの整合性を保つため）
            $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            out(['ok' => true]);
            break;

        case 'reorder_products':
            $ids = $input['ids'] ?? [];
            $stmt = $pdo->prepare("UPDATE products SET display_order = :order WHERE id = :id");
            foreach ($ids as $i => $id) {
                $stmt->execute(['order' => $i + 1, 'id' => (int)$id]);
            }
            out(['ok' => true]);
            break;

        default:
            out(['ok' => false, 'error' => '不明なアクションです。']);
    }
} catch (Throwable $e) {
    out(['ok' => false, 'error' => $e->getMessage()]);
}
