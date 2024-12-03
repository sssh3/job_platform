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
    $usertype = $_POST['usertype'];

    // Prevent SQL Injection
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);
} else {
    // For visitor
    $username = 'visitor';
    $password = 'visitor';
    $_POST['action'] = 'Login';
}

// Record sql time
$start_time = microtime(true);

if ($_POST['action'] === 'Login') {
    // Verify user
    $sql = "SELECT * FROM users WHERE user_name='$username' AND `password`='$password'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Successful login
        $_SESSION['user_name'] = $username;
        $_SESSION['logged_in'] = true;

        // Store user_id
        $user_id = $result->fetch_assoc()['u_id'];
        $_SESSION['user_id'] = $user_id;

        // Store user_type_name in $_SESSION["type"]
        $result = $conn->query("SELECT user_type_name FROM user_types 
                                JOIN users ON users.user_name = '$username'
                                    AND users.user_type_id = user_types.user_type_id
                                ");
        $row = $result->fetch_assoc();
        $_SESSION["type"] = $row["user_type_name"];

        // Print time used on page
        $time_used = microtime(true) - $start_time;
        $_SESSION["msg"] =  "Login SQL time used : " . round($time_used, 4) . "s";

        if (isset($_SESSION['last_visited_url'])) {
            $last_url = $_SESSION['last_visited_url'];
            header("Location: $last_url");
        } else {
            header("Location: /job_platform/");
        }

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

    // Get user_type_id
    $sql = "SELECT user_type_id FROM user_types WHERE user_type_name = '$usertype'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $userTypeId = $row['user_type_id'];
    
    $sql = "INSERT INTO users (user_name, `password`, user_type_id) 
            VALUES ('$username', '$password', '$userTypeId')";
    $conn->query($sql);
    $_SESSION["msg"] = "Register success by name \"$username\" as a $usertype.";
    // Print time used on page
    $time_used = microtime(true) - $start_time;
    $_SESSION["msg"] =  $_SESSION["msg"] . "<br>Register SQL time used : " . round($time_used, 4) . "s";
    header("Location: /job_platform/login");
}




