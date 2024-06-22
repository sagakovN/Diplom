<?php
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

if ($_SESSION['user']['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    if ($_POST['action'] == 'verify') {
        $stmt = $pdo->prepare("UPDATE users SET verified = TRUE WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    } elseif ($_POST['action'] == 'reject') {
        // Удаление пользователя (переписывание столбца verified на FALSE)
        $stmt = $pdo->prepare("UPDATE users SET verified = FALSE WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }
}

$unverifiedUsers = $pdo->query("SELECT * FROM users WHERE role = 'user' AND verified = FALSE")->fetchAll();
$verifiedUsers = $pdo->query("SELECT * FROM users WHERE role = 'user' AND verified = TRUE")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/admin.css">
    <link rel="stylesheet" href="style_css/user_sidebar.css">
    <link rel="stylesheet" href="style_css/footer.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Admin Panel</title>
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
                <a href="teacher.php" id="name_user"><?= htmlspecialchars($userData['first_name']) ?></a>
                <a href="index.php">Выход</a>
            </div>
        </header>
        <div class="main-container">
            <div class="tables-container">
                <div class="table-wrapper">
                    <h3>Пользователи, ожидающие верификации</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Фамилия</th>
                                <th>Email</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($unverifiedUsers as $user):
                                $key = base64_decode($user['encryption_key']);
                                $first_name = decrypt($user['first_name'], $key);
                                $last_name = decrypt($user['last_name'], $key);
                                $email = decrypt($user['email'], $key);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($first_name) ?></td>
                                    <td><?= htmlspecialchars($last_name) ?></td>
                                    <td><?= htmlspecialchars($email) ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="action" value="verify" class="btn btn-success">Верифицировать</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger">Отклонить</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-wrapper">
                    <h3>Авторизованные пользователи</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Фамилия</th>
                                <th>Email</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($verifiedUsers as $user):
                                $key = base64_decode($user['encryption_key']);
                                $first_name = decrypt($user['first_name'], $key);
                                $last_name = decrypt($user['last_name'], $key);
                                $email = decrypt($user['email'], $key);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($first_name) ?></td>
                                    <td><?= htmlspecialchars($last_name) ?></td>
                                    <td><?= htmlspecialchars($email) ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-danger">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="sidebar.js"></script>
</body>
</html>
