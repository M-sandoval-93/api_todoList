<?php
    namespace Models;

    use Models\Mailer;
    use CustomExceptions\ExceptionAccount;
    use DateTime;
    use Exception;
    use Flight;
    use PDO;

    class RegisterAccount extends Connection {

        public function __construct() {
            parent::__construct();
        }

        // method to check existence and status of a user account
        // @param $dataUserAccount -> object with user account data
        private function existingUserAccount(object $dataUserAccount) :void {
            // sentencia SQL select
            $querySelectUserAccount = $this->preConsult("
                select ua.id_user_account, ua.user_email, ua.state_account,
                ev.code, ev.code_expiration
                from user_account ua
                left join email_verification ev on ev.email_to_verify = ua.user_email
                where ua.user_email = ?");

            // ejecución de la consulta SQL
            $querySelectUserAccount->execute([$dataUserAccount->userEmail]);
            $account = $querySelectUserAccount->fetch(PDO::FETCH_OBJ);
            
            if($account) {
                // create of the dates
                $currentDate = new DateTime();
                $expirationDate = new DateTime($account->code_expiration);

                // there in an active user account
                if ($account->state_account !== 0) 
                    ExceptionAccount::emailAlreadyExists();

                if ($expirationDate > $currentDate) {
                    // inactive user account
                    ExceptionAccount::needToActiveAccount();
                
                } else {
                    // expired activation code
                    $this->registerAndSendActivationCode($dataUserAccount);
                    ExceptionAccount::expiredActivationCode();

                }
            }
        } 

        // method to register account in user_account table
        // @param $dataUserAccount -> object with user account data
        private function setUserAccoun(object $dataUserAccount) :void {
            // sentencia SQL insert
            $queryInsertUserAccount = $this->preConsult("insert into user_account 
                (user_name, user_password, user_email) values (?, ?, ?);");

            // ejecución de la consulta SQL
            $queryInsertUserAccount->execute([
                $dataUserAccount->userName,
                $dataUserAccount->userPassword,
                $dataUserAccount->userEmail
            ]);
        }

        // method to register account in email_verification table
        // @param $userEmail -> email of account
        private function setActivationCode(string $userEmail) :int {
            // generar codigo de validacion
            $activationCode = rand(100000, 999999);

            // sentencia SQL para registrar email y codigo de verificacion
            $queryInsertEmailVerification = $this->preConsult("insert into email_verification (email_to_verify, code)
                    values (?, ?)
                    on duplicate key update code = ?, 
                    code_expiration = (current_timestamp + interval 60 minute);");

            // ejecución de la sentencia preparada
            $queryInsertEmailVerification->execute([
                $userEmail,
                $activationCode,
                $activationCode
            ]);

            return $activationCode;
        }

        // method to register and send activation code
        // @param $dataUserAccount -> object with user account data
        private function registerAndSendActivationCode(object $dataUserAccount) :void {
            // obtaining activation code
            $activationCode = $this->setActivationCode($dataUserAccount->userEmail);

            // sending activation code to email
            Mailer::sendActivationCode($dataUserAccount->userEmail, $dataUserAccount->userName, $activationCode);
        }

        // método para registrar cuenta de usuario
        // method to register user account
        public function setAccount() :void {
            // datos del usuario que se registra
            $dataUserAccount = Flight::request()->data;
            
            try {
                // start transaction      ==================>
                $this->beginTransaction();

                // cheking the existence and status of the user account
                $this->existingUserAccount($dataUserAccount);

                // insert user account
                $this->setUserAccoun($dataUserAccount);

                // insert and send activation code
                $this->registerAndSendActivationCode($dataUserAccount);

                // confirm transaction ==================>
                 $this->commit();

                Flight::json([
                    "message" => "Success",
                ]);

            } catch (Exception $error) {
                //reverse transaction     ==================>
                $this->rollBack();

                $errorCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($errorCode, json_encode([
                    "message" => "Error: ". $error->getMessage(),
                ]));

            } finally {
                $this->closeConnection();
            }
        }

        // método estático para reenviar código de verificación
        // static method to resend verification code
        // public static function reSendVerificationCode(object $dataUserAccount) :void {
        //     $db = new self();

        //     // generar codigo de validacion
        //     $verification_code = rand(100000, 999999);

        //     // sentencia SQL para actualizar codigo de validación de la cuenta
        //     $queryUpdateVerificationCode = $db->preConsult("update email_verification 
        //         set code = ?, code_expiration = (current_timestamp + interval 60 minute) where email_to_verify = ?;");

        //     // ejecución de la sentencia preparada
        //     $queryUpdateVerificationCode->execute([$verification_code,$dataUserAccount->userEmail]);

        //     Mailer::setEmailWithCode($verification_code, $dataUserAccount);
        // }
    }


?>