<?php
$servername = "localhost";
$username = "root"; // Default XAMPP MySQL username
$password = ""; // Default XAMPP MySQL password is empty

// Connect to MySQL server
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dbName = 'job_platform_db';

// Function to check if the database exists
function databaseExists($dbName, $conn) {
    $result = $conn->query("SHOW DATABASES LIKE '$dbName'");
    return $result && $result->num_rows > 0;
}

$databaseExists = databaseExists($dbName, $conn);

$conn->close();

if (!$databaseExists): // Only show the form if the database does not exist
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup</title>
</head>
<body>
    <h1>Database Setup</h1>
    <form action="/job_platform/config/process_choice.php" method="post" style="margin: 10px;">
        <p>job_platform_db does not exist.</p>
        <p>Please see the User Guide in <b>README.md</b>. It is also accesible in the navigation bar.</p>
        <p>Select how you want to set up your database:</p>
        <input type="radio" id="run_scripts" name="setup_choice" value="run_scripts" required>
        <label for="run_scripts">Run PHP Scripts (It may take a while. The time limit is set to 300 seconds.)</label><br>
        <input type="radio" id="import_sql" name="setup_choice" value="import_sql">
        <label for="import_sql">Import SQL File Manually</label><br><br>
        <input type="submit" value="Submit" style="font-size: 16px;">
    </form>
</body>
</html>

<?php endif; ?>