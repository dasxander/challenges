<?
// https://projecteuler.net/problem=54
// 1:05h (main logic in) + 0:20 (refactoring) + 0:45 (solved one case) + 0:15 (solved all 5 example cases)
// Total: 2:25

/*
    1 High Card: Highest value card.
    2 One Pair: Two cards of the same value.
    3 Two Pairs: Two different pairs. first pair at 10x
    4 Three of a Kind: Three cards of the same value.
    5 Straight: All cards are consecutive values. (start with highest value)
    6 Flush: All cards of the same suit.
    7 Full House: Three of a kind and a pair. (add values for the kind at 10x and pair at 1x)
    8 Four of a Kind: Four cards of the same value.
    9 Straight Flush: All cards are consecutive values of same suit. (start with highest value)
    10 Royal Flush: Ten, Jack, Queen, King, Ace, in same suit.
    
    Spades, Diamonds (red), Hearts (red) and Clubs (from clovers?)
         
    Ten 10, Jack 11, Queen 12,  King 13, Ace 14        
*/
class E54
{
    var $hands = array();
    var $scores = array();
    
    
    function judge()
    {
        $rounds = array(
            '5H 5C 6S 7S KD 2C 3S 8S 8D TD',
            '5D 8C 9S JS AC 2C 5C 7D 8S QH',
            '2D 9C AS AH AC 3D 6D 7D TD QD',
            '4D 6S 9H QH QC 3D 6D 7H QD QS',
            '2H 2D 4C 4D 4S 3C 3D 3S 9S 9D'
        );
        
        $rounds = trim(file_get_contents('p054_poker.txt'));
        $rounds = explode(chr(10), $rounds);
        
        
        $wins = array(1=>0, 2=>0);
                
        foreach ($rounds as $round) {
            $winner = $this->judgeRound($round);
            $wins[$winner] = $wins[$winner] + 1;
        }    
        echo 'Totals: ';
        print_r($wins);
    }
    
    
    function judgeRound($round) 
    {
        $this->winner = false;
        
        $cards = explode(' ', $round);
        
        $this->hands[1] = array_slice($cards, 0, 5);
        $this->hands[2] = array_slice($cards, 5);
        
        $this->scores[1] = $this->generateScores(1);
        $this->scores[2] = $this->generateScores(2);
        
        $scores = $this->scores;

        while (!$this->winner && (count($scores[1]) > 0 or count($scores[2]) > 0)) {
            
            foreach (range(1,2) as $playerId) {
                $best[$playerId] = array_slice($scores[$playerId], 0, 1, true);
                $scores[$playerId] = array_slice($scores[$playerId], 1);
            }
                
            foreach (range(1,2) as $playerId) {
                $opponentId = $playerId == 1 ? 2 : 1;
                if (
                    (key($best[$playerId]) > key($best[$opponentId])) or 
                    (key($best[$playerId]) == key($best[$opponentId]) && current($best[$playerId]) > current($best[$opponentId]))
                ) {
                    $this->winner = $playerId;
                } 
            }
            
        }
        
        echo $this->winner;
        print_r($best);
        print_r($this->scores);
        echo '<br>';

        
        if (!$this->winner) {
            throw new Exception('Winner can not be determined');
        }
        
        
        return $this->winner;
        
        
    }
    
    function generateScores($player_id)
    {
        foreach ($this->hands[$player_id] as $card) {
            $cards[] = array('value'=>$this->valueScore(substr($card, 0, 1)), 'suit'=>substr($card, 1));
        }
        
        $cardsByValue = array_sort($cards, 'value', 'asc');
        
        // high card
        foreach ($cardsByValue as $card) {
            $scores[1] = $this->compactCards($cardsByValue);
        }
        
        $valueStraights = array();
        $previousValue = false;
        $pairs = array();
        
        // pairs, 3 and 4 of a kind and full house
        foreach ($cardsByValue as $card) {
        
            $value = $card['value'];
            
            // track longest chain
            $groupsByValue[$value][] = $card;
            if (!$previousValue or $previousValue+1 == $value) {
                if (!$previousValue) {
                    $base = $value;
                    $valueStraights[$base] = 0; // since it will get incremented by one 3 lines later
                }
                $previousValue = $value;
                $valueStraights[$base]++;
            }
        }
        
        foreach ($groupsByValue as $groupValue=>$matches) {
            if (count($matches) == 1) {
                // no pair
            } elseif (count($matches) == 2) { // pairs
                $pairs[$groupValue] = $groupValue;
            } elseif (count($matches) == 3) { // three of a kind
                $scores[4] = $groupValue;
            } elseif (count($matches) == 4) { // four of a kind
                $scores[8] = $groupValue;
            }
        }
        
        
        if (isset($scores[4]) && count($pairs) == 1) { // full house
            $scores[7] = $this->compactValues(array(current($pairs), $scores[4]));
        } elseif (count($pairs) == 2) { // two pair!
            $scores[3] = $this->compactValues(array(current($pairs), end($pairs)));
        } elseif (count($pairs) == 1) { // a pair
            $scores[2] = current($pairs);
        }
        
        if ($valueStraights[$base] == 5) { // 5 cards in a row
            if (count(array_group($cards, 'suit')) == 1) { // 
                if ($base == 10) {
                    $scores[10] = 1; // royal flush
                } else {
                    $scores[9] = $this->compactCards($cardsByValue); // straight flush
                }
            } else {
                $scores[5] = $this->compactCards($cardsByValue); // straight
            }
        }
        
        if (count(array_group($cards, 'suit')) == 1) {
            $scores[6] = $this->compactCards($cardsByValue); // flush
        } 
        
        
        krsort($scores);
        
        return $scores;    
    }
    
    function valueScore($value)
    {
        if (preg_match('/[2-9]/', $value)) {
            return $value;
        }
        
        $highCards = array('T'=>10, 'J'=>11, 'Q'=>12, 'K'=>13, 'A'=>14);
        if (!isset($highCards[$value])) {
            throw new Exception('Invalid input for card value: ' . $value);
        } else {
            return $highCards[$value];
        }
    }
    
    function compactCards($cards) {
        foreach ($cards as $card) {
            $values[] = $card['value'];
        }
        return $this->compactValues($values);
    }
    
    function compactValues($values)
    {
        $o = 0;
        $za = 0; foreach ($values as $value) { $za++; // not using keyes just to be on the safe side
            $o+= $value * pow(10, $za); // could use ** if PHP version < 5.6 is not a concern
        }
        return $o;
    }
    
    
    
    
}
    
    
$judge = new E54();
$judge->judge();    



function array_sort($multArray, $sortField='', $desc="asc")
{
    if ($sortField == '') { return $multArray; }
    
    $tmpKey='';
    $ResArray=array();
    $maIndex=array_keys($multArray);
    $maSize=count($multArray)-1;

    for($i=0; $i < $maSize ; $i++) {
        $minElement=$i;
        $tempMin=(isSet($multArray[$maIndex[$i]][$sortField])) ? $multArray[$maIndex[$i]][$sortField] : '';
        $tmpKey=$maIndex[$i];

        for($j=$i+1; $j <= $maSize; $j++) {
            if(isSet($multArray[$maIndex[$j]][$sortField]) && $multArray[$maIndex[$j]][$sortField] < $tempMin ) {
                $minElement=$j;
                $tmpKey=$maIndex[$j];
                $tempMin=$multArray[$maIndex[$j]][$sortField];
            }
        }
        $maIndex[$minElement]=$maIndex[$i];
        $maIndex[$i]=$tmpKey;
    }

    if($desc == "asc") {
        for($j=0;$j<=$maSize;$j++) {
            $ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
        }
    } else {
        for($j=$maSize;$j>=0;$j--) {
            $ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
        }
    }
    return $ResArray;
}   



function array_group($array, $param="", $flatten_on="")
{
    $keep_array = FALSE;
    if (count(explode(',', $param)) > 1) {
        $flatten_on = trim(end(explode(',', $param)));
        $param = trim(current(explode(',', $param)));
        $keep_array = TRUE;
    }
    
    $keys = array_keys($array);
    $array_size = count($array);
    for ($i=0; $i<$array_size; $i++) { // used instead of foreach for ~20% better speed, see http://stackoverflow.com/questions/2413249/why-is-foreach-so-slow
        $a_id = $keys[$i];
        $details = $array[$a_id];
        if ($param == "") {
            $o[$details][] = $a_id;
        } elseif ($flatten_on != '') {
            $flatten_value = $details[$flatten_on];
            if ($keep_array) { 
                $o[$details[$param]][$flatten_value][$a_id] = $details;
            } else {
                $o[$details[$param]][$flatten_value] = $flatten_value;
            }
        } else {
            $o[$details[$param]][$a_id] = $details;
        }
    }
    return $o;
} 
