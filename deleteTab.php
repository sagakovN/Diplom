<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tabId'])) {
    $tabId = $_POST['tabId'];

    try {
        $pdo->beginTransaction();

        // Получение всех assignments, связанных с табом
        $stmt = $pdo->prepare("SELECT assignments_id FROM tab_assignments WHERE tab_id = :tabId");
        $stmt->execute([':tabId' => $tabId]);
        $assignments = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($assignments)) {
            // Удаление всех комментариев, связанных с assignments
            $stmt = $pdo->prepare("DELETE FROM comments WHERE assignment_id IN (" . implode(',', array_map('intval', $assignments)) . ")");
            $stmt->execute();

            // Удаление всех assignments, связанных с табом
            $stmt = $pdo->prepare("DELETE FROM assignments WHERE id IN (" . implode(',', array_map('intval', $assignments)) . ")");
            $stmt->execute();
        }

        // Удаление всех связей в табе
        $stmt = $pdo->prepare("DELETE FROM tab_assignments WHERE tab_id = :tabId");
        $stmt->execute([':tabId' => $tabId]);

        // Удаление таба
        $stmt = $pdo->prepare("DELETE FROM tabs WHERE id = :tabId");
        $stmt->execute([':tabId' => $tabId]);

        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Tab ID not provided.']);
}
?>
