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

        protected function preConsult(string $query) :object {
            return $this->connection->prepare($query);
        }

        protected function beginTransaction() :void {
            $this->connection->beginTransaction();
        }
        
        protected function commit() :void {
            $this->connection->commit();
        }
        
        protected function rollBack() :void {
            $this->connection->rollBack();
        }
        
        protected function closeConnection() :void {
            $this->connection = null;
        }
    }

?>