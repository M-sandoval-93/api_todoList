<?php

    namespace Models;

    use Firebase\JWT\JWT;
    use Flight;
    use PDO;
    use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

    class Auth extends Connection {

        public function __construct() {
            parent::__construct();
        }

        // método para generar token
        // method to generate token
        private function setToken(object $data) :string {
            $dateNow = strtotime("now");
            $key = $_ENV['JWT_KEY'];
            $payload = [
                'exp' => $dateNow + 21600,
                // 'exp' => $dateNow + 10,
                'id_usuario' => $data->id_user,
                'id_privilege' => $data->id_privilege
            ];

            return JWT::encode($payload, $key, 'HS256');
        }

        // método para verificar cuenta de usuario
        // mathod to verify user account
        public function auth() :void {
            $user = Flight::request()->data->user;
            $pass = Flight::request()->data->pass;

            $queryVerifyAccount = $this->preConsult("select * from user where email_user = ?");

            try {
                $queryVerifyAccount->execute([$user]);

                if ($queryVerifyAccount->rowCount() === 1) {
                    $userAccount = $queryVerifyAccount->fetch(PDO::FETCH_OBJ);

                    if ($pass !== $userAccount->password_user) {
                        throw new Exception("Contraseña incorrecta", 401);
                    }

                    $this->array = [
                        'token' => $this->setToken($userAccount),
                        'userName' => $userAccount->name_user,
                        'privilege' => $userAccount->id_privilege
                    ];

                    Flight::json($this->array);
                    return;
                }

                throw new Exception("La cuenta de usuario no existe", 406);

            } catch (Exception $error) {
                $statusCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($statusCode, json_encode([
                    "message" => "Error: ". $error->getMessage()
                ]));

            } finally {
                $this->closeConnection();
            }            
        }

        // método para obtener los datos decodificados del token
        // method to obtanin the decoded data from the token
        protected function getToken() :object {
            // obtención de las cabeceras de la petición
            // getting the request headers
            $headers = apache_request_headers();

            // condición para verificar existencia de la cabecera de autorización
            // condition to verify the existence of the authorization header
            if (!isset($headers['Authorization'])) {
                throw new Exception("Acceso denegado", 401);
            }

            // obtención del token
            // getting the token
            $authorization = $headers['Authorization'];
            $authorizationArray = explode(" ", $authorization);
            $token = $authorizationArray[1];

            // comprovación de la estructura del token
            // checking the token structure
            if (count($authorizationArray) !== 2 || $authorizationArray[0] !== 'Bearer') {
                throw new Exception("Formato de token inválido", 401);
            }

            // obtención de la clave de codificación / decodificación del token
            // obtaining the token encryption / decryption key
            $key = $_ENV['JWT_KEY'];

            try {
                // retorno de los datos decodificados del token
                // return decoded token data
                return JWT::decode($token, new Key($key, 'HS256'));

            // control de error por expiración del token
            // token expiration error handling
            } catch (ExpiredException $expiredException) {
                Flight::halt(401, json_encode([
                    "message" => "Error: ". $expiredException->getMessage()
                ]));

            // control de error en el flujo de la función
            // error handling in function flow
            } catch (Exception $error) {
                $statusCode = $error->getCode() ? $error->getCode() : 404;
                Flight::halt($statusCode, json_encode([
                    "message" => "Error: ". $error->getMessage()
                ]));
            }
        }

        // método para validar token de acceso
        // method to validate access token
        protected function validateToken() :void {
            // obtención de los datos decodificados del token
            // getting the decoded data from the token
            $idUser = $this->getToken()->id_usuario;
            $querySQL = $this->preConsult("select id_privilege from user where id_user = ?");

            try {
                $querySQL->execute([$idUser]);
                if ($querySQL->rowCount() !== 1) 
                    throw new Exception("Usuario inválido", 401);

            } catch (Exception $error) {
                $statusCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($statusCode, json_encode([
                    "message" => "Error: ". $error->getMessage(),
                ]));

            } finally {
                $this->closeConnection(); // revisar si mantengo el cierre de la conexión
            }
        }

        // método para verificar comprobar privilegios
        // method to check privileges
        protected function validatePrivilege(array $necessaryPrivilege) :void {
            $privilege = $this->getToken()->id_privilege;

            try {
                if (!in_array($privilege, $necessaryPrivilege, true)) 
                    throw new Exception("Privilegios insuficientes", 403);

            } catch (Exception $error) {
                $statusCode = $error->getCode() ? $error->getCode() : 404;

                Flight::halt($statusCode, json_encode([
                    "message" => "Error: ". $error->getMessage(),
                ]));
            }
        }
    }

?>