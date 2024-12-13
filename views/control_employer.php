<?php
session_start();

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

if (!isset($_SESSION['user_id'])) {
    $_SESSION['msg'] = "Please log in before using the control panel.";
    header("Location: /job_platform/login");
    exit();
} else if ($_SESSION['type'] != 'employer') {
    $_SESSION['msg'] = "Incorrect account type.";
    header("Location: /job_platform/login");
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

// Handle actions (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $applicationId = $_POST['application_id'];
    $action = $_POST['action'];

    $response = [
        'status' => 'error',
        'message' => 'Invalid action'
    ];

    // Check if the application exists
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE application_id = :application_id");
    $stmt->execute(['application_id' => $applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($application) {
        // Perform the action based on the request
        switch ($action) {
            case 'view_resume':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'resume_viewed' WHERE application_id = :application_id");
                $stmt->execute(['application_id' => $applicationId]);
                $response['status'] = 'success';
                $response['action'] = 'resume_viewed';
                break;
            case 'interview':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'interview' WHERE application_id = :application_id");
                $stmt->execute(['application_id' => $applicationId]);
                $response['status'] = 'success';
                $response['action'] = 'interview';
                break;
            case 'offer':
                    // 修改为状态更新为 'offer' 而非 'hired'
                    $stmt = $pdo->prepare("UPDATE applications SET status = 'offer' WHERE application_id = :application_id");
                    $stmt->execute(['application_id' => $applicationId]);
                    $response['status'] = 'success';
                    $response['action'] = 'offer';  // 确保返回的是 'offer'
                    break;
            case 'reject':
                $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE application_id = :application_id");
                $stmt->execute(['application_id' => $applicationId]);
                $response['status'] = 'success';
                $response['action'] = 'rejected';
                break;
            default:
                $response['message'] = 'Invalid action specified';
        }
    } else {
        $response['message'] = 'Application not found';
    }

    // Return the response as JSON
    echo json_encode($response);
    exit();
}

// Status mapping
$statusMap = [
    'applied' => 'Applied',
    'resume_viewed' => 'Resume Viewed',
    'interview' => 'Interviewed',
    'offer' => 'Offered',
    'rejected' => 'Rejected',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Control Panel</title>
    <link rel="stylesheet" href="/job_platform/assets/css/control_panel_style.css"> <!-- Ensure your CSS file is correctly linked -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
</head>
<?php include 'header.php'; ?>
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
    display: flex;  /* Use flexbox to align buttons in a row */
    justify-content: space-evenly;  /* Space out buttons evenly */
    gap: 10px;  /* Optional: Adds some space between buttons */
}

.status-buttons button {
    padding: 8px 15px;
    background: linear-gradient(45deg, #ff6347, #ff4500);  
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;  
    min-width: 200px;  /* Ensure buttons have a minimum width for consistency */
    text-align: center;  /* Ensures button text is centered */
}

.status-buttons button:hover {
    background: linear-gradient(45deg, #ff4500, #ff6347);  
    transform: scale(1.1);  
}

.status-buttons button:active {
    transform: scale(0.95);  
}

/* Adjust withdrawal button's border style */
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

/* Popup style */
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

/* Backdrop behind the popup */
.popup-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998;
}

/* Popup button */
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

/* Media Queries for responsiveness */
@media (max-width: 768px) {
    .control-panel-container {
        padding: 15px;
        max-width: 100%;
    }

    .application-item h2 {
        font-size: 1.1em;
    }

    .status-buttons button {
        min-width: 150px;
    }
}

@media (max-width: 480px) {
    .status-buttons {
        flex-direction: column;  /* Stack buttons vertically */
        align-items: center;
    }

    .status-buttons button {
        width: 100%;  /* Make buttons full-width on small screens */
    }
}
</style>
<body>



<div class="control-panel-container">
    <h1>Company Control Panel</h1>
    <div class="applications-list">
        <?php foreach ($applications as $application) : ?>
            <div class="application-item" id="application-<?php echo $application['application_id']; ?>">
                <h2>Job Title: <?php echo htmlspecialchars($application['job_title']); ?></h2>
                <p>Applicant: <?php echo htmlspecialchars($application['jobseeker_name']); ?></p>
                <p>Status: <span id="status-<?php echo $application['application_id']; ?>"><?php echo $statusMap[$application['status']]; ?></span></p>
                <p>Resume: <a href="/job_platform/views/view_resume.php?user_id=<?php echo htmlspecialchars($application['user_id']); ?>" target="_blank">View Resume</a></p>

                <div class="status-buttons">
                    <?php if ($application['status'] == 'applied') : ?>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="view_resume">View Resume</button>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="interview">Interview</button>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="offer">Offer</button>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="reject">Reject</button>
                    <?php elseif ($application['status'] == 'resume_viewed') : ?>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="interview">Interview</button>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="offer">Offer</button>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="reject">Reject</button>
                    <?php elseif ($application['status'] == 'interview') : ?>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="offer">Offer</button>
                        <button class="status-action" data-application-id="<?php echo $application['application_id']; ?>" data-action="reject">Reject</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


    <br>
    <ul class="jobs-management-list" id="jobs-management-list">
        <script src="/job_platform/assets/js/jobsManagement.js"></script>
    </ul>
    <div id="companyId" value=<?php echo $companyId;?>></div>
    <p id="filters-sql-time"></p>
    <p id="matches"></p>
</div>

<!-- Include Footer -->
<?php include 'footer.php'; ?>

<script src="/job_platform/assets/js/job_actions.js"></script>
</body>
</html>
