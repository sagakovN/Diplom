<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['user']) && $_SESSION['user']['role'] == 'user') {
    $studentId = $_SESSION['user']['id'];

    try {
        // Получение групп, в которых состоит студент
        $stmt = $pdo->prepare("SELECT group_id FROM group_members WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $studentId]);
        $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($groupIds)) {
            echo json_encode(['success' => false, 'message' => 'Студент не состоит ни в одной группе.']);
            exit();
        }

        // Логируем группы студента
        error_log("Группы студента: " . implode(',', $groupIds));

        // Получение таблиц, доступных для групп студента
        $stmt = $pdo->prepare("SELECT DISTINCT t.id, t.name FROM tabs t
                               JOIN tab_groups tg ON t.id = tg.tab_id
                               WHERE tg.group_id IN (" . implode(',', array_map('intval', $groupIds)) . ")");
        $stmt->execute();
        $tabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Логируем найденные таблицы
        error_log("Таблицы для студента: " . json_encode($tabs));

        echo json_encode(['success' => true, 'tabs' => $tabs]);
    } catch (PDOException $e) {
        error_log("Ошибка базы данных: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
