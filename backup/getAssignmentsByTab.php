<?php
// getAssignmentsByTab.php
require("pdo_connect.php");

// Установим тип содержимого, который будет возвращаться
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['tab_id'])) {
    $tabId = $_GET['tab_id'];
    try {
        // Получаем задания для данной вкладки
        $sql = "SELECT a.id, a.title, a.description, a.file_paths 
                FROM assignments a 
                INNER JOIN tab_assignments ta ON a.id = ta.assignment_id 
                WHERE ta.tab_id = :tabId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':tabId' => $tabId]);

        // Получаем результаты
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'assignments' => $assignments]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No tab ID provided.']);
}
?>
