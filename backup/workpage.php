<?php
session_start();
require("pdo_connect.php");
include("roleVerification.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style_workpage.css">
    <title>Электронная таблица</title>
</head>
<body>
    <header>
        <div class="header_action">
            <a href="user.php" class="user_account"><?= htmlspecialchars($userData['first_name']) ?></a>
            <a href="index.php" class="exit">Выход</a>
        </div>
    </header>
    <div class="container">
    <div class="sidebar">
        <ul id="tabsList"></ul>
        <button id="createTabButton">Создать вкладку</button>
    </div>
    <div class="container_tasks">
        <button id="createTasksButton">+</button>
        <div class="main_container"></div>
    </div>
    </div>
    <footer>
    </footer>
    <script src='action_on_tab.js'></script>
</body>
</html>