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
                <span class="login-menu-item"> | <a id="user-link" href="/job_platform/">Profile</a> | </span>
                <span class="login-menu-item"><a id="user-link" href="/job_platform/">Control Panel</a> | </span>
                <span class="login-menu-item"><a id="user-link" href="/job_platform/utils/logout.php">Logout</a> | </span>
            </li>
            <?php else:?>
            <h1>Welcome to UIC Jobseeking Platform</h1>
            <li class="login-reminder" style="margin: 10px; display: inline-block; font-family: Times New Roman;">
            <span style="margin-right: 10px;font-family: Times New Roman;">You are not logged in. Please log in.</span>
            <span class="login-menu-item" style="font-family: Times New Roman;">
                 | <a id="user-link" href="/job_platform/login">Login/Register</a> | 
            </span>
        </li>
        <?php endif;?>
    </ul>
    

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
            <h2>1. User Registration/Login</h2>
            <p>Register or log in as a job seeker or a recruitment company. Quick registration supported via email or mobile.</p>
        </div>

        <div class="section" id="resume-management">
            <h2>2. Resume Management</h2>
            <p>Upload profile pictures, edit resumes, and manage work experience, education, and skills.</p>
        </div>


        <div class="section" id="job-matching">
            <h2>3. Job Search/Matching</h2>
            <p>Search for jobs by location or industry type. Receive recommendations for highly matched jobs.</p>
        </div>

        <div class="section" id="recruitment-management">
            <h2>4. Recruitment Company Management</h2>
            <p>Post job requirements, manage applicants, view resumes, and track application statuses.</p>
        </div>

        <div class="section" id="interview">
            <h2>5. Interview Arrangement</h2>
            <p>Coordinate interview times and communicate directly to ensure smooth recruitment processes.</p>
        </div>

        <div class="section" id="application-management">
            <h2>6. Job Application Management</h2>
            <p>Apply for jobs, check application statuses, receive interview notifications, and communicate with companies.</p>
        </div>

        <div class="section" id="analytics">
            <h2>7. Data Analysis/Feedback</h2>
            <p>Analyze resume submissions for companies and provide job seekers with feedback on interviews.</p>
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
            // Select a random message
            $randomMessage = $messages[array_rand($messages)];
            echo "For " . $type . "s: " . $randomMessage;
        } else {
            // Fallback message if file is empty or not found
            echo "Welcome to our site!";
        }
    ?></p>
</footer>

</html>
