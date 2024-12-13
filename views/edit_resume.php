<?php
session_start();


// If not logged in, redirect to the login page
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
    // Start measuring SQL execution time
    $start_time = microtime(true);
    // Get the current user's resume URL
    $stmt = $pdo->prepare("SELECT resume FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $jobseeker = $stmt->fetch(PDO::FETCH_ASSOC);

    // Resume upload
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {
        $resume = $_FILES['resume'];
        $upload_dir = __DIR__ . '/../uploads/resumes/'; // Upload directory
        $allowed_extensions = ['pdf', 'doc', 'docx']; // Allowed file types
        $file_extension = strtolower(pathinfo($resume['name'], PATHINFO_EXTENSION)); // Get file extension
        $new_file_name = uniqid('resume_') . '.' . $file_extension; // Generate a unique file name
        $upload_path = $upload_dir . $new_file_name;

        // Check if the file extension is within the allowed range
        if (in_array($file_extension, $allowed_extensions)) {
            // Check for file upload errors
            if ($resume['error'] !== UPLOAD_ERR_OK) {
                echo "<script>alert('File upload error: " . $resume['error'] . "');</script>";
            } else {
                // Check file size (set to a maximum of 10MB here)
                if ($resume['size'] > 10 * 1024 * 1024) {
                    echo "<script>alert('File size exceeds the maximum limit of 10MB.');</script>";
                } else {
                    // Try moving the file to the specified directory
                    if (move_uploaded_file($resume['tmp_name'], $upload_path)) {

                        $search = 'htdocs';
                        $position = strpos($upload_path, $search);
                        if ($position !== false) {
                            // Add the length of 'htdocs' to get the position after it
                            $start = $position + strlen($search);
                            
                            // Extract the substring starting from the position after 'htdocs'
                            $pathAfter = substr($upload_path, $start);
                            echo $pathAfter;
                        }

                        // Update the resume in the database
                        $stmt_update = $pdo->prepare("UPDATE jobseekers SET resume= :resume WHERE u_id = :u_id");
                        $stmt_update->bindParam(':resume', $pathAfter);
                        $stmt_update->bindParam(':u_id', $u_id);

                        if ($stmt_update->execute()) {
                            echo "<script>alert('Resume uploaded successfully!'); window.location.href='edit_resume.php';</script>";
                        } else {
                            echo "<script>alert('Failed to update database.');</script>";
                        }
                    } else {
                        echo "<script>alert('Failed to upload resume. Please try again.');</script>";
                    }
                }
            }
        } else {
            echo "<script>alert('Invalid file type. Only PDF, DOC, and DOCX are allowed.');</script>";
        }
    }
    // 记录结束时间并计算总时间
    $end_time = microtime(true);
    $sql_time = $end_time - $start_time;
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
    <title>Edit Resume</title>
    <style>
     /* Container styles */
.container {
    width: 80%;
    margin: 20px auto; /* Center align */
    text-align: center; /* Center the content */
    flex-grow: 1; /* Allow the container to occupy remaining space */
    position: relative; /* For positioning the back button */
    display: flex;
    flex-direction: column; /* Arrange content vertically */
    align-items: center; /* Center align horizontally */
}

/* Title styles */
h2 {
    color: #2c3e50;
}

/* Form styles */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 10px 0;
    text-align: center; /* Center the form content */
    display: flex; /* Make the form a flex container */
    flex-direction: column; /* Arrange form content vertically */
    justify-content: center; /* Center vertically */
    align-items: center; /* Center horizontally */
}

/* Label styles */
label {
    font-weight: bold;
    margin-top: 10px;
    text-align: center; /* Center the label */
}

/* File input styles */
input[type="file"] {
    margin-top: 5px;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

/* Button styles */
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

/* Back button styles */
.btn-back {
    margin-top: 20px;
    display: inline-block;
    padding: 10px 20px;
    background-color: #3498db; /* Unified color */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none; /* Remove link underline */
    position: absolute;
    top: 20px;
    left: 20px;
}

.btn-back:hover {
    background-color: #2980b9;
}

/* Profile image styles */
.profile-img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}

/* Delete button styles */
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
/* SQL 执行时间显示 */
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
    <h2>Edit Resume</h2>

    <!-- Back button -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- Resume upload form -->
    <form method="POST" enctype="multipart/form-data" action="edit_resume.php">
        <label for="resume">Upload Your Resume:</label>
        <input type="file" name="resume" required><br>

        <button type="submit">Upload Resume</button>
    </form>

    <?php if ($jobseeker['resume']): ?>
        <h3>Current Resume:</h3>
        <a href="<?php echo $jobseeker['resume']; ?>" target="_blank">View Current Resume</a>
    <?php endif; ?>
    <div class="sql-time">
        <p>SQL execution time: <?php echo number_format($sql_time, 6); ?> seconds.</p>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
