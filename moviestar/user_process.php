<?php 

    require_once("globals.php");
    require_once("db.php");
    require_once("models/User.php");
    require_once("models/Message.php");
    require_once("dao/UserDAO.php");

    $message = new Message($BASE_URL);

    $userDao = new UserDAO($conn, $BASE_URL);

    //reagatar o tipo do formulário
    $type = filter_input(INPUT_POST, "type");

    if ($type === "update") {
        //reagata dados do usuário
        $userData = $userDao->verifyToken();

        //receber dados do post
        $name = filter_input(INPUT_POST, "name");
        $lastname = filter_input(INPUT_POST, "lastname");
        $email = filter_input(INPUT_POST, "email");
        $bio = filter_input(INPUT_POST, "bio");

        //criar novo usuário
        $user = new User();

        //preencher dados do usuário
        $userData->name = $name;
        $userData->lastname = $lastname;
        $userData->email = $email;
        $userData->bio = $bio;

        //upload de imagem
        if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
            
            $image = $_FILES["image"];
            $imageTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $jpgArray = ['image/jpeg', 'image/jpg'];

            //verificar o tipo de imagem
            if (in_array($image['type'], $imageTypes)) {

                //verificar se é jpg
                if (in_array($image['type'], $jpgArray)) {
                    
                    
                    $imageFile = imagecreatefromjpeg($image['tmp_name']);

                   
                //image png
                } else {

                   
                    $imageFile = imagecreatefrompng($image['tmp_name']);
                        
                }

                $imageName = $user->imageGenerateName();

                imagejpeg($imageFile, "./img/users/" . $imageName, 100);

                $userData->image = $imageName;

                


            } else {

                $message->setMessage("Tipo inválido de imagem, insira png ou jpg", "error", "back");
            }
        }

        $userDao->update($userData);

    // atualizar a senha do usuário    
    } else if ($type === "changepassword") {

        $password = filter_input(INPUT_POST, "password");
        $confirmpassword = filter_input(INPUT_POST, "confirmpassword");
       
        $userData = $userDao->verifyToken();
        $id = $userData->id;

        if ($password === $confirmpassword) {

            //criar novo usuário
            $user = new User;

            $finalPassword = $user->generatePassword($password);

            $user->password = $finalPassword;
            $user->id = $id;

            $userDao->changePassword($user);


        } else {

            $message->setMessage("As senhas não são iguais", "error", "back");

        }


    } else {

        $message->setMessage("Informações invalidas", "error", "index.php");
    }
