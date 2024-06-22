<?php
require("pdo_connect.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['assignmentId'])) {
    $assignmentId = $_GET['assignmentId'];

    try {
        $stmt = $pdo->prepare("SELECT id, description, created_at FROM descriptions WHERE assignment_id = :assignmentId ORDER BY created_at ASC");
        $stmt->execute([':assignmentId' => $assignmentId]);
        $descriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'descriptions' => $descriptions]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Assignment ID не предоставлен.']);
}
?>
