<?php
session_start();
require("pdo_connect.php");
require("authorization/checking_authorization.php");
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style_css/authorization_css.css">
    <title>Авторизация</title>
</head>
<body>
    <div class="main_container">
        <form action="authorization.php" method="post" class="form_authorization">
            <h1>Авторизация</h1>
            <input type="text" name="login" id="login" placeholder="Логин">
            <input type="password" name="password" id="password" placeholder="Пароль">
            <input type="submit" name="button" id="button" class="button" value="Войти"> 
        </form>
    </div>
</body>
</html>