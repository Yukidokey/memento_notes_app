<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers.php';

class AuthController
{
    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $db = get_db();
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND status = "active"');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];

                // If there is an intended URL (e.g. QR check-in), send the user there after login.
                if (!empty($_SESSION['intended_url'])) {
                    $target = $_SESSION['intended_url'];
                    unset($_SESSION['intended_url']);
                    header('Location: ' . $target);
                    exit;
                }

                redirect(BASE_URL . '/dashboard');
            } else {
                $error = 'Invalid credentials.';
                require __DIR__ . '/../views/auth/login.php';
                return;
            }
        } else {
            require __DIR__ . '/../views/auth/login.php';
        }
    }

    public function handleAdminLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $db = get_db();

            // Hard-wired admin bootstrap: if credentials match the known admin,
            // ensure the account exists (or create it) and log in.
            if ($email === 'admin@attendify.local' && $password === 'Penelope123!@') {
                // Try to find existing admin with this email
                $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND role = "admin" LIMIT 1');
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                if (!$user) {
                    // Create the admin user if it does not exist yet
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare('
                        INSERT INTO users (name, email, password_hash, role, status)
                        VALUES (:name, :email, :hash, "admin", "active")
                    ');
                    $stmt->execute([
                        'name' => 'Admin User',
                        'email' => $email,
                        'hash' => $hash,
                    ]);

                    $id = (int)$db->lastInsertId();
                    $user = [
                        'id' => $id,
                        'name' => 'Admin User',
                        'email' => $email,
                        'role' => 'admin',
                    ];
                }

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];

                redirect(BASE_URL . '/dashboard');
                return;
            }

            // Normal admin login path (for other admin accounts, if any)
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND status = "active" AND role = "admin"');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];

                redirect(BASE_URL . '/dashboard');
                return;
            }

            $error = 'Invalid admin credentials.';
            require __DIR__ . '/../views/auth/login_admin.php';
            return;
        } else {
            require __DIR__ . '/../views/auth/login_admin.php';
        }
    }

    public function handleRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $studentNumber = trim($_POST['student_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $errors = [];
            if ($name === '') {
                $errors[] = 'Name is required.';
            }
            if ($email === '') {
                $errors[] = 'Email is required.';
            }
            if ($studentNumber === '') {
                $errors[] = 'Student number is required.';
            }
            if ($password === '' || $passwordConfirm === '') {
                $errors[] = 'Password and confirmation are required.';
            } elseif ($password !== $passwordConfirm) {
                $errors[] = 'Passwords do not match.';
            } else {
                // Stronger password policy: at least 8 chars, with letters and numbers
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters long.';
                }
                if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                    $errors[] = 'Password must contain both letters and numbers.';
                }
            }

            if (!empty($errors)) {
                $error = implode(' ', $errors);
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            $db = get_db();

            // Check for existing user
            $stmt = $db->prepare('SELECT 1 FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn()) {
                $error = 'An account with this email already exists.';
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            $db->beginTransaction();
            try {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare('
                    INSERT INTO users (name, email, password_hash, role)
                    VALUES (:name, :email, :hash, :role)
                ');
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'hash' => $passwordHash,
                    'role' => 'student',
                ]);

                $userId = (int)$db->lastInsertId();

                $stmt = $db->prepare('
                    INSERT INTO student_profiles (user_id, student_number)
                    VALUES (:uid, :student_number)
                ');
                $stmt->execute([
                    'uid' => $userId,
                    'student_number' => $studentNumber,
                ]);

                $db->commit();

                $_SESSION['user'] = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'student',
                ];

                redirect(BASE_URL . '/dashboard');
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Registration failed. Please try again.';
                require __DIR__ . '/../views/auth/register.php';
            }
        } else {
            require __DIR__ . '/../views/auth/register.php';
        }
    }

    public function handleRegisterTeacher(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $employeeNumber = trim($_POST['employee_number'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $errors = [];
            if ($name === '') {
                $errors[] = 'Name is required.';
            }
            if ($email === '') {
                $errors[] = 'Email is required.';
            }
            if ($employeeNumber === '') {
                $errors[] = 'Employee number is required.';
            }
            if ($department === '') {
                $errors[] = 'Department is required.';
            }
            if ($password === '' || $passwordConfirm === '') {
                $errors[] = 'Password and confirmation are required.';
            } elseif ($password !== $passwordConfirm) {
                $errors[] = 'Passwords do not match.';
            } else {
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters long.';
                }
                if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                    $errors[] = 'Password must contain both letters and numbers.';
                }
            }

            if (!empty($errors)) {
                $error = implode(' ', $errors);
                require __DIR__ . '/../views/auth/register_teacher.php';
                return;
            }

            $db = get_db();

            // Check for existing user
            $stmt = $db->prepare('SELECT 1 FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn()) {
                $error = 'An account with this email already exists.';
                require __DIR__ . '/../views/auth/register_teacher.php';
                return;
            }

            $db->beginTransaction();
            try {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare('
                    INSERT INTO users (name, email, password_hash, role)
                    VALUES (:name, :email, :hash, :role)
                ');
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'hash' => $passwordHash,
                    'role' => 'teacher',
                ]);

                $userId = (int)$db->lastInsertId();

                $stmt = $db->prepare('
                    INSERT INTO teacher_profiles (user_id, employee_number, department)
                    VALUES (:uid, :employee_number, :department)
                ');
                $stmt->execute([
                    'uid' => $userId,
                    'employee_number' => $employeeNumber,
                    'department' => $department,
                ]);

                $db->commit();

                $_SESSION['user'] = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'teacher',
                ];

                redirect(BASE_URL . '/dashboard');
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Registration failed. Please try again.';
                require __DIR__ . '/../views/auth/register_teacher.php';
            }
        } else {
            require __DIR__ . '/../views/auth/register_teacher.php';
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        redirect(BASE_URL . '/');
    }
}

