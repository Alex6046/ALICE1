<?php
include 'db.php';

// Create Users Table if not exists (just in case)
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'student',
    verification_code VARCHAR(6),
    email_verified_at TIMESTAMP NULL
)");

// Create User Profile Table
$conn->query("CREATE TABLE IF NOT EXISTS user_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Create Events Table (needed for FKs maybe, or just for Admin to approve into)
$conn->query("CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE,
    venue VARCHAR(255),
    status VARCHAR(50)
)");

// Insert Test Student
$password = password_hash('password123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT IGNORE INTO users (id, username, email, password, role) VALUES (1, 'student_test', 'student@test.com', ?, 'student')");
$stmt->bind_param("s", $password);
$stmt->execute();

$conn->query("INSERT IGNORE INTO user_profile (user_id, full_name, bio) VALUES (1, 'Test Student', 'I am a test student')");

// Insert Test Admin
$stmt = $conn->prepare("INSERT IGNORE INTO users (id, username, email, password, role) VALUES (2, 'admin_test', 'admin@test.com', ?, 'admin')");
$stmt->bind_param("s", $password);
$stmt->execute();

echo "Test data setup complete.";
?>
