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
                    PDO::ATTR_EMULATE_PREPARES => FALSE,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                )
            );
        } catch (PDOException $e) {
            throw new DatabaseException(NULL, NULL, $e);
        }
    }

    public function execute($query, $args = array()) {
        try {
            $stm = $this->handler->prepare($query);
            $status = $stm->execute($args);
            if ($status === FALSE) throw new DatabaseException($query, $args);
            $instruction = strtoupper(explode(' ', trim($query))[0]);
            switch ($instruction) {
                case 'SELECT':
                    $results = $stm->fetchAll();
                    if ($results === FALSE) throw new DatabaseException($query, $args);
                    return $results;
                case 'INSERT':
                    return $this->handler->lastInsertId();
                case 'UPDATE':
                case 'DELETE':
                default:
                    return $stm->rowCount();
            }
        } catch (PDOException $e) {
            throw new DatabaseException($query, $args, $e);
        }
    }
}
