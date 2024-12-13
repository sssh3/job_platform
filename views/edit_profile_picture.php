<?php
session_start();

// If the user is not logged in, redirect to the login page
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

    // Track SQL execution time
    $start_time = microtime(true); // Start timing

    // Get the current user's avatar BLOB data
    $stmt = $pdo->prepare("SELECT avatar FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $jobseeker = $stmt->fetch(PDO::FETCH_ASSOC);

    // End SQL execution time and calculate duration
    $end_time = microtime(true);
    $sql_time = $end_time - $start_time;

    // Upload avatar
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
        $avatar = $_FILES['avatar'];

        // Check if the file upload was successful
        if ($avatar['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('File upload error: " . $avatar['error'] . "');</script>";
        } else {
            // Read the file content as binary data
            $avatar_data = file_get_contents($avatar['tmp_name']);

            // Start timing for update operation
            $start_time = microtime(true);

            // Update the avatar field in the database
            $stmt_update = $pdo->prepare("UPDATE jobseekers SET avatar = :avatar WHERE u_id = :u_id");
            $stmt_update->bindParam(':avatar', $avatar_data, PDO::PARAM_LOB);
            $stmt_update->bindParam(':u_id', $u_id);
            $stmt_update->execute();

            // End timing for update operation
            $end_time = microtime(true);
            $sql_time = $end_time - $start_time;

            echo "<script>alert('Profile picture uploaded successfully!'); window.location.href='edit_profile_picture.php';</script>";
        }
    }

    // Delete avatar
    if (isset($_POST['delete_avatar']) && $jobseeker && $jobseeker['avatar']) {
        // Start timing for delete operation
        $start_time = microtime(true);

        // Update the avatar field in the database to NULL
        $stmt_delete = $pdo->prepare("UPDATE jobseekers SET avatar = NULL WHERE u_id = :u_id");
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        // End timing for delete operation
        $end_time = microtime(true);
        $sql_time = $end_time - $start_time;

        echo "<script>alert('Avatar deleted successfully!'); window.location.href='edit_profile_picture.php';</script>";
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
    <title>Edit Profile Picture</title>
    <style>
        /* container style */
        .container {
            width: 80%;
            margin: 20px auto; /* Center align */
            text-align: center; /* Center align content */
            flex-grow: 1; /* Make container take up remaining space */
            position: relative; /* For positioning the back button in the top-left corner */
            display: flex;
            flex-direction: column; /* Arrange content vertically */
            align-items: center; /* Center align horizontally */
        }

        /* Title style */
        h2 {
            color: #2c3e50;
        }

        /* Form style */
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
            text-align: center; /* Center align form content */
            display: flex; /* Make form a flex container */
            flex-direction: column; /* Arrange form content vertically */
            justify-content: center; /* Center align content vertically */
            align-items: center; /* Center align content horizontally */
        }

        /* Label style */
        label {
            font-weight: bold;
            margin-top: 10px;
            text-align: center; /* Center align label */
        }

        /* File input style */
        input[type="file"] {
            margin-top: 5px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* Button style */
        button {
            background-color: #3498db;
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

        /* Back button style */
        .btn-back {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db; /* Uniform color */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none; /* Remove underline from link */
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .btn-back:hover {
            background-color: #2980b9;
        }

        /* Avatar display style */
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Delete button style */
        .btn-delete {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        /* SQL execution time display style */
        .sql-time {
            margin-top: 20px;
            font-size: 14px;
            
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
    <h2>Edit Profile Picture</h2>

    <!-- Back button -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- Upload avatar form -->
    <form method="POST" enctype="multipart/form-data" action="edit_profile_picture.php">
        <label for="avatar">Upload Your Avatar:</label>
        <input type="file" name="avatar" required><br>

        <button type="submit">Upload Avatar</button>
    </form>

    <!-- Display SQL execution time -->
    <div class="sql-time">
        <p>SQL execution time for fetching avatar: <?php echo number_format($sql_time, 6); ?> seconds.</p>
    </div>

    <?php if ($jobseeker['avatar']): ?>
        <h3>Current Avatar:</h3>
        <!-- Display and allow downloading of current avatar -->
        <img src="data:image/jpeg;base64,<?php echo base64_encode($jobseeker['avatar']); ?>" alt="Current Avatar" class="profile-img">
        
        <!-- Delete avatar button -->
        <form method="POST" action="edit_profile_picture.php">
            <button type="submit" name="delete_avatar" class="btn-delete">Delete Avatar</button>
        </form>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
