<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

// Получаем выбранный фильтр
$catFilter = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

// Загружаем категории для меню
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Запрос услуг
$sql = "SELECT s.id, s.title, s.price, LEFT(s.description, 120) as short_desc, c.name as cat_name
        FROM services s
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE s.is_active = 1";
$params = [];
if ($catFilter) {
    $sql .= " AND s.category_id = ?";
    $params[] = $catFilter;
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог услуг — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1100px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        h1 { color: #4a3f35; margin-bottom: 24px; }
        .filter { margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap; }
        .filter a { padding: 8px 16px; background: #fff; border: 1px solid #d0cbc3; border-radius: 20px; text-decoration: none; color: #5a524a; }
        .filter a.active, .filter a:hover { background: #9c7c5c; color: #fff; border-color: #9c7c5c; }
        .catalog { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e8e5df; display: flex; flex-direction: column; }
        .card h3 { margin: 8px 0; color: #2c2520; }
        .card p { color: #666; flex: 1; line-height: 1.5; }
        .card .meta { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 12px; border-top: 1px solid #eee; }
        .btn { display: inline-block; padding: 10px 20px; background: #9c7c5c; color: #fff; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #8a6b4d; }
    </style>
</head>
<body>
    <h1>Юридические услуги</h1>

    <div class="filter">
        <a href="/services.php" class="<?= !$catFilter ? 'active' : '' ?>">Все</a>
        <?php foreach ($cats as $c): ?>
            <a href="/services.php?category=<?= $c['id'] ?>" class="<?= $catFilter == $c['id'] ? 'active' : '' ?>">
                <?= e($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="catalog">
        <?php foreach ($services as $s): ?>
            <div class="card">
                <span style="color:#8a7e6b; font-size:0.8rem;"><?= e($s['cat_name']) ?></span>
                <h3><?= e($s['title']) ?></h3>
                <p><?= e($s['short_desc']) ?>...</p>
                <div class="meta">
                    <strong>от <?= number_format($s['price'], 0, ',', ' ') ?> ₽</strong>
                    <a href="/service.php?id=<?= $s['id'] ?>" class="btn">Подробнее</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>