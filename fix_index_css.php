<?php
$f = __DIR__ . '/public/index.php';
$c = file_get_contents($f);
$c = str_replace('<link rel="stylesheet" href="common.css">' . "\n", '', $c);
$c = str_replace('<link rel="stylesheet" href="common.css">', '', $c);
file_put_contents($f, $c);
echo "done\n";