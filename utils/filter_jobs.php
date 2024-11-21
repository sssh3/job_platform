<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
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

// Params: {search-country, search-province, search-city, job-type, search-title}
$params = [
    'search-country' => $_GET['search-country'] ?? '',
    'search-province' => $_GET['search-province'] ?? '',
    'search-city' => $_GET['search-city'] ?? '',
    'job-type' => $_GET['job-type'] ?? '',
    'search-title' => $_GET['search-title'] ?? ''
];

// Initialize the SQL query with basic filtering
$sql = "SELECT
            job_id, job_title, min_salary, max_salary,
            cities.city_name, 
            provinces.admin1_name, 
            countries.name AS country_name, 
            users.user_name,
            job_types.job_type_name
        FROM jobs
        JOIN cities ON jobs.address_id = cities.address_id
        JOIN provinces ON cities.admin1_code = provinces.admin1_code AND cities.country_code = provinces.country_code
        JOIN countries ON provinces.country_code = countries.code
        JOIN users ON jobs.employer_id = users.u_id
        JOIN user_types ON users.user_type_id = user_types.user_type_id
        JOIN job_types ON jobs.job_type_id = job_types.job_type_id
        WHERE user_types.user_type_name = 'employer'
    ";

// Add filtering conditions based on form inputs
if ($params['search-country'] !== '') {
    $loc = $conn->real_escape_string($params['search-country']);
    $sql .= " AND countries.name = '$loc'";
}

if ($params['search-province'] !== '') {
    $loc = $conn->real_escape_string($params['search-province']);
    $sql .= " AND admin1_name = '$loc'";
}

if ($params['search-city'] !== '') {
    $loc = $conn->real_escape_string($params['search-city']);
    $sql .= " AND city_name = '$loc'";
}

if ($params['job-type'] !== '') {
    $loc = $conn->real_escape_string($params['job-type']);
    $sql .= " AND job_type_name = '$loc'";
}

if ($params['search-title'] !== '') {
    $jobTitle = $conn->real_escape_string($params['search-title']);
    $sql .= " AND job_title LIKE '%$jobTitle%'";
}

// Record sql time
$start_time = microtime(true);

// Execute the query
$result = $conn->query($sql);

$time_used = microtime(true) - $start_time;

$details = [];
$count = $result->num_rows;
if ($count > 0) {
    $rowCount = 0;
    while($row = $result->fetch_assoc()) {
        if ($rowCount >= 50) {
            break; // Exit the loop after processing 50 rows
        }
        $rowCount++;
        $location = $row['city_name'] . ', ' . $row['admin1_name'] . ', ' . $row['country_name'];
        $details[] = [
            'sqlTime' => round($time_used, 3),
            'jobId' => 'job-' . $row['job_id'],
            'count' => $count,
            'title' => $row['job_title'],
            'jobType' => $row['job_type_name'],
            'employer' => $row['user_name'],
            'location' => $location,
            'minSalary' => $row['min_salary'],
            'maxSalary' => $row['max_salary'],
        ];
    }
}

// Close the connection
$conn->close();

// Return the JSON-encoded array
echo json_encode($details);
?>
