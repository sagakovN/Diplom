<?php
if (!isset($_SESSION['user'])) {
    // Если в сессии нет данных о пользователе, перенаправляем на страницу входа
    header("Location: authorization.php");
    exit;
}
// Используем другое имя переменной для данных пользователя из сессии
$userData = $_SESSION['user'];
?>