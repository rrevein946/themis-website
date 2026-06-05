<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

// Получаем всех пользователей с ролью specialist (или admin, если они тоже юристы)
$stmt = $pdo->query("
    SELECT id, first_name, last_name, phone, role, specialization, photo_url 
    FROM users 
    WHERE role IN ('specialist', 'admin') 
    ORDER BY last_name ASC
");
$lawyers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ЮК “Фемида” | Юристы</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ШАПКА -->
  <header>
    <div class="container nav-container">
      <a href="index.html" class="logo">
        <img src="images/logo.png" alt="Логотип Фемида">
        <div class="logo-text">
          <h2>"ФЕМИДА"</h2>
          <span>юридическая компания</span>
        </div>
      </a>
      
      <button class="menu-toggle" aria-label="Меню">☰</button>
      
      <ul class="nav-links">
        <li><a href="index.html">Главная</a></li>
        <li><a href="about.html">О нас</a></li>
        <li><a href="practice.html">Области практики</a></li>
        <li><a href="services.php">Услуги</a></li>
        <li><a href="lawyers.php" class="active">Юристы</a></li>
        <li><a href="contacts.html">Контакты</a></li>
        <li><a href="#" class="btn-cabinet">Личный кабинет</a></li>
      </ul>
    </div>
  </header>

  <main class="lawyers-page">
    <section class="lawyers-hero"></section>

    <div class="lawyers-container">
      <div class="lawyers-title-block">
        <h2 class="section-title">НАША КОМАНДА</h2>
        <div class="underline"></div>
      </div>

      <div class="lawyers-grid">
        <?php if (empty($lawyers)): ?>
          <p style="text-align:center; width:100%; color: var(--text-light);">Список юристов пока пуст.</p>
        <?php else: ?>
          <?php foreach ($lawyers as $lawyer): ?>
            <a href="lawyer.php?id=<?= $lawyer['id'] ?>" class="lawyer-card-link">
              <div class="lawyer-card">
                <div class="lawyer-photo">
                  <img src="<?= e($lawyer['photo_url']) ?>" alt="<?= e($lawyer['first_name'] . ' ' . $lawyer['last_name']) ?>">
                </div>
                <h3 class="lawyer-name"><?= e($lawyer['first_name'] . ' ' . $lawyer['last_name']) ?></h3>
                
                <span class="lawyer-status <?= $lawyer['role'] === 'admin' ? 'status-advocate' : 'status-lawyer' ?>">
                  <?= $lawyer['role'] === 'admin' ? 'Адвокат' : 'Юрист' ?>
                </span>
                
                <ul class="lawyer-areas">
                  <?php 
                    $areas = explode(',', $lawyer['specialization']);
                    foreach ($areas as $area): 
                  ?>
                    <li><?= e(trim($area)) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- ФУТЕР -->
  <footer class="main-footer">
    <div class="container footer-content">
      <h2 class="footer-title">ВАМ НУЖНА ЮРИДИЧЕСКАЯ ПОМОЩЬ?</h2>
      <p class="footer-subtitle">Наши специалисты всегда на связи</p>
      <p class="footer-text">Закажите наши услуги на сайте или по номеру телефона</p>
      
      <div class="footer-contacts">
        <p>Адрес: г. Уфа, ул. Фрунзенская, д. 45, к. 3</p>
        <p>Горячая линия: +7-999-045-11-22</p>
        <p>Мы ВКонтакте: @femida_law_ufa</p>
      </div>
      <div class="footer-copyright">
        @Юридическая компания Фемида. Все права защищены © 2026
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>