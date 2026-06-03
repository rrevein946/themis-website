<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';
require_once __DIR__ . '/../src/helpers/auth.php';
requireAdmin();

$msg = '';
$action = $_GET['action'] ?? 'list';
$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Обработка POST (сохранение / удаление)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');
        $catId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $price = filter_var(str_replace(',', '.', $_POST['price'] ?? '0'), FILTER_VALIDATE_FLOAT) ?: 0;
        $desc = trim($_POST['description'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (!$title || !$catId) {
            $msg = '<div class="msg err">Заполните название и категорию</div>';
        } else {
            if ($id) {
                $pdo->prepare("UPDATE services SET title=?, category_id=?, price=?, description=?, is_active=? WHERE id=?")
                    ->execute([$title, $catId, $price, $desc, $active, $id]);
                $msg = '<div class="msg ok">✅ Услуга обновлена</div>';
            } else {
                $pdo->prepare("INSERT INTO services (title, category_id, price, description, is_active) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$title, $catId, $price, $desc, $active]);
                $msg = '<div class="msg ok">✅ Услуга добавлена</div>';
            }
            $action = 'list';
        }
    } elseif ($postAction === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        try {
            $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
            $msg = '<div class="msg ok">🗑️ Услуга удалена</div>';
        } catch (PDOException $e) {
            $msg = '<div class="msg err">❌ Нельзя удалить: на услугу есть заявки</div>';
        }
    }
}

// Загрузка данных
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$current = null;

if ($action === 'list') {
    $services = $pdo->query("SELECT s.*, c.name as cat_name FROM services s LEFT JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC")->fetchAll();
} elseif ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$editId]);
    $current = $stmt->fetch();
    if (!$current) { $action = 'list'; $msg = '<div class="msg err">Услуга не найдена</div>'; }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление услугами — Админка</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        .msg { padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; }
        .msg.ok { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .msg.err { background: #fff3f3; color: #c62828; border: 1px solid #ffcdd2; }
        .form { background: #fff; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-weight: 500; font-size: 0.9rem; color: #444; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        textarea { height: 100px; resize: vertical; }
        .btn { padding: 10px 20px; background: #9c7c5c; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #8a6b4d; }
        .btn-del { background: #c62828; } .btn-del:hover { background: #a12222; }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-block; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #faf9f7; color: #666; }
        a { color: #9c7c5c; text-decoration: none; }
    </style>
</head>
<body>
    <a href="/admin/index.php">← Назад в панель</a>
    <h1>📚 Управление услугами</h1>

    <?= $msg ?>

    <?php if ($action !== 'list'): ?>
        <div class="form">
            <h3><?= $current ? 'Редактирование' : 'Добавление' ?> услуги</h3>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= $current['id'] ?? '' ?>">
                
                <div class="row">
                    <div><label>Название *</label><input type="text" name="title" value="<?= e($current['title'] ?? '') ?>" required></div>
                    <div><label>Категория *</label>
                        <select name="category_id" required>
                            <option value="">Выберите</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($current['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div><label>Цена (₽)</label><input type="number" step="0.01" name="price" value="<?= $current['price'] ?? '0' ?>"></div>
                    <div style="display:flex;align-items:flex-end;padding-bottom:12px;">
                        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_active" <?= ($current['is_active'] ?? 1) ? 'checked' : '' ?>> Активна</label>
                    </div>
                </div>
                <div style="margin-bottom:12px;"><label>Описание</label><textarea name="description"><?= e($current['description'] ?? '') ?></textarea></div>
                <button type="submit" class="btn">💾 Сохранить</button>
                <a href="/admin/services.php" style="margin-left:12px;">Отмена</a>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <div style="margin-bottom:16px;"><a href="/admin/services.php?action=add" class="btn">➕ Добавить услугу</a></div>
        <table>
            <thead><tr><th>ID</th><th>Название</th><th>Категория</th><th>Цена</th><th>Статус</th><th>Действия</th></tr></thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><strong><?= e($s['title']) ?></strong></td>
                    <td><?= e($s['cat_name']) ?></td>
                    <td><?= number_format($s['price'], 2, ',', ' ') ?> ₽</td>
                    <td><?= $s['is_active'] ? '<span style="color:#2e7d32">Активна</span>' : '<span style="color:#888">Скрыта</span>' ?></td>
                    <td>
                        <a href="/admin/services.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm">✏️</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить услугу?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-del">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>