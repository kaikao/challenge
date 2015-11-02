<?php
// in some case, try to increase nesting time to solve sudoku, 95% of sudoku problems won't reach this leavel
ini_set('xdebug.max_nesting_level', 10000);

class Sudoku
{
    private $sudokuStr;
    private $sudokuDigits = array();
    private $unresolvedKeys = array();
    private $columns = array();
    private $rows = array();
    private $squares = array();
    private $crsKeys = array();
    private $possibleDigits = array();
    private $relatedCRSKeys = array();
    private $excludeDigits = array();
    private $triedKeys = array();

    public function __construct($sudokuStr)
    {
        if (!preg_match('/^[0-9.]{81}$/', $sudokuStr)) {
            throw new \Exception('Invalid sudoku string.');
        }
        $sudokuStr = str_replace('.', '0', $sudokuStr);

        $this->sudokuStr = $sudokuStr;

        $this->initSudoku();
    }

    private function initSudoku()
    {
        // prepare for get keys' related keys
        $crsKeysMapping = array(
            'column' => array(),
            'row' => array(),
            'square' => array()
        );

        foreach (str_split($this->sudokuStr) as $key => $digit) {
            // sudoku digits
            $this->sudokuDigits[] = $digit;

            // unresolved keys and exclude digits
            if ($digit < 1) {
                $this->unresolvedKeys[] = $key;
                $this->excludeDigits[$key] = array();
            }

            // columns
            $columnIndex = $key % 9;
            if (!isset($this->columns[$columnIndex])) {
                $this->columns[$columnIndex] = array();
            }
            $this->columns[$columnIndex][] = $digit;

            // rows
            $rowIndex = intval($key / 9);
            if (!isset($this->rows[$rowIndex])) {
                $this->rows[$rowIndex] = array();
            }
            $this->rows[$rowIndex][] = $digit;

            // squares
            $squareIndex = intval($columnIndex / 3) + intval($key / 27) * 3;
            if (!isset($this->squares[$squareIndex])) {
                $this->squares[$squareIndex] = array();
            }
            $this->squares[$squareIndex][] = $digit;

            // collect indices for all columns, rows and grids, so we can check later
            // 0 is column, row or square, 1 is digit position in C, R or S.
            $this->crsKeys[] = array(
                'column' => array($columnIndex, count($this->columns[$columnIndex]) - 1),
                'row' => array($rowIndex, count($this->rows[$rowIndex]) - 1),
                'square' => array($squareIndex, count($this->squares[$squareIndex]) - 1),
            );

            // prepare for keys' related keys
            $crsKeysMapping['column'][$columnIndex][] = $key;
            $crsKeysMapping['row'][$rowIndex][] = $key;
            $crsKeysMapping['square'][$squareIndex][] = $key;
        }

        // keys' realted keys
        foreach ($this->sudokuDigits as $i => $sudokuDigit) {
            foreach ($crsKeysMapping as $crsKeyMapping) {
                // column, rows, squares
                foreach ($crsKeyMapping as $crsRelatedKeys) {
                    if (!in_array($i, $crsRelatedKeys)) {
                        continue;
                    }
                    $this->relatedCRSKeys[$i][] = array_diff($crsRelatedKeys, array($i));
                }
            }
        }

    }

    private function isPossibleDigit($key, $digit)
    {
        return !(in_array($digit, $this->columns[$this->crsKeys[$key]['column'][0]])
            || in_array($digit, $this->rows[$this->crsKeys[$key]['row'][0]])
            || in_array($digit, $this->squares[$this->crsKeys[$key]['square'][0]]));
    }

    public function addDigit($key, $digit)
    {
        $this->sudokuDigits[$key] = $digit;
        $this->columns[$this->crsKeys[$key]['column'][0]][$this->crsKeys[$key]['column'][1]] = $digit;
        $this->rows[$this->crsKeys[$key]['row'][0]][$this->crsKeys[$key]['row'][1]] = $digit;
        $this->squares[$this->crsKeys[$key]['square'][0]][$this->crsKeys[$key]['square'][1]] = $digit;
    }

    public function removeDigit($key)
    {
        $this->sudokuDigits[$key] = 0;
        $this->columns[$this->crsKeys[$key]['column'][0]][$this->crsKeys[$key]['column'][1]] = 0;
        $this->rows[$this->crsKeys[$key]['row'][0]][$this->crsKeys[$key]['row'][1]] = 0;
        $this->squares[$this->crsKeys[$key]['square'][0]][$this->crsKeys[$key]['square'][1]] = 0;
    }

    public function getDigit($key)
    {
        return $this->sudokuDigits[$key];
    }

    private function resetUnresolvedKeyConditions($startUnresolvedKey)
    {
        for ($i=$startUnresolvedKey+1; $i<count($this->unresolvedKeys); $i++) {
            $this->removeDigit($this->unresolvedKeys[$i]);
            $this->excludeDigits[$this->unresolvedKeys[$i]] = array();
        }
    }

    private function loopSearchDigits($startKey=0)
    {
        $this->resetUnresolvedKeyConditions($startKey);

        // try each possible digits, if failed, jump back to pervios one and try another.
        for ($i=$startKey; $i<count($this->unresolvedKeys); $i++) {
            // get unresolved key
            $unresolvedKey = $this->unresolvedKeys[$i];

            // get this key's possible digits
            $possibleDigits = $this->possibleDigits[$unresolvedKey];

            // try every digits
            $isSuccessful = false;
            foreach ($possibleDigits as $possibleDigit) {
                // skip if impossible or we've tried and been failed before
                if (!$this->isPossibleDigit($unresolvedKey, $possibleDigit) || in_array($possibleDigit, $this->excludeDigits[$unresolvedKey])) {
                    continue;
                }

                // add
                $this->addDigit($unresolvedKey, $possibleDigit);

                // tracking tried keys
                $this->triedKeys[] = $i;

                $isSuccessful = true;
                break;
            }

            // failed, jump back to pervios key
            if (!$isSuccessful) {
                // get pervious key
                $prevKey = array_pop($this->triedKeys);

                // get previos unresolved key
                $prevUnresolvedKey = $this->unresolvedKeys[$prevKey];

                // add a exclude digit for pervios key, avoid try this digit again
                $this->excludeDigits[$prevUnresolvedKey][] = $this->getDigit($prevUnresolvedKey);

                // remove pervios digit
                $this->removeDigit($prevUnresolvedKey);

                // get start key
                $startKey = max($prevKey, 0);

                return $this->loopSearchDigits($startKey);
            }
        }

        return;
    }

    private function initPossibleDigits()
    {
        $this->possibleDigits = array();

        foreach ($this->unresolvedKeys as $key => $unresolvedKey) {
            // init possible digit array
            $this->possibleDigits[$unresolvedKey] = array();

            // try possible numbers from 1 to 9
            $isSuccessful = false;
            foreach (range(1, 9) as $number) {
                // skip if this number is already exist in relared column, row or square.
                if (!$this->isPossibleDigit($unresolvedKey, $number)) {
                    continue;
                }

                $this->possibleDigits[$unresolvedKey][] = $number;
                $isSuccessful = true;
            }

            if (!$isSuccessful) {
                return false;
            }
        }

        return true;
    }

    public function tryPutPossibleDigits()
    {
        // init possible digits
        $this->possibleDigits = array();

        // process
        foreach ($this->unresolvedKeys as $key => $unresolvedKey) {
            // init possible digit array
            $this->possibleDigits[$unresolvedKey] = array();

            // try possible numbers from 1 to 9
            foreach (range(1, 9) as $number) {
                // skip if this number is already exist in relared column, row or square.
                if (!$this->isPossibleDigit($unresolvedKey, $number)) {
                    continue;
                }

                $this->possibleDigits[$unresolvedKey][] = $number;
            }

            if (count($this->possibleDigits[$unresolvedKey]) > 1) {
                continue;
            }

            // catch error when we are trying put any digit.
            if (!isset($this->possibleDigits[$unresolvedKey][0])) {
                return false;
            }

            // add digit if this is the only possible digit
            $this->addDigit($unresolvedKey, $this->possibleDigits[$unresolvedKey][0]);

            // solved, so remove unresolved key and possible digits
            unset($this->unresolvedKeys[$key]);
            unset($this->possibleDigits[$unresolvedKey]);

            return $this->tryPutPossibleDigits();
        }

        // make indices of unresolved keys be straight, 0~n
        $this->unresolvedKeys = array_values($this->unresolvedKeys);

        // if a key's realted column, row or square has no possible digits is same as this key's possible digit, put in.
        $this->putPossibleDigitIfNotExistInOtherRelatedCRS();

        return false;
    }

    private function putPossibleDigitIfNotExistInOtherRelatedCRS()
    {
        $this->initPossibleDigits();

        foreach ($this->unresolvedKeys as $key => $unresolvedKey) {
            // get possible digits
            $possibleDigits = $this->possibleDigits[$unresolvedKey];

            // get related units
            $relatedUnits = $this->relatedCRSKeys[$unresolvedKey];

            // check is any possible digit is NOT exist in any one of related units(column, row or square)
            foreach ($possibleDigits as $possibleDigit) {

                foreach ($relatedUnits as $relatedUnit) {

                    // set a flag
                    $flag = false;
                    foreach ($relatedUnit as $relatedKey) {
                        // get related key's possible digits
                        if (!isset($this->possibleDigits[$relatedKey])) {
                            continue;
                        }
                        $relatedKeyPossibleDigits = $this->possibleDigits[$relatedKey];

                        if (in_array($possibleDigit, $relatedKeyPossibleDigits)) {
                            $flag = true;
                        }
                    }

                    // put
                    if (!$flag) {
                        $this->addDigit($unresolvedKey, $possibleDigit);
                        unset($this->unresolvedKeys[$key]);
                        unset($this->possibleDigits[$unresolvedKey]);

                        // try put if key's possible digits count = 1
                        return $this->tryPutPossibleDigits();
                    }

                }
            }
        }
    }

    public function solveByTryingEachPossibleDigit()
    {
        // sort by possible digits count, low to high
        uasort($this->possibleDigits, function($a, $b) {
            return count($a) > count($b) ? 1 : -1;
        });

        // try to get correct key and digit to solve this sudoku
        $correctKey = 0;
        $correctDigit = 0;

        // TODO: need to refactoring some methods to avoid using too many memory and clone method.
        // clone and try
        foreach ($this->possibleDigits as $key => $possibleDigits) {
            $isSolved = false;
            foreach ($possibleDigits as $possibleDigit) {
                // clone self to avoid affect data structure
                $tmpSudoku = clone $this;
                $tmpSudoku->addDigit($key, $possibleDigit);
                $tmpSudoku->tryPutPossibleDigits();
                $isSolved = $tmpSudoku->isSolved();

                if ($isSolved) {
                    $correctKey = $key;
                    $correctDigit = $possibleDigit;
                    unset($tmpSudoku);
                    break;
                }
            }
            if ($isSolved) {
                break;
            }
        }

        // if get correct key and digit, return
        if ($correctKey && $correctDigit) {
            return array(
                'key' => $correctKey,
                'digit' => $correctDigit,
            );
        }

        return false;
    }

    // @deprecated
    // TODO: check if execuive this one can increase solving speed.
    private function sortByPossibleDigitsCount()
    {
        $tmp = $this->possibleDigits;
        uasort($tmp, function($a, $b) {
            return count($a) < count($b) ? -1 : 1;
        });

        $this->unresolvedKeys = array_keys($tmp);
    }

    public function isSolved()
    {
        if (!in_array(0, $this->sudokuDigits)) {
            return true;
        }
        return false;
    }

    public function solve()
    {
        // fill in digit if there is only one digit can be put in this position
        $this->tryPutPossibleDigits();
        if ($this->isSolved()) {
            return $this->getAnswerStr();
        }

        // clone this object and do some trys
        $result = $this->solveByTryingEachPossibleDigit();
        if ($result) {
            $this->addDigit($result['key'], $result['digit']);
            $this->tryPutPossibleDigits();
            return $this->getAnswerStr();
        }

        // @deprecated
        // sort by possible digits count, low to high
        // $this->sortByPossibleDigitsCount();

        // still no answer? loop searching
        $this->loopSearchDigits();

        return $this->getAnswerStr();
    }

    public function draw()
    {
        foreach ($this->sudokuDigits as $key => $n) {
            echo $n;
            if ($key%3 == 2 && ($key+1)%9 != 0) {
                echo " | ";
            }
            if ($key%27 == 26 && $key <= 60) {
                echo "<br/>-----+-----+-----";
            }
            if ($key%9 == 8) {
                echo "<br/>";
            }
        }
        echo '<br/><br/>';
    }

    public function getAnswerStr()
    {
        $str = '';
        foreach ($this->sudokuDigits as $digit) {
            $str .= $digit;
        }
        return $str;
    }

}

// ====================== main ===========================

$filename = dirname(__FILE__) . '/sudoku.txt';
$file = fopen($filename, 'r');
if (!$file) {
    throw new \Exception('Could not open file ' . $filename);
}

$sudokuStrs = array();
$i = -1;
while (($line = fgets($file)) !== false) {
    if (false !== strpos($line, 'Grid')) {
        $i++;
        continue;
    }
    $line = str_replace("\n", "", $line);
    if (!isset($sudokuStrs[$i])) {
        $sudokuStrs[$i] = '';
    }
    $sudokuStrs[$i] .= $line;
}
fclose($file);

$now = microtime(true);
$sum = 0;
foreach ($sudokuStrs as $sudokuStr) {
    $sudoku = new Sudoku($sudokuStr);
    // var_dump($sudokuStr);
    $answer = $sudoku->solve();
    // echo $solvedSudokuStr . '<br/>';
    $sum += intval(substr($answer, 0, 3));
    unset($sudoku);
}
$tookTime = microtime(true) - $now;


echo "\n\nSum: " . $sum . "\n";
echo "took time: " . $tookTime . " secs.\n";
