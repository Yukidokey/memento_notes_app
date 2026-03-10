<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['user'] = $row['fullname'];
            $_SESSION['user_id'] = $row['id'];

            header("Location: dashboard.php");
            exit();

        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }

    } else {
        echo "<script>alert('User not found');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Sign In</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: var(--text-light);
            transition: 0.3s;
            z-index: 10;
        }
        .toggle-password:hover {
            color: var(--mint);
        }
        /* Ensure input padding accounts for the icon */
        .password-container input {
            padding-right: 45px !important;
        }
    </style>
</head>
<body class="full-screen-bg">
    <div class="overlay"></div>

    <div class="glass-card fade-in">
        <div class="logo">🌿</div>
        <h2 style="color: var(--emerald); margin-bottom: 5px;">Malasakit</h2>
        <p style="color: var(--text-light); margin-bottom: 25px; font-size: 0.95rem;">Welcome back, Ka-Malasakit.</p>

<form id="login-form" action="login.php" method="POST">

    <div class="form-group" style="text-align: left;">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="name@example.com" required>
    </div>
    
    <div class="form-group" style="text-align: left;">
        <label>Password</label>
        <div class="password-container">
            <input type="password" name="password" id="login-password" placeholder="••••••••" required>
            <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
        </div>
    </div>

    <button type="submit" class="btn-glow">Sign In</button>

</form>

        <p style="margin-top: 25px; font-size: 0.9rem; color: var(--text-main);">
            New here? <a href="signup.php" style="color: var(--mint); font-weight: 700; text-decoration: none;">Create Account</a>
        </p>
    </div>

    <script>
// Show/Hide Password Toggle
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#login-password');

togglePassword.addEventListener('click', function () {

    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');

});
    </script>
</body>
</html>