<?php
session_start();
require("pdo_connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['commentId']) && isset($_POST['reactionType'])) {
    $commentId = $_POST['commentId'];
    $reactionType = $_POST['reactionType'];

    // Получаем ID пользователя
    $userId = $_SESSION['user']['id'];

    try {
        // Проверяем, существует ли уже реакция пользователя на этот комментарий
        $stmt = $pdo->prepare("SELECT * FROM comment_reactions WHERE comment_id = :comment_id AND user_id = :user_id");
        $stmt->execute([':comment_id' => $commentId, ':user_id' => $userId]);
        $existingReaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingReaction) {
            // Обновляем тип реакции, если уже существует
            $stmt = $pdo->prepare("UPDATE comment_reactions SET reaction_type = :reaction_type WHERE comment_id = :comment_id AND user_id = :user_id");
            $stmt->execute([':reaction_type' => $reactionType, ':comment_id' => $commentId, ':user_id' => $userId]);
        } else {
            // Вставляем новую реакцию, если не существует
            $stmt = $pdo->prepare("INSERT INTO comment_reactions (comment_id, user_id, reaction_type) VALUES (:comment_id, :user_id, :reaction_type)");
            $stmt->execute([':comment_id' => $commentId, ':user_id' => $userId, ':reaction_type' => $reactionType]);
        }

        // Получаем автора комментария и название доски для создания уведомления
        $stmt = $pdo->prepare("
            SELECT c.user_id, t.name AS tab_name
            FROM comments c
            JOIN assignments a ON c.assignment_id = a.id
            JOIN tab_assignments ta ON a.id = ta.assignments_id
            JOIN tabs t ON ta.tab_id = t.id
            WHERE c.id = :commentId
        ");
        $stmt->execute([':commentId' => $commentId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comment) {
            $message = 'На ваш комментарий дали реакцию в доске "' . htmlspecialchars($comment['tab_name']) . '".';
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->execute([$comment['user_id'], $message]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
