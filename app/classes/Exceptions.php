<?php

class InvalidPlayer extends Exception {
    function __construct($player) {
        $msg = '[InvalidPlayer]['.$this->getFile().':'.$this->getLine().'] -- '.$player;
        parent::__construct($msg);
    }
}

class InvalidArguments extends Exception {
    function __construct($args) {
        $msg = '[InvalidArguments]['.$this->getFile().':'.$this->getLine().'] -- '.json_encode($args);
        parent::__construct($msg);
    }
}
