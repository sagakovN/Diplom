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
                
                // Теперь найдем и удалим контент этой вкладки из mainContainer
                const tabContent = document.getElementById('tabContent' + tabId);
                if (tabContent) {
                    mainContainer.removeChild(tabContent);
                }
                
                console.log('Успешное удаление вкладки и её контента.');
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

// Обработчик нажатия для кнопки createTasksButton
document.getElementById('createTasksButton').addEventListener('click', function() {
    createTaskFormForActiveTab();
});

function loadAndDisplayAssignments(tabId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getAssignmentsByTab.php?tab_id=' + encodeURIComponent(tabId), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                const assignments = response.assignments; // assignments определена здесь
                const tabContent = document.getElementById('tabContent' + tabId);
                tabContent.innerHTML = ''; // Очистка предыдущего содержимого

                // Перебор и отображение каждого задания
                assignments.forEach(function(assignment) {
                    const div = document.createElement('div');
                    div.className = 'assignment';
                    div.innerHTML = `<h3>${assignment.title}</h3><p>${assignment.description}</p>`;

                    // Добавляем кнопку удаления для каждой задачи
                    const deleteButton = document.createElement('button');
                    deleteButton.textContent = 'Удалить';
                    deleteButton.onclick = function() { deleteAssignment(assignment.id, div); };
                    
                    // Добавляем кнопку редактирования
                    const editButton = document.createElement('button');
                    editButton.textContent = 'Редактировать';
                    editButton.onclick = function() { showEditForm(assignment, div); };
                    
                    div.appendChild(editButton);
                    div.appendChild(deleteButton);
                    tabContent.appendChild(div);
                });
            } else {
                console.error('Ошибка при загрузке заданий:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send();
}

function showEditForm(assignment, assignmentElement) {
    // Удаляем текущее содержимое задачи для замены его формой редактирования
    assignmentElement.innerHTML = '';

    // Создаем форму редактирования
    const form = document.createElement('form');
    form.innerHTML = `
        <label for="title">Название:</label>
        <input type="text" id="title" name="title" value="${assignment.title}">
        <label for="description">Описание:</label>
        <textarea id="description" name="description">${assignment.description}</textarea>
        <button type="submit">Сохранить изменения</button>
    `;
    
    form.onsubmit = function(event) {
        event.preventDefault();
        submitEditForm(new FormData(form), assignment.id, assignmentElement);
    };
    
    assignmentElement.appendChild(form);
}

function deleteAssignment(assignmentId, assignmentElement) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteAssignment.php', true); // Предполагается наличие скрипта deleteAssignment.php на сервере
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Удаляем элемент задачи из DOM
                assignmentElement.parentNode.removeChild(assignmentElement);
                console.log('Задача успешно удалена');
            } else {
                console.error('Ошибка при удалении задачи:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };

    xhr.send('assignmentId=' + encodeURIComponent(assignmentId));
}

// Эта функция вызывается каждый раз при клике на вкладку
function activateTab(tab) {
    const tabContentId = 'tabContent' + tab.getAttribute('data-tab-id');
    let tabContent = document.getElementById(tabContentId);

    // Скрываем все элементы контента
    document.querySelectorAll('.main_container > div').forEach(function(div) {
        div.style.display = 'none';
    });

    if (!tabContent) {
        // Если контент для вкладки не найден, создаем блок для контента
        tabContent = document.createElement('div');
        tabContent.id = tabContentId; // ID соответствует data-tab-id активной вкладки
        tabContent.classList.add('tab-content');
        // Пока оставим его пустым, т.к. форма будет добавлена при нажатии на кнопку
        mainContainer.appendChild(tabContent);
    }

    // Показываем контент только для активной вкладки
    tabContent.style.display = 'flex';
    tabContent.style.flexDirection = 'row';

    // Теперь проверяем, нужно ли отображать кнопку для создания задания
    checkTabContent(tab.getAttribute('data-tab-id'));

    // Загружаем и отображаем задания для активной вкладки
    const tabId = tab.getAttribute('data-tab-id');
    loadAndDisplayAssignments(tabId)
}

// Добавляем обработчик для всех существующих и будущих вкладок
tabsList.addEventListener('click', function(event) {
    if (event.target.matches('.table_list')) {
        // Деактивируем все вкладки
        document.querySelectorAll('.table_list').forEach(function(tab) {
            tab.classList.remove('active');
        });
        // Активируем кликнутую вкладку
        event.target.classList.add('active');
        activateTab(event.target);
    }
}, true);

// Изначально скрываем кнопку создания задания
document.getElementById('createTasksButton').style.display = 'none';

function checkTabContent(tabId) {
    // Запрос к серверу для проверки наличия сохраненной формы для вкладки
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'checkTabContent.php?tab_id=' + encodeURIComponent(tabId), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (isTeacher) {
                document.getElementById('createTasksButton').style.display = 'block';
            } else {
                document.getElementById('createTasksButton').style.display = 'none';
            }
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при проверке контента вкладки.');
    };
    xhr.send();
}

// Перепишите обработчик нажатия на вкладку, чтобы он также проверял контент
tabsList.addEventListener('click', function(event) {
    if (event.target.matches('.table_list')) {
        // ... код для переключения вкладки ...
        checkTabContent(event.target.getAttribute('data-tab-id'));
    }
}, true);

function createTaskFormForActiveTab() {
    const activeTab = document.querySelector('.table_list.active');
    const tabId = activeTab.getAttribute('data-tab-id');
    const tabContentId = 'tabContent' + tabId;
    let tabContent = document.getElementById(tabContentId);

    // Если нет контейнера для контента, создаем его
    if (!tabContent) {
        tabContent = document.createElement('div');
        tabContent.id = tabContentId;
        tabContent.classList.add('tab-content');
        mainContainer.appendChild(tabContent);
    }

    // Создаем форму внутри контейнера для контента
    const form = document.createElement('form');
    form.innerHTML = `
        <label for="title">Название:</label>
        <input type="text" id="title" name="title">
        <label for="description">Описание:</label>
        <textarea id="description" name="description"></textarea>
        <input type="hidden" name="tab_id" value="${tabId}">
        <button type="submit">Создать</button>
    `;
    form.onsubmit = submitTaskForm; // Убедитесь, что у вас есть функция submitTaskForm
    tabContent.appendChild(form);
}


function getActiveTabId() {
    // Вернуть id активной вкладки
    const activeTab = document.querySelector('.table_list.active'); // Предполагается, что у активной вкладки есть класс 'active'
    return activeTab ? activeTab.getAttribute('data-content-id') : null;
}

function submitTaskForm(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const tabContent = event.target.closest('.tab-content'); // Предположим, что форма находится внутри .tab-content

    // Отправить данные формы на сервер
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'createAssignment.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                const assignment = response.assignment;
                
                // Создаем элемент для нового задания
                const div = document.createElement('div');
                div.className = 'assignment';
                div.innerHTML = `<h3>${assignment.title}</h3><p>${assignment.description}</p>`;
                
                // Добавляем новое задание в контейнер текущей вкладки
                tabContent.appendChild(div);

                // Очистка формы
                event.target.remove();
            } else {
                console.error('Ошибка при добавлении задания:', response.message);
            }
        } else {
            console.error('Ошибка при создании задания: ', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при создании задания.');
    };
    xhr.send(formData);
}

function submitEditForm(formData, assignmentId, assignmentElement) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'editAssignment.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    // Формируем тело запроса из данных формы
    const encodedDataPairs = [];
    formData.forEach((value, key) => {
        encodedDataPairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
    });
    const urlEncodedData = encodedDataPairs.join('&') + '&assignmentId=' + encodeURIComponent(assignmentId);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Обновляем задачу в DOM на основе отредактированных данных
                assignmentElement.innerHTML = `
                    <h3>${formData.get('title')}</h3>
                    <p>${formData.get('description')}</p>
                `;
    
                // Добавляем кнопку редактирования обратно к элементу задачи
                const editButton = document.createElement('button');
                editButton.textContent = 'Редактировать';
                editButton.onclick = function() { showEditForm(response.assignment, assignmentElement); };
                assignmentElement.appendChild(editButton);
                
                // Добавляем кнопку удаления обратно к элементу задачи
                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Удалить';
                deleteButton.onclick = function() { deleteAssignment(response.assignment.id, assignmentElement); };
                assignmentElement.appendChild(deleteButton);
    
                console.log('Задача успешно обновлена');
            } else {
                console.error('Ошибка при обновлении задачи:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };

    xhr.send(urlEncodedData);
}

