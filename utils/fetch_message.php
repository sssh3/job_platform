<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "job_platform_db");


$sender_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id']; // Ensure you pass this parameter in the URL

$query = "
    SELECT sender_id, message, timestamp 
    FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY timestamp
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('iiii', $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
