<?php
// Проверка сессии, безопасности, валидации данных, etc.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'pdo_connect.php';
    
    $assignmentId = $_POST['assignmentId'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("UPDATE assignments SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $assignmentId]);
        
        echo json_encode(['success' => true, 'assignment' => ['id' => $assignmentId, 'title' => $title, 'description' => $description]]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка обновления задачи: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Некорректный запрос.']);
}
?>
