function openTab(evt, tabName) {
    // Получаем все элементы с классом "tab-content" и скрываем их
    var tabcontent = document.getElementsByClassName("tab-content");
    for (var i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
  
    // Получаем все элементы с классом "tab-button" и убираем класс "active"
    var tablinks = document.getElementsByClassName("tab-button");
    for (var i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
  
    // Показываем текущий контент вкладки и добавляем класс "active" на кнопку, которая открывает вкладку
    document.getElementById(tabName).style.display = "flex";
    evt.currentTarget.className += " active";
  }
  