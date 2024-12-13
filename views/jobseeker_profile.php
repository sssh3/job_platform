<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /job_platform/login');
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

    // 记录脚本开始时间
    $startTime = microtime(true);

    // 执行 SQL 查询
    $stmt = $pdo->prepare("SELECT * FROM jobseekers WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $jobseeker = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_cert = $pdo->prepare("SELECT * FROM certifications WHERE u_id = :u_id");
    $stmt_cert->bindParam(':u_id', $u_id);
    $stmt_cert->execute();
    $certifications = $stmt_cert->fetchAll(PDO::FETCH_ASSOC);

    $stmt_lang = $pdo->prepare("SELECT * FROM language_skills WHERE u_id = :u_id");
    $stmt_lang->bindParam(':u_id', $u_id);
    $stmt_lang->execute();
    $language_skills = $stmt_lang->fetchAll(PDO::FETCH_ASSOC);

    $stmt_intern = $pdo->prepare("SELECT * FROM internships WHERE u_id = :u_id");
    $stmt_intern->bindParam(':u_id', $u_id);
    $stmt_intern->execute();
    $internships = $stmt_intern->fetchAll(PDO::FETCH_ASSOC);

    $stmt_activity = $pdo->prepare("SELECT * FROM extracurricular_activities WHERE u_id = :u_id");
    $stmt_activity->bindParam(':u_id', $u_id);
    $stmt_activity->execute();
    $activities = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

    // 记录脚本结束时间
    $endTime = microtime(true);
    
    // 计算总查询执行时间
    $totalQueryTime = round($endTime - $startTime, 4);  // 保留四位小数
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
    <title>Jobseeker Profile</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>

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
            <p><strong>GPA:</strong> <?php echo $jobseeker['GPA']; ?></p>
            <p><strong>Phone:</strong> <?php echo $jobseeker['phone']; ?></p>
            <p><strong>Short Intro:</strong> <?php echo $jobseeker['short_intro']; ?></p>
            <a href="edit_basic_info.php" class="edit-btn">Edit</a>
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
            <?php if ($jobseeker['resume']) : ?>
                <p>Current Resume: 
                    
                    <a href="<?php echo $jobseeker['resume']; ?>" target="_blank">Download</a>
                </p>
            <?php else : ?>
                <p>No resume uploaded.</p>
            <?php endif; ?>
            <a href="edit_resume.php" class="edit-btn">Edit</a>
        </div>

        <!-- Display SQL Query Time -->
        <div class="query-time">
            <p>Total SQL Query Time: <?php echo $totalQueryTime; ?> seconds</p>
        </div>

    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
