
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
<header>


    <!-- <h1>Welcome to Our Job-seeking Platform</h1> -->
    <nav>
        <!-- Logo wrapped in a link to the homepage -->
        <a href="/job_platform/">
            <img src="/job_platform/assets\images\logo.png" alt="Logo" width="80" height="74">
            <img src="/job_platform/assets\images\logo1.png" alt="Logo" width="130" height="74">

        </a>

        <ul class="horizontal-menu">
        <li><a href="/job_platform/">Home</a ></li>
        <li><a href="/job_platform/jobs">Browse Jobs</a ></li>
        <li><a href="/job_platform/readme">Readme</a ></li>
        <li><a href="/job_platform/contact">Contact</a ></li>
        </ul>

        
    </nav>
        <ul>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true):?>
            <li style="margin: 10px; display: inline-block;font-family: 'Times New Roman', serif;font-size: 20px;">
                <span style="margin-right: 10px;">Welcome, 
                    <?php 
                        if (isset($_SESSION["type"])) {
                            echo htmlspecialchars($_SESSION['type']);
                        };
                        echo " ";  
                        if (($_SESSION["type"] == 'job-seeker') || ($_SESSION["type"] == 'employer')){
                            echo htmlspecialchars($_SESSION["username"]);
                        }
                ?>
                </span>
                <span style="display: inline;"> | <a id="user-link" href="/job_platform/views/<?php echo ($_SESSION['type'] == 'employer') ? 'company_profile.php' : 'jobseeker_profile.php'; ?>">Profile</a> | </span>
                <span style="display: inline;"><a id="user-link" href="/job_platform/">Control Panel</a> | </span>
                <span style="display: inline;"><a id="user-link" href="/job_platform/utils/logout.php">Logout</a> | </span>
            </li>
            
            <?php else:?>
            <li style="margin: 10px; display: inline-block;">
                <span style="display: inline;font-family: 'Times New Roman', serif;font-size: 20px;">
                    You are not logged in. Please log in. 
                     | <a id="user-link" href="/job_platform/login">Login/Register</a> | 
                </span>        
            </li>
        <?php endif;?>
    </ul>
</header>
