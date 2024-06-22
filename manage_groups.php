<?php
session_start();
require("pdo_connect.php");

if ($_SESSION['user']['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['group_name'])) {
        // Создание новой группы
        $groupName = $_POST['group_name'];
        $teacherId = $_SESSION['user']['id'];
        
        $stmt = $pdo->prepare("INSERT INTO groups (name, teacher_id) VALUES (:name, :teacher_id)");
        $stmt->execute([':name' => $groupName, ':teacher_id' => $teacherId]);
        $groupId = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'group_id' => $groupId, 'group_name' => $groupName]);
        exit();
    } elseif (isset($_POST['group_id']) && isset($_POST['student_id']) && isset($_POST['action'])) {
        // Добавление или удаление студента из группы
        $groupId = $_POST['group_id'];
        $studentId = $_POST['student_id'];
        $action = $_POST['action'];
        
        if ($action == 'add') {
            // Проверка, если студент уже находится в группе
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM group_members WHERE group_id = :group_id AND student_id = :student_id");
            $stmt->execute([':group_id' => $groupId, ':student_id' => $studentId]);
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, student_id) VALUES (:group_id, :student_id)");
                $stmt->execute([':group_id' => $groupId, ':student_id' => $studentId]);
            }
        } elseif ($action == 'remove') {
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = :group_id AND student_id = :student_id");
            $stmt->execute([':group_id' => $groupId, ':student_id' => $studentId]);
        }

        // Получение обновленного списка студентов в группе
        $stmt = $pdo->prepare("SELECT users.id, users.first_name, users.last_name, users.encryption_key 
                               FROM group_members 
                               JOIN users ON group_members.student_id = users.id 
                               WHERE group_members.group_id = :group_id");
        $stmt->execute([':group_id' => $groupId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($members as &$member) {
            $encryptionKey = base64_decode($member['encryption_key']);
            $member['first_name'] = decrypt($member['first_name'], $encryptionKey);
            $member['last_name'] = decrypt($member['last_name'], $encryptionKey);
        }

        echo json_encode(['success' => true, 'members' => $members]);
        exit();
    } elseif (isset($_POST['delete_group_id'])) {
        // Удаление группы
        $groupId = $_POST['delete_group_id'];

        // Удаление всех записей в group_members, которые ссылаются на эту группу
        $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = :group_id");
        $stmt->execute([':group_id' => $groupId]);

        // Удаление группы
        $stmt = $pdo->prepare("DELETE FROM groups WHERE id = :id");
        $stmt->execute([':id' => $groupId]);

        echo json_encode(['success' => true, 'group_id' => $groupId]);
        exit();
    }
}

// Получение всех студентов
$students = $pdo->query("SELECT * FROM users WHERE role = 'user' AND verified = TRUE")->fetchAll(PDO::FETCH_ASSOC);

// Получение всех групп преподавателя
$teacherId = $_SESSION['user']['id'];
$groups = $pdo->query("SELECT * FROM groups WHERE teacher_id = $teacherId")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/manage_groups.css">
    <link rel="stylesheet" href="style_css/user_sidebar.css">
    <link rel="stylesheet" href="style_css/footer.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Управление группами</title>
</head>
<body>
    <div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="#">
                        <?php
                        if ($_SESSION['user']['role'] == 'teacher') {
                            echo 'Преподаватель';
                        } elseif ($_SESSION['user']['role'] == 'user') {
                            echo 'Студент';
                        }
                        ?>
                    </a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 'teacher'): ?>
                        <a href="teacher.php" class="sidebar-link">
                            <i class="lni lni-user"></i>
                            <span>Профиль</span>
                        </a>
                    <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 'user'): ?>
                        <a href="user.php" class="sidebar-link">
                            <i class="lni lni-user"></i>
                            <span>Профиль</span>
                        </a>
                    <?php endif; ?>
                </li>
                <li class="sidebar-item">
                    <a href="workpage.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Таблицы</span>
                    </a>
                </li>
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 'teacher'): ?>
                    <li class="sidebar-item">
                        <a href="manage_groups.php" class="sidebar-link">
                            <i class="lni lni-network"></i>
                            <span>Группы</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="admin.php" class="sidebar-link">
                            <i class="lni lni-protection"></i>
                            <span>Верификация</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="sidebar-item">
                    <a href="chat.php" class="sidebar-link">
                        <i class="lni lni-envelope"></i>
                        <span>Беседы</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Настройки</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="index.php" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Вернуться на сайт</span>
                </a>
            </div>
        </aside>
        <header>
            <div class="button_header">
                <a href="teacher.php" id="name_user"><?= htmlspecialchars($_SESSION['user']['first_name']) ?></a>
                <a href="index.php">Выход</a>
            </div>
        </header>
        <div class="main-container">
            <div class="manage-container">
                <h1>Управление группами</h1>
                <h3>Создать новую группу</h3>
                <form id="create-group-form" method="post">
                    <label for="group_name">Название группы:</label>
                    <input type="text" id="group_name" name="group_name" required>
                    <button type="submit">Создать группу</button>
                </form>
                <h3>Существующие группы</h3>
                <div class="group-list" id="group-list">
                    <?php foreach ($groups as $group): ?>
                        <div class="group-item-wrapper" id="group-wrapper-<?= $group['id'] ?>" onclick="showStudents(<?= $group['id'] ?>)">
                            <div class="group-item">
                                <span><?= htmlspecialchars($group['name']) ?></span>
                            </div>
                            <div id="students_<?= $group['id'] ?>" class="student-list" style="display: none;">
                                <form action="" class="search-form">
                                <input type="text" class="search-students" placeholder="Поиск студентов..." style="display: none;">
                                <button class="search-btn">Найти</button>
                                </form>
                                <h3>Все студенты</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Имя</th>
                                            <th>Фамилия</th>
                                            <th>Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <?php
                                            // Проверка, находится ли студент в группе
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM group_members WHERE group_id = :group_id AND student_id = :student_id");
                                            $stmt->execute([':group_id' => $group['id'], ':student_id' => $student['id']]);
                                            $isInGroup = $stmt->fetchColumn() > 0;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars(decrypt($student['first_name'], base64_decode($student['encryption_key']))) ?></td>
                                                <td><?= htmlspecialchars(decrypt($student['last_name'], base64_decode($student['encryption_key']))) ?></td>
                                                <td>
                                                    <?php if ($isInGroup): ?>
                                                        <button class="btn-remove" onclick="updateStudentGroup(<?= $group['id'] ?>, <?= $student['id'] ?>, 'remove')">Удалить</button>
                                                    <?php else: ?>
                                                        <button class="btn-add" onclick="updateStudentGroup(<?= $group['id'] ?>, <?= $student['id'] ?>, 'add')">Добавить</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn-delete" style="display: none;" onclick="event.stopPropagation(); deleteGroup(<?= $group['id'] ?>)">Удалить группу</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <h3>Студенты в группах</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Группа</th>
                                <th>Студенты</th>
                            </tr>
                        </thead>
                        <tbody id="group-members-list">
                            <?php foreach ($groups as $group): ?>
                                <tr id="group_<?= $group['id'] ?>">
                                    <td><?= htmlspecialchars($group['name']) ?></td>
                                    <td>
                                        <?php
                                        $groupId = $group['id'];
                                        $stmt = $pdo->prepare("SELECT users.id, users.first_name, users.last_name, users.encryption_key 
                                                               FROM group_members 
                                                               JOIN users ON group_members.student_id = users.id 
                                                               WHERE group_members.group_id = :group_id");
                                        $stmt->execute([':group_id' => $groupId]);
                                        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($members as &$member) {
                                            $encryptionKey = base64_decode($member['encryption_key']);
                                            $member['first_name'] = decrypt($member['first_name'], $encryptionKey);
                                            $member['last_name'] = decrypt($member['last_name'], $encryptionKey);
                                            echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '<br>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="action_on_groups.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="sidebar.js"></script>
</body>
</html>
