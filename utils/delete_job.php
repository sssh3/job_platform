<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Database connection (update with your credentials)
$host = 'localhost';
$db = 'job_platform_db';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add filtering conditions based on form inputs
if (isset($_GET['job_id']) && $_GET['job_id'] != '') {
    $jobId = explode('-', $_GET['job_id'])[1];
    $jobId = $conn->real_escape_string($jobId);
    $jobId = (int)$jobId;
}

$sql = "
DELETE FROM applications
WHERE job_id = $jobId;
";

$conn->query($sql);

$sql = "
DELETE FROM jobs
WHERE job_id = $jobId;
";

$conn->query($sql);

$result = $conn->query($sql);


header("Location: /job_platform/views/control_employer.php");
