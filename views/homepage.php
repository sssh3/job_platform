<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>
    <main>
        <h2>Home Page</h2>
        <p>This is the main content of the home page.</p>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>