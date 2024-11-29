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

    // 获取当前的用户头像 BLOB 数据
    $stmt = $pdo->prepare("SELECT avatar FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $jobseeker = $stmt->fetch(PDO::FETCH_ASSOC);

    // 上传头像
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
        $avatar = $_FILES['avatar'];

        // 检查文件上传是否成功
        if ($avatar['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('File upload error: " . $avatar['error'] . "');</script>";
        } else {
            // 读取文件内容为二进制数据
            $avatar_data = file_get_contents($avatar['tmp_name']);

            // 更新数据库中的 avatar 字段
            $stmt_update = $pdo->prepare("UPDATE jobseekers SET avatar = :avatar WHERE u_id = :u_id");
            $stmt_update->bindParam(':avatar', $avatar_data, PDO::PARAM_LOB);
            $stmt_update->bindParam(':u_id', $u_id);
            $stmt_update->execute();

            echo "<script>alert('Profile picture uploaded successfully!'); window.location.href='edit_profile_picture.php';</script>";
        }
    }

    // 删除头像
    if (isset($_POST['delete_avatar']) && $jobseeker && $jobseeker['avatar']) {
        // 更新数据库中的 avatar 字段为空
        $stmt_delete = $pdo->prepare("UPDATE jobseekers SET avatar = NULL WHERE u_id = :u_id");
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

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
    <title>Edit Profile Picture</title>
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
        input[type="file"] {
            margin-top: 5px;
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
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
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
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile Picture</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 上传头像表单 -->
    <form method="POST" enctype="multipart/form-data" action="edit_profile_picture.php">
        <label for="avatar">Upload Your Avatar:</label>
        <input type="file" name="avatar" required><br>

        <button type="submit">Upload Avatar</button>
    </form>

    <?php if ($jobseeker['avatar']): ?>
        <h3>Current Avatar:</h3>
        <!-- 提供下载并查看当前头像 -->
        <img src="data:image/jpeg;base64,<?php echo base64_encode($jobseeker['avatar']); ?>" alt="Current Avatar" class="profile-img">
        
        <!-- 删除头像按钮 -->
        <form method="POST" action="edit_profile_picture.php">
            <button type="submit" name="delete_avatar" class="btn-delete">Delete Avatar</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
