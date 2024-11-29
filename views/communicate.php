<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
    <link rel="stylesheet" href="/job_platform/assets/css/chatStyle.css">
</head>

    <?php include 'header.php'; ?>
    
    <?php
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['msg'] = "Please log in before sending a message.";
        header("Location: /job_platform/login");
        exit();
    } else {
        $this_id = $_SESSION['user_id'];
        echo '<span id="thisId" style="visibility: hidden;">' . htmlspecialchars($this_id) . '</span>';
    }
    
    ?>


<body id="chat-body">
    <div class="chat-container">
        <div class="chat-box" id="chat-box">
            <!-- Messages will be dynamically inserted here -->
        </div>
        <form id="chat-form">
            <input type="text" id="message" placeholder="Type a message..." autocomplete="off">
            <button type="submit">Send</button>
        </form>
        <?php
        if (isset($_SESSION["msg"])) {
            $msg = $_SESSION["msg"];
            UNSET($_SESSION["msg"]);
            echo "<p> $msg </p>";
        } 
        ?>
    </div>

    <script src="/job_platform/assets/js/communicate.js"></script>

    <?php include 'footer.php'; ?>
</body>
</html>