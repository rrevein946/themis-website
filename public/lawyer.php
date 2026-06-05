<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

// Получаем ID из URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: /lawyers.php");
    exit;
}

// Ищем юриста по ID (только specialist или admin)
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, phone, role, specialization, photo_url 
    FROM users 
    WHERE id = ? AND role IN ('specialist', 'admin')
");
$stmt->execute([$id]);
$lawyer = $stmt->fetch();

// Если не найден, показываем 404
if (!$lawyer) {
    http_response_code(404);
    die('Юрист не найден');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($lawyer['first_name'] . ' ' . $lawyer['last_name']) ?> — Фемида</title>
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
      <ul class="nav-links">
        <li><a href="index.html">Главная</a></li>
        <li><a href="about.html">О нас</a></li>
        <li><a href="practice.html">Области практики</a></li>
        <li><a href="services.php">Услуги</a></li>
        <a href="lawyer.php?id=<?= $lawyer['id'] ?>" class="lawyer-card-link">
        <li><a href="contacts.html">Контакты</a></li>
        <li><a href="#" class="btn-cabinet">Личный кабинет</a></li>
      </ul>
    </div>
  </header>

  <main class="lawyers-page">
    <section class="lawyers-hero"></section>

    <div class="lawyers-container" style="padding-top: 60px; padding-bottom: 80px;">
      <a href="/lawyers.php" style="display:inline-block; margin-bottom:30px; color:var(--accent); text-decoration:none;">← Назад к команде</a>
      
      <div class="lawyer-card" style="max-width: 600px; margin: 0 auto; padding: 40px; text-align: center;">
        <div class="lawyer-photo" style="width: 180px; height: 180px; margin-bottom: 30px;">
          <img src="<?= e($lawyer['photo_url']) ?>" alt="<?= e($lawyer['first_name'] . ' ' . $lawyer['last_name']) ?>">
        </div>
        
        <h1 class="lawyer-name" style="font-size: 2rem; margin-bottom: 10px;">
          <?= e($lawyer['first_name'] . ' ' . $lawyer['last_name']) ?>
        </h1>
        
        <span class="lawyer-status <?= $lawyer['role'] === 'admin' ? 'status-advocate' : 'status-lawyer' ?>" style="font-size: 1rem; padding: 6px 16px; margin-bottom: 25px;">
          <?= $lawyer['role'] === 'admin' ? 'Адвокат' : 'Юрист' ?>
        </span>
        
        <div style="text-align: left; margin-top: 30px;">
          <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 15px; color: var(--text-main);">Области практики:</h3>
          <ul class="lawyer-areas" style="margin-bottom: 30px;">
            <?php foreach (explode(',', $lawyer['specialization']) as $area): ?>
              <li style="font-size: 1.1rem; padding: 10px 0;"><?= e(trim($area)) ?></li>
            <?php endforeach; ?>
          </ul>

          <?php if ($lawyer['phone']): ?>
            <p style="font-size: 1.1rem; color: var(--text-light);">
              📞 <a href="tel:<?= e($lawyer['phone']) ?>" style="color: var(--accent);"><?= e($lawyer['phone']) ?></a>
            </p>
          <?php endif; ?>
        </div>

        <a href="/services.php" class="btn" style="display: inline-block; margin-top: 30px; padding: 12px 30px; background: var(--accent); color: #fff; border-radius: 4px; text-decoration: none;">Записаться на консультацию</a>
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
      </div>
      <div class="footer-copyright">@Юридическая компания Фемида. Все права защищены © 2026</div>
    </div>
  </footer>
  <script src="script.js"></script>
</body>
</html>