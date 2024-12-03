<?php
session_start();
// 开启错误报告
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    $_SESSION['msg'] = "Please log in before using the control panel.";
    header("Location: /job_platform/login");
    exit();
} else if ($_SESSION['type'] == 'visitor') {
    $_SESSION['msg'] = "Visitors cannot use the control panel. Please logout and switch to other accounts.";
    header("Location: /job_platform/login");
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

$userId = $_SESSION['user_id'];  // 获取当前用户的 ID
$showPopup = false;  // 控制是否显示弹窗

// 获取当前用户的投递申请信息
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取职位的 job_title 和 employer_id，以及公司名称 company_name
foreach ($applications as &$application) {
    // 获取 job_title 和 employer_id
    $jobStmt = $pdo->prepare("SELECT job_title, employer_id FROM jobs WHERE job_id = :job_id");
    $jobStmt->execute(['job_id' => $application['job_id']]);
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);

    // 获取公司名称 company_name
    $companyStmt = $pdo->prepare("SELECT company_name FROM companies WHERE u_id = :employer_id");
    $companyStmt->execute(['employer_id' => $job['employer_id']]);
    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);

    // 添加 job_title 和 company_name 到 application 数组，如果公司不存在，设置为 null
    $application['job_title'] = $job['job_title'];
    $application['company_name'] = $company ? $company['company_name'] : null;
}

// 处理撤回投递请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'withdraw') {
    $applicationId = $_POST['application_id'];

    // 删除对应的投递记录
    $stmt = $pdo->prepare("DELETE FROM applications WHERE application_id = :application_id AND user_id = :user_id");
    $stmt->execute(['application_id' => $applicationId, 'user_id' => $userId]);

    // 设置弹窗显示
    $showPopup = true;
}

// 状态映射，用于控制进度条的百分比
$statusMap = [
    'applied' => 10, // 投递
    'resume_viewed' => 25, // 简历被查看
    'interview' => 50, // 面试
    'offer' => 100, // 已录取
    'rejected' => 100, // 被拒绝
    'withdrawn' => 0, // 撤回
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobseeker Control Panel</title>
    <link rel="stylesheet" href="/job_platform/assets/css/control_panel_style.css">
    <style>
        .control-panel-container {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }

        .applications-list {
            margin-top: 30px;
        }

        .application-item {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: space-between;
            box-sizing: border-box;
            width: 100%;
        }

        .application-item h2 {
            margin: 0;
            font-size: 1.2em;
            color: #333;
        }

        .application-item p {
            margin: 10px 0;
            font-size: 1em;
            color: #666;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress {
            height: 100%;
            background-color: #4caf50;
            text-align: center;
            line-height: 20px;
            color: white;
            border-radius: 10px 0 0 10px;
            width: 0%;
            transition: width 1s ease-in-out;
        }

        .status-buttons {
            margin-top: 10px;
        }

        .status-buttons button {
            margin-right: 10px;
            padding: 8px 15px;
            background: linear-gradient(45deg, #ff6347, #ff4500);  
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;  
        }

        .status-buttons button:hover {
            background: linear-gradient(45deg, #ff4500, #ff6347);  
            transform: scale(1.1);  
        }

        .status-buttons button:active {
            transform: scale(0.95);  
        }

        /* 调整撤回按钮的边框样式 */
        .withdraw-button {
            padding: 5px 10px;
            border: 1px solid #ff4500;
            background-color: transparent;
            color: #ff4500;
            border-radius: 3px;
            font-size: 1em;
            transition: all 0.3s ease-in-out;
        }

        .withdraw-button:hover {
            background-color: #ff4500;
            color: white;
            transform: scale(1.1);
        }

        .withdraw-button:active {
            transform: scale(0.95);
        }

        /* 弹窗的样式 */
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            text-align: center;
            width: 300px;
        }

        .popup button {
            padding: 10px 20px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<?php include 'header.php'; ?>
<body>
    <div class="control-panel-container">
        <h1>Jobseeker Control Panel</h1>

        <div class="applications-list">
            <?php foreach ($applications as $application) : ?>
                <div class="application-item">
                    <h2><?php echo htmlspecialchars($application['job_title']); ?></h2>
                    <p>Company: <?php echo $application['company_name'] ? htmlspecialchars($application['company_name']) : 'N/A'; ?></p>
                    <p>Status: <?php echo ucfirst($application['status']); ?></p>
                    
                    <!-- 显示进度条 -->
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $statusMap[$application['status']] ?>%"></div>
                    </div>

                    <!-- 撤回投递按钮 -->
                    <?php if ($application['status'] !== 'withdrawn') : ?>
                        <form method="POST" class="status-buttons">
                            <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                            <button type="submit" name="action" value="withdraw" class="withdraw-button">Withdraw Application</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 弹窗 -->
    <?php if ($showPopup): ?>
        <div class="popup">
            <p>You have successfully withdrawn your application.</p>
            <button onclick="window.location.reload();">OK</button>
        </div>
    <?php endif; ?>
</body>

<?php include 'footer.php'; ?>
</html>
