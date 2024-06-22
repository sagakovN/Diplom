<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json'); // Указываем, что ответ будет в формате JSON

// Предполагаем, что user_id хранится в $_SESSION['user']['id']
if (isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];

    try {
        $sql = "SELECT id, name, teacher_id, created_at FROM tabs WHERE teacher_id = :teacher_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':teacher_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $tabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'tabs' => $tabs]);
    } catch (PDOException $e) {
        // Если произошла ошибка, отправляем ее в ответе
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User ID is not set in session.']);
}
?>
