<?php
// Database Setup Script - Run this once to create all necessary tables
// Access this file in your browser: http://localhost/MalasakitApp/database_setup.php

$host = "localhost";
$user = "root";
$password = "";
$database = "malasakit_db";

// Create connection without database
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

$conn->select_db($database);

// Create users table (if not exists)
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    birthday DATE,
    purok VARCHAR(50),
    barangay VARCHAR(50),
    philhealth VARCHAR(50),
    blood_type VARCHAR(10),
    allergies TEXT,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "Users table ready<br>";

// Create appointments table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    patient_name VARCHAR(100) NOT NULL,
    health_worker VARCHAR(100) NOT NULL,
    visit_type VARCHAR(50) NOT NULL,
    appointment_date DATE NOT NULL,
    reason VARCHAR(100) NOT NULL,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$conn->query($sql);
echo "Appointments table ready<br>";

// Create health_records table
$sql = "CREATE TABLE IF NOT EXISTS health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    record_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    health_worker VARCHAR(100),
    location VARCHAR(100),
    record_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$conn->query($sql);
echo "Health Records table ready<br>";

// Create reminders table
$sql = "CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reminder_type VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    due_date DATE,
    frequency VARCHAR(20),
    status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$conn->query($sql);
echo "Reminders table ready<br>";

// Create doctors/health_workers table
$sql = "CREATE TABLE IF NOT EXISTS health_workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    specialty VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'Available',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "Health Workers table ready<br>";

// Insert default health workers
$check = "SELECT COUNT(*) as count FROM health_workers";
$result = $conn->query($check);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $workers = [
        "INSERT INTO health_workers (name, title, location, specialty, phone, status, image_url) VALUES ('Maria Clara, RM', 'Registered Midwife', 'Barangay Health Center - Poblacion', 'Maternal & Child Care', '09123456789', 'Available', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria&gender=female')",
        "INSERT INTO health_workers (name, title, location, specialty, phone, status, image_url) VALUES ('Juan Dela Cruz', 'Barangay Health Worker', 'Community Outreach - Purok 7', 'General Health Support', '09123456780', 'Available', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Juan&gender=male')",
        "INSERT INTO health_workers (name, title, location, specialty, phone, email, status, image_url) VALUES ('Dr. Jose Rizal, MD', 'General Physician', 'Municipal Health Office - Town Center', 'General Physician', '09123456788', 'jose@malasakit.ph', 'Busy', 'https://api.dicebear.com/7.x/avataaars/svg?seed=DocRizal&gender=male')",
        "INSERT INTO health_workers (name, title, location, specialty, phone, status, image_url) VALUES ('Dr. Elena Adarna', 'Pediatric Specialist', 'Rural Health Unit - Sitio Maligaya', 'Pediatric Specialist', '09123456781', 'Available', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Elena&gender=female')",
        "INSERT INTO health_workers (name, title, location, specialty, phone, status, image_url) VALUES ('Antonio Luna, RND', 'Nutritionist-Dietitian', 'Community Center - Barangay Malinis', 'Nutritionist-Dietitian', '09123456782', 'Available', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Antonio&gender=male')",
        "INSERT INTO health_workers (name, title, location, specialty, phone, status, image_url) VALUES ('Luz Viminda', 'Senior Health Lead', 'Senior Health Lead - Purok 1', 'Elderly Care Lead', '09123456783', 'In Field', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Luz&gender=female')"
    ];
    
    foreach ($workers as $sql) {
        $conn->query($sql);
    }
    echo "Default health workers inserted<br>";
}

// Create messages table
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    worker_id INT,
    sender_type VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (worker_id) REFERENCES health_workers(id)
)";
$conn->query($sql);
echo "Messages table ready<br>";

echo "<h2>Setup Complete! Your database is ready.</h2>";
echo "<p>Now you can use your Malasakit system with database connectivity.</p>";
echo "<a href='login.php' style='color: #10b981; font-size: 1.2rem;'>Go to Login Page</a>";

$conn->close();
?>

