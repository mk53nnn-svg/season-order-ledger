<?php
require_once __DIR__ . '/config/db.php';
$pdo = get_pdo();
$pdo->exec("DELETE FROM products");
$pdo->exec("DELETE FROM genres");
$pdo->exec("DELETE FROM seasons");
$pdo->exec("INSERT INTO seasons (name, start_date, end_date, is_active) VALUES ('2025-2026シーズン', '2025-11-01', '2026-03-31', 1)");
$pdo->exec("INSERT INTO genres (name, display_order) VALUES ('おたより', 1), ('連絡帳・出席簿', 2), ('画材', 3)");
$pdo->exec("INSERT INTO products (genre_id, product_code, product_name, display_order) SELECT id, '3071061', 'おたより用紙A', 1 FROM genres WHERE display_order=1");
$pdo->exec("INSERT INTO products (genre_id, product_code, product_name, display_order) SELECT id, '3071062', 'おたより用紙B', 2 FROM genres WHERE display_order=1");
$pdo->exec("INSERT INTO products (genre_id, product_code, product_name, display_order) SELECT id, '3081001', '連絡帳', 1 FROM genres WHERE display_order=2");
$pdo->exec("INSERT INTO products (genre_id, product_code, product_name, display_order) SELECT id, '3081002', '出席簿', 2 FROM genres WHERE display_order=2");
$pdo->exec("INSERT INTO products (genre_id, product_code, product_name, display_order) SELECT id, '4011001', 'クレヨン12色', 1 FROM genres WHERE display_order=3");
echo "完了しました\n";