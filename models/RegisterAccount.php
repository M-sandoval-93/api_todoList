<?php
    namespace Models;

    use Models\Mailer;
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
            $querySelectUserAccount = $this->preConsult("select * from user_account where user_email = ?");

            // ejecución de la consulta SQL
            $querySelectUserAccount->execute([$data->userEmail]);

            // comprobación de existencia
            if ($querySelectUserAccount->rowCount() !== 0) {
                // obtención de un objeto con los datos    
                $account = $querySelectUserAccount->fetch(PDO::FETCH_OBJ);

                // comprobación de estado
                if ($account->state_account === 0) {
                    // obtención del codigo para validar correo
                    $code = $this->setTableEmailVerification($data);

                    // enviar email con el codigo

                    throw new Exception("La cuenta esta inactiva, se ha enviado un nuevo codigo de activación a tu correo" . $code, 404);
                }
                throw new Exception("La cuenta ya existe", 404);
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
            $verification_code = rand(1000, 9999);

            // sentencia SQL
            $queryInsertEmailVerification = $this->preConsult("insert into email_verification (email_to_verify, code)
                values (?, ?);");

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
                $this->existingUserAccount($dataUserAccount);
                // insert en tabla cuenta de usuario
                $this->setTableUserAccount($dataUserAccount);
                // insert en tabla verificar email
                $code = $this->setTableEmailVerification($dataUserAccount);

                // confirmar la transacción ==================>
                $this->commit();
                $email = "contacto@svtech.cl";
                $asunto = "Cógido para validar cuenta de usuario";
                $cuerpo = "<h2>Validación de cuenta de usuario registrada !</h2>
                    <p>código de validación {$code}</p>";

                Mailer::sendMailer($email, $asunto, $cuerpo);

                // enviar email con el codigo
                // Flight::json($code);

            } catch (Exception $error) {
                //revertir transaccion      ==================>
                $this->rollBack();

                Flight::halt(404, json_encode([
                    "message" => "Error: ". $error,
                ]));

            } finally {
                $this->closeConnection();
            }
        }
    }


?>