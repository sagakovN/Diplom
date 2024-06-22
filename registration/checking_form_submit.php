<?php
session_start();
require("pdo_connect.php");

// Подключаем библиотеку Sodium
if (!extension_loaded('sodium')) {
    die("Sodium extension is not loaded.");
}

function generateKey() {
    return sodium_crypto_secretbox_keygen();
}

function encrypt($plaintext, $key) {
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
    return base64_encode($nonce . $ciphertext);
}

$key = generateKey();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Отладка: вывод значений полей
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $login = $_POST['login'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = encrypt($_POST['name'], $key);
    $surname = encrypt($_POST['surname'], $key);
    $email = encrypt($_POST['email'], $key);

    try {
        $sql = "INSERT INTO users (login, password, first_name, last_name, email, role, verified, encryption_key) 
                VALUES (:login, :password, :name, :surname, :email, 'user', FALSE, :encryption_key)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':encryption_key', base64_encode($key));

        $stmt->execute();

        echo "Пользователь успешно зарегистрирован с ролью 'user'.";

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $pdo_error = "Ошибка: " . $e->getMessage();
        echo "<script>console.error(" . json_encode($pdo_error) . ");</script>";
        echo $pdo_error;
    }
}
?>
