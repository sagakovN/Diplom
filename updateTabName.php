<?php
session_start();
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    $tabId = isset($_POST['tabId']) ? $_POST['tabId'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;

    if ($tabId && $name) {
        try {
            $stmt = $pdo->prepare("UPDATE tabs SET name = :name WHERE id = :tabId");
            $stmt->execute([':name' => $name, ':tabId' => $tabId]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tab ID или название не предоставлены.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
