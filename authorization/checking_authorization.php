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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            if ($user['verified']) {
                $key = base64_decode($user['encryption_key']);
                $first_name = decrypt($user['first_name'], $key);
                $last_name = decrypt($user['last_name'], $key);
                $email = decrypt($user['email'], $key);

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'role' => $user['role']
                ];

                if ($user['role'] == 'teacher') {
                    header("Location: teacher.php");
                } elseif ($user['role'] == 'user') {
                    header("Location: user.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['error_message'] = "Ваш аккаунт не верифицирован. Пожалуйста, дождитесь одобрения администратора.";
            }
        } else {
            $_SESSION['error_message'] = "Неверный пароль.";
        }
    } else {
        $_SESSION['error_message'] = "Неверно введены логин или пароль.";
    }

    header("Location: authorization.php");
    exit();
}
?>
