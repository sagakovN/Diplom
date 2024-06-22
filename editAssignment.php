<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    $assignmentId = isset($_POST['assignmentId']) ? $_POST['assignmentId'] : null;
    $title = isset($_POST['title']) ? $_POST['title'] : '';

    if ($assignmentId && $title) {
        try {
            $stmt = $pdo->prepare("UPDATE assignments SET title = :title, updated_at = NOW() WHERE id = :assignmentId");
            $stmt->execute([
                ':title' => $title,
                ':assignmentId' => $assignmentId
            ]);

            // Получение связанного tab_id
            $stmt = $pdo->prepare("SELECT tab_id FROM tab_assignments WHERE assignments_id = :assignmentId");
            $stmt->execute([':assignmentId' => $assignmentId]);
            $tabId = $stmt->fetchColumn();

            echo json_encode(['success' => true, 'tabId' => $tabId]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Assignment ID или название задачи не предоставлены.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
