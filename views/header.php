<?php session_start(); ?>

<!DOCTYPE html>
<header>
    <!-- <h1>Welcome to Our Job-seeking Platform</h1> -->
    <nav>
        <!-- Logo wrapped in a link to the homepage -->
        <a href="/job_platform/">
            <img src="/job_platform/assets/images/logo.svg" alt="Logo" width="289" height="74">
        </a>
        <ul>
            <li><a href="/job_platform/">Home</a></li>
            <li><a href="/job_platform/jobs">Browse Jobs</a></li>
            <li><a href="/job_platform/readme">Readme</a></li>
            <li><a href="/job_platform/contact">Contact</a></li>
        </ul>

        <ul>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <p style="margin: 0px;">Welcome, 
                    <?php 
                        if (isset($_SESSION["type"])) {echo htmlspecialchars($_SESSION['type']);};
                        echo "<br>";
                        if (($_SESSION["type"] == 'job-seeker') || ($_SESSION["type"] == 'employer')){
                            echo htmlspecialchars($_SESSION["user_name"]);}
                    ?>
                </p>

                <!-- TODO -->
                <li><a href="/job_platform/">Profile</a></li>
                <li><a href="/job_platform/">Control Panel</a></li>
                <!-- TODO -->

                <li><a href="/job_platform/utils/logout.php">Logout</a></li>
            <?php else: ?>
                <p>You are not logged in</p>
                <li><a href="/job_platform/login">Login/Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>