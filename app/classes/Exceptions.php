<?php

abstract class ClientException extends Exception {
    function __construct($msg) {
        parent::__construct($msg);
    }
}

class InvalidPlayer extends ClientException {
    function __construct($player) {
        $msg = '[InvalidPlayer]['.$this->getFile().':'.$this->getLine().'] -- '.$player;
        parent::__construct($msg);
    }
}

class InvalidArguments extends ClientException {
    function __construct($args) {
        $msg = '[InvalidArguments]['.$this->getFile().':'.$this->getLine().'] -- '.json_encode($args);
        parent::__construct($msg);
    }
}

class InvalidPosition extends ClientException {
    function __construct($pos) {
        $msg = '[InvalidPosition]['.$this->getFile().':'.$this->getLine().'] -- '.json_encode($pos);
        parent::__construct($msg);
    }
}

class InvalidTurn extends ClientException {
    function __construct($player) {
        $msg = '[InvalidTurn]['.$this->getFile().':'.$this->getLine().'] -- '.$player;
        parent::__construct($msg);
    }
}

class DatabaseException extends Exception {
    function __construct($query, $args, $e = NULL) {
        $details = array($query, $args);
        $msg = '[DatabaseException]['.$this->getFile().':'.$this->getLine().'] -- '.json_encode($details);
        parent::__construct($msg, 0, $e);
    }
}

class InvalidGame extends Exception {
    function __construct($game_id) {
        $msg = '[InvalidGame]['.$this->getFile().':'.$this->getLine().'] -- '.$game_id;
        parent::__construct($msg);
    }
}

class ForbiddenOperation extends Exception {
    function __construct($details) {
        $msg = '[ForbiddenOperation]['.$this->getFile().':'.$this->getLine().'] -- '.$details;
        parent::__construct($msg);
    }
}
