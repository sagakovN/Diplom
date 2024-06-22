<?php
session_start();
require("pdo_connect.php");
include("roleVerification.php");

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

$user_id = $_SESSION['user']['id'];

// Получение личных чатов пользователя
$private_chats = [];
try {
    $stmt = $pdo->prepare("SELECT pc.*, u.first_name, u.last_name, u.encryption_key FROM private_chats pc
                            JOIN users u ON (u.id = CASE WHEN pc.user1_id = ? THEN pc.user2_id ELSE pc.user1_id END)
                            WHERE pc.user1_id = ? OR pc.user2_id = ?");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $private_chats = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}

function formatDate($date) {
    return date('d-m-Y', strtotime($date));
}

function formatTime($time) {
    return date('H:i', strtotime($time));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/chat.css">
    <link rel="stylesheet" href="style_css/user_sidebar.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Чат</title>
    <script>
        $(document).ready(function(){
            let allUsers = [];

            $('#messageForm').on('submit', function(event){
                event.preventDefault();
                
                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.error('Ошибка:', data.error);
                            if (data.details) {
                                console.error('Детали:', data.details);
                            }
                            return;
                        }

                        $('#messagesContainer').append(data.message_html);
                        $('#message').val('');

                        if (data.private_chat_id) {
                            $('input[name="private_chat_id"]').val(data.private_chat_id);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Ошибка при отправке сообщения:', textStatus, errorThrown);
                    }
                });
            });


            $('#searchForm').on('submit', function(event) {
                event.preventDefault(); // Предотвращаем перезагрузку страницы
                
                const query = $('input[name="search_query"]').val().trim();
                if (query === '') {
                    console.log("Поисковый запрос пустой, запрос на сервер не отправляется.");
                    return;
                }
                
                $.ajax({
                    url: 'search_user.php',
                    method: 'POST',
                    dataType: 'json',
                    success: function(data) {
                        console.log('Search response:', data); // Выводим ответ сервера в консоль
                        allUsers = data;
                        filterUsers();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Ошибка при поиске пользователей:', textStatus, errorThrown);
                        console.error('Ответ сервера:', jqXHR.responseText);
                    }
                });
            });

            // Фильтрация пользователей на клиенте
            function filterUsers() {
                const query = $('input[name="search_query"]').val().toLowerCase();
                $('#searchResults').empty();
                allUsers.forEach(user => {
                    const fullName = (user.first_name + ' ' + user.last_name).toLowerCase();
                    if (fullName.includes(query)) {
                        $('#searchResults').append(
                            '<li><a href="#" class="start-chat" data-id="' + user.id + '">' + user.first_name + ' ' + user.last_name + '</a></li>'
                        );
                    }
                });
                if ($('#searchResults').children().length === 0) {
                    $('#searchResults').append('<li>Пользователи не найдены</li>');
                }
            }

            $(document).on('input', 'input[name="search_query"]', filterUsers);

            // Обработчик начала чата
            $(document).on('click', '.start-chat', function(event) {
                event.preventDefault();
                const userId = $(this).data('id');
                $.ajax({
                    url: 'start_chat.php',
                    method: 'POST',
                    data: { recipient_id: userId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.error('Ошибка:', data.error);
                            return;
                        }

                        // Переход к чату
                        window.location.href = 'chat.php?private_chat_id=' + data.private_chat_id;
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Ошибка при создании чата:', textStatus, errorThrown);
                    }
                });
            });
        });
    </script>
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
                <div class="right_header">
                    <a href="user.php" id="name_user"><?= htmlspecialchars($_SESSION['user']['first_name']) ?></a>
                    <a href="index.php">Выход</a>
                </div>
            </div>
        </header>
        <div class="main-container">
            <div class="chat-list">
                <form id="searchForm" method="POST">
                    <input type="text" name="search_query" placeholder="Поиск студентов">
                    <button type="submit" name="search_student">Найти</button>
                </form>
                <ul id="searchResults"></ul>
                <ul>
                    <li><strong>Личные чаты</strong></li>
                    <?php if (!empty($private_chats)): ?>
                        <?php foreach ($private_chats as $private_chat): ?>
                            <li>
                                <a href="chat.php?private_chat_id=<?= $private_chat['id'] ?>">
                                    <?php
                                    $key = base64_decode($private_chat['encryption_key']);
                                    $first_name = decrypt($private_chat['first_name'], $key);
                                    $last_name = decrypt($private_chat['last_name'], $key);
                                    echo htmlspecialchars($first_name . ' ' . $last_name);
                                    ?>
                                </a>
                                <form action="delete_chat.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="private_chat_id" value="<?= $private_chat['id'] ?>">
                                    <button type="submit" class="delete-chat-btn">Удалить</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Нет личных чатов</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="chat-window">
                <?php if (isset($_GET['private_chat_id'])): ?>
                    <?php
                    $private_chat_id = $_GET['private_chat_id'];
                    $messages_stmt = $pdo->prepare("SELECT m.*, u.first_name, u.last_name, u.encryption_key FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.private_chat_id = ? ORDER BY m.created_at ASC");
                    $messages_stmt->execute([$private_chat_id]);
                    $messages = $messages_stmt->fetchAll();
                    ?>
                    <div class="messages" id="messagesContainer">
                        <?php
                        $last_date = null;
                        foreach ($messages as $message):
                            $current_date = formatDate($message['created_at']);
                            if ($current_date != $last_date):
                                $last_date = $current_date;
                        ?>
                            <div class="date-indicator">
                                <span><?= $current_date ?></span>
                            </div>
                        <?php endif; ?>
                            <?php
                            $key = base64_decode($message['encryption_key']);
                            $first_name = decrypt($message['first_name'], $key);
                            $last_name = decrypt($message['last_name'], $key);
                            ?>
                            <div class="message <?= $message['sender_id'] == $user_id ? 'sender' : 'receiver' ?>">
                                <strong><?= htmlspecialchars($first_name . ' ' . $last_name) ?>:</strong>
                                <p><?= htmlspecialchars($message['message']) ?></p>
                                <span><?= formatTime($message['created_at']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form id="messageForm">
                        <input type="hidden" name="private_chat_id" value="<?= $private_chat_id ?>">
                        <input type="hidden" name="sender_id" value="<?= $user_id ?>">
                        <input type="hidden" name="recipient_id">
                        <textarea name="message" id="message" placeholder="Введите ваше сообщение"></textarea>
                        <button type="submit">Отправить</button>
                    </form>
                <?php else: ?>
                    <p>Выберите чат для начала общения</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <footer></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="sidebar.js"></script>
</body>
</html>
