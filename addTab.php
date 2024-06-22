<?php
session_start();
require("pdo_connect.php");
include("roleVerification.php"); // Подключение вашей функции проверки роли

header('Content-Type: application/json');

if (!$isTeacher) { // Использование переменной $isTeacher из подключенного файла
    echo json_encode(['success' => false, 'message' => 'Only teachers can create tabs.']);
    exit;
}

if (isset($_POST['tabName'])) {
    if (isset($_SESSION['user']['id'])) {
        $tabName = $_POST['tabName'];
        $userId = $_SESSION['user']['id']; // Обращаемся к 'id', а не к 'user_id'

        try {
            $sql = "INSERT INTO tabs (name, teacher_id, created_at) VALUES (:name, :teacher_id, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $tabName, ':teacher_id' => $userId]);
            $tabId = $pdo->lastInsertId();
            
            echo json_encode(['success' => true, 'tabId' => $tabId]);
        } catch (PDOException $e) {
            error_log("Ошибка подключения к базе данных: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID is not set in session.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Tab name is not set in the request.']);
}
?>
