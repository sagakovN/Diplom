<?php
session_start();
require("pdo_connect.php");

if ($_SESSION['user']['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$teacherId = $_SESSION['user']['id'];
$tabId = $_GET['tabId'];

// Предполагаем, что у вас есть таблицы students, files, и grades
$sql = "SELECT students.id, students.name, files.file_name, files.file_path, grades.passed 
        FROM students
        JOIN files ON students.id = files.student_id
        LEFT JOIN grades ON students.id = grades.student_id
        WHERE files.tab_id = :tabId";

$stmt = $pdo->prepare($sql);
$stmt->execute(['tabId' => $tabId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'students' => $students]);
?>
