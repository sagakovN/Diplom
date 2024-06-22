<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $assignmentId = isset($_POST['assignmentId']) ? $_POST['assignmentId'] : null;
    $topicName = isset($_POST['topicName']) ? $_POST['topicName'] : '';

    if ($assignmentId && $topicName) {
        try {
            $pdo->beginTransaction();

            // Обновление контента в таблице 'assignments'
            $stmt = $pdo->prepare("UPDATE assignments SET title = :title, updated_at = NOW() WHERE id = :assignmentId");
            $stmt->execute([
                ':title' => $topicName,
                ':assignmentId' => $assignmentId
            ]);

            // Получение связанного tab_id
            $stmt = $pdo->prepare("SELECT tab_id FROM tab_assignments WHERE assignments_id = :assignmentId");
            $stmt->execute([':assignmentId' => $assignmentId]);
            $tabId = $stmt->fetchColumn();

            $pdo->commit();
            
            echo json_encode(['success' => true, 'assignmentId' => $assignmentId, 'tabId' => $tabId]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Assignment ID или topic name не предоставлены.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
