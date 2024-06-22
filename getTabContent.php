<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['tabId'])) {
    $tabId = $_GET['tabId'];
    $userId = $_SESSION['user']['id'];
    $userRole = $_SESSION['user']['role'];
    error_log("Received tabId: " . $tabId);

    try {
        // Получить информацию о задании через связь tab_assignments
        $stmt = $pdo->prepare("SELECT a.* FROM assignments a JOIN tab_assignments ta ON a.id = ta.assignments_id WHERE ta.tab_id = :tab_id");
        $stmt->execute([':tab_id' => $tabId]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Fetched assignment: " . print_r($assignment, true));

        // Проверка, что задание найдено
        if (!$assignment) {
            echo json_encode(['success' => false, 'message' => 'Задание не найдено.']);
            exit;
        }

        // Получить все описания для задания
        $stmt = $pdo->prepare("SELECT id, description, created_at FROM descriptions WHERE assignment_id = :assignment_id ORDER BY created_at ASC");
        $stmt->execute([':assignment_id' => $assignment['id']]);
        $descriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched descriptions: " . print_r($descriptions, true));

        // Получить информацию о группах, которым назначена таблица
        $stmt = $pdo->prepare("SELECT g.name FROM tab_groups tg JOIN groups g ON tg.group_id = g.id WHERE tg.tab_id = :tab_id");
        $stmt->execute([':tab_id' => $tabId]);
        $groups = $stmt->fetchAll(PDO::FETCH_COLUMN);
        error_log("Fetched groups: " . print_r($groups, true));

        // Получить прикрепленные файлы
        $stmt = $pdo->prepare("SELECT id, file_name, file_path, uploaded_by_role FROM tab_files WHERE assignment_id = :assignment_id");
        $stmt->bindValue(':assignment_id', $assignment['id'], PDO::PARAM_INT);
        $stmt->execute();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched files: " . print_r($files, true));

        echo json_encode(['success' => true, 'assignment' => $assignment, 'groups' => $groups, 'descriptions' => $descriptions, 'files' => $files, 'role' => $userRole]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
