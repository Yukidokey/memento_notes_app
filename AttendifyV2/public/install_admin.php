<?php

// One-time installer to create an admin account with a known password.
// After running successfully, DELETE this file for security.

require_once __DIR__ . '/../config.php';

try {
    $db = get_db();

    // Check if an admin already exists
    $stmt = $db->query("SELECT id, email FROM users WHERE role = 'admin' LIMIT 1");
    $existing = $stmt->fetch();

    if ($existing) {
        echo 'Admin already exists with email: ' . htmlspecialchars($existing['email'], ENT_QUOTES, 'UTF-8') . '<br>';
        echo 'If you want to change it, update the row in the database manually.';
        exit;
    }

    $name = 'Admin User';
    $email = 'admin@attendify.local';
    $plainPassword = 'Penelope123!@';
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare('
        INSERT INTO users (name, email, password_hash, role, status)
        VALUES (:name, :email, :hash, :role, :status)
    ');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'hash' => $hash,
        'role' => 'admin',
        'status' => 'active',
    ]);

    echo 'Admin account created successfully.<br>';
    echo 'Email: ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'Password: ' . htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8') . '<br><br>';
    echo 'For security, please delete public/install_admin.php after logging in.';
} catch (Exception $e) {
    http_response_code(500);
    echo 'Failed to create admin: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

