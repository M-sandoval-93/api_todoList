<?php 

    namespace Models;

    use Models\Mailer;
    use Errors\ExceptionAccount;
    use DateTime;
    use Exception;
    use Flight;
    use PDO;

    class ActivateAccount extends Connection {

        public function __construct() {
            parent::__construct();
        }

        // method to cheking the activation code
        private function checkActivationCode(object $dataUserAccount) :array {
            $queryCheckAccount = $this->preConsult("
                select  ua.id_user_account, ua.user_email, ua.state_account,
                ev.code, ev.code_expiration, ua.user_name
                from user_account ua
                left join email_verification ev on ev.email_to_verify = ua.user_email
                where ua.user_email = ?
                and ev.code = ?;");

            $queryCheckAccount->execute([$dataUserAccount->userEmail, $dataUserAccount->code]);
            $account = $queryCheckAccount->fetch(PDO::FETCH_OBJ);

            if ($account) {
                $currentDate = new DateTime();
                $expirationDate = new DateTime($account->code_expiration);

                if ($account->state_account !== 0)
                    return ["status" => "active"];

                if ($expirationDate < $currentDate)
                    return [
                        "status" => "expired",
                        "userName" => $account->user_name,
                    ];

                return ["status" => "inactive"];

            }
                
            return ["status" => "invalid"];
        }

        // method to activate user account
        private function updateStateUserAccount(object $data) :void {
            $queryActivateAccount = $this->preConsult("update user_account 
                set state_account = 1 where user_email = ?;");

            if (!$queryActivateAccount->execute([$data->userEmail]))
                ExceptionAccount::problemActivatingAccount();
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

        // method to activation user account
        public function activateUserAccount() {
            $dataUserAccount = Flight::request()->data;

            try {
                $this->beginTransaction();
                $setActivationAccount = $this->checkActivationCode($dataUserAccount);

                switch ($setActivationAccount["status"]) {
                    case "active":
                        ExceptionAccount::emailAlreadyExists();
                        break;

                    case "expired":
                        $activationCode = $this->setActivationCode($dataUserAccount->userEmail);
                        Mailer::sendActivationCode($dataUserAccount->userEmail, $setActivationAccount["userName"], $activationCode);                    
                        Flight::json([
                            "error" => "El código a expirado, se a enviado un nuevo código de activación.",
                        ], 404);
                        break;

                    case "inactive":
                        $this->updateStateUserAccount($dataUserAccount);
                        Flight::json([
                            "message" => "Cuenta activada",
                        ]);
                        break;
                        
                    case "invalid":
                        ExceptionAccount::invalidCode();
                        break;
                }

                $this->commit();
                
            } catch (Exception $error) {
                $this->rollBack();
                $errorCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($errorCode, json_encode([
                    "error" => $error->getMessage(),
                ]));

            } finally {
                $this->closeConnection();

            }
        }
    }



?>