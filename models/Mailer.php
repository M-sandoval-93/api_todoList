<?php

    namespace Models;

    use PHPMailer\PHPMailer\{PHPMailer, Exception, SMTP};
    use Templates\MailTemplate;

    class Mailer {
        // method to send mail
        // @param email -> email al cual se enviará el correo
        // @param asunto -> asunto del correo
        // @param cuerpo -> cuerpo del correo
        public static function sendMailer(string $email, string $asunto, string $cuerpo) :bool {
            // inicialización de la instancia mail; pasar true para mostrar excepciones
            $mail = new PHPMailer(true);

            try {
                // server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // opción para mostrar mensajes de debug
                $mail->SMTPDebug = SMTP::DEBUG_OFF; 
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

                $mail->isHTML(true);
                $mail->Subject = mb_convert_encoding($asunto, 'ISO-8859-1', 'UTF-8');

                // cuerpo del correo
                $mail->Body = mb_convert_encoding($cuerpo, 'ISO-8859-1', 'UTF-8');

                if ($mail->send()) return true;

                return false;
  
            } catch (Exception $error) {
                throw new Exception($mail->ErrorInfo, 500);
            }
        }

        public static function sendActivationCode(string $userEmail, string $userName, int $activationCode) :void {
            $mailTemplate = new MailTemplate();
            $link = $_ENV['VALIDATE_LINK'] . "?email=" . urlencode($userEmail);
            $bodyMail = $mailTemplate->getTemplateSendCode($userName, $activationCode, $link);
            $subject = "Activa tu cuenta de usuario";

            if (!Mailer::sendMailer($userEmail, $subject, $bodyMail)) {
                throw new Exception("Problemas para enviar codigo", 500);
            }
        }

        // método estático para enviar código por mail
        // static méthod to send code by mail
        // @code -> codigo para validar la cuenta
        // @data -> objeto que requiere; userEmail - userName
        // public static function setEmailWithCode(int $code, object $data) :void {
        //     $mailTemplate = new MailTemplate();
        //     $link = $_ENV['VALIDATE_LINK'] . "?email=" . urlencode($data->userEmail);
        //     $bodyMail = $mailTemplate->getTemplateSendCode($data->userName, $code, $link);
        //     $subject = "Cógido para validar cuenta de usuario";

        //     if (!self::sendMailer($data->userEmail, $subject, $bodyMail)) {
        //         throw new Exception("Problemas para enviar codigo", 500);
        //     }
        // }

    }


?>