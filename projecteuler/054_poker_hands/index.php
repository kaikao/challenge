<?php
/***
 * Rule of poker hands
 *
 * Must be 5 cards.
 * Ace can be 1 or 14.
 *
 * Order of Comparison: Category -> Point -> Suit
 *
 * Category: Straight flush(同花順) > Four of a kind(鐵支) > Full house(葫蘆) > Flush(同花) > Straight(順子) > Three of a kind(三條) > Two pairs(兩對) > One pair(一對) > High card
 * Point: A(14 or 1) > K(13) > Q(12) > J(11) > 10 > 9 > 8 > 7 > 6 > 5 > 4 > 3 > 2
 * Suit: Spades(黑桃) > Hearts(紅心) > Clubs(梅花) > Diamonds(方塊)
 *
 */

class PokerHand
{
    // hand name
    const HAND_STRAIGHT_FLUSH = 'Straight flush';
    const HAND_FOUR_OF_A_KIND = 'Four of kind';
    const HAND_FULL_HOUSE = 'Full house';
    const HAND_FLUSH = 'Flush';
    const HAND_STRAIGHT = 'Straight';
    const HAND_THREE_OF_A_KIND = 'Three of a kind';
    const HAND_TWO_PAIRS = 'Two pairs';
    const HAND_ONE_PAIR = 'One pair';
    const HAND_HIGH_CARD = 'High card';

    private $hand = array();
    private $serializedHand = array();
    private $handInfo = array(
        'name' => '',
        'values' => 0,
        'high_point' => 0,
        'high_suit' => '',
    );
    private $samePointCards = array();
    public $isCompareSuit = false;

    public static function getInstance($hand, $isCompareSuit=false)
    {
        // explode by space
        if (!is_array($hand)) {
            $hand = explode(' ', $hand);
        }

        if (!is_array($hand)) {
            throw new \Exception('hand is not an array or a valid string.');
        }

        if (count($hand) !== 5) {
            throw new \Exception('Total cards must be 5.');
        }

        return new self($hand, $isCompareSuit);
    }

    private function __construct($hand, $isCompareSuit)
    {
        // set original hand
        $this->hand = $hand;

        // set args
        $this->isCompareSuit = $isCompareSuit;

        // get and set serialized hand
        $this->setSerializedHand();

        // analyse hand and set hand info
        $this->setHandInfo();
    }

    // =============== hand related methods ================

    private function setSerializedHand()
    {
        $serializedHand = array();
        foreach ($this->hand as $card) {
            $serializedHand[] = array(
                'point' => $this->getPointValue(substr($card, 0, 1)),
                'suit' => substr($card, 1, 1),
            );
        }

        $this->serializedHand = $serializedHand;

        // sort from low to high
        $this->sortSerializedHand();
    }

    private function sortSerializedHand()
    {
        // from low to high
        uasort($this->serializedHand, function($a, $b) {
            return $a['point'] > $b['point'] ? 1 : -1;
        });

        $this->serializedHand = array_values($this->serializedHand);
    }

    public function getSerializedHand()
    {
        return $this->serializedHand;
    }

    private function getHandValue($handName) {
        $values = array(
            self::HAND_STRAIGHT_FLUSH => 9,
            self::HAND_FOUR_OF_A_KIND => 8,
            self::HAND_FULL_HOUSE => 7,
            self::HAND_FLUSH => 6,
            self::HAND_STRAIGHT => 5,
            self::HAND_THREE_OF_A_KIND => 4,
            self::HAND_TWO_PAIRS => 3,
            self::HAND_ONE_PAIR => 2,
            self::HAND_HIGH_CARD => 1
        );

        if (!isset($values[$handName])) {
            throw new \Exception($handName . ' is invalid.');
        }

        return $values[$handName];
    }

    private function changeCardAceValueForStraightIfNeeded()
    {
        // if Ace as 14 can be straight, skip
        if (end($this->serializedHand)['point'] - $this->serializedHand[0]['point'] == count($this->serializedHand) - 1) {
            return;
        }

        $this->serializedHand[count($this->serializedHand)-1]['point'] = 1;
        $this->sortSerializedHand();
    }

    private function getHandName()
    {
        // init
        $isStraight = true;
        $isFlush = true;
        $samePointCards = array();

        // check every cards
        for ($i=0; $i<count($this->serializedHand); $i++) {

            $currentCard = $this->serializedHand[$i];

            if (isset($this->serializedHand[$i+1])) {
                $nextCard = $this->serializedHand[$i+1];

                // check is flush
                if ($currentCard['suit'] != $nextCard['suit']) {
                    $isFlush = false;
                }

                // check is straight
                if ($currentCard['point']+1 != $nextCard['point']) {

                    // for normal consective case
                    if ($i < count($this->serializedHand)-2) {
                        $isStraight = false;
                    } else {
                        if ($nextCard['point'] != 14 || $this->serializedHand[0]['point'] != 2) {
                            $isStraight = false;
                        }
                    }
                }

            }

            // check same point cards
            if (!isset($samePointCards[$currentCard['point']])) {
                $samePointCards[$currentCard['point']] = array(
                    'point' => 0,
                    'count' => 0,
                    'suit' => 0,
                );
            }
            $samePointCards[$currentCard['point']]['point'] = $currentCard['point'];
            $samePointCards[$currentCard['point']]['count']++;
            $samePointCards[$currentCard['point']]['suit'] = $this->getSuitValue($currentCard['suit']) > $samePointCards[$currentCard['point']]['suit'] ? $this->getSuitValue($currentCard['suit']) : $samePointCards[$currentCard['point']]['suit'];
        }

        // sort, high to low
        uasort($samePointCards, function($a, $b) {
            if ($a['count'] == $b['count']) {
                return $a['point'] > $b['point'] ? -1 : 1;
            }
            return $a['count'] > $b['count'] ? -1 : 1;
        });

        // set
        $this->samePointCards = array_values($samePointCards);

        // get same point counts
        $samePointCounts = array_column($samePointCards, 'count');

        // Straight flush
        if ($isStraight && $isFlush) {
            $this->changeCardAceValueForStraightIfNeeded();
            return self::HAND_STRAIGHT_FLUSH;
        }

        // Four of a kind
        if (in_array(4, $samePointCounts)) {
            return self::HAND_FOUR_OF_A_KIND;
        }

        // Full house
        if (count($samePointCounts) == 2 && in_array(2, $samePointCounts) && in_array(3, $samePointCounts)) {
            return self::HAND_FULL_HOUSE;
        }

        // Flush
        if ($isFlush) {
            return self::HAND_FLUSH;
        }

        // Straight
        if ($isStraight) {
            $this->changeCardAceValueForStraightIfNeeded();
            return self::HAND_STRAIGHT;
        }

        // Three of a kind & Two pairs
        if (count($samePointCounts) == 3) {
            // Three of a kind
            if (in_array(3, $samePointCounts)) {
                return self::HAND_THREE_OF_A_KIND;
            }

            // Two pairs
            if (count(array_filter($samePointCounts, function($var) {
                return $var == 2;
            })) == 2) {
                return self::HAND_TWO_PAIRS;
            }
        }

        // One pair
        if (count($samePointCounts) == 4) {
            return self::HAND_ONE_PAIR;
        }

        return self::HAND_HIGH_CARD;
    }

    // =============== point related methods ================

    private function getPointValue($point)
    {
        $pointValues = array(
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            'T' => 10,
            'J' => 11,
            'Q' => 12,
            'K' => 13,
            'A' => 14,
        );

        if (!isset($pointValues[$point])) {
            throw new \Exception($point . ' point is invalid.');
        }

        return $pointValues[$point];
    }

    // =============== suit related methods ================

    private function getSuitValue($suit) {
        $suitValues = array(
            'S' => 4,
            'H' => 3,
            'C' => 2,
            'D' => 1,
        );

        if (!isset($suitValues[$suit])) {
            throw new \Exception($suit . ' is invalid, must be S, H, C or D.');
        }

        return $suitValues[$suit];
    }

    // =============== hand info related methods ================

    /**
     * 0 -> hand value
     * 1... -> point values and suit values
     */
    private function getValuesByHandName($handName)
    {
        $values = array($this->getHandValue($handName));
        $pointValues = array();

        switch ($handName) {
            case self::HAND_FOUR_OF_A_KIND:
            case self::HAND_FULL_HOUSE:
            case self::HAND_FLUSH:
            case self::HAND_THREE_OF_A_KIND:
            case self::HAND_TWO_PAIRS:
            case self::HAND_ONE_PAIR:
            case self::HAND_HIGH_CARD:
                $pointValues = array_column($this->samePointCards, 'point');
                break;

            case self::HAND_STRAIGHT_FLUSH:
            case self::HAND_STRAIGHT:
                $pointValues = array($this->samePointCards[0]['point']);
                break;
        }

        // merge
        $values = array_merge($values, $pointValues);

        // add suit values if need
        if ($this->isCompareSuit) {
            $values = array_merge($values, array_column($this->samePointCards, 'suit'));
        }

        return $values;
    }

    private function setHandInfo()
    {
        // name
        $handName = $this->getHandName();

        // values
        $values = $this->getValuesByHandName($handName);

        $this->handInfo = array(
            'name' => $handName,
            'values' => $values,
        );
    }

    public function getHandInfo()
    {
        return $this->handInfo;
    }

    public static function getWinnerHands($hands)
    {
        // check is array and at least more than 2 elements
        if (!is_array($hands) || count($hands) < 2) {
            throw new \Exception('At least 2 hands to be compared.');
        }

        // get the max values count to determine how long dex string is
        $maxValuesCount = max(array_map(function($arr) {
            return count($arr['values']);
        }, $hands));

        // because some values of point or hand are more than 10, so we use hex to compare, combine values to a hex string
        $hexValues = array();
        foreach ($hands as $key => $hand) {
            if (!isset($hexValues[$key])) {
                $hexValues[$key] = '0x';
            }
            for ($i=0; $i<$maxValuesCount; $i++) {
                $hexValues[$key] .= isset($hand['values'][$i]) ? dechex($hand['values'][$i]) : 0;
            }
        }

        // get the max value
        $maxHexValue = max($hexValues);

        // get winner hands
        $winHands = array();
        foreach ($hexValues as $hexValueKey => $hexValue) {
            if ($maxHexValue == $hexValue) {
                $winHands[$hexValueKey] = $hands[$hexValueKey];
            }
        }

        // return false if all tie
        if (count($winHands) == count($hands)) {
            return false;
        }

        return $winHands;
    }
}



////////////////////////// MAIN //////////////////////////////

if (isset($_GET['hands']) && isset($_GET['is_compare_suit'])) {
    // get parameters
    $hands = $_GET['hands'];
    $isCompareSuit = $_GET['is_compare_suit'] == 1 ? true : false;

    // calculate
    $now = microtime(true);
    $handsInfo = array();
    foreach ($hands as $hand) {
        $handsInfo[] = PokerHand::getInstance($hand, $isCompareSuit)->getHandInfo();
    }
    $winners = PokerHand::getWinnerHands($handsInfo);
    $tookTime = microtime(true) - $now;


    // tie
    if (false === $winners) {
        echo json_encode(array(
            'msg' => 'Tie!',
            'took_time' => $tookTime,
        ));
        exit;
    }

    // get win message
    key(end($winners));
    $lastWinnerKey = key($winners);
    $msg = 'Player ';
    foreach ($winners as $winnerKey => $winner) {
        $msg .= ++$winnerKey;
        if (count($winners) >= 2 && $lastWinnerKey+1 != $winnerKey) {
            $msg .= ', ';
        }
    }
    $msg .= ' Win!';
    echo json_encode(array(
        'msg' => $msg,
        'took_time' => $tookTime,
    ));
    exit;


}
