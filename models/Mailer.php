<?php

    namespace Models;

    use Flight;
    use PHPMailer\PHPMailer\{PHPMailer, Exception, SMTP};
    

    class Mailer {

        public static function sendMailer(string $email, string $asunto, string $cuerpo) :bool {
            // inicialización de la instancia mail; pasar true para mostrar excepciones
            $mail = new PHPMailer(true);

            try {
                // server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // para mostrar mensajes, en producción cambiar por DEBUG_OFF
                $mail->isSMTP();
                $mail->Host = $_ENV['MAIL_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['MAIL_USER'];
                $mail->Password = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $_ENV['MAIL_PORT'];

                // preparación del envio del correo
                $mail->setFrom($_ENV['MAIL_USER'], 'SV TECH');

                // dirección receptora del correo
                $mail->addAddress($email);
                // // enviar copia de correo
                // $mail->addReplyTo($_ENV['MAIL_USER'], 'New register');

                $mail->isHTML(true);
                $mail->Subject = mb_convert_encoding($asunto, 'ISO-8859-1', 'UTF-8'); // agregar asunto

                // cuerpo del correo
                // $mail->Body = utf8_decode($cuerpo);
                $mail->Body = mb_convert_encoding($cuerpo, 'ISO-8859-1', 'UTF-8');

                if ($mail->send()) return true;

                return false;
  
            } catch (Exception $error) {
                Flight::halt(404, json_encode([
                    "message" => "Error: ". $mail->ErrorInfo,
                ]));
            }
        }
    }


?>