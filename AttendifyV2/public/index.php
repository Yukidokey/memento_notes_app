<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/');
$relativePath = '/' . ltrim(str_replace($basePath, '', $path), '/');

if ($relativePath === '/' || $relativePath === '/index.php') {
    if (is_logged_in()) {
        redirect(BASE_URL . '/dashboard');
    }
    require __DIR__ . '/../src/views/auth/login.php';
    exit;
}

switch (true) {
    case str_starts_with($relativePath, '/admin-login'):
        require __DIR__ . '/../src/controllers/AuthController.php';
        (new AuthController())->handleAdminLogin();
        break;
    case str_starts_with($relativePath, '/login'):
        require __DIR__ . '/../src/controllers/AuthController.php';
        (new AuthController())->handleLogin();
        break;
    case str_starts_with($relativePath, '/register-teacher'):
        require __DIR__ . '/../src/controllers/AuthController.php';
        (new AuthController())->handleRegisterTeacher();
        break;
    case str_starts_with($relativePath, '/register'):
        require __DIR__ . '/../src/controllers/AuthController.php';
        (new AuthController())->handleRegister();
        break;
    case str_starts_with($relativePath, '/logout'):
        require __DIR__ . '/../src/controllers/AuthController.php';
        (new AuthController())->logout();
        break;
    case str_starts_with($relativePath, '/dashboard'):
        require_login();
        require __DIR__ . '/../src/controllers/DashboardController.php';
        (new DashboardController())->index();
        break;
    case str_starts_with($relativePath, '/admin/users'):
        require_login();
        require_role('admin');
        require __DIR__ . '/../src/controllers/UserController.php';
        (new UserController())->handle($relativePath);
        break;
    case str_starts_with($relativePath, '/admin/classes'):
        require_login();
        require_role('admin');
        require __DIR__ . '/../src/controllers/AdminClassController.php';
        (new AdminClassController())->handle($relativePath);
        break;
    case str_starts_with($relativePath, '/teacher/sessions'):
        require_login();
        require_role('teacher');
        require __DIR__ . '/../src/controllers/TeacherSessionController.php';
        (new TeacherSessionController())->handle($relativePath);
        break;
    case str_starts_with($relativePath, '/student/attendance'):
        require_login();
        require_role('student');
        require __DIR__ . '/../src/controllers/StudentAttendanceController.php';
        (new StudentAttendanceController())->handle($relativePath);
        break;
    case str_starts_with($relativePath, '/qr'):
        require __DIR__ . '/../src/controllers/QrController.php';
        (new QrController())->show();
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
}

