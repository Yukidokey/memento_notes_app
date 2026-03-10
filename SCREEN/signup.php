<?php
// DATABASE CONNECTION
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "screenhub_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SIGNUP LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    // Password strength check (server-side)
    $strength = 0;
    if (strlen($pass) >= 8) $strength++;
    if (preg_match('/[A-Z]/', $pass)) $strength++;
    if (preg_match('/[a-z]/', $pass)) $strength++;
    if (preg_match('/[0-9]/', $pass)) $strength++;
    if (preg_match('/[^A-Za-z0-9]/', $pass)) $strength++;

    if ($strength < 3) {
        echo '<script>alert("Password is too weak. Please use a stronger password.");</script>';
    } else {

        if ($pass != $confirm) {
            echo "<script>alert('Passwords do not match');</script>";
        } else {

            // Hash password
            $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (username, email, password)
                    VALUES ('$user', '$email', '$hashedPassword')";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['username'] = $user; // Set this to the username/email used for signup
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<script>alert('Error creating account');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<title>ScreenHub | Sign Up</title>

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>

<body class="screenhub-auth">

<div class="screenhub-bg"></div>

<div class="screenhub-wrapper">

  <div class="screenhub-logo">SCREENHUB</div>

  <div class="screenhub-card">

<h2>Create account</h2>

<form method="POST">

  <div class="screenhub-field">
    <input type="text" name="username" required>
    <label>Username</label>
  </div>

  <div class="screenhub-field">
    <input type="email" name="email" required>
    <label>Email</label>
  </div>

  <div class="screenhub-field password-field">
    <input type="password" name="password" id="signup-password" required oninput="checkStrength(this.value)">
    <label>Password</label>
    <span class="toggle-eye" onclick="togglePassword('signup-password', this)">
      <i class="fa-solid fa-eye"></i>
    </span>
    <div id="strength-bar" style="height:6px; width:100%; background:#eee; margin-top:4px; border-radius:3px;">
      <div id="strength-fill" style="height:100%; width:0%; background:red; border-radius:3px;"></div>
    </div>
  </div>

  <div class="screenhub-field password-field">
    <input type="password" name="confirmPassword" id="confirmPassword" required>
    <label>Confirm password</label>
    <span class="toggle-eye" onclick="togglePassword('confirmPassword', this)">
      <i class="fa-solid fa-eye"></i>
    </span>
  </div>

  <p id="signupError" class="screenhub-error"></p>

  <button type="submit" class="screenhub-btn">Create Account</button>

</form>

<p class="screenhub-link">
  Already have an account?
  <a href="index.php">Sign in</a>
</p>

  </div>

</div>

<script>
function togglePassword(id, el){
  const input = document.getElementById(id);
  const icon = el.querySelector("i");

  if(input.type === "password"){
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  }else{
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

function checkStrength(pw) {
  let strength = 0;
  if (pw.length >= 8) strength++;
  if (/[A-Z]/.test(pw)) strength++;
  if (/[a-z]/.test(pw)) strength++;
  if (/[0-9]/.test(pw)) strength++;
  if (/[^A-Za-z0-9]/.test(pw)) strength++;
  let bar = document.getElementById('strength-fill');
  let colors = ['red','orange','yellow','#7adf7a','green'];
  let widths = ['20%','40%','60%','80%','100%'];
  bar.style.width = widths[strength-1] || '0%';
  bar.style.background = colors[strength-1] || 'red';
}
</script>

</body>
</html>
