<?php
session_start();
require("pdo_connect.php");
header('Content-Type: application/json');

ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '/path/to/your/error.log');
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient_id'])) {
        $user_id = $_SESSION['user']['id'];
        $recipient_id = $_POST['recipient_id'];

        // Проверка на существование чата
        $stmt = $pdo->prepare("SELECT id FROM private_chats WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
        $stmt->execute([$user_id, $recipient_id, $recipient_id, $user_id]);
        $chat = $stmt->fetch();

        if ($chat) {
            $private_chat_id = $chat['id'];
        } else {
            // Создание нового чата
            $stmt = $pdo->prepare("INSERT INTO private_chats (user1_id, user2_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $recipient_id]);
            $private_chat_id = $pdo->lastInsertId();
        }

        echo json_encode(['private_chat_id' => $private_chat_id]);
    } else {
        echo json_encode(['error' => 'Неверные параметры запроса.']);
    }
} catch (Exception $e) {
    error_log("Ошибка создания чата: " . $e->getMessage());
    echo json_encode(['error' => 'Ошибка при создании чата.', 'details' => $e->getMessage()]);
}
exit();
?>
