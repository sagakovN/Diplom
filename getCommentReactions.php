<?php
session_start();
require("pdo_connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['commentId'])) {
    $commentId = $_GET['commentId'];

    try {
        // Получаем количество лайков и дизлайков для комментария
        $stmt = $pdo->prepare("SELECT 
                                  (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = :comment_id AND reaction_type = 'like') AS likes,
                                  (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = :comment_id AND reaction_type = 'dislike') AS dislikes
                               FROM comment_reactions
                               WHERE comment_id = :comment_id
                               LIMIT 1");
        $stmt->execute([':comment_id' => $commentId]);
        $reactions = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reactions) {
            echo json_encode(['success' => true, 'likes' => $reactions['likes'], 'dislikes' => $reactions['dislikes']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Комментарии не найдены']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос.']);
}
?>
