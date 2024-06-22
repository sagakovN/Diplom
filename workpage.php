<?php
session_start();
require("pdo_connect.php");
include("roleVerification.php");

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];
$groups = $pdo->query("SELECT * FROM groups WHERE teacher_id = $userId")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device=width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style_workpage.css">
    <link rel="stylesheet" href="style_css/user_sidebar.css">
    <link rel="stylesheet" href="style_css/footer.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Электронная таблица</title>
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
                        if ($userRole == 'teacher') {
                            echo 'Преподаватель';
                        } elseif ($userRole == 'user') {
                            echo 'Студент';
                        }
                        ?>
                    </a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <?php if ($userRole == 'teacher'): ?>
                        <a href="teacher.php" class="sidebar-link">
                            <i class="lni lni-user"></i>
                            <span>Профиль</span>
                        </a>
                    <?php elseif ($userRole == 'user'): ?>
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
                <?php if ($userRole == 'teacher'): ?>
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
                <div class="right_header">
                    <a href="user.php" id="name_user"><?= htmlspecialchars($_SESSION['user']['first_name']) ?></a>
                    <a href="index.php">Выход</a>
                </div>
            </div>
        </header>
        <div class="main-container">
            <div class="wrapp-1">
                <ul id="tabsList"></ul>
                <?php if ($_SESSION['user']['role'] == 'teacher'): ?>
                    <button id="createTabButton">Создать доску</button>
                <?php endif; ?>
            </div>
            <div class="wrapp-3" style="position: relative; padding-bottom: 200px;"> <!-- Добавлен отступ снизу -->
                <div class="tasks"></div>
            </div>
            <div class="wrapp-4">
                <div class="comments-section" id="commentsSection" style="display: none;">
                    <h3>Комментарии</h3>
                    <div id="commentsContainer"></div>
                    <form id="commentForm">
                        <textarea name="comment" placeholder="Напишите комментарий..." required></textarea>
                        <button type="submit"><i class="lni lni-envelope"></i></button>
                        <input type="hidden" id="assignmentId" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Модальное окно для списка студентов -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-report-modal">&times;</span>
            <h2>Список студентов</h2>
            <table id="studentsTable" class="files-table">
                <thead>
                    <tr>
                        <th>Имя студента</th>
                        <th>Файл</th>
                        <th>Зачет</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Студенты будут добавлены динамически -->
                </tbody>
            </table>
            <button id="saveGradesButton">Сохранить</button>
        </div>
    </div>

    <!-- Модальное окно для назначения таблицы группам -->
<div id="groupModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Назначить таблицу группам</h2>
        <form id="assignGroupsForm">
            <?php foreach ($groups as $group): ?>
                <div>
                    <input type="checkbox" id="group_<?= $group['id'] ?>" name="groups[]" value="<?= $group['id'] ?>">
                    <label for="group_<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></label>
                </div>
            <?php endforeach; ?>
            <input type="hidden" name="tabId" id="tabId">
            <button type="submit">Сохранить</button>
        </form>
    </div>
</div>


    <footer></footer>
    <?php if ($_SESSION['user']['role'] == 'teacher'): ?>
        <script src='action_on_tab.js'></script>
    <?php else: ?>
        <script src='getTabStudent.js'></script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>
