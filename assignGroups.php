<?php
session_start();
require("pdo_connect.php");

if ($_SESSION['user']['role'] != 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tabId = $_POST['tabId'];
    $groups = isset($_POST['groups']) ? $_POST['groups'] : [];

    try {
        $pdo->beginTransaction();

        // Удалить все текущие назначения для этой таблицы
        $stmt = $pdo->prepare("DELETE FROM tab_groups WHERE tab_id = :tab_id");
        $stmt->execute([':tab_id' => $tabId]);

        // Добавить новые назначения
        $stmt = $pdo->prepare("INSERT INTO tab_groups (tab_id, group_id) VALUES (:tab_id, :group_id)");
        foreach ($groups as $groupId) {
            $stmt->execute([':tab_id' => $tabId, ':group_id' => $groupId]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
