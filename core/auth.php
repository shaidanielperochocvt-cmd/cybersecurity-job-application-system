<?php
declare(strict_types=1);

function is_logged_in(): bool
{
    return isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id']);
}

function current_user_id(): int
{
    if (!is_logged_in()) {
        http_response_code(401);
        exit('You must log in first.');
    }

    return (int) $_SESSION['user']['id'];
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access the system.');
        redirect('login.php');
    }
}

function redirect_if_logged_in(): void
{
    if (is_logged_in()) {
        redirect('index.php');
    }
}
