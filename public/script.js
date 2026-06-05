document.addEventListener('DOMContentLoaded', () => {

  // === ПРОВЕРКА АВТОРИЗАЦИИ ДЛЯ КНОПКИ "ЛИЧНЫЙ КАБИНЕТ" ===
  const cabinetBtn = document.querySelector('.btn-cabinet');
  
  if (cabinetBtn) {
    fetch('/check_auth.php')
      .then(response => response.json())
      .then(data => {
        if (data.isLoggedIn) {
          cabinetBtn.href = '/dashboard.php';
        } else {
          cabinetBtn.href = '/login.php';
        }
      })
      .catch(error => {
        console.error('Ошибка проверки авторизации:', error);
        cabinetBtn.href = '/login.php';
      });
  }

  // === МЕНЮ-БУРГЕР ===
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.nav-links');
  if (toggle) toggle.addEventListener('click', () => nav.classList.toggle('show'));

  // === ПОДСВЕТКА АКТИВНОЙ СТРАНИЦЫ ===
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

}); 

// === РАСКРЫТИЕ КАРТОЧЕК ОБЛАСТЕЙ ПРАКТИКИ ===
function toggleCard(card) {
  const isActive = card.classList.contains('active');
  
  document.querySelectorAll('.law-card').forEach(c => c.classList.remove('active'));
  
  if (!isActive) {
    card.classList.add('active');
    
    setTimeout(() => {
      const rect = card.getBoundingClientRect();
      const windowHeight = window.innerHeight;
      if (rect.bottom > windowHeight) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 300);
  }
}

// === ЗАКРЫТИЕ КАРТОЧЕК ПРИ КЛИКЕ ВНЕ ИХ ===
document.addEventListener('click', function(e) {
  if (!e.target.closest('.law-card')) {
    document.querySelectorAll('.law-card').forEach(c => c.classList.remove('active'));
  }
});
