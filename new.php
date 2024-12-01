<?php
// Turn off output buffering
ob_end_flush();

// Send some output
echo "Hello, World!<br/>";

// Flush the system output buffer
flush();

// Simulate a long-running process
sleep(5);

echo "This is printed after a delay.<br/>";
?>
