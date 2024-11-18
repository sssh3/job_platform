<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "job_platform_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$queryCountry = isset($_GET['country']) ? $_GET['country'] : '';

if (!isset($_GET['city'])) {
    // Search for country
    $sql = "SELECT `name` FROM countries WHERE name LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$queryCountry%";
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }

    echo json_encode($locations);
} else {
    // Search for city
    $queryCity = $_GET['city'];
    $sql = "SELECT city_name AS `name`
        FROM cities
        JOIN countries 
            ON cities.country_code = countries.code
            AND countries.name = ?
        WHERE city_name LIKE ?
        LIMIT 20
    ";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$queryCity%";
    $stmt->bind_param('ss', $queryCountry, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }

    echo json_encode($locations);
}





$stmt->close();
$conn->close();
?>
