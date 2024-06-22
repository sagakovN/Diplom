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

// delete_tab.js
function attachDeleteHandler(tabId, tabElement) {
    const deleteButton = tabElement.querySelector('.delete-button'); // Предполагая, что у вас есть кнопка с классом delete-button
    if (deleteButton) {
        deleteButton.addEventListener('click', function() { deleteTab(tabId, tabElement); });
    }
}

