<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';
require_once __DIR__ . '/../src/helpers/auth.php';

requireAuth();

$user = getCurrentUser($pdo);

// Загружаем ТОЛЬКО заявки текущего пользователя
$stmt = $pdo->prepare("
    SELECT a.id, a.created_at, a.client_message, a.specialist_response, 
           s.name as status_name, sv.title as service_title
    FROM applications a
    JOIN statuses s ON a.status_id = s.id
    JOIN services sv ON a.service_id = sv.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$apps = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        h1 { margin: 0; color: #2c2520; }
        .nav a { margin-left: 16px; color: #9c7c5c; text-decoration: none; }
        .profile-card { background: #fff; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #faf9f7; color: #666; font-weight: 500; }
        .status { padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; }
        .status-new { background: #e3f2fd; color: #1565c0; }
        .status-progress { background: #fff3e0; color: #e65100; }
        .status-done { background: #e8f5e9; color: #2e7d32; }
        .empty { padding: 32px; text-align: center; color: #888; background: #fff; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Личный кабинет</h1>
        <div class="nav">
            <a href="/services.php">Каталог</a>
            <a href="/logout.php">Выйти</a>
        </div>
    </div>

    <div class="profile-card">
        <h3>👤 <?= e($user['first_name']) ?> <?= e($user['last_name']) ?></h3>
        <p>📧 <?= e($user['email']) ?> | 📞 <?= e($user['phone'] ?? 'Не указан') ?></p>
    </div>

    <h2>📄 Мои заявки</h2>
    <?php if (empty($apps)): ?>
        <div class="empty">У вас пока нет заявок. <a href="/services.php" style="color:#9c7c5c">Перейти в каталог</a></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Услуга</th>
                    <th>Сообщение</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apps as $app): 
                    $statusClass = match($app['status_name']) {
                        'Новая' => 'status-new',
                        'В обработке', 'Подтверждена' => 'status-progress',
                        'Завершена' => 'status-done',
                        default => ''
                    };
                ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($app['created_at'])) ?></td>
                    <td><strong><?= e($app['service_title']) ?></strong></td>
                    <td><?= e(mb_substr($app['client_message'], 0, 50)) ?>...</td>
                    <td><span class="status <?= $statusClass ?>"><?= e($app['status_name']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>