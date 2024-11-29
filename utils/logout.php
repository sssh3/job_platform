<?php
session_start();


if (isset($_SESSION['last_visited_url'])) {
    $last_url = $_SESSION['last_visited_url'];
    $_SESSION = array();
    session_destroy();
    header("Location: $last_url");
} else {
    $_SESSION = array();
    session_destroy();
    header("Location: /job_platform/");
}
exit();

