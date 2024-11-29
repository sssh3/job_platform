<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    // 获取当前的用户简历 URL
    $stmt = $pdo->prepare("SELECT resume FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $jobseeker = $stmt->fetch(PDO::FETCH_ASSOC);

    // 上传简历
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {
        $resume = $_FILES['resume'];
        $upload_dir = __DIR__ . '/../uploads/resumes/'; // 上传目录
        $allowed_extensions = ['pdf', 'doc', 'docx']; // 允许的文件类型
        $file_extension = strtolower(pathinfo($resume['name'], PATHINFO_EXTENSION)); // 获取文件扩展名
        $new_file_name = uniqid('resume_') . '.' . $file_extension; // 生成唯一的文件名
        $upload_path = $upload_dir . $new_file_name;

        // 检查文件扩展名是否在允许的范围内
        if (in_array($file_extension, $allowed_extensions)) {
            // 检查文件上传错误
            if ($resume['error'] !== UPLOAD_ERR_OK) {
                echo "<script>alert('File upload error: " . $resume['error'] . "');</script>";
            } else {
                // 检查文件大小（这里设置为最大10MB）
                if ($resume['size'] > 10 * 1024 * 1024) {
                    echo "<script>alert('File size exceeds the maximum limit of 10MB.');</script>";
                } else {
                    // 尝试移动文件到指定目录
                    if (move_uploaded_file($resume['tmp_name'], $upload_path)) {

                        $search = 'htdocs';
                        $position = strpos($upload_path, $search);
                        if ($position !== false) {
                            // Add the length of 'htdocs' to get the position after it
                            $start = $position + strlen($search);
                            
                            // Extract the substring starting from the position after 'htdocs'
                            $pathAfter = substr($upload_path, $start);
                            echo $pathAfterHtdocs;
                        }

                        // 更新数据库中的 resume
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
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resume</title>
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
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Resume</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 上传简历表单 -->
    <form method="POST" enctype="multipart/form-data" action="edit_resume.php">
        <label for="resume">Upload Your Resume:</label>
        <input type="file" name="resume" required><br>

        <button type="submit">Upload Resume</button>
    </form>

    <?php if ($jobseeker['resume']): ?>
        <h3>Current Resume:</h3>
        <a href="<?php echo $jobseeker['resume']; ?>" target="_blank">View Current Resume</a>
    <?php endif; ?>
</div>

</body>
</html>
