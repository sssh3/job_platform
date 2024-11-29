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

    // 获取当前的用户基本信息
    $stmt = $pdo->prepare("SELECT * FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // 上传和更新基本信息
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_basic_info'])) {
        $first_name = $_POST['first_name'];
        $family_name = $_POST['family_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $short_intro = $_POST['short_intro'];

        // 如果用户已有基本信息，则更新
        if ($user_info) {
            $stmt_update = $pdo->prepare("UPDATE jobseekers SET 
                first_name = :first_name, 
                family_name = :family_name, 
                email = :email, 
                phone = :phone, 
                short_intro = :short_intro
                WHERE u_id = :u_id");
            $stmt_update->bindParam(':u_id', $u_id);
            $stmt_update->bindParam(':first_name', $first_name);
            $stmt_update->bindParam(':family_name', $family_name);
            $stmt_update->bindParam(':email', $email);
            $stmt_update->bindParam(':phone', $phone);
            $stmt_update->bindParam(':short_intro', $short_intro);
            $stmt_update->execute();
        } else {
            // 如果没有记录，插入新的基本信息
            $stmt_insert = $pdo->prepare("INSERT INTO jobseekers 
                (u_id, first_name, family_name, email, phone, short_intro) 
                VALUES 
                (:u_id, :first_name, :family_name, :email, :phone, :short_intro)");
            $stmt_insert->bindParam(':u_id', $u_id);
            $stmt_insert->bindParam(':first_name', $first_name);
            $stmt_insert->bindParam(':family_name', $family_name);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':phone', $phone);
            $stmt_insert->bindParam(':short_intro', $short_intro);
            $stmt_insert->execute();
        }

        // 更新成功，跳转回页面
        echo "<script>alert('Basic Information updated successfully!'); window.location.href='edit_basic_info.php';</script>";
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
    <title>Edit Basic Information</title>
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
        input[type="text"], input[type="email"], input[type="tel"], textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
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
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Basic Information</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 编辑基本信息表单 -->
    <form method="POST" action="edit_basic_info.php">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="<?php echo isset($user_info['first_name']) ? $user_info['first_name'] : ''; ?>" required><br>

        <label for="family_name">Family Name:</label>
        <input type="text" name="family_name" value="<?php echo isset($user_info['family_name']) ? $user_info['family_name'] : ''; ?>" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo isset($user_info['email']) ? $user_info['email'] : ''; ?>" required><br>

        <label for="phone">Phone:</label>
        <input type="tel" name="phone" value="<?php echo isset($user_info['phone']) ? $user_info['phone'] : ''; ?>" required><br>

        <label for="short_intro">Short Introduction:</label>
        <textarea name="short_intro" rows="4"><?php echo isset($user_info['short_intro']) ? $user_info['short_intro'] : ''; ?></textarea><br>

        <button type="submit" name="update_basic_info">Update Information</button>
    </form>
</div>

</body>
</html>
