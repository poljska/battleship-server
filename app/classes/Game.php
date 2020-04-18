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
            $this->player_1_ships = '{}';
            $this->player_1_shots = '[]';
            $this->player_2_ships = '{}';
            $this->player_2_shots = '[]';
            $this->status = array();
            $this->status['status'] = 'Ongoing';
            $this->status['turn'] = 'Player1';
            $this->status['nbPlayers'] = '0';
        }
    }

    public function getGameId() {
        return $this->game_id;
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
        $return['player_1_ships'] = json_decode($this->player_1_ships);
        $return['player_1_shots'] = json_decode($this->player_1_shots);
        $return['player_2_ships'] = json_decode($this->player_2_ships);
        $return['player_2_shots'] = json_decode($this->player_2_shots);
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
        $return = array();
        $return['game_id'] = $this->game_id;
        $return['timestamp'] = $this->timestamp;
        $return['status'] = $this->status;
        switch ($player) {
            case 'player1':
                $return['ships'] = $this->json_decode(player_1_ships);
                $return['shots'] = $this->json_decode(player_1_shots);
                break;

            case 'player2':
                $return['ships'] = $this->json_decode(player_2_ships);
                $return['shots'] = $this->json_decode(player_2_shots);
                break;

            default:
                throw new InvalidPlayer($player);
                break;
        }
        return $return;
    }

    public function setShips($player, $positions) {
        // TODO
    }

    public function fire($player, $position) {
        // TODO
    }
}
