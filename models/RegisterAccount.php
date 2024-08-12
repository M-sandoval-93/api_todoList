<?php
    namespace Models;

    use Models\Mailer;
    use Errors\ExceptionAccount;
    use DateTime;
    use Exception;
    use Flight;
    use PDO;

    class RegisterAccount extends Connection {
        public function __construct() {
            parent::__construct();
        }

        // method to verification exists user account
        private function existingUserAccount(object $dataUserAccount) :array {
            // sentencia SQL select
            $querySelectUserAccount = $this->preConsult("
                select ua.id_user_account, ua.user_email, ua.state_account,
                ev.code, ev.code_expiration
                from user_account ua
                left join email_verification ev on ev.email_to_verify = ua.user_email
                where ua.user_email = ?");

            // SQL statement execute
            $querySelectUserAccount->execute([$dataUserAccount->userEmail]);
            $account = $querySelectUserAccount->fetch(PDO::FETCH_OBJ);

            if ($account) {
                $currentDate = new DateTime();
                $expirationDate = new DateTime($account->code_expiration);

                if ($account->state_account !== 0)
                    return ["status" => "active"];

                if ($expirationDate > $currentDate)
                    return ["status" => "inactive"];

                return ["status" => "expired"];
            }

            return ["status" => "not found"];
        }

        // method to register account in user_account table
        private function setUserAccoun(object $dataUserAccount) :void {
            $queryInsertUserAccount = $this->preConsult("insert into user_account 
                (user_name, user_password, user_email) values (?, ?, ?);");

            $queryInsertUserAccount->execute([
                $dataUserAccount->userName,
                $dataUserAccount->userPassword,
                $dataUserAccount->userEmail
            ]);
        }

        // method to register account in email_verification table
        private function setActivationCode(string $userEmail) :int {
            $activationCode = rand(100000, 999999);

            $queryInsertEmailVerification = $this->preConsult("insert into email_verification (email_to_verify, code)
                    values (?, ?)
                    on duplicate key update code = ?, 
                    code_expiration = (current_timestamp + interval 60 minute);");

            $queryInsertEmailVerification->execute([
                $userEmail,
                $activationCode,
                $activationCode
            ]);

            return $activationCode;
        }

        // method to register and send activation code
        private function registerAndSendActivationCode(object $dataUserAccount) :void {
            $activationCode = $this->setActivationCode($dataUserAccount->userEmail);
            Mailer::sendActivationCode($dataUserAccount->userEmail, $dataUserAccount->userName, $activationCode);
        }

        // method to register user account
        public function setAccount() :void {
            $dataUserAccount = Flight::request()->data;
            
            try {
                $this->beginTransaction();
                $setAccountStatus = $this->existingUserAccount($dataUserAccount);

                switch ($setAccountStatus["status"]) {
                    case "active":
                        ExceptionAccount::emailAlreadyExists();
                        break;

                    case "inactive":
                        ExceptionAccount::needToActiveAccount();
                        break;

                    case "expired":
                        $this->registerAndSendActivationCode($dataUserAccount);
                        Flight::json([
                            "error" => "El código a expirado, se a enviado un nuevo código de activación.",
                        ], 404);
                        break;

                    case "not found":
                        $this->setUserAccoun($dataUserAccount);
                        $this->registerAndSendActivationCode($dataUserAccount);

                        Flight::json([
                            "message" => "Success",
                        ]);
                        break;
                }

                $this->commit();        

            } catch (Exception $error) {
                $this->rollBack();
                $errorCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($errorCode, json_encode([
                    "Error" => $error->getMessage(),
                ]));

            } finally {
                $this->closeConnection();

            }
        }
    }


?>