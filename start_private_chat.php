<?php
session_start();
require("pdo_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user1_id = $_SESSION['user']['id'];
    $user2_id = $_POST['user_id'];

    // Проверка на существование чата
    $check_chat = $pdo->prepare("SELECT id FROM private_chats WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
    $check_chat->execute([$user1_id, $user2_id, $user2_id, $user1_id]);
    $chat = $check_chat->fetch();

    if ($chat) {
        // Если чат уже существует, перенаправляем на него
        $chat_id = $chat['id'];
    } else {
        // Если чата нет, создаем его
        $stmt = $pdo->prepare("INSERT INTO private_chats (user1_id, user2_id, created_at) VALUES (?, ?, NOW()) RETURNING id");
        $stmt->execute([$user1_id, $user2_id]);
        $chat_id = $stmt->fetchColumn();
    }

    header("Location: chat.php?private_chat_id=$chat_id");
    exit();
}
?>
