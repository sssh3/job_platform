<?php
session_start();

// 如果没有登录，跳转到登录页面
if (!isset($_SESSION['user_id'])) {
    header('Location: /job_platform/login');
    exit;
}

// 获取用户ID
$u_id = $_SESSION['user_id'];

// 连接数据库
$host = 'localhost';
$db = 'job_platform_db';  // 请根据你的数据库名称调整
$user = 'root';  // 你的数据库用户名
$pass = '';  // 你的数据库密码

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 查询求职者信息
    $stmt = $pdo->prepare("SELECT * FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $jobseeker = $stmt->fetch(PDO::FETCH_ASSOC);

    // 查询证书
    $stmt_cert = $pdo->prepare("SELECT * FROM certifications WHERE u_id = :u_id");
    $stmt_cert->bindParam(':u_id', $u_id);
    $stmt_cert->execute();
    $certifications = $stmt_cert->fetchAll(PDO::FETCH_ASSOC);

    // 查询语言能力
    $stmt_lang = $pdo->prepare("SELECT * FROM language_skills WHERE u_id = :u_id");
    $stmt_lang->bindParam(':u_id', $u_id);
    $stmt_lang->execute();
    $language_skills = $stmt_lang->fetchAll(PDO::FETCH_ASSOC);

    // 查询实习经历
    $stmt_intern = $pdo->prepare("SELECT * FROM internships WHERE u_id = :u_id");
    $stmt_intern->bindParam(':u_id', $u_id);
    $stmt_intern->execute();
    $internships = $stmt_intern->fetchAll(PDO::FETCH_ASSOC);

    // 查询社团活动
    $stmt_activity = $pdo->prepare("SELECT * FROM extracurricular_activities WHERE u_id = :u_id");
    $stmt_activity->bindParam(':u_id', $u_id);
    $stmt_activity->execute();
    $activities = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobseeker Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .navbar a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
        }
        .navbar a:hover {
            background-color: #ddd;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            text-align: center;
            color: #333;
        }
        .profile-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .profile-section h3 {
            color: #333;
        }
        .profile-section ul {
            list-style-type: none;
            padding: 0;
        }
        .profile-section ul li {
            padding: 5px 0;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }
        .edit-btn:hover {
            background-color: #45a049;
        }
        .profile-img {
            text-align: center;
        }
        .profile-img img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
        }
        
    </style>
</head>





<body>
<
    <div class="navbar">
        <a href="homepage.php">Home</a>
        
    </div>

    <div class="container">
        <h2>Jobseeker Profile</h2>

        <!-- Profile Picture Section -->
        <div class="profile-section profile-img">
            <h3>Profile Picture</h3>
            <?php if ($jobseeker['avatar']) : ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($jobseeker['avatar']); ?>" alt="Profile Picture">
            <?php else : ?>
                <p>No profile picture uploaded.</p>
            <?php endif; ?>
            <a href="edit_profile_picture.php" class="edit-btn">Edit</a>
        </div>


        <!-- Basic Info Section -->
        <div class="profile-section">
            <h3>Basic Information</h3>
            <p><strong>Name:</strong> <?php echo $jobseeker['first_name'] . " " . $jobseeker['family_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $jobseeker['email']; ?></p>
            <p><strong>Phone:</strong> <?php echo $jobseeker['phone']; ?></p>
            <p><strong>Short Intro:</strong> <?php echo $jobseeker['short_intro']; ?></p>
            <a href="edit_profile/edit_basic_info.php" class="edit-btn">Edit</a>
        </div>

        <!-- Certifications Section -->
        <div class="profile-section">
            <h3>Certifications</h3>
            <ul>
                <?php foreach ($certifications as $cert) : ?>
                    <li><?php echo $cert['certification_name']; ?> (Issued by: <?php echo $cert['issuing_organization']; ?>, Date: <?php echo $cert['certification_date']; ?>)</li>
                <?php endforeach; ?>
            </ul>
            <a href="edit_certifications.php" class="edit-btn">Edit</a>
        </div>

        <!-- Language Skills Section -->
        <div class="profile-section">
            <h3>Language Skills</h3>
            <ul>
                <?php foreach ($language_skills as $skill) : ?>
                    <li><?php echo $skill['language_name']; ?> - <?php echo $skill['proficiency_level']; ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="edit_language_skills.php" class="edit-btn">Edit</a>
        </div>

        <!-- Internships Section -->
        <div class="profile-section">
            <h3>Internships</h3>
            <ul>
                <?php foreach ($internships as $intern) : ?>
                    <li><strong>Company:</strong> <?php echo $intern['company_name']; ?>, <strong>Position:</strong> <?php echo $intern['job_title']; ?>, <strong>Duration:</strong> <?php echo $intern['start_date'] . ' to ' . $intern['end_date']; ?>,<strong>Description:</strong> <?php echo $intern['job_description']; ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="edit_internships.php" class="edit-btn">Edit</a>
        </div>

        <!-- Extracurricular Activities Section -->
        <div class="profile-section">
            <h3>Extracurricular Activities</h3>
            <ul>
                <?php foreach ($activities as $activity) : ?>
                    <li><strong>Activity:</strong> <?php echo $activity['activity_name']; ?>, <strong>Position:</strong> <?php echo $activity['position']; ?>, <strong>Duration:</strong> <?php echo $activity['start_date'] . ' to ' . $activity['end_date']; ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="edit_extracurricular_activities.php" class="edit-btn">Edit</a>
        </div>

        <!-- Resume Section -->
        <div class="profile-section">
            <h3>Resume</h3>
            <?php if ($resume_data && $resume_data['resume']) : ?>
                <p>Current Resume: 
                    <a href="data:application/pdf;base64,<?php echo base64_encode($resume_data['resume']); ?>" target="_blank">Download</a>
                </p>
            <?php else : ?>
                <p>No resume uploaded.</p>
            <?php endif; ?>
            <a href="edit_resume.php" class="edit-btn">Edit</a>
        </div>


    



    <?php include 'footer.php'; ?>
</body>
</html>