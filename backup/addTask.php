<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

// Проверка, что это POST-запрос и пользователь аутентифицирован
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    // Предполагаем, что у вас есть переменная в сессии $_SESSION['user']['id'] для ID пользователя
    $userId = $_SESSION['user']['id'];
    
    // Получение данных из POST-запроса
    $tabId = isset($_POST['tabId']) ? $_POST['tabId'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    // Проверка, что ID вкладки и описание задачи предоставлены
    if ($tabId && $description) {
        try {
            // Добавление задачи в таблицу 'assignments'
            $stmt = $pdo->prepare("INSERT INTO assignments (title, description, teacher_id, created_at, updated_at) VALUES (:title, :description, :teacher_id, NOW(), NOW())");
            $stmt->execute([
                ':title' => '', // Если у вас есть поле для заголовка задачи, заполните его данными или удалите эту строку
                ':description' => $description,
                ':teacher_id' => $userId
            ]);
            $assignmentId = $pdo->lastInsertId();

            // Связывание задачи с вкладкой
            $stmt = $pdo->prepare("INSERT INTO tab_assignments (tab_id, assignment_id) VALUES (:tab_id, :assignment_id)");
            $stmt->execute([
                ':tab_id' => $tabId,
                ':assignment_id' => $assignmentId
            ]);

            // Возвращаем успешный ответ с ID новой задачи
            echo json_encode(['success' => true, 'taskId' => $assignmentId]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tab ID or description not provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
