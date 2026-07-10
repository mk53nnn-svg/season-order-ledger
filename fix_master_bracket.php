<?php
$f = __DIR__ . '/public/master.php';
$c = file_get_contents($f);
$c = str_replace(
    "  if (bottomBtns) bottomBtns.style.display = 'flex';\n}\n}",
    "  if (bottomBtns) bottomBtns.style.display = 'flex';\n}",
    $c
);
file_put_contents($f, $c);
echo "done\n";