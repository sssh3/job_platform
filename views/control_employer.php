<?php
session_start();
// Enable error reporting

// error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    $_SESSION['msg'] = "Please log in before using the control panel.";
    header("Location: /job_platform/login");
    exit();
} else if ($_SESSION['type'] != 'employer') {
    $_SESSION['msg'] = "Incorrect account type.";
    header("Location: /job_platform/login");
    exit();
}

// Database connection
$host = 'localhost';
$db = 'job_platform_db';  
$user = 'root'; 
$pass = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}

// Get current company ID from session
$companyId = $_SESSION['user_id'];  // Assuming the company ID is stored in session as user_id

// Get jobs posted by the company
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = :employer_id");
$stmt->execute(['employer_id' => $companyId]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all applications for these jobs
$stmt = $pdo->prepare("SELECT a.*, j.job_title, CONCAT(u.first_name, ' ', u.family_name) AS jobseeker_name FROM applications a 
                       JOIN jobs j ON a.job_id = j.job_id 
                       JOIN jobseekers u ON a.user_id = u.u_id 
                       WHERE j.employer_id = :employer_id");

$stmt->execute(['employer_id' => $companyId]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $applicationId = $_POST['application_id'];
        $action = $_POST['action'];

        // Update actions
        switch ($action) {
            case 'view_resume':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'resume_viewed' WHERE application_id = :application_id");
                break;
            case 'interview':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'interview' WHERE application_id = :application_id");
                break;
            case 'offer':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'hired' WHERE application_id = :application_id");
                break;
            case 'reject':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE application_id = :application_id");
                break;
        }
        
        // Execute the update query
        $stmt->execute(['application_id' => $applicationId]);
    }
}

// Status mapping
$statusMap = [
    'applied' => 'Applied',
    'resume_viewed' => 'Resume Viewed',
    'interview' => 'Interviewing',
    'offer' => 'Offer Given',
    'rejected' => 'Rejected',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Control Panel</title>
    <link rel="stylesheet" href="/job_platform/assets/css/control_panel_style.css">
    <style>
        .control-panel-container {
         padding: 20px;
        max-width: 900px;
        margin: 0 auto;
        
        align-items: center; /* Center content horizontally */
        justify-content: center; /* Center content vertically if needed */
        text-align: center; /* Center the text of the title */
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

        .status-buttons {
        margin-top: 10px;
        display: flex;
        flex-wrap: nowrap; /* 防止换行 */
        justify-content: space-between; /* 按钮之间的间距 */
        width: 100%; /* 确保父容器占满一行 */
    }

    .status-buttons button {
        padding: 8px 15px;
        background: linear-gradient(45deg, #ff6347, #ff4500);  
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;  
        flex: 1; /* 使每个按钮在容器中占据相等的空间 */
        margin: 0 5px; /* 添加按钮之间的左右间距 */
    }

    .status-buttons button:hover {
        background: linear-gradient(45deg, #ff4500, #ff6347);  
        transform: scale(1.1);  
    }

    .status-buttons button:active {
        transform: scale(0.95);  
    }
    </style>
</head>
<?php include 'header.php'; ?>
<body>
    <div class="control-panel-container">
        <h1>Company Control Panel</h1>

        <div class="applications-list">
            <?php foreach ($applications as $application) : ?>
                <div class="application-item">
                    <h2>Job Title: <?php echo htmlspecialchars($application['job_title']); ?></h2>
                    <p>Applicant: <?php echo htmlspecialchars($application['jobseeker_name']); ?></p>

                    <p>Status: <?php echo $statusMap[$application['status']]; ?></p>
                    <p>Resume: <a href="/job_platform/views/view_resume.php?user_id=<?php echo htmlspecialchars($application['user_id']); ?>" target="_blank">View Resume</a></p>

                    <div class="status-buttons">
                        <?php if ($application['status'] == 'applied') : ?>
                            <form method="POST" class="status-buttons">
                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                <button type="submit" name="action" value="view_resume">View Resume</button>
                                <button type="submit" name="action" value="interview">Interview</button>
                                <button type="submit" name="action" value="offer">Offer</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        <?php elseif ($application['status'] == 'resume_viewed') : ?>
                            <form method="POST" class="status-buttons">
                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                <button type="submit" name="action" value="interview">Interview</button>
                                <button type="submit" name="action" value="offer">Offer</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        <?php elseif ($application['status'] == 'interview') : ?>
                            <form method="POST" class="status-buttons">
                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                <button type="submit" name="action" value="offer">Offer</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>
