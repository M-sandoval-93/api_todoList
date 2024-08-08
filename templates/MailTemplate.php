<?php
    namespace Templates;

    use Exception;

    class MailTemplate {

        private function templateSendCode(string $userName, string $code, string $validationLink) :string {
            // ruta del archivo de la plantilla html
            $templatePath = __DIR__ . "/mailTemplate.html";

            // verificar que el archivo de la plantilla existe
            if (!file_exists($templatePath)) throw new Exception("La plantilla de correo no se encuentra");

            // cargar el contenido del archivo plantilla
            $templateContent = file_get_contents($templatePath);

            // remplazar los placeholders con valores dinámicos
            $templateContent = str_replace('{{USER_NAME}}', $userName, $templateContent);
            $templateContent = str_replace('{{CODE}}', $code, $templateContent);
            $templateContent = str_replace('{{VALIDATION_LINK}}', $validationLink, $templateContent);

            // devolver el contenido de la plantilla personalizada
            return $templateContent;
        }

        public function getTemplateSendCode(string $userName, string $code, string $validationLink) :string {
            return $this->templateSendCode($userName, $code, $validationLink);
        }
    }
?>