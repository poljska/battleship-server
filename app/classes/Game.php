<?php

final class Game {
    private $id;              // Internal ID
    private $game_id;         // Public game ID
    private $timestamp;       // Game creation timestamp
    private $player_1_ships;  // Positions of all player 1 ships
    private $player_1_shots;  // Positions targeted by player 1
    private $player_2_ships;  // Positions of all player 2 ships
    private $player_2_shots;  // Positions targeted by player 2
    private $status;          // Informations about game (complete / turn / finished / ...)

    function __construct($game_id = NULL) {
        if ($game_id) {  // Existing game
            // TODO
        } else {  // New game
            // TODO
        }
    }

    public function save() {
        // TODO
    }

    public function delete() {
        // TODO
    }

    public function getGame() {
        // TODO
    }
    
    public function getSummary() {
        // TODO
    }

    public function getOngoingGame($player) {
        // TODO
    }

    public function setShips($player, $positions) {
        // TODO
    }

    public function fire($player, $position) {
        // TODO
    }
}
