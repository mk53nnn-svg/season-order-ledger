<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/backorder.php';

$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
if ($seasonId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'season_id が指定されていません。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = get_pdo();

$sql = "
  SELECT
    p.id            AS product_id,
    p.product_code,
    p.unit_quantity,
    p.product_name,
    p.display_order AS product_order,
    g.id            AS genre_id,
    g.name          AS genre_name,
    g.display_order AS genre_order,
    COALESCE(o.order_count, 0)    AS order_count,
    COALESCE(o.order_qty_sum, 0)  AS order_qty_sum,
    COALESCE(po.po_qty_sum, 0)    AS po_qty_sum,
    COALESCE(po.po_count, 0)      AS po_count,
    b.expected_date               AS expected_date
  FROM products p
  INNER JOIN genres g ON g.id = p.genre_id
  LEFT JOIN (
    SELECT product_id, COUNT(*) AS order_count, SUM(quantity) AS order_qty_sum
    FROM orders
    WHERE season_id = :season_id1
    GROUP BY product_id
  ) o ON o.product_id = p.id
  LEFT JOIN (
    SELECT product_id, COUNT(*) AS po_count, SUM(quantity) AS po_qty_sum
    FROM purchase_orders
    WHERE season_id = :season_id2
    GROUP BY product_id
  ) po ON po.product_id = p.id
  LEFT JOIN product_backorders b
    ON b.product_id = p.id AND b.season_id = :season_id3
  WHERE p.is_active = 1
  ORDER BY g.display_order ASC, g.id ASC, p.display_order ASC, p.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['season_id1' => $seasonId, 'season_id2' => $seasonId, 'season_id3' => $seasonId]);
$rows = $stmt->fetchAll();

$result = array_map(function ($row) {
    $orderQty = (int)$row['order_qty_sum'];
    $poQty = (int)$row['po_qty_sum'];
    $stock = $poQty - $orderQty;

    // 状態の判定：在庫マイナス → 未発注 / 品切れ中の期間内 → 品切れ中 / それ以外 → 発注済
    $expected = $row['expected_date'];
    $backorderActive = is_backorder_active($expected);
    if ($stock < 0) {
        $status = 'needed';
    } elseif ($backorderActive) {
        $status = 'backorder';
    } else {
        $status = 'ordered';
    }

    return [
        'product_id' => (int)$row['product_id'],
        'product_code' => $row['product_code'],
        'unit_quantity' => $row['unit_quantity'],
        'product_name' => $row['product_name'],
        'genre_name' => $row['genre_name'],
        'order_count' => (int)$row['order_count'],
        'order_qty_sum' => $orderQty,
        'po_count' => (int)$row['po_count'],
        'po_qty_sum' => $poQty,
        'stock' => $stock,
        'status' => $status,
        'expected_date' => $backorderActive ? $expected : null,
    ];
}, $rows);

echo json_encode(['ok' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);