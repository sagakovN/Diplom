<?php
session_start();
require('pdo_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['assignmentId'])) {
    $assignmentId = $_GET['assignmentId'];
    $role = $_SESSION['user']['role']; // Получение роли пользователя из сессии

    try {
        $stmt = $pdo->prepare("SELECT id, assignment_id, file_name, file_path, user_id, uploaded_by_role FROM tab_files WHERE assignment_id = ?");
        $stmt->execute([$assignmentId]);

        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Логирование данных, которые возвращаются
        error_log('Файлы, полученные из базы данных: ' . json_encode($files));

        echo json_encode(['success' => true, 'files' => $files]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
