<?php
$ans = 0;
for ($i=100; $i<1000; $i++) {
    for ($j=100; $j<1000; $j++) {
        $p = $i * $j;
        if ($p == strrev($p)) {
            $ans = max($ans, $p);
        }
    }
}

echo "Ans: " . $ans . "\n";
