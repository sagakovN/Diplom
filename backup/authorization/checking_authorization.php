<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'], // Добавьте это
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone_number' => $user['phone_number'],
                'role' => $user['role'] // Добавьте это
            ];
            header("Location: user.php");
            exit();
        } else {
            // Неверный пароль
            echo "Неверный пароль.";
        }
    } else {
        echo "Пользователь с таким логином не найден.";
    }
}
?>