<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

// Если уже авторизован — сразу в кабинет
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }

    if (empty($errors)) {
        // Поиск пользователя по email (подготовленный запрос!)
        $stmt = $pdo->prepare("SELECT id, email, password_hash, first_name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Проверка пароля
        if ($user && password_verify($password, $user['password_hash'])) {
            // Успешный вход: сохраняем в сессию
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'];
            
            // Редирект в зависимости от роли
            $redirect = $user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $errors[] = 'Неверный email или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 420px; margin: 80px auto; padding: 0 20px; background: #f8f7f4; }
        .form { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .form h1 { margin: 0 0 24px; color: #2c2520; text-align: center; }
        .field { margin-bottom: 16px; }
        .field label { display: block; margin-bottom: 6px; font-weight: 500; color: #4a3f35; }
        .field input { width: 100%; padding: 10px 12px; border: 1px solid #d0cbc3; border-radius: 4px; font-size: 1rem; }
        .field input:focus { outline: none; border-color: #9c7c5c; }
        .errors { background: #fff3f3; border: 1px solid #ffcdd2; color: #c62828; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .btn { width: 100%; padding: 12px; background: #9c7c5c; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        .btn:hover { background: #8a6b4d; }
        .link { text-align: center; margin-top: 20px; color: #666; }
        .link a { color: #9c7c5c; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form">
        <h1>Вход в систему</h1>

        <?php if ($errors): ?>
            <div class="errors">
                <?php foreach ($errors as $err): ?>
                    <div>• <?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>

        <div class="link">
            Нет аккаунта? <a href="/register.php">Зарегистрироваться</a>
        </div>
    </div>
</body>
</html>