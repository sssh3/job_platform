<?php 
session_start(); 
$parse = parse_url($_SERVER['REQUEST_URI']);
$path = $parse['path'];
if ($path !== '/job_platform/login') {
    // Store the current URL in the session
    $_SESSION['last_visited_url'] = $_SERVER['REQUEST_URI'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIC Recruitment Platform - Homepage</title>
 
    <link rel="icon" type="image/png" href="assets\images\logo2.png">
    <link rel="stylesheet" href="/job_platform/assets/css/homeStyle.css">
</head>
<body>
    <header>
        
        <ul>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true):?>
            <li style="margin: 10px; display: inline-block;front-family:Times New Roman;">
                <span style="margin-right: 10px;">Welcome, 
                    <?php 
                        if (isset($_SESSION["type"])) {
                            echo htmlspecialchars($_SESSION['type']);
                        };
                        echo " ";  
                        if (($_SESSION["type"] == 'job-seeker') || ($_SESSION["type"] == 'employer')){
                            echo htmlspecialchars($_SESSION["user_name"]);
                        }
                ?>
                </span>
                <span class="login-menu-item"> | <a id="user-link" href="/job_platform/views/<?php echo ($_SESSION['type'] == 'employer') ? 'company_profile.php' : 'jobseeker_profile.php'; ?>">Profile</a> | </span>
                <span class="login-menu-item"><a id="user-link" href="/job_platform/views/<?php echo ($_SESSION['type'] == 'employer') ? 'control_employer.php' : 'control_jobseeker.php'; ?>">Control Panel</a> | </span>
                <span class="login-menu-item"><a id="user-link" href="/job_platform/utils/logout.php">Logout</a> | </span>
            </li>
            <?php else:?>
            <h1>Welcome to UIC Jobseeking Platform</h1>
            <li class="login-reminder" style="margin: 10px; display: inline-block; font-family: Times New Roman;">
            <span style="margin-right: 10px;font-family: Times New Roman;">You are not logged in.</span>
            <span class="login-menu-item" style="font-family: Times New Roman;">
                 | <a id="user-link" href="/job_platform/login">Login/Register</a> | 
            </span>
        </li>
        <?php endif;?>
    </ul>

    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>
    
    </header>

    <nav>
        <a href="/job_platform/">Homepage</a>
        <a href="/job_platform/jobs">Browse Jobs</a>
        <a href="/job_platform/readme">Readme</a>
        <a href="/job_platform/contact">Contact</a>
    </nav>

    <div class="container">

        <div class="section" id="Title">
            <h2>The Funtions of Our Websites:</h2>
        </div>

        <div class="section" id="register-login">
           <a href="/job_platform/login"> 
                <h2>1. User Registration/Login</h2>
           </a>
            <p style="font - size: 26px; color: blue;" >Cover four user types: </p>
            <p>The registration and login procedures for job seekers or employers</p>
            <p>Administrator login (enabling direct modification and access to the database)</p>
            <p>Visitor login (where a visitor is restricted from sending messages or creating applications/job positions)</p>
        </div>

        <div class="section" id="profile-management">
            <h2>2. Profile Management</h2>
            <p style="font - size: 26px; color: blue;">|Click on the "Profile" button on the login menu after login|</p>
                <p>For Jobseekers: Upload a personal profile picture, edit and manage their personal resumes, which include basic information, certifications, language skills, internship experience, and extracurricular activities</p> 
                <p>For Employers: Upload company profiles, such as the company name, industry, location, company size, website, social media accounts, and a description of the company</p> 
        </div>


        <div class="section" id="job-browsing">
            <a href="/job_platform/jobs">
                <h2>3. Job Browsing</h2>
            </a>
            <p style="font - size: 26px; color: blue;">|Click on the "Browse Job" button on the navigation bar|</p>
            <p>Employers can post new jobs and the job will be displayed at browse job page</p>
            <p>Job seekers can filtering jobs according to location, job type, and job title and view basic information about jobs that meet the filter criteria and click on the basic </p>
            <p>information to access job details </p>
        </div>

        <div class="section" id="communication">
            <h2>4. Communication</h2>
            <p style="font - size: 26px; color: blue;">|Click on the "contact employer" button on the job details pages|</p>
            <p>Enables job seekers send a message to the employer</p>
        </div>

        <div class="section" id="application">
            <h2>5. Application</h2>
            <p style="font - size: 26px; color: blue;">|Enable participation from both job seekers and employers|</p>
            <p>Jobseeker: Allows job seekers to apply for suitable jobs and view the list of jobs they have applied for </p>
            <p>Employer: Display all the received resumes and provide buttons for viewing, interviewing, offering/rejecting at control panel</p>
            <p>The application status (Delivered → Resume is viewed → Interview → feedback) is displayed to both jobseeker and employer </p>
        </div>
    </div>

</body>
<footer>
    <p>2024 Database Management Systems Group Project</p>
    <p><?php
        // Read the file into an array of lines
        if (isset($_SESSION["type"]) && ($_SESSION["type"] == "job-seeker" || $_SESSION["type"] == "employer")){
                $type = $_SESSION["type"];
        } else {
            if (rand(0,1) == 1) {
                $type = 'job-seeker';
            } else {
                $type = 'employer';
            }
        }
        $messages = file(__DIR__ . '/../assets/text/tips_' . $type . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Check if we have any messages
        if ($messages) {
            // Select a random messages
            $randomMessage = $messages[array_rand($messages)];
            echo "For " . $type . "s: " . $randomMessage;
        } else {
            // Fallback message if file is empty or not found
            echo "Welcome to our site!";
        }
    ?></p>
</footer>

</html>
