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

    // 查询现有的认证信息
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 更新认证信息
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

    // 新增认证信息
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

    // 删除认证信息
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
    <title>Edit Certifications</title>
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
        input[type="text"], input[type="date"] {
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
    <h2>Edit Certifications</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 新增认证信息表单 -->
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
    <!-- 显示现有认证信息，允许编辑和删除 -->
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

</body>
</html>
