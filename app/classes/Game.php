<?php
use Ramsey\Uuid\Uuid;
require_once "Exceptions.php";


final class Game {
    private $id;              // Internal ID
    private $game_id;         // Public game ID
    private $timestamp;       // Game creation timestamp
    private $player_1_ships;  // Positions of all player 1 ships
    private $player_1_shots;  // Positions targeted by player 1
    private $player_2_ships;  // Positions of all player 2 ships
    private $player_2_shots;  // Positions targeted by player 2
    private $status;          // Additional data (status / turn / number of players / winner)

    function __construct($game_id = NULL) {
        if ($game_id) {  // Existing game
            // TODO
        } else {  // New game
            $this->game_id = Uuid::uuid4()->toString();
            $this->timestamp = time();
            $this->player_1_ships = array();
            $this->player_1_shots = array();
            $this->player_2_ships = array();
            $this->player_2_shots = array();
            $this->status = array();
            $this->status['status'] = 'Ongoing';
            $this->status['turn'] = 'Player1';
            $this->status['nbPlayers'] = '0';
        }
    }

    public function getGameId() {
        return $this->game_id;
    }

    public function getTurn() {
        return $this->status['turn'];
    }

    public function save() {
        // TODO
    }

    public function delete() {
        // TODO
    }

    public function getGame() {
        $return = array();
        $return['game_id'] = $this->game_id;
        $return['timestamp'] = $this->timestamp;
        $return['player_1_ships'] = $this->player_1_ships;
        $return['player_1_shots'] = $this->player_1_shots;
        $return['player_2_ships'] = $this->player_2_ships;
        $return['player_2_shots'] = $this->player_2_shots;
        $return['status'] = $this->status;
        return $return;
    }

    public function getSummary() {
        $return = array();
        $return['game_id'] = $this->game_id;
        $return['timestamp'] = $this->timestamp;
        $return['status'] = $this->status;
        return $return;
    }

    public function getOngoingGame($player) {
        if (!Game::isPlayer($player)) throw new InvalidPlayer($player);
        $return = array();
        $return['game_id'] = $this->game_id;
        $return['timestamp'] = $this->timestamp;
        $return['status'] = $this->status;
        if ($player == 'Player1') {
            $return['ships'] = $this->player_1_ships;
            $return['shots'] = $this->player_1_shots;
        }
        else {
            $return['ships'] = $this->player_2_ships;
            $return['shots'] = $this->player_2_shots;
        }
        return $return;
    }

    public function setShips($player, $ships) {
        if (!Game::isPlayer($player)) throw new InvalidPlayer($player);
        if (!is_array($ships)) throw new InvalidArguments($ships);
        if (count($ships) != 5) throw new InvalidArguments($ships);
        if (!isset($ships['carrier'], $ships['battleship'], $ships['destroyer'],
                   $ships['submarine'], $ships['patrol_boat'])
           ) throw new InvalidArguments($ships);
        foreach ($ships as $shipName => $shipPositions) {
            if (!is_array($shipPositions)) throw new InvalidArguments($ships);
            if (!Game::sizeGood($shipName, $shipPositions)) throw new InvalidArguments($ships);
            if (!Game::validPositions($shipPositions)) throw new InvalidArguments($ships);
            if (!Game::isContinuous($shipPositions)) throw new InvalidArguments($ships);
        }
        if (Game::overlap($ships)) throw new InvalidArguments($ships);
        if ($player == 'Player1') {
            $this->player_1_ships = $ships;
        }
        else {
            $this->player_2_ships = $ships;
        }
        return TRUE;
    }

    public function fire($player, $position) {
        if (!Game::isPlayer($player)) throw new InvalidPlayer($player);
        if (!$this->isTurn($player)) throw new InvalidTurn($player);
        if (!Game::isPosition($position)) throw new InvalidPosition($position);
        if ($player == 'Player1') {
            $hit = Game::inArrayDepth2($position, $this->player_2_ships);
            array_push($this->player_1_shots, array($position, $hit));
        }
        else {
            $hit = Game::inArrayDepth2($position, $this->player_1_ships);
            array_push($this->player_2_shots, array($position, $hit));
        }
        $this->nextTurn();
        return $hit;
    }

    private function isTurn($player) {
        if (!Game::isPlayer($player)) return FALSE;
        return $player == $this->status['turn'];
    }

    private function nextTurn() {
        $this->status['turn'] = ($this->status['turn'] == 'Player1') ? 'Player2' : 'Player1';
    }

    private static function isPlayer($player) {
        if (!is_string($player)) return FALSE;
        switch ($player) {
            case 'Player1':
            case 'Player2':
                return TRUE;
            default:
                return FALSE;
        }
    }

    private static function sizeGood($shipName, $shipPositions) {
        $size = array('carrier' => 5, 'battleship' => 4, 'destroyer' => 3, 'submarine' => 3, 'patrol_boat' => 2);
        return count($shipPositions) == $size[$shipName];
    }

    private static function validPositions($shipPositions) {
        foreach ($shipPositions as $position) {
            if (!Game::isPosition($position)) return FALSE;
        }
        return TRUE;
    }

    private static function isPosition($position) {
        if (!is_array($position)) return FALSE;
        if (count($position) != 2) return FALSE;
        foreach ($position as $v) {
            if (!is_int($v)) return FALSE;
            if ($v < 1 || $v > 10) return FALSE;
        }
        return TRUE;
    }

    private static function isContinuous($shipPositions) {
        sort($shipPositions);
        $previousPosition = $shipPositions[0];
        for ($i=1; $i < count($shipPositions); $i++) {
            if (abs(($previousPosition[0] - $shipPositions[$i][0]) +
                    ($previousPosition[1] - $shipPositions[$i][1])) != 1)
                return FALSE;
            $previousPosition = $shipPositions[$i];
        }
        return TRUE;
    }

    private static function overlap($ships) {
        $allPosition = array();
        foreach ($ships as $shipPositions) {
            foreach ($shipPositions as $position) {
                if (!in_array($position, $allPosition)) array_push($allPosition, $position);
                else return TRUE;
            }
        }
        return FALSE;
    }

    private static function inArrayDepth2($needle , $haystack) {
        foreach ($haystack as $item) {
            if (in_array($needle, $item)) return TRUE;
        }
        return FALSE;
    }
}
