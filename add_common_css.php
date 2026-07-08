<?php
$files = [
  __DIR__ . '/public/order_input.php',
  __DIR__ . '/public/summary.php',
  __DIR__ . '/public/product_detail.php',
  __DIR__ . '/public/master.php',
  __DIR__ . '/public/initial_stock_input.php',
  __DIR__ . '/public/index.php',
  __DIR__ . '/public/bulk_order.php',
];

foreach ($files as $f) {
  $c = file_get_contents($f);
  if (strpos($c, 'common.css') !== false) {
    echo basename($f) . " already has common.css, skipping\n";
    continue;
  }
  $c = str_replace(
    '<meta charset="UTF-8">',
    '<meta charset="UTF-8">' . "\n" . '<link rel="stylesheet" href="common.css">',
    $c
  );
  file_put_contents($f, $c);
  echo basename($f) . " done\n";
}