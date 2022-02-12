<?php
namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login (Router $router) {

        isLogin();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)){
               //Comprobar que exista el usuario
               $usuario = Usuario::where('email', $auth->email );

               if ($usuario){
                   //Verificar el password
                   if ( $usuario->comprobarPasswordAndVerificado($auth->password) ) {
                       if (!isset($_SESSION)) {
                        session_start();
                       }

                       $_SESSION['id'] = $usuario->id;
                       $_SESSION['nombre'] = $usuario->nombre. " " . $usuario->apellido;
                       $_SESSION['email'] = $usuario->email;
                       $_SESSION['login'] = true;

                        //Redireccionamiento
                        if ($usuario->admin === "1"){
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');

                        } else {
                           header('Location: /cita');
                        }

                       debuguear($_SESSION);
                   }
               } else {
                   Usuario::setAlerta('error', 'Usuario no encontrado');
               }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }

    public static function logout () {

        if (!isset($_SESSION)) {
            session_start();
           }
        $_SESSION = [];

        header('Location: /');
    }

    public static function olvide (Router $router) {

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if (empty($alertas)){
                $usuario = Usuario::where('email', $auth->email);

                if ($usuario && $usuario->confirmado === '1') {
                    
                    //Generar un token
                    $usuario->crearToken();
                    $usuario->guardar();

                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    Usuario::setAlerta('exito', 'Reestablecimiento de contraseña enviado');
                    Usuario::setAlerta('exito', 'Revisa tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            }
        }
        
        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar (Router $router) {
        
        $alertas = [];
        $error = false;


        $token = s($_GET['token']);
        //Buscar usuario por su token
        $usuario = Usuario::where('token', $token);

        // debuguear($usuario);

        if (empty($usuario)){
            Usuario::setAlerta('error', 'Token No Válido');
            $error = true;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //leer el nuevo password y guardarlo
            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            if (empty($alertas)) {

                $usuario->password = null;

                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();

                if ($resultado){
                    header('Location: /');
                }

                debuguear($usuario);   

            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);
    }

    public static function crear (Router $router) {

        $usuario = new Usuario;
        $alertas = [];
        
        if( $_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if (empty($alertas)){

               $resultado = $usuario->existeUsuario();
                if ($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                } else {
                /*No está registrado*/

                    //Hashear el password
                    $usuario->hashPassword();
                    //Generar un token único
                    $usuario->crearToken();
                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    //Crear el Usuario
                    $resultado = $usuario->guardar();

                    if ($resultado){
                        header('Location: /mensaje');
                    }

                    // debuguear($usuario);
                }
            }

        }

        $router->render('auth/crear-cuenta',[
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje (Router $router) {
        $router->render('auth/mensaje');
    }

    public static function confirmar (Router $router) {

        $alertas = [];
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)){
            //mostrar mensaje de error
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            //modificar al usuario confirmado
            $usuario->confirmado =  '1';
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }

        //Obtener alertas
        $alertas = Usuario::getAlertas();

        //Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }

}