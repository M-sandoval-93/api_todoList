<?php

    namespace Models;

    use Exception;
    use Flight;


    class Connection {
        protected $connection;
        protected $array = [];

        public function __construct() {
            try {
                Flight::register(
                    'connection',
                    'PDO',
                    array('mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']));
                $this->connection = Flight::connection();

            } catch(Exception $error) {
                Flight::halt(404, json_encode([
                    "message" => "connection error:" .$error->getMessage(),
                ]));
            }

        }

        protected function preConsult($query) {
            return $this->connection->prepare($query);
        }

        protected function beginTransaction() {
            $this->connection->beginTransaction();
        }
        
        protected function commit() {
            $this->connection->commit();
        }
        
        protected function rollBack() {
            $this->connection->rollBack();
        }
        
        protected function closeConnection() {
            $this->connection = null;
        }
    }

?>