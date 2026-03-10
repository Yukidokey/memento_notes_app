<?php
session_start();
header('Content-Type: application/json');

// DATABASE CONNECTION
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "screenhub_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed."]);
    exit();
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($_SESSION['username'])) {
    echo json_encode(["success" => false, "error" => "Not logged in."]);
    exit();
}

$username = $_SESSION['username'];
$newUsername = isset($input['username']) ? trim($input['username']) : $username;
$newPassword = isset($input['password']) ? $input['password'] : '';
$newAvatar = isset($input['avatar']) ? $input['avatar'] : '';

// Update username
if ($newUsername !== $username) {
    $stmt = $conn->prepare("UPDATE users SET username=? WHERE username=?");
    $stmt->bind_param("ss", $newUsername, $username);
    $stmt->execute();
    $stmt->close();
    $_SESSION['username'] = $newUsername;
}

// Update password if provided
if ($newPassword !== '') {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt->bind_param("ss", $hashedPassword, $_SESSION['username']);
    $stmt->execute();
    $stmt->close();
}

// Update avatar in session and database
if ($newAvatar !== '') {
    // Store avatar in DB (make sure users table has an avatar column)
    $stmt = $conn->prepare("UPDATE users SET avatar=? WHERE username=?");
    $stmt->bind_param("ss", $newAvatar, $_SESSION['username']);
    $stmt->execute();
    $stmt->close();
    // Refresh session avatar from DB
    $_SESSION['avatar'] = $newAvatar;
}

$conn->close();
echo json_encode(["success" => true]);
?>
