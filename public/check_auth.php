<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/helpers/helpers.php';

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'userName' => $_SESSION['user_name'] ?? null
]);
exit;