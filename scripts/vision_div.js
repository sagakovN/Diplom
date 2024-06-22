function openTab(evt, tabName) {
  // Убираем класс "active" у всех кнопок и скрываем все вкладки
  var tablinks = document.getElementsByClassName("tab-button");
  for (var i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  var tabcontent = document.getElementsByClassName("tab-content");
  for (var i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
  }
  
  // Добавляем класс "active" к текущей кнопке вкладки
  if (evt !== null) {
    evt.currentTarget.className += " active";
  }

  // Отображаем текущую вкладку и применяем к ней стили flex
  var activeTabContent = document.getElementById(tabName);
  activeTabContent.style.display = "flex";
  activeTabContent.style.justifyContent = "center";
  activeTabContent.style.alignItems = "center";
}

document.addEventListener("DOMContentLoaded", function() {
  // Определение максимальной высоты среди всех tab-content
  var max_height = 0;
  var tabcontent = document.getElementsByClassName("tab-content");
  for (var i = 0; i < tabcontent.length; i++) {
    if (tabcontent[i].offsetHeight > max_height) {
      max_height = tabcontent[i].offsetHeight;
      max_height = max_height - 100;
    }
  }
  // Установка максимальной высоты для всех tab-content
  for (var i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.height = max_height + 'px';
  }

  // Инициализация первой вкладки
  openTab(null, 'Tab1');
});


