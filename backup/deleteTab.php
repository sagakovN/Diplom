<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher') {
    if (isset($_POST['tabId'])) {
        $tabId = $_POST['tabId'];
        try {
            // Начало транзакции
            $pdo->beginTransaction();

            // Удаление связей между вкладкой и заданиями
            $sql = "DELETE FROM tab_assignments WHERE tab_id = :tabId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':tabId' => $tabId]);

            // Удаление заданий, которые больше не связаны ни с одной вкладкой
            $sql = "DELETE FROM assignments WHERE id NOT IN (SELECT assignment_id FROM tab_assignments)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            // Удаление самой вкладки
            $sql = "DELETE FROM tabs WHERE id = :tabId AND teacher_id = :teacherId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':tabId' => $tabId, ':teacherId' => $_SESSION['user']['id']]);

            // Подтверждение транзакции
            $pdo->commit();
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            // Откат транзакции при возникновении ошибки
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tab ID not provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request.']);
}
?>
