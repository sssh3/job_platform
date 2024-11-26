<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// User is logged in, proceed with page content
echo "Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!";