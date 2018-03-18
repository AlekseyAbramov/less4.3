<?php
    session_start();
    if (!empty($_POST)) {
        if(!empty($_POST['register'])) {
             if ($_POST['login'] && !$_POST['password']) {
                 echo 'Вы не ввели пароль';
             }
             if (!$_POST['login'] && $_POST['password']) {
                  echo 'Вы не ввели логин';
             }
             if (!$_POST['login'] && !$_POST['password']) {
                   echo 'Вы не ввели лигин и пароль';
             }
             if ($_POST['login'] && $_POST['password']) {
                  $user = strip_tags($_POST['login']);
                  $password = password_hash(strip_tags($_POST['password']), PASSWORD_DEFAULT);
                  $pdo = new PDO("mysql:host=localhost;dbname=netologi;charset=utf8", "root", "");
                  $sth = $pdo->prepare("SELECT `login` FROM `user` WHERE login=?");
                  $sth->execute(array($user));
                  $w = $sth->fetchColumn();
                  if($w) {
                     echo 'Такой пользователь уже есть. Введите другой логин.';
                 } else {
                     $sql_add_user = "INSERT INTO `user`(`login`, `password`) VALUES ('$user','$password')";
                     $pdo->query($sql_add_user);
                     echo 'Вы успешно зарегистрировались, можите войти в систему.';
                  }
             }   
        }
        
        if(!empty($_POST['sign_in'])){
            if(!$_POST['login'] && !$_POST['password']) {
                echo 'Вы не ввели логин и пароль';
            }
            if($_POST['login'] && !$_POST['password']) {
                echo 'Вы не ввели пароль';
            }
            if(!$_POST['login'] && $_POST['password']) {
                echo 'Вы не ввели логин';
            }
            if($_POST['login'] && $_POST['password']) {
                $user = strip_tags($_POST['login']);
                $password = $_POST['password'];
                $pdo = new PDO("mysql:host=localhost;dbname=netologi;charset=utf8", "root", "");
                $sth = $pdo->prepare("SELECT `id`, `login`, `password` FROM `user` WHERE login=?");
                $sth->execute(array($user));
                $w = $sth->fetch();
                if (!$w) {
                    echo 'Такого пользователя нет';
                } else {
                    if($w['login'] == $user && password_verify($password, $w['password'])) {
                        $_SESSION['user'] = $user;
                        $_SESSION['id'] = $w['id'];
                        header('Location: index.php');
                    } else {
                        echo 'Вы ввели не правильный пароль';
                    }
                }
            }
        }
    }
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <p>Введите данные для регистрации или войдите, если уже регистрировались:</p>

        <form method="POST">
            <input type="text" name="login" placeholder="Логин" />
            <input type="password" name="password" placeholder="Пароль" />
            <input type="submit" name="sign_in" value="Вход" />
            <input type="submit" name="register" value="Регистрация" />
        </form>
    </body>
</html>
