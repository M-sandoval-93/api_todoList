<?php

    namespace Errors;

    use Exception;

    class ExceptionAccount extends Exception {

        public static function emailAlreadyExists() :self {
            throw new self("El E-mail registra una cuenta de usuario activa.", 409);
        }

        public static function needToActiveAccount() :self {
            throw new self("El E-mail registra una cuenta de usuario no activada.", 409);
        }

        public static function expiredActivationCode() :self {
            throw new self("El código a expirado, se a enviado un nuevo código de activación.", 404);
        }

        public static function invalidCode() :self {
            throw new self("Código inválido, revisa tu correo.", 409);
        }

        public static function problemActivatingAccount() :self {
            throw new self("Error para activar cuenta, intente más tarde", 500);
        }
    }



?>