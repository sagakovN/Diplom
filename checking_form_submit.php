<?php
session_start();
require("pdo_connect.php");

// Подключаем библиотеку Sodium
if (!extension_loaded('sodium')) {
    die("Sodium extension is не loaded.");
}

if (!function_exists('generateKey')) {
    function generateKey() {
        return sodium_crypto_secretbox_keygen();
    }
}

if (!function_exists('encrypt')) {
    function encrypt($plaintext, $key) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
        return base64_encode($nonce . $ciphertext);
    }
}

if (!function_exists('isPasswordStrong')) {
    function isPasswordStrong($password) {
        // Пароль должен быть минимум 8 символов, содержать хотя бы одну цифру, одну заглавную и одну строчную букву
        return preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $password);
    }
}

$key = generateKey();

$error_message = "";
$error_fields = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $required_fields = array('login', 'password', 'name', 'surname', 'email');
    $field_names = array(
        'login' => 'Логин',
        'password' => 'Пароль',
        'name' => 'Имя',
        'surname' => 'Фамилия',
        'email' => 'Почта'
    );
    $error = false;

    foreach ($required_fields as $field) {
        if (empty(trim($_POST[$field]))) {
            $error = true;
            $error_fields[$field] = $field_names[$field] . " введен неверно.";
        }
    }

    if (!$error) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Проверка сложности пароля
        if (!isPasswordStrong($password)) {
            $error_message = "Пароль должен быть минимум 8 символов, содержать хотя бы одну цифру, одну заглавную и одну строчную букву.";
            $error_fields['password'] = $error_message;
            $error = true;
        }

        // Проверка на уникальность логина
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $error_message = "Логин уже занят.";
            $error_fields['login'] = $error_message;
            $error = true;
        }

        if (!$error) {
            $password = password_hash($password, PASSWORD_DEFAULT);
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

                echo "<script>location.href='index.php';</script>";
                exit();
            } catch (PDOException $e) {
                $error_message = "Ошибка: " . $e->getMessage();
            }
        }
    }
}
?>
