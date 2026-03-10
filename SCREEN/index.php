<?php
session_start();

// DATABASE CONNECTION
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "screenhub_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// LOGIN LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$user' OR email='$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_pass = isset($row['password']) ? trim($row['password']) : '';
        // Use password_verify for hashed passwords
        if ($pass !== '' && $db_pass !== '' && password_verify($pass, $db_pass)) {
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo '<script>alert("Incorrect password");</script>';
        }
    } else {
        echo '<script>alert("User not found");</script>';
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<title>ScreenHub | Sign In</title>

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>

<body class="screenhub-auth">

<div class="screenhub-bg"></div>

<div class="screenhub-wrapper">

  <div class="screenhub-logo">SCREENHUB</div>

  <div class="screenhub-card">

<h2>Sign in</h2>

<form method="POST">

  <div class="screenhub-field">
    <input type="text" name="username" required>
    <label>Username or Email</label>
  </div>

  <div class="screenhub-field password-field">
    <input type="password" name="password" id="password" required>
    <label>Password</label>

    <span class="toggle-eye" onclick="togglePassword('password', this)">
      <i class="fa-solid fa-eye"></i>
    </span>
  </div>

  <button type="submit" class="screenhub-btn">Sign In</button>

</form>

<p class="screenhub-link">
  New to ScreenHub?
  <a href="signup.php">Create account</a>
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
</script>

</body>
</html>
