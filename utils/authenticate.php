<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Database connection
$servername = "localhost";
$db_username = "root"; // Default XAMPP username
$db_password = ""; // Default XAMPP password
$dbname = "job_platform_db";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$username = $_POST['username'];
$password = $_POST['password'];

// Prevent SQL Injection
$username = $conn->real_escape_string($username);
$password = $conn->real_escape_string($password);

// Verify user
$sql = "SELECT * FROM users WHERE user_name='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Successful login
    $_SESSION['username'] = $username;
    $_SESSION['logged_in'] = true;
    echo "Login successful! Welcome, " . $username;
    header("Location: /job_platform/");
    exit();
} else {
    // Invalid credentials
    echo "Invalid username or password.";
    exit();
}
