<?php session_start(); ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
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
            <input type="submit" name="action" value="Register">
            <br>
            <label for="button">OR:</label>
            <input type="button" value="Login as a visitor" onclick="window.location.href='utils/authenticate.php';">
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>

