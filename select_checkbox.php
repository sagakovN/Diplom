<?php
// Подключение к базе данных
require 'pdo_connect.php';

// Получаем список вкладок из базы данных
$tabs = $pdo->query("SELECT id, name FROM tabs")->fetchAll(PDO::FETCH_ASSOC);

?>