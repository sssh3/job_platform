<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    $_SESSION['msg'] = "Please log in before applying for a job.";
    header("Location: /job_platform/login");
    exit();
}

$userId = $_SESSION['user_id'];  // 获取当前用户的 ID

// 获取 URL 中的 job_id 参数
$jobId = isset($_GET['job_id']) ? $_GET['job_id'] : null;
if (!$jobId) {
    echo "Job ID is missing!";
    exit();
}

// 连接数据库
$host = 'localhost';
$db = 'job_platform_db';  
$user = 'root'; 
$pass = '';  

try {
    // 创建 PDO 实例
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // 设置错误模式为异常
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage();
    exit();
}

// 获取当前用户的资料
$stmt = $pdo->prepare("SELECT * FROM jobSeekers WHERE u_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$result = [];

if (!$user) {
    $result = ['success' => false, 'message' => 'You are not a jobseeker, and therefore cannot apply for jobs.'];
} else {
    // 检查用户是否已上传简历
    if (empty($user['resume'])) {
        $result = ['success' => false, 'message' => 'You have not uploaded your resume yet. Please update your profile and upload your resume before applying for jobs.'];
    } else {
        // 检查用户是否已申请该职位
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE job_id = :job_id AND user_id = :user_id");
        $stmt->execute(['job_id' => $jobId, 'user_id' => $userId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            // 如果没有申请过该职位，则插入新记录，并将状态设置为 'applied'
            $query = "INSERT INTO applications (user_id, job_id, resume_url, status) VALUES (:user_id, :job_id, :resume_url, 'applied')";
            try {
                $stmt = $pdo->prepare($query);
                // 插入申请记录，并将简历 URL 从 jobSeekers 表传入
                $stmt->execute(['user_id' => $userId, 'job_id' => $jobId, 'resume_url' => $user['resume']]);

                $result = ['success' => true, 'message' => 'You have successfully applied for this job. You can check your job application progress in your control panel.'];
            } catch (PDOException $e) {
                $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        } else {
            $result = ['success' => false, 'message' => 'You have already applied for this job. You can check your job application progress in your control panel.'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply Job</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
    <style>
        /* 应用容器的样式 */
        .apply-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 80%;
            max-width: 600px;
            position: absolute;      /* 使用绝对定位 */
            top: 50%;                /* 垂直居中 */
            left: 50%;               /* 水平居中 */
            transform: translate(-50%, -50%); /* 通过平移来精确居中 */
        }

        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1rem;
            margin-top: 20px;
            white-space: pre-wrap;   /* 保留换行符，避免文字溢出 */
        }

        /* 成功或失败消息 */
        .message-success {
            color: #28a745; /* 成功绿色 */
            font-weight: bold;
        }

        .message-error {
            color: #dc3545; /* 错误红色 */
            font-weight: bold;
        }

        /* 按钮样式 */
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Footer样式 */
        footer {
            background-color: #f8f9fa;
            width: 100%;
            padding: 10px;
            position: absolute;
            bottom: 0;
            text-align: center;
            color: #333;
        }
    </style>
</head>

<?php include 'header.php'; ?>

<body>
    <div class="apply-container">
        <h1>Job Application Status</h1>
        <p class="<?php echo $result['success'] ? 'message-success' : 'message-error'; ?>">
            <?php
                // 将消息断为两行
                $message = htmlspecialchars($result['message']);
                $messageLines = explode(".", $message);
                echo implode(".<br>", $messageLines); // 添加换行符
            ?>
        </p>

        <?php if ($result['success']) : ?>
            <a href="/job_platform/views/jobs.php" class="btn">Go back to Job Details</a>
            <a href="/job_platform/views/control_jobseeker.php" class="btn">Go to control panel to check apply process</a>
        <?php else : ?>
            <a href="/job_platform/views/jobs.php" class="btn">Go back to Job List</a>
        <?php endif; ?>

        <?php
        if (isset($_SESSION["msg"])) {
            $msg = $_SESSION["msg"];
            unset($_SESSION["msg"]);
            echo "<p>" . htmlspecialchars($msg) . "</p>";
        } 
        ?>
    </div>

    <footer>
        <p>Job Platform &copy; 2024</p>
    </footer>
</body>

<?php include 'footer.php'; ?>

</html>
