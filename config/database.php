<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database configuration
$servername = "localhost";
$username = "root"; // Default XAMPP MySQL username
$password = ""; // Default XAMPP MySQL password is empty

try {
    // Create a connection to MySQL
    $conn = new PDO("mysql:host=$servername", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SHOW DATABASES LIKE 'job_platform_db'");
    if ($stmt->rowCount() > 0) {
        // echo "Database 'job_platform_db' already exists. Exiting script.<br>";
    }
    else {

        // SQL to create the database
        $sql = "CREATE DATABASE IF NOT EXISTS job_platform_db";
        $conn->exec($sql);
        echo "Database 'job_platform_db' created successfully<br>";

        // Connect to the newly created database
        $conn->exec("USE job_platform_db");

        // SQL to create the users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            u_id INT AUTO_INCREMENT PRIMARY KEY,
            user_name VARCHAR(50) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            user_type ENUM('visitor', 'job-seeker', 'employer', 'admin') NOT NULL
        )";
        $conn->exec($sql);
        echo "Table 'users' created successfully<br>";

        // Create admin and visitor
        $sql = "INSERT INTO users (user_name, `password`, user_type) VALUES 
                    ('admin', 'admin', 'admin'),
                    ('visitor', 'visitor', 'visitor')";
        $conn->exec($sql);

        // Create 'countries' table for iso3166 two-letter country code
        $sql = file_get_contents(__DIR__ . '/../assets/data/iso3166.sql');
        $conn->exec($sql);

        // Create cities table
        $sql = "CREATE TABLE IF NOT EXISTS cities (
            address_id INT AUTO_INCREMENT PRIMARY KEY,
            city_name VARCHAR(50) NOT NULL,
            country_code CHAR(2),
            FOREIGN KEY (country_code) REFERENCES countries(code)
        )";
        $conn->exec($sql);
        echo "Table 'cities' created successfully<br>";

        // Get valid country codes
        $validCountryCodes = [];
        $stmt = $conn->query("SELECT code FROM countries");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $validCountryCodes[] = $row['code'];
        }

        // Insert real world cities instances from GeoNames (The file contains cities with more than 15,000 people)
        $citiesFile = fopen(__DIR__ . '/../assets/data/cities15000.txt', 'r');
        if ($citiesFile) {
            // Prepare an SQL statement for inserting data
            $stmt = $conn->prepare("INSERT INTO cities (city_name, country_code) VALUES (:city_name, :country_code)");
    
            while (($line = fgetcsv($citiesFile, 0, "\t")) !== false) {
                // Extract necessary fields
                $cityName = $line[2]; // name
                $countryCode = $line[8]; // country code
                
                // Check if the country code is valid
                if (in_array($countryCode, $validCountryCodes)) {
                    // Bind parameters and execute the statement
                    $stmt->bindParam(':city_name', $cityName);
                    $stmt->bindParam(':country_code', $countryCode);
                    $stmt->execute();
                }
            }
            fclose($citiesFile);
            echo "cities insertion success<br>";

        // Create Jobs table
        $sql = "CREATE TABLE IF NOT EXISTS jobs (
            job_id INT AUTO_INCREMENT PRIMARY KEY,
            job_title VARCHAR(255) NOT NULL,
            requirements VARCHAR(1023),
            benefits VARCHAR(1023) NOT NULL,
            min_salary INT NOT NULL,
            max_salary INT NOT NULL,
            address_id INT,
            FOREIGN KEY (address_id) REFERENCES cities(address_id)
        )";
        $conn->exec($sql);
        echo "Table 'jobs' created successfully<br>";
    }
}
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
