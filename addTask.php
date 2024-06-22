<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $tabId = isset($_POST['tabId']) ? $_POST['tabId'] : null;
    $topicName = isset($_POST['topicName']) ? $_POST['topicName'] : '';

    if ($tabId && $topicName) {
        try {
            // Добавление контента в таблицу 'assignments'
            $stmt = $pdo->prepare("INSERT INTO assignments (title, created_at, updated_at) VALUES (:title, NOW(), NOW())");
            $stmt->execute([
                ':title' => $topicName
            ]);
            $assignmentId = $pdo->lastInsertId();

            // Добавление связи в таблицу 'tab_assignments'
            $stmt = $pdo->prepare("INSERT INTO tab_assignments (tab_id, assignments_id) VALUES (:tab_id, :assignments_id)");
            $stmt->execute([
                ':tab_id' => $tabId,
                ':assignments_id' => $assignmentId
            ]);

            echo json_encode(['success' => true, 'assignmentId' => $assignmentId, 'tabId' => $tabId]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tab ID или topic name не предоставлены.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
