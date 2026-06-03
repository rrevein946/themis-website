<?php
/**
 * Проверка: пользователь авторизован?
 */
function isAuth(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Проверка: пользователь — администратор?
 */
function isAdmin(): bool {
    return isAuth() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Требовать авторизацию: если нет — редирект на вход
 */
function requireAuth(): void {
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Требовать права администратора: если нет — редирект на главную
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /');
        exit;
    }
}

/**
 * Получить текущего пользователя из БД (если нужно)
 * Использует глобальный $pdo из database.php
 */
function getCurrentUser(?PDO $pdo = null): ?array {
    if (!isAuth() || !$pdo) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return $user ?: null;
}