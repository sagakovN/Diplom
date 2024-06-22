document.getElementById('create-group-form').addEventListener('submit', function(event) {
    event.preventDefault();
    let formData = new FormData(this);
    fetch('manage_groups.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let newGroup = document.createElement('div');
            newGroup.classList.add('group-item-wrapper');
            newGroup.id = 'group-wrapper-' + data.group_id;
            newGroup.setAttribute('onclick', `showStudents(${data.group_id})`);
            newGroup.innerHTML = `
                <div class="group-item">
                    <span>${data.group_name}</span>
                </div>
                <div id="students_${data.group_id}" class="student-list" style="display: none;">
                    <input type="text" class="search-students" placeholder="Поиск студентов..." style="display: none;">
                    <h3>Все студенты</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Фамилия</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${generateStudentRows(data.group_id)}
                        </tbody>
                    </table>
                </div>
                <button class="btn-delete" style="display: none;" onclick="event.stopPropagation(); deleteGroup(${data.group_id})">Удалить</button>
            `;
            document.getElementById('group-list').appendChild(newGroup);
            this.reset();
        }
    })
    .catch(error => console.error('Error:', error));
});

function showStudents(groupId) {
    let studentList = document.getElementById('students_' + groupId);
    let deleteButton = document.querySelector(`#group-wrapper-${groupId} .btn-delete`);
    let searchBox = document.querySelector(`#group-wrapper-${groupId} .search-students`);
    if (studentList.style.display === 'none' || studentList.style.display === '') {
        studentList.style.display = 'block';
        deleteButton.style.display = 'block'; // Показать кнопку "Удалить"
        searchBox.style.display = 'block'; // Показать поисковую строку
    } else {
        studentList.style.display = 'none';
        deleteButton.style.display = 'none'; // Скрыть кнопку "Удалить"
        searchBox.style.display = 'none'; // Скрыть поисковую строку
    }
}

function generateStudentRows(groupId) {
    let rows = '';
    let students = JSON.parse('<?php echo json_encode($students); ?>');
    students.forEach(student => {
        let firstName = atob(student.first_name);
        let lastName = atob(student.last_name);
        let isInGroup = student.is_in_group ? 'Удалить' : 'Добавить';
        let buttonClass = student.is_in_group ? 'btn-remove' : 'btn-add';
        let action = student.is_in_group ? 'remove' : 'add';
        rows += `<tr>
                    <td>${firstName}</td>
                    <td>${lastName}</td>
                    <td>
                        <button class="${buttonClass}" onclick="updateStudentGroup(${groupId}, ${student.id}, '${action}')">${isInGroup}</button>
                    </td>
                </tr>`;
    });
    return rows;
}

function updateStudentGroup(groupId, studentId, action) {
    fetch('manage_groups.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'group_id': groupId,
            'student_id': studentId,
            'action': action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновление интерфейса: замена кнопки "Добавить" на "Удалить" и наоборот
            let button = document.querySelector(`#students_${groupId} button[onclick="updateStudentGroup(${groupId}, ${studentId}, '${action}')"]`);
            if (action === 'add') {
                button.textContent = 'Удалить';
                button.classList.remove('btn-add');
                button.classList.add('btn-remove');
                button.setAttribute('onclick', `updateStudentGroup(${groupId}, ${studentId}, 'remove')`);
            } else {
                button.textContent = 'Добавить';
                button.classList.remove('btn-remove');
                button.classList.add('btn-add');
                button.setAttribute('onclick', `updateStudentGroup(${groupId}, ${studentId}, 'add')`);
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteGroup(groupId) {
    if (confirm("Вы уверены, что хотите удалить эту группу?")) {
        fetch('manage_groups.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'delete_group_id': groupId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Удаление группы и кнопки из списка без перезагрузки страницы
                let groupWrapperElement = document.getElementById('group-wrapper-' + data.group_id);
                if (groupWrapperElement) {
                    groupWrapperElement.remove();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
