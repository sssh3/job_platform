<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$u_id = $_SESSION['user_id'];
$host = 'localhost';
$db = 'job_platform_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query existing certifications
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update certification information
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_certification'])) {
        $certification_id = $_POST['certification_id'];
        $certification_name = $_POST['certification_name'];
        $certification_date = $_POST['certification_date'];
        $issuing_organization = $_POST['issuing_organization'];

        $stmt_update = $pdo->prepare("UPDATE certifications SET certification_name = :certification_name, certification_date = :certification_date, issuing_organization = :issuing_organization WHERE certification_id = :certification_id AND u_id = :u_id");
        $stmt_update->bindParam(':certification_id', $certification_id);
        $stmt_update->bindParam(':certification_name', $certification_name);
        $stmt_update->bindParam(':certification_date', $certification_date);
        $stmt_update->bindParam(':issuing_organization', $issuing_organization);
        $stmt_update->bindParam(':u_id', $u_id);
        $stmt_update->execute();

        header('Location: edit_certifications.php');
        exit;
    }

    // Add new certification information
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_certification'])) {
        $certification_name = $_POST['certification_name'];
        $certification_date = $_POST['certification_date'];
        $issuing_organization = $_POST['issuing_organization'];

        $stmt_insert = $pdo->prepare("INSERT INTO certifications (u_id, certification_name, certification_date, issuing_organization) VALUES (:u_id, :certification_name, :certification_date, :issuing_organization)");
        $stmt_insert->bindParam(':u_id', $u_id);
        $stmt_insert->bindParam(':certification_name', $certification_name);
        $stmt_insert->bindParam(':certification_date', $certification_date);
        $stmt_insert->bindParam(':issuing_organization', $issuing_organization);
        $stmt_insert->execute();

        header('Location: edit_certifications.php');
        exit;
    }

    // Delete certification information
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_certification'])) {
        $certification_id = $_POST['certification_id'];

        $stmt_delete = $pdo->prepare("DELETE FROM certifications WHERE certification_id = :certification_id AND u_id = :u_id");
        $stmt_delete->bindParam(':certification_id', $certification_id);
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        header('Location: edit_certifications.php');
        exit;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/job_platform/assets/css/jobseekerStyle.css">
    <title>Edit Certifications</title>
    <style>
       
    h2 {
    text-align: center;
    color: #2c3e50;
    }

    form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 10px auto;
    width: 80%; /* Set width to 80% */
    max-width: 600px; /* Limit maximum width */
    }

    label {
    font-weight: bold;
    display: inline-block;
    margin-top: 10px;
    }

    input[type="text"], select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    }

    button {
    background-color: #3498db;  /* Set button color to blue */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 15px;
    }

    button:hover {
    background-color: #2980b9;
    }

    .activity-form {
    margin-bottom: 20px;
    }

    .container {
    width: 80%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center; /* Center content */
    justify-content: flex-start; /* Align content to the top */
    position: relative; /* Position child elements relative to this container */
    }

    .btn-back {
    position: absolute; /* Use absolute positioning */
    top: 20px; /* 20px from the top */
    left: 20px; /* 20px from the left */
    padding: 10px 20px;
    background-color: #3498db;  /* Set button color to blue */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none; /* Remove underline from link */
    }

    .btn-back:hover {
    background-color: #2980b9;
    }

    hr {
    border: 1px solid #ddd;
    margin: 20px 0;
    }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>

<div class="container">
    <h2>Edit Certifications</h2>

    <!-- Back button -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- Add new certification form -->
    <form method="POST" action="edit_certifications.php" class="activity-form">
        <h3>Add New Certification</h3>
        <label for="certification_name">Certification Name:</label>
        <input type="text" name="certification_name" required><br>

        <label for="certification_date">Certification Date:</label>
        <input type="date" name="certification_date" required><br>

        <label for="issuing_organization">Issuing Organization:</label>
        <input type="text" name="issuing_organization" required><br>

        <button type="submit" name="add_certification">Add Certification</button>
    </form>

    <h3>Your Certifications</h3>
    <!-- Display existing certifications, allowing editing and deletion -->
    <?php foreach ($certifications as $certification): ?>
        <form method="POST" action="edit_certifications.php" class="activity-form">
            <input type="hidden" name="certification_id" value="<?php echo $certification['certification_id']; ?>">
            
            <label for="certification_name">Certification Name:</label>
            <input type="text" name="certification_name" value="<?php echo $certification['certification_name']; ?>" required><br>

            <label for="certification_date">Certification Date:</label>
            <input type="date" name="certification_date" value="<?php echo $certification['certification_date']; ?>" required><br>

            <label for="issuing_organization">Issuing Organization:</label>
            <input type="text" name="issuing_organization" value="<?php echo $certification['issuing_organization']; ?>" required><br>

            <button type="submit" name="edit_certification">Update Certification</button>
            <button type="submit" name="delete_certification" onclick="return confirm('Are you sure you want to delete this certification?');">Delete Certification</button>
        </form>
        <hr>
    <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
