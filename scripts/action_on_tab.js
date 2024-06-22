const tabsList = document.getElementById('tabsList');
const mainContainer = document.querySelector('.main_container');
let isTeacher;
let tabCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    fetchTabs();
    checkUserRole();
});

function createTabHandler() {
    if (!isTeacher) {
        console.error('Только пользователи с ролью "teacher" могут создавать вкладки.');
        return; // Прерываем функцию, если пользователь не является учителем
    }
    
    tabCounter++;
    const newTabId = 'tabContent' + tabCounter;

    // Создаем новую вкладку
    const tab = document.createElement('li');
    tab.textContent = 'Вкладка ' + tabCounter;
    tab.classList.add('table_list');
    tab.setAttribute('data-content-id', newTabId);
    tabsList.appendChild(tab);

    // Создаем кнопку удаления для каждой вкладки
    const deleteButton = document.createElement('button');
    deleteButton.textContent = 'D';
    deleteButton.classList.add('button_delete'); // Добавляем класс для стилизации
    deleteButton.onclick = function() { deleteTab(tabCounter, tab); }; // Добавляем обработчик для удаления

    tab.appendChild(deleteButton); // Добавляем кнопку удаления к вкладке


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

    // Функция для добавления вкладки в БД
    addTabToDatabase('Вкладка ' + tabCounter);

};

function deleteTab(tabId, tabElement) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteTab.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Удаляем элемент вкладки из DOM
                tabElement.parentNode.removeChild(tabElement);
                console.log('Успешное удаление вкладки.');
            } else {
                console.error('Ошибка при удалении вкладки:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };

    xhr.send('tabId=' + encodeURIComponent(tabId));
}

// Функция для запроса роли пользователя и установки слушателя для кнопки
function checkUserRole() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getRole.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            console.log('Ответ сервера:', response); // Добавьте этот лог
            isTeacher = response.isTeacher;
            console.log('Пользователь - учитель:', isTeacher); // Добавьте этот лог
            const createTabButton = document.getElementById('createTabButton');
            if (isTeacher) {
                // Устанавливаем обработчик событий только если пользователь - учитель
                createTabButton.addEventListener('click', createTabHandler);
            } else {
                // Если пользователь не учитель, скрываем кнопку создания вкладки
                createTabButton.style.display = 'none';
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

function addTabToDatabase(tabName) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'addTab.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Вкладка добавлена в базу данных.');
                // После успешного добавления вкладки в базу данных
                // вызываем fetchTabs, чтобы заново получить и отобразить список вкладок
                fetchTabs(); // Это обновит список вкладок, включая новую вкладку
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

function populateTabs(tabs) {
    // Очистите tabsList перед добавлением новых элементов, чтобы избежать дублирования
    tabsList.innerHTML = '';

    tabs.forEach(function(tab) {
        const li = document.createElement('li');
        li.textContent = tab.name;
        li.classList.add('table_list');
        li.setAttribute('data-tab-id', tab.id);

        // Создаем кнопку удаления
        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'D';
        deleteButton.classList.add('button_delete');
        deleteButton.addEventListener('click', function() {
            deleteTab(tab.id, li);
        });

        li.appendChild(deleteButton);
        tabsList.appendChild(li);
    });
}


function fetchTabs() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getTabs.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    populateTabs(response.tabs);
                } else {
                    console.error('Ошибка при получении вкладок: ', response.message);
                }
            } catch (e) {
                // Выводим содержимое ответа для диагностики
                console.error('Ошибка при разборе JSON:', e);
                console.error('Ответ сервера:', xhr.responseText);
            }
        } else {
            console.error('Ошибка запроса: ', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };

    xhr.send();
}
