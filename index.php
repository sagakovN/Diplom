<?php
session_start();
require("pdo_connect.php");
require("roleVerification.php");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/style.css">
    <link rel="stylesheet" href="style_css/footer.css">
    <link rel="shortcut icon" href="logo/logo_1_copy-removebg-preview.ico">
    <title>Главная страница</title>
</head>
<body>
    <header>
        <nav>
            <div class="logo_ul_list">
                <div class="logo">
                    <a href="index.php"><img src="logo/logo_1_copy-removebg-preview.ico" size></a>
                </div>
                <div>
                    <ul class="ul_list">
                        <li><a href="#">О нас</a></li>
                        <li><a href="#">Поддержка</a></li>
                        <li><a href="#">Справка</a></li>
                    </ul>
                </div>
            </div>
            <div class="button">
                <a href="authorization.php">Войти</a>
                <a href="registration.php">Регистрация</a>
            </div>
        </nav>
    </header>
    <div class="welcome">
        <img src="background/Remove-bg.ai_1710074713375.png" alt="">
    </div>
    <div class="information">
    <div class="selection_info">
        <button class="info_button active" onclick="openTab(event, 'Tab1')">Дистанционная работа</button>
        <button class="info_button" onclick="openTab(event, 'Tab2')">Чат</button>
        <button class="info_button" onclick="openTab(event, 'Tab3')">Статистика</button>
        <button class="info_button" onclick="openTab(event, 'Tab4')">Возможности</button>
    </div>
    <div id="Tab1" class="tab-content">
        <div class="p_info_tab_content">
            <p>Централизованное хранение учебных материалов</p>
            <p>Интерактивное выполнение и сдача заданий</p>
            <p>Обратная связь и оценивание в реальном времени</p>
            <p>Взаимодействие и коллаборация</p>
        </div>
        <img src="background/Remove-bg.ai_1712744026244.png" alt="Картинка 1">
    </div>
    <div id="Tab2" class="tab-content">
        <p>Чат платформы обеспечивает мгновенное общение между студентами и преподавателями, позволяя обсуждать учебные материалы, задавать вопросы и делиться идеями, что способствует созданию активного и вовлеченного образовательного сообщества.</p>
        <img src="background/Remove-bg.ai_1712744026244.png" alt="Картинка 2">
    </div>
    <div id="Tab3" class="tab-content">
        <p>Наглядный прогресс и аналитика: Блок статистики предоставляет подробные данные о выполнении заданий и активности участников, позволяя студентам и преподавателям отслеживать успехи, определять области для улучшения и адаптировать учебный процесс для достижения лучших результатов.</p>
        <img src="background/Remove-bg.ai_1712744026244.png" alt="Картинка 3">
    </div>
    <div id="Tab4" class="tab-content">
        <div class="p_info_tab_content">
            <p>Доступ к расширенным учебным материалам и курсам</p>
            <p>Инструменты для самостоятельного тестирования и оценки знаний</p>
            <p>Сотрудничество с индустриальными и научными партнерами</p>
            <p>Разработка портфолио и карьерное планирование</p>
            <p>Интерактивные мероприятия и сетевое взаимодействие</p>
        </div>
        <img src="background/Remove-bg.ai_1712744026244.png" alt="Картинка 4">
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
            <li>Discord</li>
        </ul>
        <ul>
            <li>Политика конфиденциальности</li>
        </ul>
    </footer>
    <script src="scripts/vision_div.js"></script>
    <script src="footer.js"></script>
</body>
</html>