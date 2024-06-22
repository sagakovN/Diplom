<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['descriptionId'])) {
    $descriptionId = $_POST['descriptionId'];

    if (!empty($descriptionId)) {
        try {
            // Получаем tabId по descriptionId
            $stmt = $pdo->prepare("SELECT ta.tab_id 
                                   FROM descriptions d
                                   JOIN tab_assignments ta ON d.assignment_id = ta.assignments_id
                                   WHERE d.id = :id");
            $stmt->execute([':id' => $descriptionId]);
            $tabId = $stmt->fetchColumn();

            // Получаем название доски по tabId
            $stmt = $pdo->prepare("SELECT name FROM tabs WHERE id = :tab_id");
            $stmt->execute([':tab_id' => $tabId]);
            $tabName = $stmt->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM descriptions WHERE id = :id");
            $stmt->execute([':id' => $descriptionId]);

            // Добавляем уведомления для пользователей
            $stmt = $pdo->prepare("
                SELECT gm.student_id
                FROM group_members gm
                JOIN tab_groups tg ON gm.group_id = tg.group_id
                WHERE tg.tab_id = :tabId
            ");
            $stmt->execute(['tabId' => $tabId]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($users) {
                foreach ($users as $user) {
                    $message = 'Описание было удалено в доске "' . htmlspecialchars($tabName) . '".';
                    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmt->execute([$user['student_id'], $message]);
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Не удалось найти пользователей для указанного таба.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $е->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Description ID не предоставлен.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
