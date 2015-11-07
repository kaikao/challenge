<?php
/**
* If patter is "abba", input is "redbluebluered", your function should return 1, meant matched.
* If patter is "aaaa", input is "redbluebluered", your function should return 0, meant not matched.
* If patter is "aabb", input is "xyzabcxzyabc", your function should return 0.
*
* Try to write a algorithm to solve any pattern with any input.
*/

function getSubstrCombinations($str, $split, $start=0, $tmpIndex=0, &$tmp=array(), &$rt=array())
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
            $rt[] = $tmp;
            continue;
        }

        // recursive
        getSubstrCombinations($str, $split, $i+1, $tmpIndex+1, $tmp, $rt);
    }

    return $rt;
}

function isMatchPattern($pattern, $input)
{
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

    // get input string combinations
    $inputCombinations = getSubstrCombinations($input, count($patternArr));

    // filter if there are any combination is same as other
    $inputCombinations = array_filter($inputCombinations, function($var) {
        if (count($var) != count(array_unique($var))) {
            return false;
        }
        return true;
    });

    // try to replace string of input and see if it's matched
    foreach ($inputCombinations as $inputCombination) {
        // clone input string
        $testString = $input;

        // replace test string's substring to pattern
        foreach ($patternMapping as $patternMap) {
            $testString = preg_replace('/' . $inputCombination[$patternMap['n']] . '/', $patternMap['p'], $testString, 1);
        }

        // after replacing, if test string == pattern string, return true
        if ($testString == $pattern) {
            var_dump($inputCombination);
            return 1;
        }
    }

    return 0;
}


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



// try this
$pattern = 'abcdeeeee';
$input = 'onetwothreefourcowcowcowcowcow';


set_time_limit(3600);
ini_set('memory_limit', '512M');

$now = microtime(true);
$isMatch = isMatchPattern($pattern, $input);
$tookTime = microtime(true) - $now;

var_dump($isMatch);
var_dump($tookTime);
