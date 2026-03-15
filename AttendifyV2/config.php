<?php

// Basic configuration for AttendifyV2

define('DB_HOST', 'localhost');
define('DB_NAME', 'attendify_v2');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL of the app (path-only so it works on localhost and LAN IP)
define('BASE_URL', '/AttendifyV2/public');

// LAN IP of the machine running XAMPP (used in QR codes).
// IMPORTANT: replace this with your actual IPv4 address from `ipconfig`.
// Example: '192.168.1.23'
define('APP_HOST', '192.168.254.138');

// Timezone
date_default_timezone_set('Asia/Manila');

// Simple PDO connection helper
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

