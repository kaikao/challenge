<?php
/***
 * Rule of brackets required
 *
 * '+' or '-' between numbers
 *      prefix is -, *, /
 *      suffix is *, /
 * '*' or '/' between numbers
 *      prefix is -
 */

// =================== functions =======================

function serializeEquationToArray($equation) {
    // rm unnecessary spaces and brackets
    $equation = str_replace(' ', '', $equation);
    $equation = str_replace('()', '', $equation);

    // remove brackets if there is only one operator between brackets
    $equation = preg_replace('/(\()([\+\-\*\/]+)(\))/', '$2', $equation);

    // add '*' if no opeator between number and bracket
    $equation = preg_replace('/(\w|\))(\()/', '$1*(', $equation);
    $equation = preg_replace('/(\))(\w)/', ')*$2', $equation);

    // check total open and close bracket count, alert if not equal
    $totalOpenBracketCount = preg_match_all('/\(/', $equation, $a);
    $totalCloseBracketCount = preg_match_all('/\)/', $equation);
    if ($totalOpenBracketCount != $totalCloseBracketCount) {
        echo '<script>alert("invalid equation");</script>';
        exit;
    }

    // init parameters
    $rt = array();
    $openBracketCount = 0;
    $closeBracketCount = 0;
    $openBracketPos = 0;
    $closeBracketPos = 0;

    // split $equation to characters
    $characters = str_split($equation);

    // process
    foreach ($characters as $key => $c) {
        // record the open bracket position
        if ($c == '(') {
            $openBracketCount++;

            // only record the first open bracket position
            if ($openBracketCount == 1) {
                $openBracketPos = $key;
            }

            continue;
        }

        // if no bracket has been recorded, push the character into array
        if ($openBracketCount == 0) {
            $rt[] = $c;
            continue;
        }

        // record the close bracket position
        if ($c == ')') {
            $closeBracketCount++;
            $closeBracketPos = $key;

            // if count of open and close bracket are equal, try to get sub equation
            if ($openBracketCount == $closeBracketCount) {

                // get sub equation
                $startPos = $openBracketPos + 1;
                $endPos = $closeBracketPos - 1;
                $subEquation = '';
                for ($i=$startPos; $i<=$endPos; $i++) {
                    $subEquation .= $characters[$i];
                }

                // serilize sub equation to array
                $subRt = array('(');
                $subRt = array_merge($subRt, serializeEquationToArray($subEquation));
                $subRt[] = ')';
                $rt[] = $subRt;

                // reset open and close bracket count
                $openBracketCount = 0;
                $closeBracketCount = 0;
            }
        }
    }

    return $rt;
}


function deserializeArrayToEquation($serializedEquation) {
    // init
    $equation = '';

    // process
    foreach ($serializedEquation as $c) {
        // recursive to get characters if $c is array
        if (is_array($c)) {
            $equation .= deserializeArrayToEquation($c);
            continue;
        }

        // get character
        $equation .= $c;
    }

    return $equation;
}


function filterBracketBySerializedEquation($serializedEquation, $prevOperator=null, $nextOperator=null) {
    if (!is_array($serializedEquation)) {
        return $serializedEquation;
    }

    foreach ($serializedEquation as $key => &$subEquation) {
        // skip if not array
        if (!is_array($subEquation)) {
            continue;
        }

        // get previous and next operator
        $prevOperator = isset($serializedEquation[$key-1])? $serializedEquation[$key-1] : $prevOperator;
        $nextOperator = isset($serializedEquation[$key+1])? $serializedEquation[$key+1] : $nextOperator;

        // set is remove bracket flag
        $isRemoveBracket = true;
        foreach ($subEquation as $subKey => $c) {
            // if not operator, skip
            if (!in_array($c, array('+', '-', '*', '/'))) {
                continue;
            }

            // case by case, for +, -, *, /
            switch ($c) {
                case '+':
                case '-':
                    if (in_array($prevOperator, array('-', '*', '/')) || in_array($nextOperator, array('*', '/'))) {
                        $isRemoveBracket = false;
                    }
                    break;

                case '*':
                case '/':
                    if ($prevOperator == '/') {
                        $isRemoveBracket = false;
                    }
                    break;

                default:
                    break;
            }
        }

        // remove unnecessary brackets
        if ($isRemoveBracket) {
            array_shift($subEquation);
            array_pop($subEquation);
        }

        // recursive filter unnecessary brackets
        $subEquation = filterBracketBySerializedEquation($subEquation, $prevOperator, $nextOperator);
    }
    unset($subEquation);

    return $serializedEquation;
}


// =================== main =======================

// examples
// $q = '1+(2/3)+2';
// $q = '((2*3))';
// $q = '(9+(8*7))';
// $q = '1+(2/3)+2*(1-2)';
// $q = '(9+(8*7))+(6+5)*(-(4-1*2+3))';
// $q = '-((1+2))';
// $q = '-11(1+2)';
// $q = '-((1+2))';
// $q = '100((2-3))(4)(2-2)555';
// $q = '((9+((8*7)))+((6(+)(5)))(*)((-)(4-(1*2)+(3))))';
// $q = '((9+((8*7)))+((6+(5)))*(-(4-(1*2)+(3))))(/)(10)';
// $q = '((-( ( (9 /( (8* 7)))-( (6 +5) )*(- ((4 -( (1 *2 )+ 3))    ))))))        ((-( ( (9 /( (8* 7)))-( (6 +5) )*(- ((4 -( (1 *2 )+ 3))    ))))))';

$q = 'x+(y+z)+(t+(v+w))';
if (isset($argv[1])) {
    $q = $argv[1];
}


$now = microtime(true);
$serializedEquation = serializeEquationToArray($q);
$equation = deserializeArrayToEquation($serializedEquation);
$filteredSerializedEquation = filterBracketBySerializedEquation($serializedEquation);
$filteredEquation = deserializeArrayToEquation($filteredSerializedEquation);
$takeTime = microtime(true) - $now;

echo "Q: " . $q . "\n";
echo "Answer: $filteredEquation\n";
echo "time: " . $takeTime . " secs.\n";

// echo '========= serialized equation =========';
// var_dump($serializedEquation);
// echo '========= filtered serialized equation =========';
// var_dump($filteredSerializedEquation);
