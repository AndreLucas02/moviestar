<?php

require_once("globals.php");
require_once("db.php");
require_once("models/User.php");
require_once("models/Message.php");
require_once("dao/UserDAO.php");

$message = new Message($BASE_URL);

$userDao = new UserDAO($conn, $BASE_URL);

// verifica o tipo do formulário

$type = filter_input(INPUT_POST, "type");

if ($type === 'register') {

    $name = filter_input(INPUT_POST, 'name');
    $lastname = filter_input(INPUT_POST, 'lastname');
    $email = filter_input(INPUT_POST, 'email');
    $password = filter_input(INPUT_POST, 'password');
    $confirmpassword = filter_input(INPUT_POST, 'confirmpassword');

    //verificação dos dados minimos
    if ($name && $lastname && $email && $password) {

        if ($password === $confirmpassword) {

            //verificar e email ja está cadastrado

            if ($userDao->findByEmail($email) === false) {

                $user = new User();

                //criação token e senha

                $userToken = $user->generateToken();
                $finalPassword = $user->generatePassword($password);

                $user->name = $name;
                $user->lastname = $lastname;
                $user->email = $email;
                $user->password = $finalPassword;
                $user->token = $userToken;

                $auth = true;

                $userDao->create($user, $auth);

            } else {

                $message->setMessage("Usuário já cadastrado, tente outro email", "error", "back");
            }

        } else {
            $message->setMessage("As senhas não são iguais", "error", "back");
        }

    } else {
        // messagem de erro, de dados faltantes
        $message->setMessage("Por favor, preencha todos os campos", "error", "back");
    }
    
} else if ($type === 'login') {

    $email = filter_input(INPUT_POST, 'email');
    $password = filter_input(INPUT_POST, 'password');

    if ($userDao->authenticateUser($email, $password)) {
        //
        $message->setMessage("Seja bem vindo !", "success", "editprofile.php");


    } else {

        $message->setMessage("Usuario ou senha estão incorreto", "error", "back");
    }
} else {

    $message->setMessage("Informações invalidas", "error", "index.php");
}
