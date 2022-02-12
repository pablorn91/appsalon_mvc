<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token) {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){

        //crear el objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = 'bbb894252431fa';
        $mail->Password = '2acd6537e4a8e7';

        $mail->setFrom('cuentas@appsalon.com');
        $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
        $mail->Subject = 'Confirma tu cuenta';

        //Set HTML
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';

        //Establecer contenido del mail
        $contenido = "<html>";
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong>. Has creado tu cuenta en App Salon, sólo debes confirmarla presionando el siguiente enlace</p>";
        $contenido .= "<p>Presiona aquí: <a href='http://appsalon_php_mvc_js_sass.test/confirmar-cuenta?token=" . $this->token . "'>Confirmar Cuenta</a> </p>";
        $contenido .= "Si no solicitaste esta cuenta, puedes ignorar el mensaje";
        $contenido .= "</html>";

        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }

    public function enviarInstrucciones(){
         //crear el objeto de email
         $mail = new PHPMailer();
         $mail->isSMTP();
         $mail->Host = 'smtp.mailtrap.io';
         $mail->SMTPAuth = true;
         $mail->Port = 2525;
         $mail->Username = 'bbb894252431fa';
         $mail->Password = '2acd6537e4a8e7';
 
         $mail->setFrom('cuentas@appsalon.com');
         $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
         $mail->Subject = 'Reestablece tu password';
 
         //Set HTML
         $mail->isHTML(true);
         $mail->CharSet  = 'UTF-8';
 
         //Establecer contenido del mail
         $contenido = "<html>";
         $contenido .= "<p><strong>Hola " . $this->nombre . "</strong>. Has solicitado reestablecer tu password, sigue el siguiente enlace para hacerlo.</p>";
         $contenido .= "<p>Presiona aquí: <a href='http://appsalon_php_mvc_js_sass.test/recuperar?token=" . $this->token . "'>Reestablecer Password</a> </p>";
         $contenido .= "Si no solicitaste este cambio, puedes ignorar el mensaje";
         $contenido .= "</html>";
 
         $mail->Body = $contenido;
 
         //Enviar el mail
         $mail->send();
    }
}