<?php
// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, все ли параметры заполнены
    $required_fields = array('login', 'password', 'name', 'surname', 'email', 'phone');
    $error = false;
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error = true;
            break;
        }
    }

    // Если не заполнены все параметры, выводим сообщение об ошибке
    if ($error) {
        echo "Пожалуйста, заполните все обязательные поля.";
    } else {
        // Собираем данные из формы
        $login = $_POST['login'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хешируем пароль
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $notifications = isset($_POST['notifications']) ? 'true' : 'false';

        // Подготавливаем SQL-запрос с использованием подготовленных выражений
        $sql = "INSERT INTO users (login, password, first_name, last_name, email, phone_number, receive_notifications) VALUES (:login, :password, :name, :surname, :email, :phone, :notifications)";
        $stmt = $pdo->prepare($sql);

        // Привязываем параметры
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':notifications', $notifications, PDO::PARAM_BOOL);

        // Выполняем запрос
        $stmt->execute();

        // Перенаправляем пользователя на index.php после выполнения запроса
        header("Location: index.php");
        exit(); // Убедимся, что скрипт завершается после перенаправления
    }
}
?>