const hamBurger = document.querySelector(".toggle-btn");
const sidebar = document.querySelector("#sidebar");
const sidebarLinks = document.querySelectorAll('.sidebar-link[data-bs-toggle="collapse"]');

let isExpanded = false; // Переменная для отслеживания состояния панели

function toggleSidebar() {
  isExpanded = !isExpanded;
  if (isExpanded) {
    expandSidebar();
  } else {
    collapseSidebar();
  }
}

function expandSidebar() {
  sidebar.classList.add("expand");
  sidebarLinks.forEach(link => {
    link.querySelector('span').classList.add('visible');
  });
}

function collapseSidebar() {
  sidebar.classList.remove("expand");
  sidebarLinks.forEach(link => {
    const collapseElement = document.getElementById(link.getAttribute('data-bs-target').substring(1));
    const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
    if (bsCollapse) {
      bsCollapse.hide();
    }
    link.classList.add('collapsed');
    link.querySelector('span').classList.remove('visible');
  });
}

// Обработчики событий для кнопки и наведения
hamBurger.addEventListener("click", toggleSidebar);
sidebar.addEventListener("mouseenter", expandSidebar);
sidebar.addEventListener("mouseleave", () => {
  if (!isExpanded) { // Сворачиваем только если панель не была раскрыта кликом
    collapseSidebar();
  }
});

// Обработчики для ссылок с подменю (без изменений)
sidebarLinks.forEach(link => {
  link.addEventListener('click', function(event) {
    event.preventDefault();
    if (!sidebar.classList.contains("expand")) {
      expandSidebar();
      isExpanded = true; // Отмечаем, что панель раскрыта кликом
    }
    this.classList.remove('collapsed');
  });
});