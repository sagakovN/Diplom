<?php
session_start();
require("pdo_connect.php");

// Подключаем библиотеку Sodium
if (!extension_loaded('sodium')) {
    die("Sodium extension is not loaded.");
}

function decrypt($ciphertext, $key) {
    $decoded = base64_decode($ciphertext);
    $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
    $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    if ($plaintext === false) {
        throw new Exception("Decryption failed");
    }
    return $plaintext;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['assignmentId'])) {
    $assignmentId = $_GET['assignmentId'];

    if (is_numeric($assignmentId)) {
        try {
            $stmt = $pdo->prepare("
                SELECT comments.*, 
                       users.first_name AS first_name_encrypted, 
                       users.last_name AS last_name_encrypted,
                       users.encryption_key AS encryption_key,
                       COALESCE(likes.count, 0) AS likes,
                       COALESCE(dislikes.count, 0) AS dislikes
                FROM comments 
                JOIN users ON comments.user_id = users.id 
                LEFT JOIN (
                    SELECT comment_id, COUNT(*) AS count 
                    FROM comment_reactions 
                    WHERE reaction_type = 'like' 
                    GROUP BY comment_id
                ) likes ON comments.id = likes.comment_id
                LEFT JOIN (
                    SELECT comment_id, COUNT(*) AS count 
                    FROM comment_reactions 
                    WHERE reaction_type = 'dislike' 
                    GROUP BY comment_id
                ) dislikes ON comments.id = dislikes.comment_id
                WHERE assignment_id = ? 
                ORDER BY created_at ASC");
            $stmt->execute([$assignmentId]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comments as &$comment) {
                $key = base64_decode($comment['encryption_key']);
                $first_name = decrypt($comment['first_name_encrypted'], $key);
                $last_name = decrypt($comment['last_name_encrypted'], $key);
                $comment['user_name'] = $first_name . ' ' . $last_name;
                unset($comment['first_name_encrypted']);
                unset($comment['last_name_encrypted']);
                unset($comment['encryption_key']);
            }

            echo json_encode(['success' => true, 'comments' => $comments]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid assignment ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
