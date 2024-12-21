<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /job_platform/login');
    exit;
}

$u_id = $_GET['user_id'];

$host = 'localhost';
$db = 'job_platform_db';  
$user = 'root';  
$pass = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Track SQL execution time
    $start_time = microtime(true); // Start timing

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
    <title>Jobseeker Resume</title>
</head>
<style>
/* SQL 执行时间显示 */
.sql-time {
            margin-top: 20px;
            font-size: 14px;
            
        }
    </style>
<body>
    <?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>

    <div class="container">
        <h2>Jobseeker Resume</h2>

        <!-- Profile Picture Section -->
        <div class="profile-section profile-img">
            <h3>Profile Picture</h3>
            <?php if ($jobseeker['avatar']) : ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($jobseeker['avatar']); ?>" alt="Profile Picture">
            <?php else : ?>
                <p>No profile picture uploaded.</p>
            <?php endif; ?>
        </div>

        <!-- Basic Info Section -->
        <div class="profile-section">
            <h3>Basic Information</h3>
            <?php if ($jobseeker): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($jobseeker['first_name'] . " " . $jobseeker['family_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($jobseeker['email']); ?></p>
                <p><strong>GPA:</strong> <?php echo htmlspecialchars($jobseeker['GPA']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($jobseeker['phone']); ?></p>
                <p><strong>Short Intro:</strong> <?php echo htmlspecialchars($jobseeker['short_intro']); ?></p>
            <?php else: ?>
                <p>No jobseeker found with the specified ID.</p>
            <?php endif; ?>
        </div>

        <!-- Certifications Section -->
        <div class="profile-section">
            <h3>Certifications</h3>
            <ul>
                <?php foreach ($certifications as $cert) : ?>
                    <li><?php echo $cert['certification_name']; ?> (Issued by: <?php echo $cert['issuing_organization']; ?>, Date: <?php echo $cert['certification_date']; ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Language Skills Section -->
        <div class="profile-section">
            <h3>Language Skills</h3>
            <ul>
                <?php foreach ($language_skills as $skill) : ?>
                    <li><?php echo $skill['language_name']; ?> - <?php echo $skill['proficiency_level']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Internships Section -->
        <div class="profile-section">
            <h3>Internships</h3>
            <ul>
                <?php foreach ($internships as $intern) : ?>
                    <li><strong>Company:</strong> <?php echo $intern['company_name']; ?>, <strong>Position:</strong> <?php echo $intern['job_title']; ?>, <strong>Duration:</strong> <?php echo $intern['start_date'] . ' to ' . $intern['end_date']; ?>,<strong>Description:</strong> <?php echo $intern['job_description']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Extracurricular Activities Section -->
        <div class="profile-section">
            <h3>Extracurricular Activities</h3>
            <ul>
                <?php foreach ($activities as $activity) : ?>
                    <li><strong>Activity:</strong> <?php echo $activity['activity_name']; ?>, <strong>Position:</strong> <?php echo $activity['position']; ?>, <strong>Duration:</strong> <?php echo $activity['start_date'] . ' to ' . $activity['end_date']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Resume Section -->
        <div class="profile-section">
            <h3>Resume</h3>
            <?php if ($jobseeker['resume']) : ?>
                <p>Current Resume: 
                    <a href="<?php echo htmlspecialchars($jobseeker['resume']); ?>" target="_blank">Download</a>
                </p>
            <?php else : ?>
                <p>No resume uploaded.</p>
            <?php endif; ?>
        </div>
        <div class="sql-time">
        <p>SQL execution time: <?php echo number_format($sql_time, 6); ?> seconds.</p>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
