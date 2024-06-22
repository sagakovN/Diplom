<?php
// checkTabContent.php
require("pdo_connect.php");

$tab_id = isset($_GET['tab_id']) ? intval($_GET['tab_id']) : 0;

function getTabContent($pdo, $tab_id) {
    try {
        $sql = "SELECT a.id 
                FROM assignments AS a 
                JOIN tab_assignments AS ta ON a.id = ta.assignment_id 
                WHERE ta.tab_id = :tab_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tab_id', $tab_id, PDO::PARAM_INT);
        $stmt->execute();

        // Если запрос возвращает хотя бы одну строку, значит контент есть
        if ($stmt->fetch()) {
            return true;
        }
    } catch (PDOException $e) {
        // В реальном приложении лучше использовать систему логирования для ошибок
        error_log('Ошибка при проверке контента вкладки: ' . $e->getMessage());
    }
    return false;
}

$contentExists = getTabContent($pdo,$tab_id); // Функция должна быть определена

echo json_encode(['hasContent' => $contentExists]);
