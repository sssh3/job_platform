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

    // 查询现有的实习经历
    $stmt = $pdo->prepare("SELECT * FROM internships WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 更新实习经历
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_internship'])) {
        $internship_id = $_POST['internship_id'];
        $company_name = $_POST['company_name'];
        $job_title = $_POST['job_title'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;  // 如果为空，设置为NULL
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;  // 如果为空，设置为NULL
        $job_description = $_POST['job_description'];

        // 检查日期格式
        if ($start_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date)) {
            die("Invalid start date format.");
        }
        if ($end_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
            die("Invalid end date format.");
        }

        $stmt_update = $pdo->prepare("UPDATE internships SET company_name = :company_name, job_title = :job_title, start_date = :start_date, end_date = :end_date, job_description = :job_description WHERE internship_id = :internship_id AND u_id = :u_id");
        $stmt_update->bindParam(':internship_id', $internship_id);
        $stmt_update->bindParam(':company_name', $company_name);
        $stmt_update->bindParam(':job_title', $job_title);
        $stmt_update->bindParam(':start_date', $start_date);
        $stmt_update->bindParam(':end_date', $end_date);
        $stmt_update->bindParam(':job_description', $job_description);
        $stmt_update->bindParam(':u_id', $u_id);
        $stmt_update->execute();

        header('Location: edit_internships.php');
        exit;
    }

    // 新增实习经历
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_internship'])) {
        $company_name = $_POST['company_name'];
        $job_title = $_POST['job_title'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;  // 如果为空，设置为NULL
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;  // 如果为空，设置为NULL
        $job_description = $_POST['job_description'];

        // 检查日期格式
        if ($start_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date)) {
            die("Invalid start date format.");
        }
        if ($end_date && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
            die("Invalid end date format.");
        }

        $stmt_insert = $pdo->prepare("INSERT INTO internships (u_id, company_name, job_title, start_date, end_date, job_description) VALUES (:u_id, :company_name, :job_title, :start_date, :end_date, :job_description)");
        $stmt_insert->bindParam(':u_id', $u_id);
        $stmt_insert->bindParam(':company_name', $company_name);
        $stmt_insert->bindParam(':job_title', $job_title);
        $stmt_insert->bindParam(':start_date', $start_date);
        $stmt_insert->bindParam(':end_date', $end_date);
        $stmt_insert->bindParam(':job_description', $job_description);
        $stmt_insert->execute();

        header('Location: edit_internships.php');
        exit;
    }

    // 删除实习经历
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_internship'])) {
        $internship_id = $_POST['internship_id'];

        $stmt_delete = $pdo->prepare("DELETE FROM internships WHERE internship_id = :internship_id AND u_id = :u_id");
        $stmt_delete->bindParam(':internship_id', $internship_id);
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        header('Location: edit_internships.php');
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
    <title>Edit Internships</title>
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
    <h2>Edit Internships</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 新增实习经历表单 -->
    <form method="POST" action="edit_internships.php" class="activity-form">
        <h3>Add New Internship</h3>
        <label for="company_name">Company Name:</label>
        <input type="text" name="company_name" required><br>

        <label for="job_title">Job Title:</label>
        <input type="text" name="job_title" required><br>

        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date"><br>

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date"><br>

        <label for="job_description">Job Description:</label>
        <textarea name="job_description" rows="4" required></textarea><br>

        <button type="submit" name="add_internship">Add Internship</button>
    </form>

    <h3>Your Internships</h3>
    <!-- 显示现有实习经历，允许编辑和删除 -->
    <?php foreach ($internships as $internship): ?>
        <form method="POST" action="edit_internships.php" class="activity-form">
            <input type="hidden" name="internship_id" value="<?php echo $internship['internship_id']; ?>">
            <label for="company_name">Company Name:</label>
            <input type="text" name="company_name" value="<?php echo $internship['company_name']; ?>" required><br>

            <label for="job_title">Job Title:</label>
            <input type="text" name="job_title" value="<?php echo $internship['job_title']; ?>" required><br>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" value="<?php echo $internship['start_date']; ?>"><br>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" value="<?php echo $internship['end_date']; ?>"><br>

            <label for="job_description">Job Description:</label>
            <textarea name="job_description" rows="4" required><?php echo $internship['job_description']; ?></textarea><br>

            <button type="submit" name="edit_internship">Update Internship</button>
            <button type="submit" name="delete_internship" onclick="return confirm('Are you sure you want to delete this internship?');">Delete Internship</button>
        </form>
        <hr>
    <?php endforeach; ?>
</div>

</body>
</html>
