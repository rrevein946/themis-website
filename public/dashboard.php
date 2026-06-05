<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';
require_once __DIR__ . '/../src/helpers/auth.php';

requireAuth();

$user = getCurrentUser($pdo);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $spec = trim($_POST['specialization'] ?? '');
    
    $photoUrl = $user['photo_url'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
        $newName = 'lawyer_' . $user['id'] . '_' . time() . '.' . $ext;
        $uploadPath = __DIR__ . '/images/' . $newName;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $photoUrl = 'images/' . $newName;
        }
        }
    }

    $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, specialization=?, photo_url=? WHERE id=?");
    $stmt->execute([$firstName, $lastName, $phone, $spec, $photoUrl, $user['id']]);
    
    $_SESSION['user_name'] = $firstName;
    
    header('Location: /dashboard.php?success=1');
    exit;
}

$successMsg = '';
if (isset($_GET['success']) && $_GET['success'] === '1') {
    $successMsg = '<div class="msg ok">✅ Профиль успешно обновлён!</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_application'])) {
    $appId = filter_input(INPUT_POST, 'app_id', FILTER_VALIDATE_INT);
    $statusId = filter_input(INPUT_POST, 'status_id', FILTER_VALIDATE_INT);
    $response = trim($_POST['specialist_response'] ?? '');
    
    $stmt = $pdo->prepare("UPDATE applications SET status_id=?, specialist_response=?, updated_at=NOW() WHERE id=? AND specialist_id=?");
    $stmt->execute([$statusId, $response, $appId, $user['id']]);
    $msg = '<div class="msg ok">✅ Ответ сохранён, статус обновлён!</div>';
}

$apps = [];
if ($user['role'] === 'user') {
    $stmt = $pdo->prepare("
        SELECT a.id, a.created_at, a.client_message, a.specialist_response, 
               st.name as status_name, sv.title as service_title
        FROM applications a
        JOIN statuses st ON a.status_id = st.id
        JOIN services sv ON a.service_id = sv.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $apps = $stmt->fetchAll();
} else {
        $stmt = $pdo->prepare("
        SELECT a.id, a.created_at, a.client_message, a.specialist_response, a.status_id,
               u.first_name as client_first, u.last_name as client_last, u.phone as client_phone,
               COALESCE(sv.title, 'Услуга удалена') as service_title, 
               COALESCE(st.name, 'Неизвестный статус') as status_name
        FROM applications a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN services sv ON a.service_id = sv.id
        LEFT JOIN statuses st ON a.status_id = st.id
        WHERE a.specialist_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $apps = $stmt->fetchAll();

    
    $statuses = $pdo->query("SELECT id, name FROM statuses ORDER BY id")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1100px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        h1, h2 { color: #2c2520; }
        .nav a { margin-left: 16px; color: #9c7c5c; text-decoration: none; }
        .msg { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .msg.ok { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        
        /* Стили для формы профиля */
        .profile-edit-form { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 40px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #4a3f35; font-size: 0.9rem; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #d0cbc3; border-radius: 4px; font-family: inherit; }
        .btn-save { padding: 10px 24px; background: #9c7c5c; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn-save:hover { background: #8a6b4d; }

        /* Стили для таблицы заявок */
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 20px; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        th { background: #faf9f7; color: #666; font-weight: 600; }
        .status { padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; }
        .status-new { background: #e3f2fd; color: #1565c0; }
        .status-progress { background: #fff3e0; color: #e65100; }
        .status-done { background: #e8f5e9; color: #2e7d32; }
        .empty { padding: 40px; text-align: center; color: #888; background: #fff; border-radius: 8px; }
        .response-box { background: #f0f4ff; padding: 10px; border-radius: 4px; border-left: 3px solid #1565c0; margin-top: 8px; font-size: 0.9rem; }

        .edit-profile-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--accent, #9c7c5c);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .edit-profile-btn:hover {
            background: #8a6b4d;
            transform: translateY(-2px);
        }

        .profile-edit-form {
            transition: all 0.3s ease;
        }

        .profile-edit-form.hidden {
            display: none;
        }

        .profile-edit-form.visible {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Карточка профиля (когда форма скрыта) */
        .profile-summary {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-summary-info h2 {
            margin: 0 0 10px;
            color: #2c2520;
        }

        .profile-summary-info p {
            margin: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Личный кабинет</h1>
        <div class="nav">
            <?php if ($user['role'] === 'admin'): ?>
                <a href="/admin/index.php">Админ-панель</a>
            <?php endif; ?>
            <a href="/services.php">Каталог</a>
            <a href="/logout.php">Выйти</a>
        </div>
    </div>

    <?= $msg ?>

<?php if ($user['role'] === 'specialist' || $user['role'] === 'admin'): ?>
    
    <?= $successMsg ?>
    
    <div class="profile-summary">
        <div class="profile-summary-info">
            <h2>👤 <?= e($user['first_name']) ?> <?= e($user['last_name']) ?></h2>
            <p>📧 <?= e($user['email']) ?> | 📞 <?= e($user['phone'] ?? 'Не указан') ?></p>
            <p style="margin-top:8px; color:#888;">
                <strong>Специализация:</strong> <?= e($user['specialization'] ?? 'Не указана') ?>
            </p>
        </div>
        <button type="button" class="edit-profile-btn" onclick="toggleEditForm()">
            ✏️ Редактировать профиль
        </button>
    </div>

    <div class="profile-edit-form hidden" id="profileEditForm">
        <h2 style="margin-top:0;">👤 Редактирование профиля</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-row">
                <div class="form-group">
                    <label>Имя</label>
                    <input type="text" name="first_name" value="<?= e($user['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Фамилия</label>
                    <input type="text" name="last_name" value="<?= e($user['last_name']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Фотография (выберите файл)</label>
                    <input type="file" name="photo" accept="image/*">
                    <small style="color:#888;">Текущее фото: <?= e($user['photo_url'] ?? 'не загружено') ?></small>
                </div>
            </div>
            <div class="form-group">
                <label>Области практики (через запятую)</label>
                <textarea name="specialization" rows="2" required><?= e($user['specialization'] ?? '') ?></textarea>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-save">💾 Сохранить изменения</button>
                <button type="button" class="btn-save" style="background:#888;" onclick="toggleEditForm()">Отмена</button>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="profile-summary">
        <div class="profile-summary-info">
            <h2>👤 <?= e($user['first_name']) ?> <?= e($user['last_name']) ?></h2>
            <p>📧 <?= e($user['email']) ?> | 📞 <?= e($user['phone'] ?? 'Не указан') ?></p>
        </div>
    </div>
<?php endif; ?>

    <h2>📄 <?= $user['role'] === 'user' ? 'Мои заявки' : 'Заявки, назначенные мне' ?></h2>
    
    <?php if (empty($apps)): ?>
        <div class="empty">
            <?= $user['role'] === 'user' ? 'У вас пока нет заявок.' : 'Вам пока не назначено ни одной заявки.' ?> 
            <a href="/services.php" style="color:#9c7c5c">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <?php if ($user['role'] !== 'user'): ?><th>Клиент</th><?php endif; ?>
                    <th>Услуга</th>
                    <th>Сообщение</th>
                    <th>Статус</th>
                    <?php if ($user['role'] !== 'user'): ?><th>Действия</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apps as $app): 
                    $statusClass = match($app['status_name']) {
                        'Новая' => 'status-new',
                        'В обработке', 'Подтверждена' => 'status-progress',
                        'Завершена', 'Отклонена' => 'status-done',
                        default => ''
                    };
                ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($app['created_at'])) ?></td>
                    <?php if ($user['role'] !== 'user'): ?>
                        <td>
                            <strong><?= e($app['client_first'] . ' ' . $app['client_last']) ?></strong><br>
                            <small style="color:#888"><?= e($app['client_phone']) ?></small>
                        </td>
                    <?php endif; ?>
                    <td><strong><?= e($app['service_title']) ?></strong></td>
                    <td>
                        <?= e(substr($app['client_message'], 0, 60)) ?>...
                        <?php if ($app['specialist_response']): ?>
                            <div class="response-box">
                                <strong>Ваш ответ:</strong><br><?= nl2br(e($app['specialist_response'])) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><span class="status <?= $statusClass ?>"><?= e($app['status_name']) ?></span></td>
                    
                    <?php if ($user['role'] !== 'user'): ?>
                        <td>
                            <form method="POST" style="display:flex; flex-direction:column; gap:8px;">
                                <input type="hidden" name="update_application" value="1">
                                <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                
                                <select name="status_id" style="padding:6px; border-radius:4px; border:1px solid #ccc;">
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $st['id'] ?>" <?= $app['status_id'] == $st['id'] ? 'selected' : '' ?>>
                                            <?= e($st['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <textarea name="specialist_response" placeholder="Ваш ответ клиенту..." rows="2" style="padding:6px; border-radius:4px; border:1px solid #ccc;"><?= e($app['specialist_response']) ?></textarea>
                                
                                <button type="submit" class="btn-save" style="padding:6px 12px; font-size:0.85rem;">Обновить</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <script>
    function toggleEditForm() {
        const form = document.getElementById('profileEditForm');
        if (form) {
            form.classList.toggle('hidden');
            form.classList.toggle('visible');
        }
    }
</script>
</body>
</html>
