<?php
// getAssignmentsByTab.php
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['tab_id'])) {
    $tabId = $_GET['tab_id'];
    try {
        $sql = "SELECT a.id 
                FROM assignments a 
                INNER JOIN tab_assignments ta ON a.id = ta.assignments_id 
                WHERE ta.tab_id = :tabId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':tabId' => $tabId]);

        $assignments = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['success' => true, 'assignmentIds' => $assignments]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No tab ID provided.']);
}
?>
