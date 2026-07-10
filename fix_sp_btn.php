<?php
$f = __DIR__ . '/public/summary.php';
$c = file_get_contents($f);
$c = str_replace(
    '<div class="sp-expand-btn-wrapper" style="display:none;">',
    '<div class="sp-expand-btn-wrapper">',
    $c
);
file_put_contents($f, $c);
echo "done\n";