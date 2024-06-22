<?php
session_start();
require("pdo_connect.php");
include("roleVerification.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['groupName']) && !empty($_POST['groupName']) && isset($_SESSION['user']['id'])) {
    $groupName = trim($_POST['groupName']);
    $creatorId = $_SESSION['user']['id']; // ID пользователя из сессии

    try {
        // Начинаем транзакцию
        $pdo->beginTransaction();

        // Вставляем информацию о группе
        $stmt = $pdo->prepare("INSERT INTO groups (name, creator_id) VALUES (:name, :creator_id)");
        $stmt->bindParam(':name', $groupName, PDO::PARAM_STR);
        $stmt->bindParam(':creator_id', $creatorId, PDO::PARAM_INT);
        $stmt->execute();
        $groupId = $pdo->lastInsertId(); // Получаем ID новой группы

        // Вставляем информацию о создателе группы как "Староста"
        $stmt = $pdo->prepare("INSERT INTO user_group (user_id, group_id, role) VALUES (:user_id, :group_id, 'Староста')");
        $stmt->bindParam(':user_id', $creatorId, PDO::PARAM_INT);
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        // Фиксируем транзакцию
        $pdo->commit();
        echo "Группа успешно создана";
    } catch (Exception $e) {
        // Если ошибка, откатываем изменения
        $pdo->rollBack();
        echo "Ошибка при создании группы: " . $e->getMessage();
    }
} else {
    echo "Необходимо ввести название группы и быть залогиненным";
}
?>
