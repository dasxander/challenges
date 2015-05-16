<?php

class TicTacToe
{
    /**
    * Run a game
    * 
    * @return void
    */
    public function runGame()
    {
        $board = $this->newBoard();
        $this->winner = false;
        $player = null;
        $this->move_n = 0;
        
        echo "Welcome to Tic-Tac-Toe game\n";
        $this->readPlayer(1);
        $this->readPlayer(2);
        echo "Starting board:\n".$this->printBoard($board)."\n";
        while (count($this->possibleMoves($board)) > 0 && !$this->winner) {
            $this->move_n++;
            $player = $this->getOpponent($player);
            $cell_id = $this->pickMove($board, $player);
            $board = $this->makeMove($board, $player, $cell_id);
            $this->winner = $this->getWinner($board);
            echo $this->summarizeMove($board, $player, $cell_id);
        }
    }
    
    
    /**
    * Board is an array such as 
    * $a[00] = 0; // cell could be used for a move
    * $a[01] = 1; // cell was used by player #1
    * $a[02] = 2; // cell was used by player #2
    */
    public function newBoard()
    {                              
        foreach (range(0,2) as $i) {
            foreach (range(0,2) as $j) {
                $board[$i.$j] = 0;
            }
        }
        return $board;
    }
    
    /**
    * Reading the player type
    * 
    * @param mixed $player
    */
    public function readPlayer($player)
    {
        while (!isset($this->players[$player])) {
            echo "Please enter the type of the player #".$player.":\n";
            echo "Options: \n strong, medium or weak for corresponding AI level\n";
            echo " or \n anything else for human player\n";
            $line = trim(fgets(STDIN));
            if (in_array(strtolower($line), array('strong', 'medium', 'weak'))) {
                $this->players[$player] = $line;
            } else {
                $this->players[$player] = 'human';
            }
            echo "Player #".$player." is set to ".$this->players[$player]."\n";
        }
    }
    
    
    /**
    * Generate an array with moves that are possible
    * 
    * @param mixed $board
    */
    public function possibleMoves($board)
    {
        $possible = array();
        foreach ($board as $key=>$value) {
            if ($value == 0) {
                $possible[$key] = 0; // as of now, no score gets assigned
            }
        }
        return $possible;
    }
    
    
    /**
    * Get the opponent 
    * 
    * @param mixed $player_id
    */
    public function getOpponent($player)
    {
        // defaults to 1 if nobody made a move yet
        return ($player == 1) ? 2 : 1;
    }
    
    
    /**
    * put your comment there...
    * 
    * @param mixed $board
    * @param mixed $player
    * @return board post move
    */
    function pickMove($board, $player)
    {
        
        $options = $this->possibleMoves($board);
        
        
        if ($this->players[$player] == 'human') {
            while(1) {
                echo "Player ".$player.", please enter the cell number for your move\n";
                $line = trim(fgets(STDIN));
                if (!in_array($this->publicToReal($line), array_keys($options))) {
                    echo "Invalid input\n";
                } else {
                    return $this->publicToReal($line);
                }       
            }
        }
        
        
        if (count($options) == 0) {
            echo 'Will throw exception\n';
            throw new \Exception('There are no possible moves that could be made');
        }
        
        $move_id = false;
        $checkableMoves = array();
        
        foreach ($options as $cell_id=>$score) {
            $board_post_us = $this->makeMove($board, $player, $cell_id);
            $winner_us = $this->getWinner($board_post_us);
            
            $board_post_op = $this->makeMove($board, $this->getOpponent($player), $cell_id);
            $winner_op = $this->getWinner($board_post_op);
            
            $score = 0;
            if ($winner_us == $player) {
                $score = -2; // this would be the best move to make since it wins the game
            } elseif ($winner_op == $this->getOpponent($player)) {
                $score = -1; // second best move to do - (try to) prevent the opponent from winning
            } else {
                $score = $this->countWinnableLines($board_post_us, $this->getOpponent($player));
            }
            $options[$cell_id] = $score;
        }
        
        $this->shuffle_assoc($options); // so that AI is less predictable, shuffling the options so that if we have multiple "best" options, a random one is picked
        if ($this->players[$player] == 'strong') {
            asort($options);
        } elseif ($this->players[$player] == 'medium') {
            // just leaving sorted as is
        } elseif ($this->players[$player] == 'weak') {
            arsort($options);
        }
        
        $move_id = key($options);
        
        return $move_id;
    }
    
    
    /**
    * Count how many potential winning combinations would the board give for a player
    * 
    * @param mixed $board
    * @param mixed $player
    */
    public function countWinnableLines($board, $player)
    {
        $winnable = 0;
        $winningLines = $this->winningLines($board, $player);
        foreach ($winningLines as $line) {
            $inline = 0;
            foreach ($line as $cell_id) {
                if ($board[$cell_id] == $this->getOpponent($player)) {
                    break;
                } else {
                    $inline++;
                }
            }
            if ($inline == 3) {
                $winnable++;
            }
        }
        
        return $winnable;
    }
    
    /**
    * @return array updated board
    */
    function makeMove($board, $player, $cell_id)
    {   
        $board[$cell_id] = $player;
        return $board;
    }
    
    /**
    * Return the player which is currently winning
    * 
    * @param mixed $board
    */
    function getWinner($board)
    {
        foreach (range(1,2) as $player) {
            if ($this->checkForWin($board, $player)) {
                return $player;
            }
        }

        return null;
    }
    
    /**
    * Check if the given player has won 
    * 
    */ 
    public function checkForWin($board, $player)
    {
        $won = false;
        
        foreach ($this->winningLines() as $line) {
            $inline = 0;
            foreach ($line as $cell_id) {
                if ($board[$cell_id] == $player) {
                    $inline++;
                } else {
                    break; // no way this option will win
                }
            }
            if ($inline == 3) {
                $won = true;
                break;
            }
        }
        
        return $won;
    }
    
    
    /**
    * The contents of this method could be replaced with the following array:
    * 
    * 
    *   $winningLines = array(
    *      // horizontal
    *        array('00', '01', '02'),
    *        array('10', '11', '12'),
    *        array('20', '21', '22'),
	*
    *        // vertical
    *        array('00', '10', '20'),
    *        array('01', '11', '21'),
    *        array('02', '12', '22'),
    *        
    *        // diagonal
    *        array('00', '11', '22'),
    *        array('20', '11', '02'),
    *    );
    *
    * Hoever, generating lines automatically is:
    * a) a nice exercise
    * b) would make supporting bigger than 3x3 boards easier
    * 
    */
    public function winningLines()
    {
        $winningLines = array();
        
        $approach = array();
        foreach (range(0,2) as $i) {
            foreach (range(0,2) as $j) {
                $approach['horizontal'][$i][] = $i.$j;
                $approach['vertical'][$i][] = $j.$i;
            }
            $approach['diagonal'][0][] = $i.$i;
            $approach['diagonal'][1][] = (2 - $i).$i;
        }
        foreach ($approach as $type=>$options) {
            foreach ($options as $option) {
                $winningLines[] = $option;
            }
        }
        
        return $winningLines;
    }

    /**
    * Prints out the board in it's given position
    * 
    * @param mixed $board
    * @return string containing the board output
    */
    public function printBoard($board, $last_move='')
    {
        $output = '';
        $convention = array('0'=>'', '1'=>'x', '2'=>'o');
        foreach (range(0,2) as $i) {
            $row = array();
            foreach (range(0,2) as $j) {
                $value = $board[$i.$j];
                $value = $convention[$value];
                if ($last_move == $i.$j) {
                    $value = strtoupper($value);
                } elseif ($value == '') {
                    $value = $this->realToPublic($i.$j);
                }
                $row[] = $value; 
            }
            $output .= implode('|', $row)."\n";
        }
        return $output;
    }
    
    /**
    * Since there is a benefit in using cell numbers that are not just 1, 2 or 5
    * for calculations, there is a need to do conversions to/and from 
    * 
    * @param mixed $real_cell_id
    * @return mixed
    */
    public function cellMap()
    {
        $board = $this->newBoard();
        $map = array();
        $i = 0; foreach ($board as $cell_id=>$score) { $i++;
            $map[$cell_id] = $i;
        }
        return $map;
    }
        
    public function realToPublic($real_cell_id)
    {
        $map = $this->cellMap();
        return $map[$real_cell_id];
    }
    
    public function publicToReal($public_cell_id)
    {
        $map = array_flip($this->cellMap());
        return $map[$public_cell_id];
    }
    
    /**
    * Summarize move
    * 
    * @param mixed $board
    */
    public function summarizeMove($board, $player, $cell_id)
    {
        $output = "Player ".$this->playerFullName($player)." has picked cell ".$this->realToPublic($cell_id)."\n";
        $output.= "Current board:\n".$this->printBoard($board, $cell_id)."\n";
        if ($this->winner) {
            $output.= "Player ".$this->playerFullName($this->winner)." has won\n";
        } elseif (count($this->possibleMoves($board)) == 0) {
            $output.= "Game has ended in draw\n";
        }
        return $output;
    }
    
    /**
    * It's nice to indicate what kind of player it is when mentioning player ID
    * 
    * @param mixed $player
    */
    public function playerFullName($player)
    {
        if ($this->players[$player] == 'human') {
            $output = $player.' (human)';
        } else {
            $output = $player.' ('.$this->players[$player].' AI)';
        }
        return $output;
    }
            
            
    
    /**
    * Just as the name says
    * 
    * @param mixed $array
    */
    protected function shuffle_assoc(&$array) {
        $keys = array_keys($array);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }
}
