<?php
session_start();
require("pdo_connect.php");

if ($_SESSION['user']['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$grades = $data['grades'];

foreach ($grades as $grade) {
    $stmt = $pdo->prepare("REPLACE INTO grades (student_id, passed) VALUES (:studentId, :passed)");
    $stmt->execute([
        'studentId' => $grade['studentId'],
        'passed' => $grade['passed']
    ]);
}

echo json_encode(['success' => true]);
?>
