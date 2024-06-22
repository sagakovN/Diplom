<?php
session_start();
header('Content-Type: application/json');

// Проверка, что у пользователя есть сессия и определена роль
if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
    // Установка флага isTeacher в зависимости от роли пользователя
    $isTeacher = $_SESSION['user']['role'] === 'teacher';
} else {
    // По умолчанию, если роль не установлена, пользователь не является учителем
    $isTeacher = false;
}

// Отправка флага isTeacher в виде JSON
echo json_encode(['isTeacher' => $isTeacher]);
