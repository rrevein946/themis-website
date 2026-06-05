<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

$catFilter = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$sql = "SELECT s.id, s.title, s.price, LEFT(s.description, 120) as short_desc, c.name as cat_name
        FROM services s
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE s.is_active = 1";
$params = [];
if ($catFilter) {
    $sql .= " AND s.category_id = ?";
    $params[] = $catFilter;
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Каталог услуг — Фемида</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js"></script>
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
        <a href="lawyers.php">Юристы</a>
        <li><a href="contacts.html">Контакты</a></li>
        <li><a href="#" class="btn-cabinet">Личный кабинет</a></li>
      </ul>
    </div>
  </header>

  <main class="services-page">
    <!-- HERO СЕКЦИЯ -->
    <section class="services-hero"></section>

    <!-- КОНТЕНТ -->
    <div class="services-container" id="catalog">
      <div class="services-title-block">
        <h2 class="section-title">НАШИ УСЛУГИ</h2>
      </div>

<!-- ФИЛЬТРЫ -->
<div class="services-filter" id="servicesFilter">
  <a href="/services.php" class="filter-btn <?= !$catFilter ? 'active' : '' ?>" data-category="">Все услуги</a>
  <?php foreach ($cats as $c): ?>
    <!-- Оставляем реальную ссылку! -->
    <a href="/services.php?category=<?= $c['id'] ?>" class="filter-btn <?= $catFilter == $c['id'] ? 'active' : '' ?>" data-category="<?= $c['id'] ?>">
      <?= e($c['name']) ?>
    </a>
  <?php endforeach; ?>
</div>

      <!-- СЕТКА УСЛУГ -->
      <div class="services-grid" id="servicesGrid">
        <div id="loadingIndicator" style="display:none; text-align:center; padding:40px;">Загрузка...</div>
        <div class="services-grid" id="servicesGrid">
        <?php foreach ($services as $s): ?>
          <a href="/service.php?id=<?= $s['id'] ?>" class="service-card">
            <div class="service-category"><?= e($s['cat_name']) ?></div>
            <h3 class="service-title"><?= e($s['title']) ?></h3>
            <p class="service-description"><?= e($s['short_desc']) ?>...</p>
            <div class="service-footer">
              <div class="service-price">от <?= number_format($s['price'], 0, ',', ' ') ?> ₽</div>
              <span class="service-btn">Подробнее</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <!-- ФУТЕР -->
  <footer class="main-footer">
    <div class="container footer-content">
      <h2 class="footer-title">ВАМ НУЖНА ЮРИДИЧЕСКАЯ ПОМОЩЬ?</h2>
      <p class="footer-subtitle">Наши специалисты всегда на связи</p>
      <p class="footer-text">Закажите наши услуги на <a href="#">сайте</a> или по номеру телефона</p>
      
      <div class="footer-contacts">
        <p>Адрес: г. Уфа, ул. Фрунзенская, д. 45, к. 3</p>
        <p>Горячая линия: <a href="tel:+79990451122">+7-999-045-11-22</a></p>
        <p>Мы ВКонтакте: <a href="#">@femida_law_ufa</a></p>
      </div>
      
      <div class="footer-copyright">
        @Юридическая компания Фемида. Все права защищены
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
