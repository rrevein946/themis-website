document.addEventListener('DOMContentLoaded', () => {

  // === ПЛАВНАЯ ПРОКРУТКА К КАТАЛОГУ ПРИ ПЕРВОМ ЗАХОДЕ С ФИЛЬТРОМ ===
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('category')) {
    const catalog = document.getElementById('servicesGrid');
    if (catalog) {
      setTimeout(() => {
        catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 300);
    }
  }

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

  // === AJAX-ФИЛЬТРАЦИЯ КАТАЛОГА УСЛУГ ===
  const filterContainer = document.getElementById('servicesFilter');
  const servicesGrid = document.getElementById('servicesGrid');
  const loadingIndicator = document.getElementById('loadingIndicator');

  if (filterContainer && servicesGrid) {
    filterContainer.addEventListener('click', (e) => {
      const btn = e.target.closest('.filter-btn');
      if (!btn) return; 
      
      e.preventDefault(); 
      
      const categoryId = btn.dataset.category;
      
      filterContainer.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      
      if (loadingIndicator) loadingIndicator.style.display = 'block';
      servicesGrid.style.opacity = '0.5';
      
      fetch(`/api/filter_services.php?category=${categoryId}`)
        .then(res => res.json())
        .then(data => {
          if (loadingIndicator) loadingIndicator.style.display = 'none';
          servicesGrid.style.opacity = '1';
          
          if (data.success) {
            if (data.services.length === 0) {
              servicesGrid.innerHTML = '<p style="text-align:center; grid-column:1/-1;">Услуги не найдены</p>';
            } else {
              servicesGrid.innerHTML = data.services.map(s => `
                <a href="/service.php?id=${s.id}" class="service-card">
                  <div class="service-category">${s.cat_name || ''}</div>
                  <h3 class="service-title">${s.title}</h3>
                  <p class="service-description">${s.short_desc}...</p>
                  <div class="service-footer">
                    <div class="service-price">от ${Number(s.price).toLocaleString('ru-RU')} ₽</div>
                    <span class="service-btn">Подробнее</span>
                  </div>
                </a>
              `).join('');
            }
          }
        })
        .catch(err => {
          console.error(err);
          if (loadingIndicator) loadingIndicator.style.display = 'none';
          servicesGrid.style.opacity = '1';
          alert('Не удалось загрузить услуги. Проверьте консоль.');
        });
    });
  }

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

    // === AJAX-ОТПРАВКА ФОРМЫ ЗАЯВКИ ===
  const applicationForm = document.getElementById('applicationForm');
  const formMessage = document.getElementById('formMessage');
  const submitBtn = document.getElementById('submitBtn');
  
  if (applicationForm && formMessage && submitBtn) {
    applicationForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const formData = new FormData(applicationForm);
      
      submitBtn.disabled = true;
      submitBtn.textContent = 'Отправка...';
      formMessage.style.display = 'none';
      
      fetch('/api/submit_application.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Отправить заявку';
        
        if (data.success) {
          formMessage.className = 'service-message ok';
          formMessage.textContent = '✅ ' + data.message;
          formMessage.style.display = 'block';
          applicationForm.reset(); // Очищаем форму
        } else {
          throw new Error(data.error || 'Неизвестная ошибка');
        }
      })
      .catch(error => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Отправить заявку';
        formMessage.className = 'service-message err';
        formMessage.textContent = '❌ ' + error.message;
        formMessage.style.display = 'block';
        console.error('AJAX отправка заявки:', error);
      });
    });
  }
});
