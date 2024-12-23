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

    // 记录开始时间
    $start_time = microtime(true);

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
        $GPA=$_POST['GPA'];

        // 如果用户已有基本信息，则更新
        if ($user_info) {
            $stmt_update = $pdo->prepare("UPDATE jobseekers SET 
                first_name = :first_name, 
                family_name = :family_name, 
                email = :email, 
                phone = :phone, 
                short_intro = :short_intro,
                GPA=:GPA
                WHERE u_id = :u_id");
            $stmt_update->bindParam(':u_id', $u_id);
            $stmt_update->bindParam(':first_name', $first_name);
            $stmt_update->bindParam(':family_name', $family_name);
            $stmt_update->bindParam(':email', $email);
            $stmt_update->bindParam(':phone', $phone);
            $stmt_update->bindParam(':short_intro', $short_intro);
            $stmt_update->bindParam(':GPA', $GPA);
            $stmt_update->execute();
        } else {
            // 如果没有记录，插入新的基本信息
            $stmt_insert = $pdo->prepare("INSERT INTO jobseekers 
                (u_id, first_name, family_name, email, GPA, phone, short_intro) 
                VALUES 
                (:u_id, :first_name, :family_name, :email, :GPA, :phone, :short_intro)");
            $stmt_insert->bindParam(':u_id', $u_id);
            $stmt_insert->bindParam(':first_name', $first_name);
            $stmt_insert->bindParam(':family_name', $family_name);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':GPA', $GPA);
            $stmt_insert->bindParam(':phone', $phone);
            $stmt_insert->bindParam(':short_intro', $short_intro);
            $stmt_insert->execute();
        }

        // 更新成功，跳转回页面
        echo "<script>alert('Basic Information updated successfully!'); window.location.href='edit_basic_info.php';</script>";
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
    <title>Edit Basic Information</title>
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
            margin: 10px 0;
            width: 80%; /* 确保宽度为80% */
            max-width: 600px; /* 最大宽度限制 */
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
            border-radius: 4px;
        }

        button {
            background-color: #3498db;  /* 统一按钮颜色为蓝色 */
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
            display: flex;
            flex-direction: column;
            align-items: center; /* 居中对齐内容 */
            justify-content: center; /* 垂直居中内容 */
            position: relative; /* 为了定位按钮 */
        }

        .container .btn-back {
            position: absolute; /* 绝对定位 */
            top: 10px; /* 距离容器顶部10px */
            left: 10px; /* 距离容器左边10px */
            padding: 10px 20px;
            background-color: #3498db;  /* 与其他按钮统一颜色 */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none; /* 去掉链接下划线 */
        }

        .container .btn-back:hover {
            background-color: #2980b9;  /* 按钮悬停效果 */
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
    <h2>Edit Basic Information</h2>

    <!-- back botton -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- edit basic information -->
    <form method="POST" action="edit_basic_info.php">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="<?php echo isset($user_info['first_name']) ? $user_info['first_name'] : ''; ?>" required><br>

        <label for="family_name">Family Name:</label>
        <input type="text" name="family_name" value="<?php echo isset($user_info['family_name']) ? $user_info['family_name'] : ''; ?>" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo isset($user_info['email']) ? $user_info['email'] : ''; ?>" required><br>
        
        <label for="GPA">GPA:</label>
        <input type="text" name="GPA" value="<?php echo isset($user_info['GPA']) ? $user_info['GPA'] : ''; ?>" required><br>
        
        <label for="phone">Phone:</label>
        <input type="tel" name="phone" value="<?php echo isset($user_info['phone']) ? $user_info['phone'] : ''; ?>" required><br>

        <label for="short_intro">Short Introduction:</label>
        <textarea name="short_intro" rows="4"><?php echo isset($user_info['short_intro']) ? $user_info['short_intro'] : ''; ?></textarea><br>

        <button type="submit" name="update_basic_info">Update Information</button>
    </form>

    <!-- 显示 SQL 执行时间 -->
    <div class="sql-time">
        <p>SQL execution time: <?php echo number_format($sql_time, 6); ?> seconds.</p>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
