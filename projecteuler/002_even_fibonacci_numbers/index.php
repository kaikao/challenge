<?php

$i = 1;
$j = 2;
$sum = $j;
while ($j < 4000000) {
    $j += $i;
    $i = $j-$i;
    if ($j % 2 == 0 ) {
        $sum += $j;
    }
}

echo "Sum: " . $sum . "\n";
