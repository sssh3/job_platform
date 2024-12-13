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
    // Start measuring SQL execution time
    $start_time = microtime(true);
    // Query the existing extracurricular activities
    $stmt = $pdo->prepare("SELECT * FROM extracurricular_activities WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update extracurricular activity
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_activity'])) {
        $activity_id = $_POST['activity_id'];
        $activity_name = $_POST['activity_name'];
        $position = $_POST['position'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;  // If empty, set to NULL
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;  // If empty, set to NULL
        $activity_description = $_POST['activity_description'];

        // Check date format
        if ($start_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date)) {
            die("Invalid start date format.");
        }
        if ($end_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
            die("Invalid end date format.");
        }

        $stmt_update = $pdo->prepare("UPDATE extracurricular_activities SET activity_name = :activity_name, position = :position, start_date = :start_date, end_date = :end_date, activity_description = :activity_description WHERE activity_id = :activity_id AND u_id = :u_id");
        $stmt_update->bindParam(':activity_id', $activity_id);
        $stmt_update->bindParam(':activity_name', $activity_name);
        $stmt_update->bindParam(':position', $position);
        $stmt_update->bindParam(':start_date', $start_date);
        $stmt_update->bindParam(':end_date', $end_date);
        $stmt_update->bindParam(':activity_description', $activity_description);
        $stmt_update->bindParam(':u_id', $u_id);
        $stmt_update->execute();

        header('Location: edit_extracurricular_activities.php');
        exit;
    }

    // Add new extracurricular activity
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_activity'])) {
        $activity_name = $_POST['activity_name'];
        $position = $_POST['position'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;  // If empty, set to NULL
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;  // If empty, set to NULL
        $activity_description = $_POST['activity_description'];

        // Check date format
        if ($start_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date)) {
            die("Invalid start date format.");
        }
        if ($end_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
            die("Invalid end date format.");
        }

        $stmt_insert = $pdo->prepare("INSERT INTO extracurricular_activities (u_id, activity_name, position, start_date, end_date, activity_description) VALUES (:u_id, :activity_name, :position, :start_date, :end_date, :activity_description)");
        $stmt_insert->bindParam(':u_id', $u_id);
        $stmt_insert->bindParam(':activity_name', $activity_name);
        $stmt_insert->bindParam(':position', $position);
        $stmt_insert->bindParam(':start_date', $start_date);
        $stmt_insert->bindParam(':end_date', $end_date);
        $stmt_insert->bindParam(':activity_description', $activity_description);
        $stmt_insert->execute();

        header('Location: edit_extracurricular_activities.php');
        exit;
    }

    // Delete extracurricular activity
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_activity'])) {
        $activity_id = $_POST['activity_id'];

        $stmt_delete = $pdo->prepare("DELETE FROM extracurricular_activities WHERE activity_id = :activity_id AND u_id = :u_id");
        $stmt_delete->bindParam(':activity_id', $activity_id);
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        header('Location: edit_extracurricular_activities.php');
        exit;
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
    <title>Edit Extracurricular Activities</title>
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
       width: 80%; /* Set the width to 80% */
       max-width: 600px; /* Limit the maximum width */
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
       background-color: #3498db;  /* Standardize button color to blue */
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
       align-items: center; /* Center the content */
       justify-content: flex-start; /* Align to the top */
       position: relative; /* Position absolutely positioned elements relative to this container */
       }
   
       .btn-back {
       position: absolute; /* Use absolute positioning */
       top: 20px; /* 20px from the top */
       left: 20px; /* 20px from the left */
       padding: 10px 20px;
       background-color: #3498db;  /* Standardize button color to blue */
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
    <h2>Edit Extracurricular Activities</h2>

    <!-- Back button -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- Add new extracurricular activity form -->
    <form method="POST" action="edit_extracurricular_activities.php" class="activity-form">
        <h3>Add New Activity</h3>
        <label for="activity_name">Activity Name:</label>
        <input type="text" name="activity_name" required><br>

        <label for="position">Position:</label>
        <input type="text" name="position" required><br>

        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date"><br>

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date"><br>

        <label for="activity_description">Description:</label>
        <textarea name="activity_description" rows="4" required></textarea><br>

        <button type="submit" name="add_activity">Add Activity</button>
    </form>

    <h3>Your Extracurricular Activities</h3>
    <form method="POST" action="edit_extracurricular_activities.php" class="activity-form">
        <?php foreach ($activities as $activity): ?>
            <div class="activity-item">
                <h4><?= htmlspecialchars($activity['activity_name']); ?></h4>
                <p><strong>Position:</strong> <?= htmlspecialchars($activity['position']); ?></p>
                <p><strong>Start Date:</strong> <?= htmlspecialchars($activity['start_date']); ?></p>
                <p><strong>End Date:</strong> <?= htmlspecialchars($activity['end_date']); ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($activity['activity_description']); ?></p>
                <button type="submit" name="edit_activity" value="<?= $activity['activity_id']; ?>">Edit</button>
                <button type="submit" name="delete_activity" value="<?= $activity['activity_id']; ?>">Delete</button>
            </div>
            <hr>
        <?php endforeach; ?>
    </form>
    <div class="sql-time">
        <p>SQL execution time: <?php echo number_format($sql_time, 6); ?> seconds.</p>
    </div>
</div>

</body>
</html>
