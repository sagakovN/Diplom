document.addEventListener('DOMContentLoaded', () => {
    const createGroupButton = document.getElementById('createGroup');
    const chatContainer = document.querySelector('.chat');
    const groupsContainer = chatContainer.querySelector('.groups-container'); // Контейнер для групп
    let groupId = 0; // Счётчик ID групп

    createGroupButton.addEventListener('click', function() {
        // Создать форму, если она еще не была создана
        const form = document.createElement('form');
        form.id = 'groupForm';
        form.innerHTML = `
            <input type="text" id="groupName" name="groupName" placeholder="Название группы" required>
            <button type="submit" id="submitGroup">Создать</button>
        `;
        chatContainer.appendChild(form);
        // Обработчик события на отправку формы
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const groupName = document.getElementById('groupName').value;
            
            // AJAX запрос на сервер для создания группы
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'create_group.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (this.status === 200 && this.responseText === "Группа успешно создана") {
                    // После успешного создания группы на сервере
                    const newGroup = document.createElement('div');
                    newGroup.className = 'group-name';
                    newGroup.id = `group-${groupId++}`; // Присвоение уникального ID
                    newGroup.textContent = groupName;
                    groupsContainer.appendChild(newGroup); // Добавляем в контейнер для групп
                    
                    document.getElementById('groupName').value = '';
                    form.remove();
                } else {
                    alert(this.responseText);
                }
            };
            
            xhr.send('groupName=' + encodeURIComponent(groupName));
        });
    });
})
document.addEventListener('DOMContentLoaded', function() {
    const groupsContainer = document.querySelector('.groups-container'); // Убедитесь, что это правильный селектор

    // Функция для получения и отображения групп
    function fetchAndDisplayGroups() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_group.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const groups = JSON.parse(this.responseText);
                    groups.forEach(group => {
                        const groupDiv = document.createElement('div');
                        groupDiv.className = 'group-name';
                        groupDiv.id = `group-${group.id}`; // Используем ID из ответа сервера
                        groupDiv.textContent = group.name;
                        groupsContainer.appendChild(groupDiv);
                    });
                } catch (e) {
                    console.error('Ошибка при парсинге данных групп', e);
                }
            } else {
                console.error('Ошибка при загрузке групп');
            }
        };
        xhr.send();
    }

    // Получение и отображение групп при загрузке страницы
    fetchAndDisplayGroups();
});
