<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';
require_once __DIR__ . '/../src/helpers/auth.php';
requireAdmin();

// Быстрая статистика
$stats = [
    'services' => $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn(),
    'apps'     => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
    'users'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        h1 { color: #2c2520; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-align: center; }
        .card h3 { margin: 0 0 8px; color: #666; font-size: 0.9rem; }
        .card .num { font-size: 2rem; font-weight: 700; color: #9c7c5c; }
        .menu { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .menu a { display: block; padding: 14px 0; border-bottom: 1px solid #eee; text-decoration: none; color: #333; font-weight: 500; }
        .menu a:last-child { border-bottom: none; }
        .menu a:hover { color: #9c7c5c; }
        .logout { margin-top: 24px; display: inline-block; color: #c62828; text-decoration: none; }
    </style>
</head>
<body>
    <h1>🛡️ Административная панель</h1>

    <div class="grid">
        <div class="card"><h3>Услуг в каталоге</h3><div class="num"><?= $stats['services'] ?></div></div>
        <div class="card"><h3>Активных заявок</h3><div class="num"><?= $stats['apps'] ?></div></div>
        <div class="card"><h3>Пользователей</h3><div class="num"><?= $stats['users'] ?></div></div>
    </div>

    <div class="menu">
        <a href="/admin/services.php">📚 Управление услугами</a>
        <a href="/admin/applications.php">📩 Заявки пользователей</a>
        <a href="/services.php" target="_blank">🌐 Открыть сайт</a>
    </div>

    <a href="/logout.php" class="logout">Выйти из системы</a>
</body>
</html>