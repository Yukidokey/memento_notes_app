<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AttendifyV2 - Login</title>
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
                <div class="brand-tagline">Cloud-Based QR Attendance</div>
            </div>
        </div>
        <h1 class="auth-title">Student / Teacher login</h1>
        <p class="auth-subtitle">Sign in to access your QR attendance account.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/login">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password visibility">
                        <span class="password-toggle-icon"></span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Sign in</button>
        </form>
        <p class="auth-footer">
            Don't have an account yet?
            <a href="<?= BASE_URL ?>/register" style="color:#00ffa3;">Sign up as student</a>
            <br>
            <a href="<?= BASE_URL ?>/register-teacher" style="color:#00ffa3;">Sign up as teacher</a>
            <br><br>
            Admin?
            <a href="<?= BASE_URL ?>/admin-login" style="color:#00ffa3;">Go to admin login</a>
        </p>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>

