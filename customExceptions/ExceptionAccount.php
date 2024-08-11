<?php

    namespace CustomExceptions;

    use Exception;


    class ExceptionAccount extends Exception {

        public static function emailAlreadyExists() :self {
            throw new self("El E-mail registra una cuenta de usuario activa", 400);
        }

        public static function needToActiveAccount() :self {
            throw new self("El E-mail registra una cuenta de usuario no activada", 400);
        }

        public static function expiredActivationCode() :self {
            throw new self("El código a expirado, se a enviado un nuevo código de activación", 404);
        }

        public static function invalidCode() :self {
            throw new self("Código inválido !", 404);
        }
    }



?>