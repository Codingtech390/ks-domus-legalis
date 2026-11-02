<?php

// declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['admin_id']) || !is_numeric($_SESSION['admin_id'])) {
    set_flash('error', 'Please sign in to access the administration panel.');
    redirect('admin/login.php');
}

$adminStatement = $pdo->prepare(
    'SELECT id, name, email FROM admins WHERE id = :id LIMIT 1'
);

$adminStatement->execute([
    'id' => (int) $_SESSION['admin_id'],
]);

$authenticatedAdmin = $adminStatement->fetch();

if (!$authenticatedAdmin) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
    session_start();

    set_flash('error', 'Your session has expired. Please sign in again.');
    redirect('admin/login.php');
}

$admin = $authenticatedAdmin;
