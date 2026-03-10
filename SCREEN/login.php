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
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Get JSON input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if(!isset($input['username']) || !isset($input['password'])){
    echo json_encode(["status"=>"error","message"=>"Please enter username and password"]);
    exit();
}

$user = $conn->real_escape_string($input['username']);
$pass = $input['password'];

// Check user
$sql = "SELECT * FROM users WHERE username='$user' OR email='$user' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $db_pass = trim($row['password']); // hashed password in DB

    if(password_verify($pass, $db_pass)){
        $_SESSION['username'] = $row['username'];
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Incorrect password"]);
    }
} else {
    echo json_encode(["status"=>"error","message"=>"User not found"]);
}

$conn->close();
?>