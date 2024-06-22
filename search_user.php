<?php
session_start();
require("pdo_connect.php");
header('Content-Type: application/json');

ini_set('log_errors', 1);
ini_set('display_errors', 1); // Временно включим отображение ошибок для отладки
ini_set('error_log', '/path/to/your/error.log');
error_reporting(E_ALL);

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

try {
    $user_id = $_SESSION['user']['id'];

    // Получить всех пользователей, кроме текущего
    $search_stmt = $pdo->prepare("SELECT id, first_name, last_name, encryption_key FROM users WHERE id != ?");
    $search_stmt->execute([$user_id]);
    $all_users = $search_stmt->fetchAll();

    $search_results = [];
    foreach ($all_users as $user) {
        $key = base64_decode($user['encryption_key']);
        $first_name = decrypt($user['first_name'], $key);
        $last_name = decrypt($user['last_name'], $key);

        // Добавляем всех пользователей в результаты
        $search_results[] = [
            'id' => $user['id'],
            'first_name' => $first_name,
            'last_name' => $last_name
        ];
    }

    // Логирование результатов перед отправкой
    error_log("Search results: " . json_encode($search_results));

    // Убедитесь, что ответ отправляется корректно
    echo json_encode($search_results);
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    echo json_encode(['error' => 'Ошибка при поиске пользователей.', 'details' => $e->getMessage()]);
}
exit();
?>
