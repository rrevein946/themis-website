<?php
// Очищаем буфер
if (ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
    
    $sql = "SELECT s.id, s.title, s.price, LEFT(s.description, 120) as short_desc, c.name as cat_name
            FROM services s
            LEFT JOIN categories c ON s.category_id = c.id
            WHERE s.is_active = 1";
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND s.category_id = ?";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'services' => $services,
        'count' => count($services)
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}