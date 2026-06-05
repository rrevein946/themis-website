<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/helpers.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['status_id'])) {
    $appId = filter_input(INPUT_POST, 'app_id', FILTER_VALIDATE_INT);
    $statusId = filter_input(INPUT_POST, 'status_id', FILTER_VALIDATE_INT);
    
    $specIdRaw = $_POST['specialist_id'] ?? '';
    $specId = $specIdRaw === '' ? null : (int)$specIdRaw;

    $stmt = $pdo->prepare("
        UPDATE applications 
        SET status_id = ?, specialist_id = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$statusId, $specId, $appId]);
    $msg = '<div class="msg ok">✅ Статус и специалист успешно обновлены</div>';
}

$specialists = $pdo->query("
    SELECT id, first_name, last_name 
    FROM users 
    WHERE role IN ('specialist', 'admin') 
    ORDER BY last_name ASC
")->fetchAll();

$apps = $pdo->query("
    SELECT a.id, a.created_at, a.client_message, a.specialist_response, a.specialist_id,
           u.email as user_email, u.first_name, u.last_name,
           s.title as service_title,
           st.name as status_name, st.id as status_id
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    JOIN statuses st ON a.status_id = st.id
    ORDER BY a.created_at DESC
")->fetchAll();

$statuses = $pdo->query("SELECT id, name FROM statuses ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявки — Админка</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        .msg { padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.85rem; vertical-align: top; }
        th { background: #faf9f7; color: #666; }
        select { padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 100%; min-width: 140px; }
        .btn { padding: 6px 12px; background: #9c7c5c; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #8a6b4d; }
        a { color: #9c7c5c; text-decoration: none; }
        .response { background: #f0f4ff; padding: 8px; border-radius: 4px; border-left: 3px solid #1565c0; margin-top: 4px; font-size: 0.8rem; }
        .no-spec { color: #c62828; font-weight: 600; }
    </style>
</head>
<body>
    <a href="/admin/index.php">← Назад в панель</a>
    <h1>📩 Заявки пользователей</h1>

    <?= $msg ?>

    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Клиент</th>
                <th>Услуга</th>
                <th>Сообщение</th>
                <th>Ответ специалиста</th>
                <th>Специалист</th> 
                <th>Статус</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($apps as $app): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($app['created_at'])) ?></td>
                <td>
                    <?= e($app['first_name']) ?> <?= e($app['last_name']) ?><br>
                    <small style="color:#888"><?= e($app['user_email']) ?></small>
                </td>
                <td><?= e($app['service_title']) ?></td>
                <td><?= nl2br(e($app['client_message'])) ?></td>
                <td>
                    <?= $app['specialist_response'] ? '<div class="response">' . nl2br(e($app['specialist_response'])) . '</div>' : '<span style="color:#aaa">—</span>' ?>
                </td>
                
                <td>
                    <select name="specialist_id" form="app-form-<?= $app['id'] ?>">
                        <option value="" <?= !$app['specialist_id'] ? 'selected' : '' ?>>Не назначен</option>
                        <?php foreach ($specialists as $spec): ?>
                            <option value="<?= $spec['id'] ?>" <?= $app['specialist_id'] == $spec['id'] ? 'selected' : '' ?>>
                                <?= e($spec['first_name'] . ' ' . $spec['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <td>
                    <strong><?= e($app['status_name']) ?></strong>
                </td>

                <td>
                    <form id="app-form-<?= $app['id'] ?>" method="POST">
                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                        
                        <select name="status_id" style="margin-bottom: 8px;">
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st['id'] ?>" <?= $app['status_id'] == $st['id'] ? 'selected' : '' ?>>
                                    <?= e($st['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn">💾 Сохранить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
