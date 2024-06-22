<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    if (!empty($userId)) {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id AND is_read = FALSE");
            $stmt->execute([':user_id' => $userId]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID не предоставлен.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
