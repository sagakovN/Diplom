<?php
session_start();
require("pdo_connect.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $private_chat_id = $_POST['private_chat_id'] ?? null;
    $sender_id = $_POST['sender_id'];
    $message = $_POST['message'];
    
    if (empty($message)) {
        echo json_encode(['error' => 'Сообщение не может быть пустым.']);
        exit();
    }

    if ($private_chat_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (private_chat_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt->execute([$private_chat_id, $sender_id, $message])) {
            echo json_encode(['error' => 'Ошибка вставки сообщения.', 'details' => $stmt->errorInfo()]);
            exit();
        }

        echo json_encode([
            'message_html' => "<div class='message sender'><strong>Вы:</strong><p>" . htmlspecialchars($message) . "</p><span>Сейчас</span></div>",
            'private_chat_id' => $private_chat_id
        ]);
    } else {
        echo json_encode(['error' => 'Неверные параметры запроса.']);
    }
    
    exit();
}
?>
