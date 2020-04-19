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

class InvalidPosition extends Exception {
    function __construct($pos) {
        $msg = '[InvalidPosition]['.$this->getFile().':'.$this->getLine().'] -- '.json_encode($pos);
        parent::__construct($msg);
    }
}

class InvalidTurn extends Exception {
    function __construct($player) {
        $msg = '[InvalidTurn]['.$this->getFile().':'.$this->getLine().'] -- '.$player;
        parent::__construct($msg);
    }
}

class InvalidOperation extends Exception {
    function __construct($details) {
        $msg = '[InvalidOperation]['.$this->getFile().':'.$this->getLine().'] -- '.$details;
        parent::__construct($msg);
    }
}
