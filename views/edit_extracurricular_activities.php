<?php
session_start();

// 如果没有登录，跳转到登录页面
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

    // 查询现有的课外活动
    $stmt = $pdo->prepare("SELECT * FROM extracurricular_activities WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 更新课外活动
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_activity'])) {
        $activity_id = $_POST['activity_id'];
        $activity_name = $_POST['activity_name'];
        $position = $_POST['position'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;  // 如果为空，设置为NULL
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;  // 如果为空，设置为NULL
        $activity_description = $_POST['activity_description'];

        // 检查日期格式
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

    // 新增课外活动
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_activity'])) {
        $activity_name = $_POST['activity_name'];
        $position = $_POST['position'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;  // 如果为空，设置为NULL
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;  // 如果为空，设置为NULL
        $activity_description = $_POST['activity_description'];

        // 检查日期格式
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

    // 删除课外活动
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_activity'])) {
        $activity_id = $_POST['activity_id'];

        $stmt_delete = $pdo->prepare("DELETE FROM extracurricular_activities WHERE activity_id = :activity_id AND u_id = :u_id");
        $stmt_delete->bindParam(':activity_id', $activity_id);
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        header('Location: edit_extracurricular_activities.php');
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
    <title>Edit Extracurricular Activities</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 20px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
        }
        label {
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
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
        .activity-form {
            margin-bottom: 20px;
        }
        .activity-form input[type="date"] {
            width: auto;
            display: inline-block;
            margin-top: 0;
        }
        .activity-form textarea {
            height: 100px;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .btn-back {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-back:hover {
            background-color: #c0392b;
        }
        hr {
            border: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Extracurricular Activities</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 新增课外活动表单 -->
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
    <!-- 显示现有课外活动，允许编辑和删除 -->
    <?php foreach ($activities as $activity): ?>
        <form method="POST" action="edit_extracurricular_activities.php" class="activity-form">
            <input type="hidden" name="activity_id" value="<?php echo $activity['activity_id']; ?>">

            <label for="activity_name">Activity Name:</label>
            <input type="text" name="activity_name" value="<?php echo $activity['activity_name']; ?>" required><br>

            <label for="position">Position:</label>
            <input type="text" name="position" value="<?php echo $activity['position']; ?>" required><br>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" value="<?php echo $activity['start_date']; ?>"><br>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" value="<?php echo $activity['end_date']; ?>"><br>

            <label for="activity_description">Description:</label>
            <textarea name="activity_description" rows="4" required><?php echo $activity['activity_description']; ?></textarea><br>

            <button type="submit" name="edit_activity">Update Activity</button>
            <button type="submit" name="delete_activity" onclick="return confirm('Are you sure you want to delete this activity?');">Cancel (Delete)</button>
        </form>
        <hr>
    <?php endforeach; ?>
</div>

</body>
</html>
