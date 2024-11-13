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

// Login or Register
if (isset($_POST['action'])) {
    // Get user input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevent SQL Injection
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);
} else {
    // For visitor
    $username = 'visitor';
    $password = 'visitor';
    $_POST['action'] = 'Login';
}
if ($_POST['action'] === 'Login') {
    // Verify user
    $sql = "SELECT * FROM users WHERE user_name='$username' AND `password`='$password'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Successful login
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        $_SESSION["type"] = $result->fetch_assoc()["user_type"];
        // echo "Login successful! Welcome, " . $username;
        header("Location: /job_platform/");
    } else {
        // Invalid credentials
        // echo "Invalid username or password.";
        $_SESSION["msg"] = "Invalid username or password.";
        header("Location: /job_platform/login");
    }
} elseif ($_POST['action'] === 'Register') {
    // Check if the user name already exsists
    $sql = "SELECT * FROM users WHERE user_name='$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $_SESSION["msg"] = "User name not available.";
        header("Location: /job_platform/login");
        exit;
    }
    // Set user_type to 'job-seeker' for temp
    $sql = "INSERT INTO users (user_name, `password`, user_type) 
            VALUES ('$username', '$password', 'job-seeker')";
    $conn->query($sql);
    $_SESSION["msg"] = "Register success by name \"$username\".";
    header("Location: /job_platform/login");
}




