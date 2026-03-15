<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AttendifyV2 - Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card glass">
        <div class="auth-brand">
            <div class="brand-logo">A</div>
            <div>
                <div class="brand-name">AttendifyV2</div>
                <div class="brand-tagline">Admin Control Panel</div>
            </div>
        </div>
        <h1 class="auth-title">Admin sign in</h1>
        <p class="auth-subtitle">Restricted access for system administrators only.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/admin-login">
            <div class="form-group">
                <label for="admin_email">Email</label>
                <input type="email" id="admin_email" name="email" required placeholder="admin@attendify.local">
            </div>
            <div class="form-group">
                <label for="admin_password">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="admin_password" name="password" required placeholder="••••••••">
                    <button type="button" class="password-toggle" data-target="admin_password" aria-label="Toggle password visibility">
                        <span class="password-toggle-icon"></span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Sign in as admin</button>
        </form>
        <p class="auth-footer">
            Not an admin?
            <a href="<?= BASE_URL ?>/" style="color:#00ffa3;">Back to main login</a>
        </p>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>

