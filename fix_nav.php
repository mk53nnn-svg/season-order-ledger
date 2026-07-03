<?php
$files = [
  __DIR__ . '/public/order_input.php',
  __DIR__ . '/public/summary.php',
  __DIR__ . '/public/product_detail.php',
  __DIR__ . '/public/master.php',
  __DIR__ . '/public/initial_stock_input.php',
];
$nav = '<div style="padding:8px 16px;background:#fff;border-bottom:1px solid #eee;"><a href="index.php" style="font-size:12px;color:#888;text-decoration:none;">&laquo; ホーム</a></div>';

foreach ($files as $f) {
  $c = file_get_contents($f);
  // 重複したホームボタンをすべて削除してから1つだけ追加
  $c = str_replace($nav, '', $c);
  $c = str_replace('<body>', '<body>' . $nav, $c);
  file_put_contents($f, $c);
  echo basename($f) . " fixed\n";
}