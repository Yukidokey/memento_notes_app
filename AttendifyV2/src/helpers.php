<?php

require_once __DIR__ . '/../config.php';

function view(string $template, array $data = []): void
{
    extract($data);
    $baseUrl = BASE_URL;
    require __DIR__ . '/views/layout/header.php';
    require __DIR__ . '/views/' . $template . '.php';
    require __DIR__ . '/views/layout/footer.php';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

