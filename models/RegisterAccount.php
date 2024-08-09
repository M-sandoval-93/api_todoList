<?php
    namespace Models;

    use Models\Mailer;
    use DateTime;
    use Exception;
    use Flight;
    use PDO;

    class RegisterAccount extends Connection {

        public function __construct() {
            parent::__construct();
        }

        // método para comprobar existencia y estado de una cuenta de usuario
        private function existingUserAccount(object $data) :bool {
            // sentencia SQL select
            $querySelectUserAccount = $this->preConsult("
                select ua.id_user_account, ua.user_email, ua.state_account,
                ev.code, ev.code_expiration
                from user_account ua
                left join email_verification ev on ev.email_to_verify = ua.user_email
                where ua.user_email = ?");

            // ejecución de la consulta SQL
            $querySelectUserAccount->execute([$data->userEmail]);

            // comprobar si la cuenta existe
            if ($querySelectUserAccount->rowCount() !== 0) {
                // obtención de un objeto con los datos
                $account = $querySelectUserAccount->fetch(PDO::FETCH_OBJ);

                // verificar cuenta activa creada
                if ($account->state_account !== 0) {
                    Flight::json([
                        "message" => "El E-mail ya tiene una cuenta activa",
                    ]);
                    return true;
                } 

                // verificar si el código esta activo
                $currentDate = new DateTime();
                $expirationDate = new DateTime($account->code_expiration);

                // comprobación del código
                if ($expirationDate > $currentDate) {
                    Flight::json([
                        "message" => "La cuenta existe, pero debe ser validada, revisa tu correo; " . $data->userEmail,
                    ]);
                    
                } else {
                    self::reSendVerificationCode($data);
                    Flight::json([
                        "message" => "La cuenta existe, pero el codigo ha caducado, nuevo código enviado a; " . $data->userEmail,
                    ]);
                }
                return true;
            }

            return false;
        } 

        // método para registrar cuenta en tabla user_account
        private function setTableUserAccount(object $data) :void {
            // sentencia SQL insert
            $queryInsertUserAccount = $this->preConsult("insert into user_account 
                (user_name, user_password, user_email) values (?, ?, ?);");

            // ejecución de la consulta SQL
            $queryInsertUserAccount->execute([
                $data->userName,
                $data->userPassword,
                $data->userEmail
            ]);
        }

        // método para registrar cuenta en tabla email_verification
        private function setTableEmailVerification(object $data) :int {
            // generar codigo de validacion
            $verification_code = rand(100000, 999999);

            // sentencia SQL para registrar email y codigo de verificacion
            $queryInsertEmailVerification = $this->preConsult("insert into email_verification (email_to_verify, code)
                values (?, ?);");

            // ejecución de la sentencia preparada
            $queryInsertEmailVerification->execute([
                $data->userEmail,
                $verification_code
            ]);

            return $verification_code;
        }

        // método para registrar cuenta de usuario
        public function setAccount() :void {
            // datos del usuario que se registra
            $dataUserAccount = Flight::request()->data;

            try {
                // iniciar transacción      ==================>
                $this->beginTransaction();

                // comprobar existencia y estado de cuenta de usuario
                if ($this->existingUserAccount($dataUserAccount)) return;

                // insert en tabla cuenta de usuario
                $this->setTableUserAccount($dataUserAccount);

                // insert en tabla verificar email
                $code = $this->setTableEmailVerification($dataUserAccount);

                // confirmar la transacción ==================>
                $this->commit();

                // envío de email con código para validar cuenta
                // $this->setEmailWithCode($code, $dataUserAccount);
                Mailer::setEmailWithCode($code, $dataUserAccount);

                Flight::json([
                "message" => "success",
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

        public static function reSendVerificationCode(object $dataUserAccount) :void {
            $db = new self();

            // generar codigo de validacion
            $verification_code = rand(100000, 999999);

            // sentencia SQL para actualizar codigo de validación de la cuenta
            $queryUpdateVerificationCode = $db->preConsult("update email_verification 
                set code = ?, code_expiration = (current_timestamp + interval 60 minute) where email_to_verify = ?;");

            // ejecución de la sentencia preparada
            $queryUpdateVerificationCode->execute([$verification_code,$dataUserAccount->userEmail]);

            Mailer::setEmailWithCode($verification_code, $dataUserAccount);
        }
    }


?>