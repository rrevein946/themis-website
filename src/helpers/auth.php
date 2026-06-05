<?php
function isAuth(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isAuth() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireAuth(): void {
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /');
        exit;
    }
}

function getCurrentUser(?PDO $pdo = null): ?array {
    if (!isAuth() || !$pdo) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, role, phone, specialization, photo_url, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return $user ?: null;
}
