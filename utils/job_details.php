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

// Initialize the SQL query with basic filtering
$sql = "SELECT DISTINCT
        job_id, job_title, min_salary, max_salary, requirements, benefits, `description`,
        cities.city_name, 
        provinces.admin1_name, 
        countries.country_name, 
        users.user_name,
        job_types.job_type_name
    FROM 
        jobs
    JOIN 
        cities USING (address_id)
    JOIN 
        provinces USING (admin1_code, country_code)
    JOIN 
        (SELECT code AS country_code, `name` AS country_name FROM countries) countries USING (country_code)
    JOIN 
        (SELECT u_id AS employer_id, user_name FROM users JOIN user_types USING (user_type_id) WHERE user_type_name = 'employer') users USING (employer_id)
    JOIN
        job_types USING (job_type_id)
    WHERE 1=1
    ";

// Add filtering conditions based on form inputs
if (isset($_GET['id']) && $_GET['id'] != '') {
    $jobId = explode('-', $_GET['id'])[1];
    $sql .= " AND job_id = '$jobId'";
}


// Record sql time
$start_time = microtime(true);

// Execute the query
$result = $conn->query($sql);

$time_used = microtime(true) - $start_time;

$details = [];
$count = $result->num_rows;
if ($count > 0) {
    while($row = $result->fetch_assoc()) {
        $location = $row['city_name'] . ', ' . $row['admin1_name'] . ', ' . $row['country_name'];
        $details = [
            'sqlTime' => round($time_used, 3),
            'jobId' => $row['job_id'],
            'desctiption' => $row['description'],
            'benefits' => $row['benefits'],
            'requirements' => $row['requirements'],
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
