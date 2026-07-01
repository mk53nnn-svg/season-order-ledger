<?php
declare(strict_types=1);

/**
 * Excel出力（シーズン全体の商品名・受注数のみ）
 *
 * 事前にPhpSpreadsheetをComposerでインストールしてください：
 *   composer require phpoffice/phpspreadsheet
 *
 * このファイルは vendor/autoload.php がある前提で動作します。
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
if ($seasonId <= 0) {
    http_response_code(400);
    echo 'season_id が指定されていません。';
    exit;
}

$pdo = get_pdo();

// シーズン名を取得（ファイル名に使う）
$stmt = $pdo->prepare("SELECT name FROM seasons WHERE id = :id");
$stmt->execute(['id' => $seasonId]);
$season = $stmt->fetch();
$seasonName = $season ? $season['name'] : 'season';

// 商品名・受注数合計のみを取得（ジャンル順・商品表示順固定）
$stmt = $pdo->prepare("
  SELECT
    g.name AS genre_name,
    p.product_name,
    COALESCE(SUM(o.quantity), 0) AS total_qty
  FROM products p
  INNER JOIN genres g ON g.id = p.genre_id
  LEFT JOIN orders o ON o.product_id = p.id AND o.season_id = :season_id
  WHERE p.is_active = 1
  GROUP BY p.id, g.name, p.product_name, g.display_order, p.display_order
  ORDER BY g.display_order ASC, g.id ASC, p.display_order ASC, p.id ASC
");
$stmt->execute(['season_id' => $seasonId]);
$rows = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('受注集計');

// ヘッダー
$sheet->setCellValue('A1', '商品名');
$sheet->setCellValue('B1', '受注数');
$sheet->getStyle('A1:B1')->getFont()->setBold(true);

$rowIndex = 2;
foreach ($rows as $row) {
    $sheet->setCellValue("A{$rowIndex}", $row['product_name']);
    $sheet->setCellValue("B{$rowIndex}", (int)$row['total_qty']);
    $rowIndex++;
}

// 列幅調整
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(12);

$filename = $seasonName . '_受注集計.xlsx';
$encodedFilename = rawurlencode($filename);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"; filename*=UTF-8''{$encodedFilename}");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
