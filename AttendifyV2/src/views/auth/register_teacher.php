<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AttendifyV2 - Teacher Sign up</title>
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
        <h1 class="auth-title">Create teacher account</h1>
        <p class="auth-subtitle">Sign up as a teacher to start creating QR attendance sessions.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/register-teacher">
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" required value="<?= isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="employee_number">Employee number</label>
                <input type="text" id="employee_number" name="employee_number" required value="<?= isset($employeeNumber) ? htmlspecialchars($employeeNumber, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" required value="<?= isset($department) ? htmlspecialchars($department, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password visibility">
                        <span class="password-toggle-icon"></span>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-track">
                        <div id="password-strength-bar" class="password-strength-bar"></div>
                    </div>
                    <div id="password-strength-label" class="password-strength-label">Enter a password</div>
                </div>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password_confirm" name="password_confirm" required>
                    <button type="button" class="password-toggle" data-target="password_confirm" aria-label="Toggle password visibility">
                        <span class="password-toggle-icon"></span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Sign up as teacher</button>
        </form>
        <p class="auth-footer">
            Already have an account?
            <a href="<?= BASE_URL ?>/" style="color:#00ffa3;">Sign in</a>
        </p>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>

