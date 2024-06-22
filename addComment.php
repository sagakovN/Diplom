<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignmentId = $_POST['assignmentId'];
    $userId = $_SESSION['user']['id'];
    $comment = $_POST['comment'];

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (assignment_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$assignmentId, $userId, $comment])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении комментария.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
