window.addEventListener('scroll', function() {
    // Получите высоту контента и окна
    const contentHeight = document.body.scrollHeight;
    const windowHeight = window.innerHeight;
  
    // Проверьте, достигли ли вы конца контента по вертикали
    if (window.scrollY + windowHeight >= contentHeight) {
      // Показать футер
      document.querySelector('footer').style.transform = 'translateY(0)';
    } else {
      // Скрыть футер
      document.querySelector('footer').style.transform = 'translateY(100%)';
    }
  });