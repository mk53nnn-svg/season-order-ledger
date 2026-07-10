<?php
$files = [
  __DIR__ . '/public/order_input.php',
  __DIR__ . '/public/summary.php',
  __DIR__ . '/public/product_detail.php',
  __DIR__ . '/public/master.php',
  __DIR__ . '/public/initial_stock_input.php',
  __DIR__ . '/public/bulk_order.php',
];

foreach ($files as $f) {
  $c = file_get_contents($f);
  // BOMを除去
  if (substr($c, 0, 3) === "\xef\xbb\xbf") {
    $c = substr($c, 3);
    file_put_contents($f, $c);
    echo basename($f) . " BOM removed\n";
  } else {
    echo basename($f) . " no BOM\n";
  }
}