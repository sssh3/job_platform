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

// 记录脚本开始执行时间
$startTime = microtime(true);  

// 获取当前用户的资料
$stmt = $pdo->prepare("SELECT * FROM users WHERE u_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $result = ['success' => false, 'message' => 'User not found.'];
} else {
    // 判断用户类型是否是 job-seeker（假设 'user_type_id' 为 3 或 0 代表求职者）
    if (!in_array($user['user_type_id'], [3, 0])) {
        $result = ['success' => false, 'message' => 'You are not a job seeker, and therefore cannot apply for jobs.'];
    } else {
        // 获取 jobSeekers 表中的简历信息
        $stmt = $pdo->prepare("SELECT * FROM jobSeekers WHERE u_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $jobSeeker = $stmt->fetch(PDO::FETCH_ASSOC);

        // 如果没有找到 jobSeeker 信息
        if (!$jobSeeker) {
            $result = ['success' => false, 'message' => 'You have not entered your personal information in the profile, and therefore cannot apply for jobs.'];
        } else {
            // 检查用户是否已上传简历
            if (empty($jobSeeker['resume'])) {
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
                        $stmt->execute(['user_id' => $userId, 'job_id' => $jobId, 'resume_url' => $jobSeeker['resume']]);

                        $result = ['success' => true, 'message' => 'You have successfully applied for this job. You can check your job application progress in your control panel.'];
                    } catch (PDOException $e) {
                        // 捕获触发器引发的错误信息
                        $errorMessage = $e->getMessage();
                        // 提取自定义错误信息
                        if (strpos($errorMessage, 'You have not uploaded your resume yet.') !== false) {
                            $result = ['success' => false, 'message' => 'You have not uploaded your resume yet. Please update your profile and upload your resume before applying for jobs.'];
                        } elseif (strpos($errorMessage, 'You are not a job seeker, and therefore cannot apply for jobs.') !== false) {
                            $result = ['success' => false, 'message' => 'You are not a job seeker, and therefore cannot apply for jobs.'];
                        } elseif (strpos($errorMessage, 'You have already applied for this job.') !== false) {
                            $result = ['success' => false, 'message' => 'You have already applied for this job.'];
                        } else {
                            // 如果是其他错误，显示其错误信息
                            $result = ['success' => false, 'message' => 'Error: ' . $errorMessage];
                        }
                    }
                } else {
                    $result = ['success' => false, 'message' => 'You have already applied for this job. You can check your job application progress in your control panel.'];
                }
            }
        }
    }
}

// 记录脚本结束时间
$endTime = microtime(true);  

// 计算总查询执行时间
$totalQueryTime = round($endTime - $startTime, 4);  // 保留四位小数
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

        <!-- 返回按钮 -->
        <a href="/job_platform/views/jobs.php?job_id=<?php echo htmlspecialchars($jobId); ?>" class="btn">Back to Job Details</a>

    </div>

    <footer>
        <p>&copy; 2024 Job Platform. All Rights Reserved.</p>
    </footer>
</body>
</html>
