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



if (isset($_GET['country']) && !isset($_GET['province']) && !isset($_GET['city'])) {
    // Search for country
    $queryCountry = $_GET['country'];
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
} elseif (isset($_GET['country']) && isset($_GET['province']) && !isset($_GET['city'])) {
    // Search for province
    $queryCountry = $_GET['country'];
    $queryProvince = $_GET['province'];
    
    $sql = "SELECT admin1_name AS `name`
        FROM provinces
        JOIN countries 
            ON countries.name = ?
            AND provinces.country_code = countries.code
        WHERE admin1_name LIKE ?
        LIMIT 10
    ";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$queryProvince%";
    $stmt->bind_param('ss', $queryCountry, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }

    echo json_encode($locations);

} elseif (isset($_GET['country']) && isset($_GET['province']) && isset($_GET['city'])) {
    // Search for city
    $queryProvince = $_GET['province'];
    $queryCity = $_GET['city'];

    $sql = "SELECT city_name AS `name`
        FROM cities
        JOIN provinces
            ON provinces.admin1_name = ?
            AND cities.admin1_code = provinces.admin1_code
            AND cities.country_code = provinces.country_code
        WHERE city_name LIKE ?
        LIMIT 20
    ";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$queryCity%";
    $stmt->bind_param('ss', $queryProvince, $searchTerm);
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
