<?php
session_start(); // Начинаем сессию
require("user/checking_user_info.php");
require("pdo_connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style_user.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Пользователь</title>
</head>
<body>
    <header>
        <div class="button_header">
            <div class="left_header">
                <a href="workpage.php">Таблицы</a>
                <a href="group.php">Группа</a>
            </div>
            <div class="right_header">
                <a href="user.php" id="name_user"><?= htmlspecialchars($userData['first_name']) ?></a>
                <a href="index.php">Выход</a>
            </div>
        </div>
    </header>
    <div class="main_container">
        <div class="container" id="number_1">
            <div class="changes">Изменения</div>
            <div class="chat">
            </div>
            <div class="anything">Что то еще</div>
        </div>
        <div class="container" id="number_2">
            <div class="image"><img src="logo/219983.png" alt=""></div>
            <div class="role_user"><p><?= htmlspecialchars($userData['role'])?></p></div>
            <div class="users_info">
                <div class="info_user">
                    <p>Имя:</p>
                    <p>Фамилия:</p>
                    <p>Email:</p>
                    <p>Номер телефона:</p>
                </div>
                <div class="info_for_database">
                    <p><?= htmlspecialchars($userData['first_name']) ?></p>
                    <p><?= htmlspecialchars($userData['last_name']) ?></p>
                    <p><?= htmlspecialchars($userData['email']) ?></p>
                    <p><?= htmlspecialchars($userData['phone_number']) ?></p>
                </div>
                
            </div>
        </div>
        <div class="container" id="number_3">
            <div class="group_info">
                <h2>Группа Информация</h2>
            </div>
            <div class="calendar" id="datepicker"></div>
        </div>
    </div>
    <footer></footer>
    <script src="scripts/calendar.js"></script>
</body>
</html>