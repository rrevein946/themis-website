<?php
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

// Обработка отправки заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    if (empty($message)) {
        $error = 'Пожалуйста, опишите суть вопроса';
    } else {
        // Сохраняем заявку (статус 1 = "Новая")
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
    <title><?= e($service['title']) ?> — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        .back { color: #9c7c5c; text-decoration: none; display: inline-block; margin-bottom: 16px; }
        h1 { color: #2c2520; margin: 0 0 8px; }
        .meta { color: #8a7e6b; margin-bottom: 24px; }
        .content { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .form-box { margin-top: 32px; padding-top: 24px; border-top: 1px solid #eee; }
        textarea { width: 100%; height: 120px; padding: 12px; border: 1px solid #d0cbc3; border-radius: 4px; font-family: inherit; resize: vertical; }
        .btn { margin-top: 12px; padding: 12px 24px; background: #9c7c5c; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #8a6b4d; }
        .msg { padding: 12px; border-radius: 4px; margin-bottom: 16px; }
        .msg.ok { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .msg.err { background: #fff3f3; color: #c62828; border: 1px solid #ffcdd2; }
        .login-hint { background: #f0f4ff; padding: 16px; border-radius: 4px; border: 1px solid #bbdefb; color: #1565c0; }
        .login-hint a { color: #0d47a1; font-weight: bold; }
    </style>
</head>
<body>
    <a href="/services.php" class="back">← Назад в каталог</a>
    <div class="content">
        <h1><?= e($service['title']) ?></h1>
        <div class="meta">Категория: <?= e($service['cat_name']) ?> | Стоимость: от <?= number_format($service['price'], 0, ',', ' ') ?> ₽</div>
        <div style="line-height:1.7; color:#444;"><?= nl2br(e($service['description'])) ?></div>

        <?php if ($success): ?>
            <div class="msg ok"><?= e($success) ?></div>
        <?php elseif ($error): ?>
            <div class="msg err"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h3>Оставить заявку на консультацию</h3>
            <?php if (isAuth()): ?>
                <form method="POST">
                    <textarea name="message" placeholder="Кратко опишите вашу ситуацию или вопрос..." required></textarea>
                    <button type="submit" class="btn">Отправить заявку</button>
                </form>
            <?php else: ?>
                <div class="login-hint">
                    Для отправки заявки необходимо <a href="/login.php">войти</a> или <a href="/register.php">зарегистрироваться</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>