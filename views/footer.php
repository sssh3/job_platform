<footer>
    <p>2024 Database Management Systems Group Project</p>
    <p><?php
        // Read the file into an array of lines
        if (isset($_SESSION["type"]) && ($_SESSION["type"] == "job-seeker" || $_SESSION["type"] == "employer")){
                $type = $_SESSION["type"];
        } else {
            if (rand(0,1) == 1) {
                $type = 'job-seeker';
            } else {
                $type = 'employer';
            }
        }
        $messages = file(__DIR__ . '/../assets/text/tips_' . $type . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Check if we have any messages
        if ($messages) {
            // Select a random message
            $randomMessage = $messages[array_rand($messages)];
            echo "For " . $type . "s: " . $randomMessage;
        } else {
            // Fallback message if file is empty or not found
            echo "Welcome to our site!";
        }
    ?></p>
</footer>
