<?php
session_start(); // Начинаем сессию
require("pdo_connect.php");
include("roleVerification.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style_group.css">
    <title>Пользователь</title>
</head>
<body>
    <header>
        <div class="button_header">
            <div class="left_header">
                <a href="workpage.php">Таблицы</a>
            </div>
            <div class="right_header">
                <a href="user.php" id="name_user"><?= htmlspecialchars($userData['first_name']) ?></a>
                <a href="index.php">Выход</a>
            </div>
        </div>
    </header>
    <div class="main_container">
        <div class="chat">
            <button id="createGroup">Создать группу</button>
            <div class="groups-container"></div>
        </div>
        <div class="massenges">
            <div class="button-invate"><button class="invate">Пригласить</button></div>
            <div class="users_invated"></div>
        </div>
    </div>
    <footer></footer>
    <script src="action_on_group.js"></script>
</body>
</html>