<?php
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Rfc4122\Validator as Rfc4122Validator;
require_once 'Database.php';
require_once 'Exceptions.php';

final class Game {
    private $id;              // Internal ID
    private $game_id;         // Public game ID
    private $timestamp;       // Game creation timestamp
    private $player_1_ships;  // Position of player 1's ships
    private $player_1_shots;  // Positions targeted by player 1
    private $player_2_ships;  // Position of player 2's ships
    private $player_2_shots;  // Positions targeted by player 2
    private $status;          // Additional informations (status / turn / number of players / winner)

    function __construct($game_id = NULL) {
        if ($game_id !== NULL) {  // Existing game
            if (!is_string($game_id)) throw new InvalidArguments($game_id);
            $v = new Rfc4122Validator();
            if (!$v->validate($game_id)) throw new InvalidArguments($game_id);
            $db = new Database();
            $sql = 'SELECT * FROM games WHERE game_id=?';
            $args = array($game_id);
            $results = $db->execute($sql, $args)[0];
            if (!$results) throw new InvalidGame($game_id);
            $this->id = $results['id'];
            $this->game_id = $results['game_id'];
            $this->timestamp = $results['creation_ts'];
            $this->player_1_ships = json_decode($results['player_1_ships'], TRUE);
            $this->player_1_shots = json_decode($results['player_1_shots']);
            $this->player_2_ships = json_decode($results['player_2_ships'], TRUE);
            $this->player_2_shots = json_decode($results['player_2_shots']);
            $this->status = json_decode($results['status'], TRUE);
        } else {  // New game
            $this->id = NULL;
            $this->game_id = Uuid::uuid4()->toString();
            $this->timestamp = date('Y-m-d H:i:s');
            $this->player_1_ships = array();
            $this->player_1_shots = array();
            $this->player_2_ships = array();
            $this->player_2_shots = array();
            $this->status = array();
            $this->status['status'] = 'New';
            $this->status['nbPlayers'] = 0;
        }
    }

    public function getGameId() {
        return $this->game_id;
    }

    public function save() {
        $db = new Database();
        if ($this->id !== NULL) {  // Existing game
            $sql = 'UPDATE games
            SET player_1_ships = ?,
                player_1_shots = ?,
                player_2_ships = ?,
                player_2_shots = ?,
                status = ?
            WHERE id = ?';
            $args = array(
                json_encode($this->player_1_ships, JSON_FORCE_OBJECT),
                json_encode($this->player_1_shots),
                json_encode($this->player_2_ships, JSON_FORCE_OBJECT),
                json_encode($this->player_2_shots),
                json_encode($this->status, JSON_FORCE_OBJECT),
                $this->id
            );
            $db->execute($sql, $args);
        }
        else {  // New game
            $sql = 'INSERT INTO games (
                game_id,
                creation_ts,
                player_1_ships,
                player_1_shots,
                player_2_ships,
                player_2_shots,
                status
              )
            VALUES
              (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
              )';
            $args = array(
                $this->game_id,
                $this->timestamp,
                json_encode($this->player_1_ships, JSON_FORCE_OBJECT),
                json_encode($this->player_1_shots),
                json_encode($this->player_2_ships, JSON_FORCE_OBJECT),
                json_encode($this->player_2_shots),
                json_encode($this->status, JSON_FORCE_OBJECT)
            );
            $id = $db->execute($sql, $args);
            $this->id = $id;
        }
    }

    public function delete() {
        if ($this->id !== NULL) {  // Existing game
            $db = new Database();
            $sql = 'DELETE FROM games WHERE id=?';
            $args = array($this->id);
            $db->execute($sql, $args);
            $this->id = NULL;
        }
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

    public function getPlayerView($player) {
        if (!Game::isPlayer($player)) throw new InvalidPlayer($player);
        $return = array();
        $return['game_id'] = $this->game_id;
        $return['timestamp'] = $this->timestamp;
        if (!$this->isNew()) {
            if ($player === 'Player1')
                $return['player_1_ships'] = $this->player_1_ships;
            else
                $return['player_2_ships'] = $this->player_2_ships;
            $return['player_1_shots'] = $this->player_1_shots;
            $return['player_2_shots'] = $this->player_2_shots;
        }
        $return['status'] = $this->status;
        return $return;
    }

    public function setShips($player, $ships) {
        if (!$this->isNew()) throw new ForbiddenOperation('This game is not new.');
        if (!Game::isPlayer($player)) throw new InvalidPlayer($player);
        if (!is_array($ships)) throw new InvalidArguments($ships);
        if (count($ships) !== 5) throw new InvalidArguments($ships);
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
        if ($player === 'Player1') {
            if ($this->player_1_ships !== array()) throw new ForbiddenOperation('Position of ships can only be set once.');
            $this->player_1_ships = $ships;
        }
        else {
            if ($this->player_2_ships !== array()) throw new ForbiddenOperation('Position of ships can only be set once.');
            $this->player_2_ships = $ships;
        }
        if ($this->player_1_ships !== array() && $this->player_2_ships !== array()) {
            $this->status['status'] = 'InProgress';
            $this->status['turn'] = 'Player1';
            unset($this->status['nbPlayers']);
        }
        return TRUE;
    }

    public function fire($player, $position) {
        if (!$this->isInProgress()) throw new ForbiddenOperation('This game is not in progress.');
        if (!Game::isPlayer($player)) throw new InvalidPlayer($player);
        if (!$this->isTurn($player)) throw new ForbiddenOperation('Not your turn '.$player);
        if (!Game::isPosition($position)) throw new InvalidPosition($position);
        if ($player === 'Player1') {
            $hit = Game::inArrayDepth2($position, $this->player_2_ships);
            array_push($this->player_1_shots, array($position, $hit));
            $victory = $this->isWin($this->player_1_shots);
        }
        else {
            $hit = Game::inArrayDepth2($position, $this->player_1_ships);
            array_push($this->player_2_shots, array($position, $hit));
            $victory = $this->isWin($this->player_2_shots);
        }
        if ($victory) {
            $this->status['status'] = 'Finished';
            $this->status['winner'] = $player;
            unset($this->status['turn']);
        }
        else {
            $this->nextTurn();
        }
        return array($position, $hit);
    }

    public function addNewPlayer() {
        if (!$this->isNew()) throw new ForbiddenOperation('This game is not new.');
        switch ($this->status['nbPlayers']) {
            case 0:
                $this->status['nbPlayers']++;
                return "Player1";
            case 1:
                $this->status['nbPlayers']++;
                return "Player2";
            default:
                throw new ForbiddenOperation('This game is already full.');
        }
    }

    public function isNew() {
        return $this->status['status'] === 'New';
    }

    public function isInProgress() {
        return $this->status['status'] === 'InProgress';
    }

    public function isFinished() {
        return $this->status['status'] === 'Finished';
    }

    private function isTurn($player) {
        if (!Game::isPlayer($player)) return FALSE;
        return $player === $this->status['turn'];
    }

    private function isWin($shots) {
        $success = array_filter($shots, function($v) { return $v[1] === TRUE; });
        $success = array_unique($success, SORT_REGULAR);
        return count($success) === 17 ? TRUE : FALSE;
    }

    private function nextTurn() {
        $this->status['turn'] = ($this->status['turn'] === 'Player1') ? 'Player2' : 'Player1';
    }

    public static function isPlayer($player) {
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
        return count($shipPositions) === $size[$shipName];
    }

    private static function validPositions($shipPositions) {
        foreach ($shipPositions as $position) {
            if (!Game::isPosition($position)) return FALSE;
        }
        return TRUE;
    }

    private static function isPosition($position) {
        if (!is_array($position)) return FALSE;
        if (count($position) !== 2) return FALSE;
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
                    ($previousPosition[1] - $shipPositions[$i][1])) !== 1)
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
