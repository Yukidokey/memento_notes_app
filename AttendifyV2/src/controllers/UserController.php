<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

class UserController
{
    public function handle(string $path): void
    {
        // For now we only support /admin/users
        if ($path === '/admin/users' || $path === '/admin/users/') {
            $this->index();
            return;
        }

        http_response_code(404);
        echo 'Not Found';
    }

    private function index(): void
    {
        $db = get_db();
        $user = current_user();

        // Simple list of all users for admin
        $stmt = $db->query('SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();

        view('admin/users/index', [
            'user' => $user,
            'users' => $users,
        ]);
    }
}

