<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tabId = isset($_POST['tabId']) ? $_POST['tabId'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;

    if (!empty($tabId) && !empty($description)) {
        try {
            // Получаем ID задания по tabId
            $stmt = $pdo->prepare("SELECT assignments_id FROM tab_assignments WHERE tab_id = :tab_id");
            $stmt->execute([':tab_id' => $tabId]);
            $assignmentId = $stmt->fetchColumn();

            // Получаем название доски по tabId
            $stmt = $pdo->prepare("SELECT name FROM tabs WHERE id = :tab_id");
            $stmt->execute([':tab_id' => $tabId]);
            $tabName = $stmt->fetchColumn();

            if ($assignmentId) {
                $stmt = $pdo->prepare("INSERT INTO descriptions (assignment_id, description, created_at) VALUES (:assignment_id, :description, NOW())");
                $stmt->execute([
                    ':assignment_id' => $assignmentId,
                    ':description' => $description
                ]);

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
                        $message = 'Добавлено новое описание в доску "' . htmlspecialchars($tabName) . '".';
                        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $stmt->execute([$user['student_id'], $message]);
                    }
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Не удалось найти пользователей для указанного таба.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Не удалось найти задание по указанному табу.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Assignment ID или описание не предоставлены.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
