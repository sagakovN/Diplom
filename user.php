<?php
session_start();
require("user/checking_user_info.php");
require("pdo_connect.php");

$userId = $userData['id'];

// Получение групп, в которых состоит студент
$groups = [];
if ($userData['role'] == 'user') { // Assuming 'user' role represents students
    $stmt = $pdo->prepare("
        SELECT groups.name
        FROM groups
        JOIN group_members ON groups.id = group_members.group_id
        WHERE group_members.student_id = :student_id
    ");
    $stmt->execute(['student_id' => $userId]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение уведомлений
$stmt = $pdo->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Функция для форматирования даты и времени
function formatDateTime($dateTime) {
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля', 5 => 'мая', 6 => 'июня',
        7 => 'июля', 8 => 'августа', 9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];
    $date = new DateTime($dateTime);
    $month = $months[(int)$date->format('n')];
    return $date->format('d') . ' ' . $month . ' в ' . $date->format('H:i');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style_user.css">
    <link rel="stylesheet" href="style_css/user_sidebar.css">
    <link rel="stylesheet" href="style_css/footer.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Пользователь</title>
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
                        if ($userData['role'] == 'teacher') {
                            echo 'Преподаватель';
                        } elseif ($userData['role'] == 'user') {
                            echo 'Студент';
                        }
                        ?></a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="<?php echo ($userData['role'] == 'teacher') ? 'teacher.php' : 'user.php'; ?>" class="sidebar-link">
                        <i class="lni lni-user"></i>
                        <span>Профиль</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="workpage.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Таблицы</span>
                    </a>
                </li>
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
                <div class="right_header">
                    <a href="user.php" id="name_user"><?= htmlspecialchars($userData['first_name']) ?></a>
                    <a href="index.php">Выход</a>
                </div>
            </div>
        </header>
        <div class="main-container">
        <img src="background/users.jpg" alt="" width="300" height="300">
            <div class="container">
                <div class="uve">
                    <h2>Уведомления</h2>
                    <?php if (count($notifications) > 0): ?>
                        <ul id="notificationList">
                            <?php foreach ($notifications as $notification): ?>
                                <li data-id="<?= htmlspecialchars($notification['id']) ?>">
                                    <?= htmlspecialchars($notification['message']) ?> 
                                    <em><?= formatDateTime($notification['created_at']) ?></em>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button id="clearNotifications">Очистить уведомления</button>
                    <?php else: ?>
                        <p>Нет новых уведомлений</p>
                    <?php endif; ?>
                </div>
                <div class="users-info">
                    <div class="info">
                        <div class="info_base">
                            <p>Имя</p>
                            <p>Фамилия</p>
                            <p>Почта</p>
                            <p>Группы:</p>
                        </div>
                        <div class="user_info">
                            <p><?= htmlspecialchars($userData['first_name']) ?></p>
                            <p><?= htmlspecialchars($userData['last_name']) ?></p>
                            <p><?= htmlspecialchars($userData['email']) ?></p>
                            <?php if ($userData['role'] == 'user'): ?>
                            <ul>
                                <?php foreach ($groups as $group): ?>
                                    <li><?= htmlspecialchars($group['name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="work-info">
                    <!-- Content for available tables -->
                    <h2>Доступные таблицы</h2>
                    <ul id="tabsList"></ul>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <ul>
            <p>Контакты</p>
            <li>Адрес: пл. Гагарина 1, ДГТУ</li>
            <li>email: donstu@donstu.ru</li>
            <li>Номер телефона: +7 (800) 100-19-30</li>
        </ul>
        <ul>
            <p>Социальные сети</p>
            <li>Сообщество Вконтакте</li>
            <li>Телеграмм</li>
            <li>Discord</li>
        </ul>
        <ul>
            <li>Политика конфиденциальности</li>
        </ul>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="sidebar.js"></script>
    <script src="getTabStudent.js"></script>
    <script>
        document.getElementById('clearNotifications').addEventListener('click', function() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'clearNotifications.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById('notificationList').innerHTML = '<p>Нет новых уведомлений</p>';
                    } else {
                        console.error('Ошибка при очистке уведомлений: ', response.message);
                    }
                } else {
                    console.error('Ошибка запроса: ', xhr.status);
                }
            };
            xhr.onerror = function() {
                console.error('Ошибка сети при попытке отправить запрос.');
            };
            xhr.send('user_id=<?= $userId ?>');
        });
    </script>
</body>
</html>
