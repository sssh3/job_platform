<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "job_platform_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id']; // This should come from the chat interface
    $message = $mysqli->real_escape_string($_POST['message']);

    $mysqli->query("INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$sender_id', '$receiver_id', '$message')");
}

// Fetch messages
$sender_id = $_SESSION['user_id'];
$receiver_id = $_GET['chat_with']; // You need to pass this parameter in the URL

$result = $mysqli->query("SELECT * FROM messages WHERE (sender_id='$sender_id' AND receiver_id='$receiver_id') OR (sender_id='$receiver_id' AND receiver_id='$sender_id') ORDER BY timestamp");

while ($row = $result->fetch_assoc()) {
    echo "<p><strong>{$row['sender_id']}:</strong> {$row['message']} <em>{$row['timestamp']}</em></p>";
}
?>