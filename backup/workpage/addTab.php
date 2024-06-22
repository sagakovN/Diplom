<?php
session_start();
require("pdo_connect.php");

// Проверяем, что запрос AJAX и переданы все необходимые параметры
if (isset($_POST['tabName']) && isset($_SESSION['user']['user_id'])) {
    $tabName = $_POST['tabName'];
    $userId = $_SESSION['user']['user_id']; // Использование user_id из сессии

    // Подготовка SQL запроса
    $sql = "INSERT INTO tabs (name, teacher_id, created_at) VALUES (:name, :teacher_id, NOW())";
    $stmt = $pdo->prepare($sql);
    
    // Привязка параметров и выполнение
    $stmt->execute([':name' => $tabName, ':teacher_id' => $userId]);

    // Отправляем ответ об успехе
    echo json_encode(['success' => true]);
} else {
    // Отправляем ответ об ошибке, если параметры не переданы
    echo json_encode(['success' => false, 'message' => 'Data is missing.']);
}
?>
