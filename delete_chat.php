<?php
session_start();
require("pdo_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chat_id = $_POST['chat_id'] ?? null;
    $private_chat_id = $_POST['private_chat_id'] ?? null;

    try {
        if ($chat_id) {
            // Удаление сообщений в групповом чате
            $stmt = $pdo->prepare("DELETE FROM messages WHERE chat_id = ?");
            $stmt->execute([$chat_id]);

            // Удаление самого чата
            $stmt = $pdo->prepare("DELETE FROM chats WHERE id = ?");
            $stmt->execute([$chat_id]);
        } elseif ($private_chat_id) {
            // Удаление сообщений в приватном чате
            $stmt = $pdo->prepare("DELETE FROM messages WHERE private_chat_id = ?");
            $stmt->execute([$private_chat_id]);

            // Удаление самого чата
            $stmt = $pdo->prepare("DELETE FROM private_chats WHERE id = ?");
            $stmt->execute([$private_chat_id]);
        }
        header("Location: chat.php");
        exit();
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
?>
