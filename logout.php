<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();
require_post();
verify_csrf();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
session_start();
set_flash('success', 'You have successfully logged out.');
redirect('login.php');
