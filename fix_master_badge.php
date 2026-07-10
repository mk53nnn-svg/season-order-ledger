<?php
$f = __DIR__ . '/public/master.php';
$c = file_get_contents($f);
$c = str_replace('<span class="genre-badge">${products.length}商品</span>', '', $c);
file_put_contents($f, $c);
echo "done\n";