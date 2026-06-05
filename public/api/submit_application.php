<?php
if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Необходимо авторизоваться'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $message = trim($_POST['message'] ?? '');
    
    if (!$serviceId) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Неверный ID услуги'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (empty($message)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Пожалуйста, опишите суть вопроса'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$serviceId]);
    if (!$stmt->fetch()) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Услуга не найдена или недоступна'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO applications (user_id, service_id, status_id, client_message, created_at) 
        VALUES (?, ?, 1, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $serviceId, $message]);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена. Вы можете отслеживать статус в личном кабинете.'
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
