<?php
// Получение данных пользователя из сессии
$userData = $_SESSION['user'];

// Проверка роли пользователя
$isTeacher = $userData['role'] === 'teacher';
?>