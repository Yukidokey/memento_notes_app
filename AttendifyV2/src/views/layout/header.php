<?php
if (!isset($baseUrl)) {
    $baseUrl = BASE_URL;
}
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AttendifyV2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<?php $bodyClass = isset($user) ? 'app-body' : 'auth-body'; ?>
<body class="<?= $bodyClass ?>">
<?php if ($user): ?>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <span class="brand-logo">A</span>
                <span class="brand-text">AttendifyV2</span>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= $baseUrl ?>/dashboard" class="nav-link">Dashboard</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= $baseUrl ?>/admin/users" class="nav-link">Users</a>
                    <a href="<?= $baseUrl ?>/admin/classes" class="nav-link">Classes</a>
                <?php elseif ($user['role'] === 'teacher'): ?>
                    <a href="<?= $baseUrl ?>/teacher/sessions" class="nav-link">My Sessions</a>
                <?php elseif ($user['role'] === 'student'): ?>
                    <a href="<?= $baseUrl ?>/student/attendance" class="nav-link">My Attendance</a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <div class="user-mini">
                    <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= e($user['name']) ?></div>
                        <div class="user-role"><?= e(ucfirst($user['role'])) ?></div>
                    </div>
                </div>
                <a href="<?= $baseUrl ?>/logout" class="logout-link">Logout</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-toggle" type="button" aria-label="Toggle navigation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <div class="topbar-title">Cloud-Based QR Attendance</div>
                </div>
                <div class="topbar-meta">
                    <span><?= date('M d, Y') ?></span>
                </div>
            </header>
            <section class="page-content">
<?php endif; ?>

