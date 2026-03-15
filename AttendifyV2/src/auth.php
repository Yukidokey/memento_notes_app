<?php

require_once __DIR__ . '/../config.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        // Remember the page the user wanted to access (e.g. QR check-in),
        // so we can send them back after a successful login.
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect(BASE_URL . '/');
    }
}

function require_role(string $role): void
{
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

