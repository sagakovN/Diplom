const tabsList = document.getElementById('tabsList');
const mainContainer = document.querySelector('.tasks');
const groupModal = document.getElementById('groupModal');
const closeModal = document.querySelector('.close');
let isTeacher;
let tabCounter = 0;
let currentTabId = null;  // Сохранение текущего таба

document.addEventListener('DOMContentLoaded', function () {
    checkUserRole();
    hideCommentsSection();
});

closeModal.onclick = function() {
    groupModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == groupModal) {
        groupModal.style.display = "none";
    }
}

function openGroupModal(tabId) {
    groupModal.style.display = "block";
    document.getElementById('tabId').value = tabId;
}

document.getElementById('assignGroupsForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'assignGroups.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Таблица успешно назначена группам.');
                groupModal.style.display = 'none'; // Закрыть модальное окно
                // Обновить отображение групп в текущем задании
                loadTabContent(formData.get('tabId'));
            } else {
                console.error('Ошибка при назначении таблицы: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(new URLSearchParams(formData).toString());
});


function hideCommentsSection() {
    const commentsSection = document.getElementById('commentsSection');
    if (commentsSection) {
        commentsSection.style.display = 'none';
    }
}

function createTabHandler() {
    if (!isTeacher) {
        console.error('Только пользователи с ролью "teacher" могут создавать вкладки.');
        return; // Прерываем функцию, если пользователь не является учителем
    }

    const tabName = prompt("Введите название вкладки:", "Вкладка " + (tabCounter + 1));
    if (!tabName) {
        console.error('Необходимо ввести название вкладки.');
        return; // Прерываем функцию, если название не было введено
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'addTab.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Вкладка успешно добавлена в базу данных.');
                fetchTabs();

                // Автоматически открываем форму для создания задания
                createContentForm(response.tabId, tabName);
            } else {
                console.error('Ошибка при добавлении вкладки: ', response.message);
            }
        } else {
            console.error('Ошибка запроса: ', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send('tabName=' + encodeURIComponent(tabName));
}

function createContentForm(tabId, tabName) {
    console.log('Создание формы контента для вкладки:', tabId, tabName);
    const formContainer = document.querySelector('.wrapp-3');
    formContainer.innerHTML = '';

    const form = document.createElement('form');
    form.enctype = 'multipart/form-data'; // Добавить атрибут для загрузки файлов
    form.innerHTML = `
        <h3>Контент для "${tabName}"</h3>
        <input type="text" name="topicName" placeholder="Название темы" required>
        <label>Прикрепить файлы:</label>
        <input type="file" name="fileUpload[]" multiple>
        <button type="submit">Сохранить контент</button>
    `;
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('tabId', tabId);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'addTask.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log('Контент успешно добавлен в базу данных.');
                        loadTabContent(tabId, tabName); // Обновить содержимое вкладки после добавления задания
                    } else {
                        console.error('Ошибка при добавлении контента: ', response.message);
                    }
                } catch (e) {
                    console.error('Ошибка при разборе JSON:', e);
                    console.error('Ответ сервера:', xhr.responseText);
                }
            } else {
                console.error('Ошибка запроса:', xhr.status);
                console.error('Ответ сервера:', xhr.responseText);
            }
        };
        xhr.onerror = function() {
            console.error('Ошибка сети при попытке отправить запрос.');
        };
        xhr.send(formData);
    });

    formContainer.appendChild(form);
}


function fetchTabs() {
    console.log('Запрос вкладок...');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getTabs.php', true);
    xhr.onload = function() {
        console.log('Ответ сервера для вкладок получен:', xhr.status);
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Ответ сервера для вкладок:', response);
                if (response.success) {
                    populateTabs(response.tabs);
                } else {
                    console.error('Ошибка при получении вкладок: ', response.message);
                }
            } catch (e) {
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

function populateTabs(tabs) {
    console.log('Заполнение списка таблиц...');
    const tabsList = document.getElementById('tabsList');
    tabsList.innerHTML = '';

    if (tabs.length === 0) {
        tabsList.innerHTML = '<li>Нет доступных таблиц</li>';
        return;
    }

    tabs.forEach(function(tab) {
        const li = document.createElement('li');
        li.classList.add('table_list');
        li.setAttribute('data-tab-id', tab.id);

        const span = document.createElement('span');
        span.textContent = tab.name;

        const editButton = document.createElement('button');
        editButton.classList.add('button_edit');
        editButton.innerHTML = '<i class="lni lni-grid-alt"></i>';
        editButton.onclick = function() {
            showEditTabForm(tab.id, tab.name);
        };

        const deleteButton = document.createElement('button');
        deleteButton.classList.add('button_delete');
        deleteButton.innerHTML = '<i class="lni lni-trash"></i>';
        deleteButton.onclick = function() {
            deleteTab(tab.id, li);
        };

        li.appendChild(span);
        li.appendChild(editButton);
        li.appendChild(deleteButton);
        li.onclick = function(event) {
            if (event.target === li || event.target === span) {
                loadTabContent(tab.id, tab.name);
            }
        };

        tabsList.appendChild(li);
    });
    console.log('Список таблиц заполнен.');
}

function showEditTabForm(tabId, currentName) {
    const newName = prompt('Введите новое название для вкладки:', currentName);
    if (newName) {
        updateTabName(tabId, newName);
    }
}

function updateTabName(tabId, newName) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'updateTabName.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Название вкладки успешно обновлено.');
                fetchTabs(); // Перезагрузить список вкладок
            } else {
                console.error('Ошибка при обновлении названия вкладки: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(`tabId=${tabId}&name=${encodeURIComponent(newName)}`);
}

function loadTabContent(tabId) {
    if (typeof tabId !== 'number' && typeof tabId !== 'string') {
        console.error('Некорректный tabId:', tabId);
        return;
    }

    console.log('Загрузка содержимого вкладки с tabId:', tabId);
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getTabContent.php?tabId=${tabId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log('Ответ сервера:', xhr.responseText);
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Полученные данные содержимого вкладки:', response);
                    document.getElementById('assignmentId').value = response.assignment.id; // Установим assignmentId в скрытое поле
                    displayTabContent(response.assignment, tabId, response.groups, response.descriptions, response.files);
                } else {
                    console.error('Ошибка при получении контента вкладки:', response.message);
                    if (response.message === 'Задание не найдено.') {
                        createContentForm(tabId, response.assignment ? response.assignment.title : '');
                    }
                }
            } catch (e) {
                console.error('Ошибка при разборе JSON:', e);
                console.error('Ответ сервера:', xhr.responseText);
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


function displayTabContent(assignment, tabId, groups, descriptions, files) {
    const formContainer = document.querySelector('.wrapp-3');
    let groupsText = 'Эту таблицу видят группы: ' + (groups.length ? groups.join(', ') : 'Нет назначенных групп');

    const escapeHtml = (unsafe) => {
        if (typeof unsafe !== 'string') {
            return '';
        }
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    let descriptionsHtml = '';
    if (descriptions && descriptions.length > 0) {
        descriptionsHtml = '<div class="description-container">';
        descriptions.forEach(description => {
            descriptionsHtml += `
                <div class="description-item">
                    <p>${escapeHtml(description.description)}</p>
                    <button class="edit_button" onclick="showEditDescriptionForm(${description.id}, '${escapeHtml(description.description)}', ${tabId})">
                        <i class="lni lni-grid-alt"></i>
                    </button>
                    <button class="delete_button" onclick="deleteDescription(${description.id}, ${tabId})">
                        <i class="lni lni-trash"></i>
                    </button>
                </div>
            `;
        });
        descriptionsHtml += '</div>';
    } else {
        descriptionsHtml = '<p>Нет описаний</p>';
    }

    let teacherFilesHtml = '<div class="files-container"><h3>Прикрепленные файлы преподавателя:</h3>';
    let studentFilesHtml = '<div class="files-container"><h3>Прикрепленные файлы студентов:</h3>';

    files.forEach(file => {
        const fileHtml = `
            <div class="file-item">
                <a href="${file.file_path}" download>${escapeHtml(file.file_name)}</a>
                <button class="delete_button" onclick="deleteFile(${file.id}, ${assignment.id}, ${tabId})">
                    <i class="lni lni-trash"></i>
                </button>
            </div>
        `;
        if (file.uploaded_by_role === 'teacher') {
            teacherFilesHtml += fileHtml;
        } else {
            studentFilesHtml += fileHtml;
        }
    });

    teacherFilesHtml += '</div>';
    studentFilesHtml += '</div>';

    formContainer.innerHTML = `
        <div class="title-container">
            <h3>${escapeHtml(assignment.title)}</h3>
            <button class="edit_button" onclick="editAssignment(${assignment.id}, '${escapeHtml(assignment.title)}')">
                <i class="lni lni-grid-alt"></i>
            </button>
        </div>
        ${descriptionsHtml}
        ${teacherFilesHtml}
        ${studentFilesHtml}
        <button class="add_description_button" onclick="showAddDescriptionForm(${tabId})">
            <i class="lni lni-plus"></i> Добавить описание
        </button>
        <button class="assign_group_button" onclick="openGroupModal(${tabId})">
            <i class="lni lni-plus"></i> Назначить группу
        </button>
        <button class="attach_file_button" onclick="showFileUploadForm(${assignment.id}, ${tabId}, '${escapeHtml(assignment.title)}')">
            <i class="lni lni-plus"></i> Прикрепить файл
        </button>
        <p>${groupsText}</p>
    `;

    fetchComments(assignment.id);

    const commentsSection = document.getElementById('commentsSection');
    if (commentsSection) {
        commentsSection.style.display = 'block';
        console.log('Секция комментариев показана.');
    } else {
        console.error('Секция комментариев не найдена.');
    }

    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        const newCommentForm = commentForm.cloneNode(true);
        commentForm.parentNode.replaceChild(newCommentForm, commentForm);
        newCommentForm.addEventListener('submit', function(event) {
            event.preventDefault();
            submitComment(assignment.id);
        });
        console.log('Форма комментариев найдена и обработчик событий установлен.');
    } else {
        console.error('Форма комментариев не найдена.');
    }
}

// Функция для добавления реакции
function addReaction(commentId, reactionType) {
    const assignmentId = document.getElementById('assignmentId').value; // Получим assignmentId из скрытого поля
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'addReaction.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Обновляем комментарии после добавления реакции
                fetchComments(assignmentId);
            } else {
                console.error('Ошибка при добавлении реакции:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(`commentId=${commentId}&reactionType=${reactionType}`);
}

function fetchComments(assignmentId) {
    if (!assignmentId) {
        console.error('Некорректный assignmentId:', assignmentId);
        return;
    }

    console.log('Загрузка комментариев для задания:', assignmentId);
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getComments.php?assignmentId=${assignmentId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    displayComments(response.comments);
                } else {
                    console.error('Ошибка при получении комментариев: ', response.message);
                }
            } catch (e) {
                console.error('Ошибка при разборе JSON:', e);
                console.error('Ответ сервера:', xhr.responseText);
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

function displayComments(comments) {
    console.log("Отображение комментариев:", comments); // Лог для проверки данных
    const commentsContainer = document.getElementById('commentsContainer');
    if (commentsContainer) {
        commentsContainer.innerHTML = '';

        comments.forEach(comment => {
            const commentElement = document.createElement('div');
            commentElement.classList.add('comment');
            commentElement.innerHTML = `
                <p><strong>${escapeHtml(comment.user_name)}</strong> <em>${formatDate(new Date(comment.created_at))}</em></p>
                <p>${escapeHtml(comment.comment)}</p>
                <div class="reactions">
                    <button class="like-button" data-comment-id="${comment.id}">👍 ${comment.likes}</button>
                    <button class="dislike-button" data-comment-id="${comment.id}">👎 ${comment.dislikes}</button>
                </div>
            `;
            commentsContainer.appendChild(commentElement);
        });

        // Добавляем обработчики событий для кнопок лайков и дизлайков
        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', function() {
                addReaction(this.dataset.commentId, 'like');
            });
        });

        document.querySelectorAll('.dislike-button').forEach(button => {
            button.addEventListener('click', function() {
                addReaction(this.dataset.commentId, 'dislike');
            });
        });

        console.log('Комментарии успешно отображены.');
    } else {
        console.error('Контейнер комментариев не найден.');
    }
}

function escapeHtml(unsafe) {
    return unsafe.replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
}

function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('ru-RU', options);
}

function editAssignment(id, title) {
    console.log('Редактирование задания:', id, title);
    const formContainer = document.querySelector('.wrapp-3');
    if (!formContainer) {
        console.error('Элемент .wrapp-3 не найден');
        return;
    }

    formContainer.innerHTML = `
        <div class="edit-assignment-form">
            <h3>Редактировать задачу</h3>
            <form id="editAssignmentForm">
                <input type="text" name="title" value="${title}" placeholder="Название задачи" required>
                <button type="submit">Сохранить изменения</button>
            </form>
        </div>
    `;

    const form = document.getElementById('editAssignmentForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('assignmentId', id);

        // Вывод данных в консоль для проверки
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'editAssignment.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Задача успешно обновлена.');
                    loadTabContent(response.tabId); // Перезагрузить содержимое вкладки
                } else {
                    console.error('Ошибка при обновлении задачи: ', response.message);
                }
            } else {
                console.error('Ошибка запроса:', xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Ошибка сети при попытке отправить запрос.');
        };
        xhr.send(formData);
    });
}

function deleteTab(tabId, tabElement) {
    console.log('Удаление вкладки:', tabId);

    // Сначала нужно получить идентификаторы заданий, связанных с этой вкладкой
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getAssignmentsByTab.php?tab_id=${tabId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                const assignmentIds = response.assignmentIds;
                if (assignmentIds.length > 0) {
                    // Удаление вкладки и связанных заданий
                    deleteTabAndAssignments(tabId, assignmentIds, tabElement);
                } else {
                    // Нет связанных заданий, просто удаляем вкладку
                    deleteTabOnly(tabId, tabElement);
                }
            } else {
                console.error('Ошибка при получении связанных заданий: ', response.message);
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

function deleteTabOnly(tabId, tabElement) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteTab.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                tabElement.parentNode.removeChild(tabElement);
                // Очистка содержимого вкладки
                const formContainer = document.querySelector('.wrapp-3');
                formContainer.innerHTML = ''; // Очистка содержимого вкладки
                hideCommentsSection(); // Скрыть блок комментариев
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

    xhr.send(`tabId=${encodeURIComponent(tabId)}`);
}

function deleteTabAndAssignments(tabId, assignmentIds, tabElement) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteTab.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                tabElement.parentNode.removeChild(tabElement);
                // Очистка содержимого вкладки
                const formContainer = document.querySelector('.wrapp-3');
                formContainer.innerHTML = ''; // Очистка содержимого вкладки
                hideCommentsSection(); // Скрыть блок комментариев
                console.log('Успешное удаление вкладки и её контента.');
            } else {
                console.error('Ошибка при удалении вкладки и её контента:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };

    const encodedAssignmentIds = assignmentIds.map(id => encodeURIComponent(id)).join(',');
    xhr.send(`tabId=${encodeURIComponent(tabId)}&assignmentIds=${encodedAssignmentIds}`);
}

// Функция для проверки роли пользователя и настройки кнопки создания вкладки
function checkUserRole() {
    console.log('Проверка роли пользователя...');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getRole.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            isTeacher = response.isTeacher;
            const createTabButton = document.getElementById('createTabButton');
            if (isTeacher) {
                createTabButton.addEventListener('click', createTabHandler);
                fetchTabs(); // Запрашиваем вкладки только для преподавателя
            } else {
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

// Обработчик для отправки формы назначения таблиц группам
document.getElementById('assignGroupsForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'assignGroups.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Таблица успешно назначена группам.');
                groupModal.style.display = 'none'; // Закрыть модальное окно
                // Обновить отображение групп в текущем задании
                loadTabContent(formData.get('tabId'), response.tabName);
            } else {
                console.error('Ошибка при назначении таблицы: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(formData);
});

function deleteDescription(descriptionId, tabId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteDescription.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Описание успешно удалено.');
                loadTabContent(tabId); // Перезагрузить содержимое вкладки
            } else {
                console.error('Ошибка при удалении описания: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(`descriptionId=${descriptionId}`);
}

function showAddDescriptionForm(tabId) {
    const formContainer = document.querySelector('.wrapp-3');
    if (document.getElementById('addDescriptionForm')) {
        console.log('Форма для добавления описания уже существует.');
        return;
    }

    formContainer.innerHTML += `
        <div class="add-description-form">
            <h3>Добавить описание</h3>
            <form id="addDescriptionForm">
                <textarea name="description" placeholder="Введите описание" required></textarea>
                <input type="hidden" name="tabId" value="${tabId}">
                <button type="submit">Сохранить описание</button>
            </form>
        </div>
    `;

    const form = document.getElementById('addDescriptionForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        
        // Log formData entries for debugging
        for (let pair of formData.entries()) {
            console.log(pair[0]+ ': ' + pair[1]);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'addDescription.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Описание успешно добавлено.');
                    loadTabContent(tabId); // Перезагрузить содержимое вкладки
                } else {
                    console.error('Ошибка при добавлении описания: ', response.message);
                }
            } else {
                console.error('Ошибка запроса:', xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Ошибка сети при попытке отправить запрос.');
        };
        xhr.send(formData);
    });
}

function fetchDescriptions(assignmentId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getDescriptions.php?assignmentId=${assignmentId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                const descriptionsContainer = document.getElementById('descriptionsContainer');
                descriptionsContainer.innerHTML = '';
                response.descriptions.forEach(description => {
                    descriptionsContainer.innerHTML += `<p>${escapeHtml(description.description)}</p>`;
                });
            } else {
                console.error('Ошибка при получении описаний: ', response.message);
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

function fetchComments(assignmentId) {
    console.log('Загрузка комментариев для задания:', assignmentId);
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getComments.php?assignmentId=${assignmentId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    displayComments(response.comments);
                } else {
                    console.error('Ошибка при получении комментариев: ', response.message);
                }
            } catch (e) {
                console.error('Ошибка при разборе JSON:', e);
                console.error('Ответ сервера:', xhr.responseText);
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

function displayComments(comments) {
    console.log("Отображение комментариев:", comments); // Лог для проверки данных
    const commentsContainer = document.getElementById('commentsContainer');
    if (commentsContainer) {
        commentsContainer.innerHTML = '';

        comments.forEach(comment => {
            const commentElement = document.createElement('div');
            commentElement.classList.add('comment');
            commentElement.innerHTML = `
                <p><strong>${escapeHtml(comment.user_name)}</strong> <em>${formatDate(new Date(comment.created_at))}</em></p>
                <p>${escapeHtml(comment.comment)}</p>
                <div class="reactions">
                    <button class="like-button" data-comment-id="${comment.id}">👍 ${comment.likes}</button>
                    <button class="dislike-button" data-comment-id="${comment.id}">👎 ${comment.dislikes}</button>
                </div>
            `;
            commentsContainer.appendChild(commentElement);
        });

        // Добавляем обработчики событий для кнопок лайков и дизлайков
        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', function() {
                addReaction(this.dataset.commentId, 'like');
            });
        });

        document.querySelectorAll('.dislike-button').forEach(button => {
            button.addEventListener('click', function() {
                addReaction(this.dataset.commentId, 'dislike');
            });
        });

        console.log('Комментарии успешно отображены.');
    } else {
        console.error('Контейнер комментариев не найден.');
    }
}

function addReaction(commentId, reactionType) {
    const assignmentId = document.getElementById('assignmentId').value; // Получим assignmentId из скрытого поля
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'addReaction.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Обновляем комментарии после добавления реакции
                fetchComments(assignmentId);
            } else {
                console.error('Ошибка при добавлении реакции:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(`commentId=${commentId}&reactionType=${reactionType}`);
}

function submitComment(assignmentId) {
    const commentForm = document.getElementById('commentForm');
    const formData = new FormData(commentForm);
    formData.append('assignmentId', assignmentId);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'addComment.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                fetchComments(assignmentId);
                commentForm.reset();
            } else {
                console.error('Ошибка при добавлении комментария: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(formData);
}

function escapeHtml(unsafe) {
    return unsafe.replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
}

function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('ru-RU', options);
}

function editAssignment(id, title) {
    console.log('Редактирование задания:', id, title);
    const formContainer = document.querySelector('.wrapp-3');
    if (!formContainer) {
        console.error('Элемент .wrapp-3 не найден');
        return;
    }

    formContainer.innerHTML = `
        <div class="edit-assignment-form">
            <h3>Редактировать задачу</h3>
            <form id="editAssignmentForm">
                <input type="text" name="title" value="${title}" placeholder="Название задачи" required>
                <button type="submit">Сохранить изменения</button>
            </form>
        </div>
    `;

    const form = document.getElementById('editAssignmentForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('assignmentId', id);

        // Вывод данных в консоль для проверки
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'editAssignment.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Задача успешно обновлена.');
                    loadTabContent(response.tabId); // Перезагрузить содержимое вкладки
                } else {
                    console.error('Ошибка при обновлении задачи: ', response.message);
                }
            } else {
                console.error('Ошибка запроса:', xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Ошибка сети при попытке отправить запрос.');
        };
        xhr.send(formData);
    });
}

// Функция для проверки роли пользователя и настройки кнопки создания вкладки
function checkUserRole() {
    console.log('Проверка роли пользователя...');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getRole.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            isTeacher = response.isTeacher;
            const createTabButton = document.getElementById('createTabButton');
            if (isTeacher) {
                createTabButton.addEventListener('click', createTabHandler);
                fetchTabs(); // Запрашиваем вкладки только для преподавателя
            } else {
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

// Обработчик для отправки формы назначения таблиц группам
document.getElementById('assignGroupsForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'assignGroups.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Таблица успешно назначена группам.');
                groupModal.style.display = 'none'; // Закрыть модальное окно
                // Обновить отображение групп в текущем задании
                loadTabContent(formData.get('tabId'), response.tabName);
            } else {
                console.error('Ошибка при назначении таблицы: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(formData);
});

function showEditDescriptionForm(descriptionId, currentDescription, tabId) {
    // Удаляем любые существующие формы редактирования
    const existingForm = document.getElementById('editDescriptionForm');
    if (existingForm) {
        existingForm.remove();
    }

    // Создаем новую форму редактирования
    const formContainer = document.querySelector('.wrapp-3');
    const editForm = document.createElement('form');
    editForm.id = 'editDescriptionForm';
    editForm.innerHTML = `
        <h3>Редактировать описание</h3>
        <textarea name="description" placeholder="Введите описание" required>${currentDescription}</textarea>
        <button type="submit">Сохранить изменения</button>
        <button type="button" onclick="cancelEditDescriptionForm()">Отмена</button>
    `;
    
    editForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const newDescription = editForm.querySelector('textarea[name="description"]').value;
        updateDescription(descriptionId, newDescription, tabId);
    });

    formContainer.appendChild(editForm);
}

function cancelEditDescriptionForm() {
    const editForm = document.getElementById('editDescriptionForm');
    if (editForm) {
        editForm.remove();
    }
}

function updateDescription(descriptionId, newDescription, tabId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'editDescription.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Описание успешно обновлено.');
                loadTabContent(tabId); // Перезагрузить содержимое вкладки
            } else {
                console.error('Ошибка при обновлении описания: ', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(`descriptionId=${descriptionId}&description=${encodeURIComponent(newDescription)}`);
}

function showFileUploadForm(assignmentId) {
    const formContainer = document.querySelector('.wrapp-3');
    const existingForm = document.getElementById('uploadFileForm');
    if (existingForm) {
        existingForm.remove();
    }

    const uploadForm = document.createElement('form');
    uploadForm.id = 'uploadFileForm';
    uploadForm.enctype = 'multipart/form-data';
    uploadForm.innerHTML = `
        <h3>Прикрепить файл</h3>
        <input type="hidden" name="assignmentId" value="${assignmentId}">
        <label for="fileUpload">Прикрепить файлы:</label>
        <input type="file" id="fileUpload" name="fileUpload[]" multiple>
        <button type="submit">Загрузить</button>
        <button type="button" onclick="cancelFileUploadForm()">Отмена</button>
    `;
    uploadForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(uploadForm);
        console.log("Assignment ID before sending: ", assignmentId);  // Добавим логирование
        formData.append('assignmentId', assignmentId);  // Добавляем assignmentId в FormData для гарантии

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'uploadFiles.php', true);

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Файлы успешно загружены.');
                    loadTabContent(tabId); // Обновить содержимое вкладки после загрузки файлов
                } else {
                    console.error('Ошибка при загрузке файлов:', response.message);
                }
            } else {
                console.error('Ошибка запроса:', xhr.status);
            }
        };

        xhr.onerror = function() {
            console.error('Ошибка сети при попытке отправить запрос.');
        };

        xhr.send(formData);
    });

    formContainer.appendChild(uploadForm);
}

function cancelFileUploadForm() {
    const uploadForm = document.getElementById('uploadFileForm');
    if (uploadForm) {
        uploadForm.remove();
    }
}

function deleteFile(fileId, assignmentId, tabId) {
    if (!confirm('Вы уверены, что хотите удалить этот файл?')) {
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteFile.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log('Файл успешно удален.');
                loadTabContent(tabId); // Обновить содержимое вкладки после удаления файла
            } else {
                console.error('Ошибка при удалении файла:', response.message);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('Ошибка сети при попытке отправить запрос.');
    };
    xhr.send(`fileId=${fileId}&assignmentId=${assignmentId}`);
}
