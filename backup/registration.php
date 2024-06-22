<?php
session_start();
require("pdo_connect.php");
require("workpage/roleVerification.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css\index_registration_CSS.css">
    <title>Регистрация</title>
</head>
<body>
    <div class="container_registration">
        <h2>Регистрация</h2>
        <form action="registration.php" method="post" class="form_registration">
            <input type="text" name="login" id="login" placeholder="Логин">
            <input type="password" name="password" id="password" placeholder="Пароль">
            <input type="text" name="name" id="name" placeholder="Имя">
            <input type="text" name="surname" id="surname" placeholder="Фамилия">
            <input type="email" name="email" id="email" placeholder="Email">
            <input type="text" name="phone" id="phone" placeholder="Номер телефона">
            <div class="checkbox-container">
                <div class="checkbox-group">
                    <input type="checkbox" id="checkbox1" name="agreement">
                    <label for="checkbox1">Согласие на обработку данных</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="checkbox2" name="notifications">
                    <label for="checkbox2">Получение уведомлений</label>
                </div>
            </div>
            <input type="submit" id="submit" class="submit" value="Зарегистрироваться">
        </form>
    </div>
</body>
</html>
