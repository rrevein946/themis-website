<?php
$host = '127.0.0.1'; // localhost или IP сервера
$db   = 'themis_db'; 
$user = 'root';      
$pass = '';          // пароль (пусто для локальной MariaDB/MySQL по умолчанию)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Подключение к БД успешно установлено!";
} catch (\PDOException $e) {
    // die('Ошибка подключения к базе данных. Проверьте логи.');
    } catch (\PDOException $e) {
    echo '<pre style="background:#fee;padding:15px;border:1px solid #fcc;border-radius:4px;">';
    echo '<strong>❌ Ошибка PDO:</strong><br>';
    echo 'Код: ' . htmlspecialchars($e->getCode()) . '<br>';
    echo 'Сообщение: ' . htmlspecialchars($e->getMessage()) . '<br>';
    echo 'Файл: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine();
    echo '</pre>';
    exit;
}
