<?php session_start(); ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
    <script>
        function allowRegister() {
            var typeSelection = document.getElementById('usertype');
            var submitButton = document.getElementById('register');
            // Check if a valid option is selected
            if (typeSelection.value !== '') {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }
    </script>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>
    <div class="center-container">
        <form action="/job_platform/utils/authenticate.php" method="post">
            <label for="username">User name:</label>
            <input type="text" name="username" id="username" class="responsive-input" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="responsive-input" required>
            <br>
            <input type="submit" name="action" value="Login">
            <select name="usertype" id="usertype" onchange="allowRegister()">
                <option value="">select type for register</option>
                <option value="job-seeker">job-seeker</option>
                <option value="employer">employer</option>
            </select>
            <input type="submit" name="action" id="register" value="Register" disabled>
            <br>
            <label for="button">OR:</label>
            <input type="button" value="Login as a visitor" onclick="window.location.href='utils/authenticate.php';">
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
