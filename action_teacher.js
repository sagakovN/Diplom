// Функция для создания чекбоксов вкладок

document.getElementById('tabsAccessForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const groupName = document.getElementById('groupName').value;
    const selectedTabs = Array.from(document.querySelectorAll('#tabsCheckboxes input:checked'))
                              .map(checkbox => checkbox.value);
    function displayTabsCheckboxes(tabs) {
    const container = document.getElementById('tabsCheckboxes');
        container.innerHTML = ''; // Очистить существующие чекбоксы
    
        // Цикл по всем вкладкам и создание чекбокса для каждой
        tabs.forEach(tab => {
            // Создание элемента чекбокса
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = 'tab' + tab.id;
            checkbox.name = 'tabIds[]'; // Используйте массив в POST
            checkbox.value = tab.id;
    
            // Создание метки для чекбокса
            const label = document.createElement('label');
            label.htmlFor = 'tab' + tab.id;
            label.textContent = tab.name;
    
            // Добавление чекбокса и метки в DOM
            container.appendChild(checkbox);
            container.appendChild(label);
            container.appendChild(document.createElement('br')); // Для переноса на новую строку
            });
        }
                            
// Пример данных, полученных от сервера
// Предположим, что переменная `tabs` содержит информацию о вкладках из PHP
const tabs = JSON.parse('<?php echo json_encode($tabs); ?>');

// Отображаем чекбоксы вкладок
displayTabsCheckboxes(tabs);
});