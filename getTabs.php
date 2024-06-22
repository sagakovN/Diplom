<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("pdo_connect.php");

if (!extension_loaded('sodium')) {
    die("Sodium extension is not loaded.");
}

function decrypt($ciphertext, $key) {
    $decoded = base64_decode($ciphertext);
    $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
    $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    if ($plaintext === false) {
        throw new Exception("Decryption failed");
    }
    return $plaintext;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $role = $_SESSION['user']['role'];

    try {
        if ($role == 'teacher') {
            // Получение таблиц, созданных преподавателем
            $stmt = $pdo->prepare("SELECT id, name FROM tabs WHERE teacher_id = :userId");
            $stmt->execute([':userId' => $userId]);
            $tabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'tabs' => $tabs]);
        } else if ($role == 'user') {
            // Получение групп, в которых состоит студент
            $stmt = $pdo->prepare("SELECT DISTINCT group_id FROM group_members WHERE student_id = :userId");
            $stmt->execute([':userId' => $userId]);
            $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($groupIds)) {
                echo json_encode(['success' => false, 'message' => 'Студент не состоит ни в одной группе.']);
                exit();
            }

            // Получение таблиц и информации о преподавателях, доступных для групп студента
            $stmt = $pdo->prepare("
                SELECT DISTINCT t.id AS tab_id, t.name AS tab_name, u.first_name, u.last_name, u.encryption_key 
                FROM tabs t
                JOIN tab_groups tg ON t.id = tg.tab_id
                JOIN users u ON t.teacher_id = u.id
                WHERE tg.group_id IN (" . implode(',', array_map('intval', $groupIds)) . ")
                ORDER BY u.last_name, u.first_name, t.name
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $teachersTabs = [];
            foreach ($result as $row) {
                $key = base64_decode($row['encryption_key']);
                $first_name = decrypt($row['first_name'], $key);
                $last_name = decrypt($row['last_name'], $key);
                $teacherName = $first_name . ' ' . $last_name;
                if (!isset($teachersTabs[$teacherName])) {
                    $teachersTabs[$teacherName] = [];
                }
                // Убедиться, что доска добавляется только один раз
                $tabId = $row['tab_id'];
                if (!array_filter($teachersTabs[$teacherName], fn($tab) => $tab['tab_id'] === $tabId)) {
                    $teachersTabs[$teacherName][] = ['tab_id' => $row['tab_id'], 'tab_name' => $row['tab_name']];
                }
            }

            echo json_encode(['success' => true, 'teachersTabs' => $teachersTabs]);
        }
    } catch (PDOException $e) {
        error_log("Ошибка базы данных: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    } catch (Exception $e) {
        error_log("Ошибка дешифрования: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка дешифрования: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request or invalid request method.']);
}
?>
