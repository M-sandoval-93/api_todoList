<?php 

    namespace Models;

    use DateTime;
    use Exception;
    use Flight;
    use PDO;

    class VerifyAccount extends Connection {

        public function __construct() {
            parent::__construct();
        }

        // método para comprobar el código de activación

        // @data -> objeto que requiere; userEmail - code
        private function checkActivationCode(object $data): void {
            // sentencia SQL select
            $queryCheckAccount = $this->preConsult("
                select  ua.id_user_account, ua.user_email, ua.state_account,
                ev.code, ev.code_expiration, ua.user_name
                from user_account ua
                left join email_verification ev on ev.email_to_verify = ua.user_email
                where ua.user_email = ?
                and ev.code = ?;");

            // ejecucón de la consulta SQL
            $queryCheckAccount->execute([$data->userEmail, $data->code]);

            // obtención de un objeto con los datos
            $account = $queryCheckAccount->fetch(PDO::FETCH_OBJ);
            
            // comprobar que el código pertenece al correo de la cuenta
            if (!$account) throw new Exception("Códgo inválido !", 404);

            // verificación del estado de la cuenta
            if ($account->state_account !== 0) throw new Exception("La cuenta ya se encuentra activa", 400);

            // verificar si el código esta activo
            $currentDate = new DateTime();
            $expirationDate = new DateTime($account->code_expiration);

            // comprobación del código
            if ($expirationDate < $currentDate) {
                // agregar nuevo dato al objeto principal
                $data->userName = $account->user_name;

                // reenvio del código de activación al correo
                RegisterAccount::reSendVerificationCode($data);
                throw new Exception("El código a caducado, se ha enviado un nuevo código a; " . $data->userEmail, 400);
            }

        }

        // método para activar cuenta de usuario

        // @data -> objeto que requiere; userEmail - code
        private function activateUserAccount(object $data) :void {

            
        }



        public function verify() {
            // datos del usuario que se registra
            $dataUserAccount = Flight::request()->data;

            try {
                // iniciar transacción      ==================>
                $this->beginTransaction();

                // verificación del código para activar la cuenta
                $this->checkActivationCode($dataUserAccount);

                // Activación de la cuenta
                $this->activateUserAccount($dataUserAccount);

                // confirmar la transacción ==================>
                $this->commit();
                
                Flight::json([
                    "message" => "Cuenta activada con éxito !",
                ]);

            } catch (Exception $error) {
                //revertir transaccion      ==================>
                $this->rollBack();

                $errorCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($errorCode, json_encode([
                    "message" => "Error: ". $error->getMessage(),
                ]));

            } finally {
                $this->closeConnection();
            }
        }
    }



?>