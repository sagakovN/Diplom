<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fileId']) && isset($_POST['assignmentId'])) {
    $fileId = $_POST['fileId'];
    $assignmentId = $_POST['assignmentId'];
    $userId = $_SESSION['user']['id'];
    $userRole = $_SESSION['user']['role'];

    try {
        // Проверка, что пользователь имеет право удалять файл
        if ($userRole === 'teacher') {
            $stmt = $pdo->prepare("SELECT file_path FROM tab_files WHERE id = :file_id AND assignment_id = :assignment_id");
        } else {
            $stmt = $pdo->prepare("SELECT file_path FROM tab_files WHERE id = :file_id AND assignment_id = :assignment_id AND user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->bindValue(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            // Удалить файл из файловой системы
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }

            // Удалить запись о файле из базы данных
            $stmt = $pdo->prepare("DELETE FROM tab_files WHERE id = :file_id");
            $stmt->execute([':file_id' => $fileId]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Файл не найден.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
