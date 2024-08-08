<?php 

    namespace Models;

    use Flight;

    class VerifyAccount extends Connection {

        public function __construct() {
            parent::__construct();
        }

        public function verify() {
            // datos del usuario que se registra
            $dataUserAccount = Flight::request()->data;

            // verificar si el correo existe
            // verificar si el código está activo
            
            // si esta activo validar cuenta

            // si no esta activo, volver a enviar el código


            Flight::json($dataUserAccount);
        }
    }



?>