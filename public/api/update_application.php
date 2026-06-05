<?php
if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/helpers.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Проверка прав администратора
    if (!isAdmin()) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Недостаточно прав'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Получаем данные из POST
    $appId = filter_input(INPUT_POST, 'app_id', FILTER_VALIDATE_INT);
    $statusId = filter_input(INPUT_POST, 'status_id', FILTER_VALIDATE_INT);
    $specIdRaw = $_POST['specialist_id'] ?? '';
    $specId = $specIdRaw === '' ? null : (int)$specIdRaw;
    
    // Валидация
    if (!$appId || !$statusId) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Неверные данные'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Проверяем, что заявка существует
    $stmt = $pdo->prepare("SELECT id FROM applications WHERE id = ?");
    $stmt->execute([$appId]);
    if (!$stmt->fetch()) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Заявка не найдена'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Обновляем заявку
    $stmt = $pdo->prepare("
        UPDATE applications 
        SET status_id = ?, specialist_id = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$statusId, $specId, $appId]);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Статус и специалист успешно обновлены'
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}