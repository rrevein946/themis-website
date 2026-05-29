<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог услуг — Фемида</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 20px; background: #f8f7f4; }
        h1 { color: #4a3f35; }
        .catalog { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e8e5df; }
        .card .cat { font-size: 0.8rem; color: #8a7e6b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .card h3 { margin: 0 0 12px; color: #2c2520; font-size: 1.25rem; }
        .card p { color: #5a524a; line-height: 1.5; margin-bottom: 16px; }
        .card .price { font-weight: 600; color: #9c7c5c; font-size: 1.1rem; }
    </style>
</head>
<body>
    <h1>Юридические услуги</h1>

    <?php if (empty($services)): ?>
        <p style="color:#666;">Услуги пока не добавлены.</p>
    <?php else: ?>
        <div class="catalog">
            <?php foreach ($services as $s): ?>
                <div class="card">
                    <div class="cat"><?= e($s['category_name']) ?></div>
                    <h3><?= e($s['title']) ?></h3>
                    <p><?= e(mb_substr($s['description'], 0, 120)) ?>...</p>
                    <div class="price">от <?= number_format($s['price'], 0, ',', ' ') ?> ₽</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>

<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/helpers.php';

$stmt = $pdo->query("
    SELECT s.id, s.title, s.price, s.description, c.name AS category_name
    FROM services s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.is_active = 1
    ORDER BY s.created_at DESC
");
$services = $stmt->fetchAll(); 
?>
