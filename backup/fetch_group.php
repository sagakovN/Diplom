<?php
session_start();
require("pdo_connect.php");

$userId = $_SESSION['user']['id'];
// Выборка групп, где пользователь является участником или создателем
$stmt = $pdo->prepare("
    SELECT DISTINCT g.id, g.name
    FROM groups g
    LEFT JOIN user_group ug ON g.id = ug.group_id
    WHERE ug.user_id = :user_id OR g.creator_id = :user_id
");
$stmt->execute(['user_id' => $userId]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Возвращаем данные в формате JSON
echo json_encode($groups);
?>
