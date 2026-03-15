-- AttendifyV2 database schema

CREATE DATABASE IF NOT EXISTS attendify_v2
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE attendify_v2;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','student') NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student profile
CREATE TABLE IF NOT EXISTS student_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  student_number VARCHAR(50) NOT NULL UNIQUE,
  course VARCHAR(100) NULL,
  year_level VARCHAR(50) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Teacher profile
CREATE TABLE IF NOT EXISTS teacher_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  employee_number VARCHAR(50) NOT NULL UNIQUE,
  department VARCHAR(100) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Courses / subjects
CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL
);

-- Sections / class groups
CREATE TABLE IF NOT EXISTS sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  course VARCHAR(100) NULL,
  year_level VARCHAR(50) NULL
);

-- Class offerings (course + section + teacher + term)
CREATE TABLE IF NOT EXISTS class_offerings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  section_id INT NOT NULL,
  teacher_id INT NOT NULL,
  term VARCHAR(50) NULL,
  schedule_pattern VARCHAR(100) NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (section_id) REFERENCES sections(id),
  FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Enrollment (students in a class offering)
CREATE TABLE IF NOT EXISTS enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_offering_id INT NOT NULL,
  student_id INT NOT NULL,
  UNIQUE KEY unique_enrollment (class_offering_id, student_id),
  FOREIGN KEY (class_offering_id) REFERENCES class_offerings(id),
  FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Sessions (each meeting)
CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_offering_id INT NOT NULL,
  session_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NULL,
  qr_token VARCHAR(255) NOT NULL,
  qr_expires_at DATETIME NOT NULL,
  status ENUM('scheduled','ongoing','ended') NOT NULL DEFAULT 'scheduled',
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_offering_id) REFERENCES class_offerings(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Attendance records
CREATE TABLE IF NOT EXISTS attendance_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  student_id INT NOT NULL,
  status ENUM('present','late','absent') NOT NULL DEFAULT 'present',
  scan_timestamp DATETIME NOT NULL,
  source ENUM('qr','manual') NOT NULL DEFAULT 'qr',
  device_id VARCHAR(100) NULL,
  location_lat DECIMAL(10,8) NULL,
  location_lng DECIMAL(11,8) NULL,
  UNIQUE KEY unique_attendance (session_id, student_id),
  FOREIGN KEY (session_id) REFERENCES sessions(id),
  FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_id INT NOT NULL,
  channel ENUM('in_app','email','whatsapp') NOT NULL DEFAULT 'in_app',
  type VARCHAR(50) NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  FOREIGN KEY (recipient_id) REFERENCES users(id)
);

-- Feedback
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  submitted_by INT NOT NULL,
  role ENUM('admin','teacher','student') NOT NULL,
  message TEXT NOT NULL,
  rating TINYINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submitted_by) REFERENCES users(id)
);

-- Devices (optional)
CREATE TABLE IF NOT EXISTS devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  device_fingerprint VARCHAR(255) NOT NULL,
  platform VARCHAR(50) NULL,
  last_seen_at DATETIME NULL,
  UNIQUE KEY unique_device (user_id, device_fingerprint),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

