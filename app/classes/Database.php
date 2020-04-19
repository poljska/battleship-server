<?php


final class Database {
    private $handler;

    private static function getConnectionString() {
        extract(parse_url(getenv('DATABASE_URL')));
        return sprintf(
            'pgsql:sslmode=require host=%s port=%s user=%s password=%s dbname=%s',
            $host,
            $port,
            $user,
            $pass,
            ltrim($path, '/')
        );
    }

    function __construct() {
        try {
            $this->handler = new PDO(Database::getConnectionString(), NULL, NULL,
                array(
                    PDO::ATTR_PERSISTENT => TRUE,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => FALSE
                )
            );
        } catch (PDOException $e) {
            throw new Exception(NULL, NULL, $e); // TODO Pass e to custom exception
        }
    }
}
