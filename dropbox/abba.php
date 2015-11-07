<?php
/**
* If patter is "abba", input is "redbluebluered", your function should return 1, meant matched.
* If patter is "aaaa", input is "redbluebluered", your function should return 0, meant not matched.
* If patter is "aabb", input is "xyzabcxzyabc", your function should return 0.
*
* Try to write a algorithm to solve any pattern with any input.
*/

function isMatchPattern($pattern, $input)
{
    echo "Pattern: " . $pattern . "\n";
    echo "Input: " . $input . "\n";

    // get pattern info
    $patternArr = array();
    foreach (str_split($pattern) as $p) {
        $patternArr[$p] = $p;
    }
    $patternArr = array_flip(array_values($patternArr));
    $patternMapping = array();
    foreach (str_split($pattern) as $p) {
        $patternMapping[] = array(
            'p' => $p,
            'n' => $patternArr[$p]
        );
    }

    return search($pattern, $patternMapping, $input, count($patternArr));
}


function search($pattern, $patternMapping, $str, $split, $start=0, $tmpIndex=0, &$tmp=array())
{
    if ($split == 0) {
        return array($str);
    }

    // get end index
    $end = strlen($str);

    // if this is first loop, set end index - 1
    if ($tmpIndex == 0 && $split > 1) {
        $end -= 1;
    }

    $substr = '';
    for ($i=$start; $i<$end; $i++) {
        // get substr
        $substr .= $str[$i];

        // push substr to tmp
        $tmp[$tmpIndex] = $substr;

        // if this is the last loop, push tmp to rt
        if ($tmpIndex == $split-1) {

            // slip if there are any substr is same as other
            if (count($tmp) != count(array_unique($tmp))) {
                continue;
            }

            // clone input string
            $testString = $str;

            // replace test string's substring to pattern
            foreach ($patternMapping as $patternMap) {
                $testString = preg_replace('/' . $tmp[$patternMap['n']] . '/', $patternMap['p'], $testString, 1);
            }

            // after replacing, if test string == pattern string, return true
            if ($testString == $pattern) {
                echo "----- Matched substr combination -----\n";
                var_dump($tmp);
                echo "----------\n";
                return true;
            }

            continue;
        }

        // recursive
        if (search($pattern, $patternMapping, $str, $split, $i+1, $tmpIndex+1, $tmp)) {
            return true;
        }
    }

    return false;
}


////////////////////////////////// main ////////////////////////////////////////
// 1
$pattern = 'abba';
$input = 'redbluebluered';

// 2
$pattern = 'aaaa';
$input = 'redbluebluered';

// 3
$pattern = 'abcdb';
$input = 'tobeornottobe';

// 4
$pattern = 'ababb';
$input = 'tobeornottobe';

// 5
$pattern = 'aaa';
$input = 'raiseraysraze';

// 6
$pattern = 'abcdeeeee';
$input = 'onetwothreefourcowcowcowcowcow';

// 7
$pattern = 'abcdeeeee';
$input = 'onetwothreefourcowcowcowcow';

// 8
$pattern = 'abcd';
$input = 'thequickbrownfox';

// 9
$pattern = 'abba';
$input = 'redredredred';

// 10
$pattern = 'aab';
$input = '111111';

// 11
$pattern = 'abb';
$input = '111111';

// 12
$pattern = 'abab';
$input = '111111';

echo "\n";
$now = microtime(true);
$isMatch = isMatchPattern($pattern, $input);
$tookTime = microtime(true) - $now;
if ($isMatch) {
    echo "[Yes!!!]\n";
} else {
    echo "[No...]\n";
}
echo 'Took time: ' . $tookTime . " secs. \n\n";
