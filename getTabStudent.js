document.addEventListener('DOMContentLoaded', function () {
    checkUserRole();
});

function checkUserRole() {
    console.log('Проверка роли пользователя...');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getRole.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Ответ сервера для роли:', response);
                isTeacher = response.isTeacher;
                const createTabButton = document.getElementById('createTabButton');
                if (isTeacher) {
                    createTabButton.addEventListener('click', createTabHandler);
                    fetchTabs(); // Запрашиваем вкладки только для преподавателя
                } else {
                    if (createTabButton) {
                        createTabButton.style.display = 'none';
                    }
                    fetchTabs(); // Запрашиваем вкладки для студента
                }
            } catch (e) {
                console.error('Ошибка при разборе JSON:', e);
                console.error('Ответ сервера:', xhr.responseText);
            }
        } else {
            console.error('Ошибка при проверке роли:', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Ошибка сети при попытке запроса роли пользователя.');
    };

    xhr.send();
}

function fetchTabs() {
    console.log('Запрос списка таблиц...');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'getTabs.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    if (response.teachersTabs) {
                        populateTabs(response.teachersTabs);
                    } else {
                        populateTabs(response.tabs);
                    }
                } else {
                    console.error('Ошибка при получении таблиц:', response.message);
                }
            } catch (e) {
                console.error('Ошибка при разборе JSON:', e);
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

function populateTabs(teachersTabs) {
    console.log('Заполнение списка таблиц...');
    const tabsList = document.getElementById('tabsList');
    tabsList.innerHTML = '';

    if (Object.keys(teachersTabs).length === 0) {
        tabsList.innerHTML = '<li>Нет доступных таблиц</li>';
        return;
    }

    for (const teacher in teachersTabs) {
        const teacherHeader = document.createElement('li');
        teacherHeader.textContent = teacher;
        teacherHeader.classList.add('teacher-header');
        tabsList.appendChild(teacherHeader);

        teachersTabs[teacher].forEach(tab => {
            const li = document.createElement('li');
            li.textContent = tab.tab_name;
            li.classList.add('table_list');
            li.setAttribute('data-tab-id', tab.tab_id);

            li.addEventListener('click', function() {
                loadTabContent(tab.tab_id, tab.tab_name);
            });

            tabsList.appendChild(li);
        });
    }
    console.log('Список таблиц заполнен.');
}

function loadTabContent(tabId, tabName) {
    console.log('Загрузка содержимого вкладки:', tabId);
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getTabContent.php?tabId=${tabId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Полученные данные содержимого вкладки:', response);
                    displayTabContent(response.assignment, tabId, response.groups, response.descriptions, response.files, response.role);
                } else {
                    console.error('Ошибка при получении контента вкладки:', response.message);
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


function displayTabContent(assignment, tabId, groups, descriptions, files, role) {
    console.log('Отображение содержимого вкладки:', assignment, groups);
    console.log('Полученные файлы:', files);
    const formContainer = document.querySelector('.wrapp-3');
    let groupsText = 'Эту таблицу видят группы: ' + (groups.length ? groups.join(', ') : 'Нет назначенных групп');

    const escapeHtml = (unsafe) => {
        if (unsafe === null || unsafe === undefined) {
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
            </div>
        `;
        if (file.uploaded_by_role === 'teacher') {
            teacherFilesHtml += fileHtml;
        } else {
            studentFilesHtml += fileHtml;
        }
    });

    teacherFilesHtml += '</ul></div>';
    studentFilesHtml += '</ul></div>';

    formContainer.innerHTML = `
        <div class="title-container">
            <h3>${escapeHtml(assignment.title)}</h3>
        </div>
        ${descriptionsHtml}
        ${teacherFilesHtml}
        ${studentFilesHtml}
        <p>${groupsText}</p>
    `;

    // Проверяем роль и добавляем кнопку для студентов
    console.log('Роль пользователя:', role); // Добавляем лог для проверки роли пользователя
    if (role === 'user') {
        console.log('Добавление кнопки "Отчитаться" для студента'); // Добавляем лог для проверки добавления кнопки
        formContainer.innerHTML += `
            <button class="attach_file_button" onclick="showFileUploadForm(${assignment.id}, ${tabId}, '${escapeHtml(assignment.title)}')">
                <i class="lni lni-plus"></i> Отчитаться
            </button>
        `;
    }

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

function showFileUploadForm(assignmentId, tabId, tabName) {
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
        formData.append('assignmentId', assignmentId);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'uploadFiles.php', true);

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Ответ от сервера при загрузке файла:', response); // Логирование ответа сервера
                    if (response.success) {
                        console.log('Файлы успешно загружены.');
                        loadTabContent(tabId, tabName); // Обновить содержимое вкладки после загрузки файлов
                    } else {
                        console.error('Ошибка при загрузке файлов:', response.message);
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



function fetchComments(assignmentId) {
    console.log('Загрузка комментариев для задания:', assignmentId);
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getComments.php?assignmentId=${assignmentId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Отображение комментариев:', response.comments); // Лог для проверки данных
                    displayComments(response.comments);
                } else {
                    console.error('Ошибка при получении комментариев:', response.message);
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
    console.log("Отображение комментариев:", comments);
    const commentsContainer = document.getElementById('commentsContainer');
    if (commentsContainer) {
        commentsContainer.innerHTML = '';

        comments.forEach(comment => {
            const commentElement = document.createElement('div');
            commentElement.classList.add('comment');
            commentElement.setAttribute('data-comment-id', comment.id);
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
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'addReaction.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Обновляем только один комментарий
                updateCommentReactions(commentId);
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

function updateCommentReactions(commentId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `getCommentReactions.php?commentId=${commentId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const commentElement = document.querySelector(`.comment[data-comment-id='${commentId}']`);
                    if (commentElement) {
                        const likeButton = commentElement.querySelector('.like-button');
                        const dislikeButton = commentElement.querySelector('.dislike-button');
                        likeButton.textContent = `👍 ${response.likes}`;
                        dislikeButton.textContent = `👎 ${response.dislikes}`;
                    }
                } else {
                    console.error('Ошибка при обновлении реакций комментария:', response.message);
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
                console.error('Ошибка при добавлении комментария:', response.message);
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
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('ru-RU', options);
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

