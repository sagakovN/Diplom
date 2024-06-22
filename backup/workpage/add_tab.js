document.addEventListener('DOMContentLoaded', function () {
    const tabsList = document.getElementById('tabsList');
    const mainContainer = document.querySelector('.main_container');
    let isTeacher;

    // Функция для запроса роли пользователя
    function checkUserRole() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'roleVerification.php', true); // Замените 'roleVerification.php' на реальный путь к файлу

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                isTeacher = response.isTeacher;
                if (!isTeacher) {
                    // Если пользователь не учитель, можно скрыть кнопку создания вкладки
                    document.getElementById('createTabButton').style.display = 'none';
                }
            } else {
                console.error('Ошибка при проверке роли: ', xhr.status);
            }
        };

        xhr.onerror = function() {
            console.error('Ошибка сети при попытке запроса роли пользователя.');
        };

        xhr.send();
    }

    checkUserRole();

    document.getElementById('createTabButton').addEventListener('click', function() {
        if (!isTeacher) {
            console.error('Только пользователи с ролью "teacher" могут создавать вкладки.');
            return; // Прерываем функцию, если пользователь не является учителем
        }
        tabCounter++;
        const newTabId = 'tabContent' + tabCounter;

        // Создаем новую вкладку
        const tab = document.createElement('li');
        tab.textContent = 'Вкладка ' + tabCounter;
        tab.setAttribute('data-content-id', newTabId);
        tabsList.appendChild(tab);

        // Создаем контент для новой вкладки
        const tabContent = document.createElement('div');
        tabContent.id = newTabId;
        tabContent.style.display = 'none'; // Скрыть контент при создании
        tabContent.textContent = 'Контент для Вкладки ' + tabCounter;
        mainContainer.appendChild(tabContent);

        // Добавляем обработчик для новой вкладки
        tab.addEventListener('click', function() {
            // Скрываем все элементы контента
            document.querySelectorAll('.main_container > div').forEach(function(div) {
                div.style.display = 'none';
            });
            // Показываем контент текущей вкладки
            document.getElementById(this.getAttribute('data-content-id')).style.display = 'block';
        });

        // Автоматически выбираем новую вкладку
        tab.click();

        function addTabToDatabase(tabName) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'addTab.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Ответ сервера: ', response);
                    if (response.success) {
                        console.log('Вкладка добавлена в базу данных.');
                    } else {
                        console.error('Ошибка при добавлении вкладки: ', response.message);
                    }
                } else {
                    console.error('Ошибка запроса: ', xhr.status);
                }
            };
        
            xhr.onerror = function() {
                // Обработка ошибок сети
                console.error('Ошибка сети при попытке отправить запрос.');
            };
        
            xhr.send('tabName=' + encodeURIComponent(tabName));
        }
        
        // Внутри обработчика создания новой вкладки, после создания элементов в DOM
        addTabToDatabase('Вкладка ' + tabCounter);

    });
});
