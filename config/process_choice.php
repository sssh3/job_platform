<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Function to check if the database exists
function databaseExists($dbName, $conn) {
    $result = $conn->query("SHOW DATABASES LIKE '$dbName'");
    return $result && $result->num_rows > 0;
}

$servername = "localhost";
$username = "root"; // Default XAMPP MySQL username
$password = ""; // Default XAMPP MySQL password is empty

// Connect to MySQL server
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dbName = 'job_platform_db';

if (databaseExists($dbName, $conn)) {
    echo "Database already exists.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice = $_POST['setup_choice'];

    if ($choice === 'run_scripts') {
        // Include and execute your PHP scripts
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/setup_data.php';
        echo "Database setup using PHP scripts is complete.";
    } elseif ($choice === 'import_sql') {
        echo "Please manually import <b>/job_platform/assets/data/job_platform_db.sql</b> in phpmyadmin. <br> It will create the database.<br><br>";
    } else {
        echo "Invalid choice.";
    }
}

if ($conn !== null && $conn instanceof mysqli) {
    $conn->close();
}

echo '<br><a href="/job_platform" style="display: inline-block; padding: 10px 20px; font-size: 16px; text-decoration: none; background-color: #007BFF; color: white; border-radius: 5px;">Go Back to Homepage</a>';

?>
