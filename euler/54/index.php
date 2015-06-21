<?
// https://projecteuler.net/problem=54
// 1:05h

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

    
$p[1] = array(
                10=>14,
                9=>14,
                
                
    
    Spades, Diamonds (red), Hearts (red) and Clubs (from clovers?)

         5H 5C 6S 7S KD
Pair of Fives
         2C 3S 8S 8D TD
Pair of Eights
         Player 2
         
         
Jack -11 , Queen 12,  King 13, Ace 14        
         
get cards for player 1 and 2
determine all possible combinations, each combination has a rank
compare player 1 against player 2
increment counter


         
*/
class E54
{
    var $hands = array();
    var $scores = array();
    
    function read() 
    {
        $input = '5H 5C 6S 7S KD 2C 3S 8S 8D TD';
        
        $cards = explode(' ', $input);
        
        $this->hands[1] = array_slice($cards, 0, 5);
        $this->hands[2] = array_slice($cards, 5);
    }
    
    function generateScores($player_id)
    {
        $rawCards = $this->hands[$player_id];
        foreach ($rawCards as $card) {
            $cards[] = array('value'=>substr($card, 0, 1), 'suit'=>substr($card, 1));
        }
        
        $cardsBySuit = array_sort($cards, 'suit', 'asc', 'value', 'asc');
        $cardsByValue = array_sort($cards, 'value', 'asc');
        
        
        // high card
        foreach ($cardsByValue as $card) {
            $scores[1] = $this->compactCards($cardsByValue);
        }
        
        
        
        // pairs, 3 and 4 of a kind and full house
        foreach ($cardsByValue as $card) {
            $groupsByValue[$card['value']][] = $card;
        }
        foreach ($groupsByValue as $groupValue=>$matches) {
            $groupScore = $this->valueScore($groupValue);
            if (count($matches) == 1) {
                // no pair
            } elseif (count($matches) == 2) { // pairs
                $pairs[$groupValue] = $groupScore;
            } elseif (count($matches) == 3) { // three of a kind
                $scores[4] = $groupScore;
            } elseif (count($matches) == 4) { // four of a kind
                $scores[8] = $groupScore;
            }
        }
        
        
        if (isset($scores[4]) && count($pairs) == 1) { // full house
            $scores[7] = $this->compactScores(array(current($pairs), $scores[4]));
        } elseif (count($pairs) == 2) { // two pair!
            $scores[3] = $this->compactScores(array(current($pairs), end($pairs)));
        } elseif (count($pairs) == 1) { // a pair
            $scores[2] = current($pairs);
        }
        
        
        
        
        
        foreach ($cardsBySuit as $card) {
            $groupsBySuit[$card['suit']][] = $card;
        }
        
            
            
        
        
        foreach ($cards as $card) {
                    
        }
        
        
    }
    
    function valueScore($value)
    {
        if (preg_match('/[0-9]/', $value)) {
            return value;
        }
        
        $highCards = array('J'=>11, 'Q'=>12, 'K'=>13, 'A'=>14);
        if (!issef($highCards[$value])) {
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
    
    function compactValues($values) {
        foreach ($values as $value) {
            $scores[] = $this->valueScore($value);
        }
        return $this->compactScores($scores);
    } 
    
    function compactScores($score)
    {
        $o = 0;
        $za = 0; foreach ($cards as $card) { $za++; // not using keyes just to be on the safe side
            $o+= $scores * pow(10, $za); // could use ** if PHP version < 5.6 is not a concern
        }
        return $o;
    }
    
    
}
    
    
    



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