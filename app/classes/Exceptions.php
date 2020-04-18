<?php

class InvalidPlayer extends Exception {
    function __construct($player) {
        $msg = $player . ': Error, player doesn\'t exist';
        parent::__construct($msg);
    }
}
