<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!$firstName) $errors[] = 'Введите имя';
    if (!$lastName) $errors[] = 'Введите фамилию';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (strlen($password) < 6) $errors[] = 'Пароль должен быть от 6 символов';
    if ($password !== $passwordConfirm) $errors[] = 'Пароли не совпадают';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже зарегистрирован';
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, role)
            VALUES (?, ?, ?, ?, 'user')
        ");
        $stmt->execute([$email, $passwordHash, $firstName, $lastName]);
        
        $success = true;
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = 'user';
        $_SESSION['user_name'] = $firstName;
        header('Location: /dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 480px; margin: 60px auto; padding: 0 20px; background: #f8f7f4; }
        .form { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .form h1 { margin: 0 0 24px; color: #2c2520; text-align: center; }
        .field { margin-bottom: 16px; }
        .field label { display: block; margin-bottom: 6px; font-weight: 500; color: #4a3f35; }
        .field input { width: 100%; padding: 10px 12px; border: 1px solid #d0cbc3; border-radius: 4px; font-size: 1rem; }
        .field input:focus { outline: none; border-color: #9c7c5c; box-shadow: 0 0 0 3px rgba(156,124,92,0.15); }
        .errors { background: #fff3f3; border: 1px solid #ffcdd2; color: #c62828; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .success { background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .btn { width: 100%; padding: 12px; background: #9c7c5c; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; margin-top: 8px; }
        .btn:hover { background: #8a6b4d; }
        .link { text-align: center; margin-top: 16px; color: #666; }
        .link a { color: #9c7c5c; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form">
        <h1>Регистрация</h1>

        <?php if ($errors): ?>
            <div class="errors">
                <?php foreach ($errors as $err): ?>
                    <div>• <?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">✅ Регистрация успешна! Перенаправляем...</div>
        <?php else: ?>
            <form method="POST">
                <div class="field">
                    <label>Имя *</label>
                    <input type="text" name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Фамилия *</label>
                    <input type="text" name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Пароль *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="field">
                    <label>Подтвердите пароль *</label>
                    <input type="password" name="password_confirm" required>
                </div>
                <button type="submit" class="btn">Зарегистрироваться</button>
            </form>
        <?php endif; ?>

        <div class="link">
            Уже есть аккаунт? <a href="/login.php">Войти</a>
        </div>
    </div>
</body>
</html>
