<?php
// createAssignment.php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $tab_id = isset($_POST['tab_id']) ? intval($_POST['tab_id']) : null;

    if (empty($title) || empty($description) || empty($tab_id)) {
        echo json_encode(['success' => false, 'message' => 'Некоторые поля пустые.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO assignments (title, description, created_at, updated_at) VALUES (:title, :description, NOW(), NOW())");
        $stmt->execute([':title' => $title, ':description' => $description]);
        $assignment_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO tab_assignments (tab_id, assignment_id) VALUES (:tab_id, :assignment_id)");
        $stmt->execute([':tab_id' => $tab_id, ':assignment_id' => $assignment_id]);

        $stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = :assignment_id");
        $stmt->execute([':assignment_id' => $assignment_id]);
        $newAssignment = $stmt->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();

        if ($newAssignment) {
            echo json_encode(['success' => true, 'assignment' => $newAssignment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Задание не найдено.']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Некорректный тип запроса.']);
}
?>
