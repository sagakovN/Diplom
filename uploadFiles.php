<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assignmentId'])) {
    $assignmentId = $_POST['assignmentId'];
    $userId = $_SESSION['user']['id'];
    $role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : null;

    // Директория для загрузки файлов
    $uploadDir = 'uploads/';

    // Проверка, существует ли директория, и если нет, создание
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    try {
        foreach ($_FILES['fileUpload']['error'] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmpName = $_FILES['fileUpload']['tmp_name'][$key];
                $name = basename($_FILES['fileUpload']['name'][$key]);
                $filePath = $uploadDir . $name;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $stmt = $pdo->prepare("INSERT INTO tab_files (assignment_id, user_id, file_name, file_path, uploaded_by_role) VALUES (:assignment_id, :user_id, :file_name, :file_path, :uploaded_by_role)");
                    $stmt->execute([
                        ':assignment_id' => $assignmentId,
                        ':user_id' => $userId,
                        ':file_name' => $name,
                        ':file_path' => $filePath,
                        ':uploaded_by_role' => $role // Добавляем роль, кто загрузил файл
                    ]);
                } else {
                    $response['message'] .= "Ошибка при загрузке файла: $name. ";
                }
            } else {
                $response['message'] .= "Ошибка при загрузке файла с кодом $error. ";
            }
        }

        $response['success'] = true;
        $response['message'] .= 'Файлы успешно загружены.';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
    }

    echo json_encode($response);
} else {
    $response['message'] = 'Неверный запрос.';
    echo json_encode($response);
}
?>
