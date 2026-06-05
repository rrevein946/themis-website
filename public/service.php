<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';
require_once __DIR__ . '/../src/helpers/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: /services.php"); exit; }

// Загружаем услугу
$stmt = $pdo->prepare("SELECT s.*, c.name as cat_name FROM services s JOIN categories c ON s.category_id = c.id WHERE s.id = ? AND s.is_active = 1");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    http_response_code(404);
    die('Услуга не найдена или снята с публикации');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    if (empty($message)) {
        $error = 'Пожалуйста, опишите суть вопроса';
    } else {
        $stmt = $pdo->prepare("INSERT INTO applications (user_id, service_id, status_id, client_message) VALUES (?, ?, 1, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $id, $message])) {
            $success = 'Заявка успешно отправлена. Вы можете отслеживать статус в личном кабинете.';
        } else {
            $error = 'Произошла ошибка. Попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($service['title']) ?> — Фемида</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <main class="service-detail-page">
    <div class="service-detail-container">
      <a href="/services.php" class="service-back">← Назад к каталогу</a>
      
      <div class="service-detail-card">
        <div class="service-detail-category"><?= e($service['cat_name']) ?></div>
        <h1 class="service-detail-title"><?= e($service['title']) ?></h1>
        
        <div class="service-detail-meta">
          <div class="service-detail-price">от <?= number_format($service['price'], 0, ',', ' ') ?> ₽</div>
        </div>
        
        <div class="service-detail-description">
          <?= nl2br(e($service['description'])) ?>
        </div>

        <?php if ($success): ?>
          <div class="service-message ok">✅ <?= e($success) ?></div>
        <?php elseif ($error): ?>
          <div class="service-message err">❌ <?= e($error) ?></div>
        <?php endif; ?>

        <div class="service-form-section">
          <h3 class="service-form-title">Оставить заявку на консультацию</h3>
          
          <?php if (isAuth()): ?>
            <form method="POST" class="service-form">
              <textarea name="message" placeholder="Кратко опишите вашу ситуацию или вопрос..." required></textarea>
              <button type="submit">Отправить заявку</button>
            </form>
          <?php else: ?>
            <div class="service-login-hint">
              Для отправки заявки необходимо <a href="/login.php">войти</a> или <a href="/register.php">зарегистрироваться</a>.
            </div>
          <?php endif; ?>
        </div>
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
