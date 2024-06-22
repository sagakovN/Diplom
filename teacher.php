<?php
session_start(); // Начинаем сессию
require("user/checking_user_info.php");
require("pdo_connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style_teacher.css">
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
        <img src="background/users.jpg" alt="" width="300" height="300">
            <div class="container">
                <div class="users-info">
                    <div class="info">
                        <div class="info_base">
                            <p>Имя</p>
                            <p>Фамилия</p>
                            <p>Почта</p>
                            <p>Роль</p>
                        </div>
                        <div class="user_info">
                            <p><?= htmlspecialchars($userData['first_name']) ?></p>
                            <p><?= htmlspecialchars($userData['last_name']) ?></p>
                            <p><?= htmlspecialchars($userData['email']) ?></p>
                            <p><?= htmlspecialchars($userData['role']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <ul>
            <p>Контакты</p>
            <li>Адрес: пл. Гагарина 1, ДГТУ</li>
            <li>email: donstu@donstu</li>
            <li>Номер телефона: +7 (800) 100-19-30</li>
        </ul>
        <ul>
            <p>Социальные сети</p>
            <li>Сообщество Вконтакте</li>
            <li>Телеграмм</li>
            <li>Discord</ли>
        </ul>
        <ul>
            <ли>Политика конфиденциальности</ли>
        </ul>
    </footer>
    </div>
    <footer></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="sidebar.js"></script>
</body>
</html>