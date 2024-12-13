<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$u_id = $_SESSION['user_id'];
$host = 'localhost';
$db = 'job_platform_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Start measuring SQL execution time
    $start_time = microtime(true);
    // Fetch existing language skills
    $stmt = $pdo->prepare("SELECT * FROM language_skills WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update language skill
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_language'])) {
        $language_skill_id = $_POST['language_skill_id'];
        $language_name = $_POST['language_name'];
        $proficiency_level = $_POST['proficiency_level'];

        $stmt_update = $pdo->prepare("UPDATE language_skills SET language_name = :language_name, proficiency_level = :proficiency_level WHERE language_skill_id = :language_skill_id AND u_id = :u_id");
        $stmt_update->bindParam(':language_skill_id', $language_skill_id);
        $stmt_update->bindParam(':language_name', $language_name);
        $stmt_update->bindParam(':proficiency_level', $proficiency_level);
        $stmt_update->bindParam(':u_id', $u_id);
        $stmt_update->execute();

        header('Location: edit_language_skills.php');
        exit;
    }

    // Add new language skill
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_language'])) {
        $language_name = $_POST['language_name'];
        $proficiency_level = $_POST['proficiency_level'];

        $stmt_insert = $pdo->prepare("INSERT INTO language_skills (u_id, language_name, proficiency_level) VALUES (:u_id, :language_name, :proficiency_level)");
        $stmt_insert->bindParam(':u_id', $u_id);
        $stmt_insert->bindParam(':language_name', $language_name);
        $stmt_insert->bindParam(':proficiency_level', $proficiency_level);
        $stmt_insert->execute();

        header('Location: edit_language_skills.php');
        exit;
    }

    // Delete language skill
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_language'])) {
        $language_skill_id = $_POST['language_skill_id'];

        $stmt_delete = $pdo->prepare("DELETE FROM language_skills WHERE language_skill_id = :language_skill_id AND u_id = :u_id");
        $stmt_delete->bindParam(':language_skill_id', $language_skill_id);
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        header('Location: edit_language_skills.php');
        exit;
    }
    // 记录结束时间并计算总时间
    $end_time = microtime(true);
    $sql_time = $end_time - $start_time;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/job_platform/assets/css/jobseekerStyle.css">
    
    <title>Edit Language Skills</title>
 
</head>
<style>
    h2 {
    text-align: center;
    color: #2c3e50;
    }

    form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 10px auto;
    width: 80%; /* Set the width to 80% */
    max-width: 600px; /* Limit the maximum width */
    }

    label {
    font-weight: bold;
    display: inline-block;
    margin-top: 10px;
    }

    input[type="text"], select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    }

    button {
    background-color: #3498db;  /* Uniform button color as blue */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 15px;
    }

    button:hover {
    background-color: #2980b9;
    }

    .activity-form {
    margin-bottom: 20px;
    }

    .container {
    width: 80%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center; /* Center content */
    justify-content: flex-start; /* Align content to the top */
    position: relative; /* So that absolute positioned elements are relative to it */
    }

    .btn-back {
    position: absolute; /* Use absolute positioning */
    top: 20px; /* 20px from the top */
    left: 20px; /* 20px from the left */
    padding: 10px 20px;
    background-color: #3498db;  /* Uniform button color as blue */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none; /* Remove underline from link */
    }

    .btn-back:hover {
    background-color: #2980b9;
    }

    hr {
    border: 1px solid #ddd;
    margin: 20px 0;
    }
    /* SQL 执行时间显示 */
    .sql-time {
            margin-top: 20px;
            font-size: 14px;
            
        }
</style>

<body>
<?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>

<div class="container">
    <h2>Edit Language Skills</h2>

    <!-- Back button -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- Add new language skill form -->
    <form method="POST" action="edit_language_skills.php" class="activity-form">
        <h3>Add New Language Skill</h3>
        <label for="language_name">Language Name:</label>
        <input type="text" name="language_name" required><br>

        <label for="proficiency_level">Proficiency Level:</label>
        <select name="proficiency_level" required>
            <option value="Basic">Basic</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
            <option value="Fluent">Fluent</option>
        </select><br>

        <button type="submit" name="add_language">Add Language Skill</button>
    </form>

    <h3>Your Language Skills</h3>
    <!-- Display existing language skills, with options to edit or delete -->
    <?php foreach ($languages as $language): ?>
        <form method="POST" action="edit_language_skills.php" class="activity-form">
            <input type="hidden" name="language_skill_id" value="<?php echo $language['language_skill_id']; ?>">
            <label for="language_name">Language Name:</label>
            <input type="text" name="language_name" value="<?php echo $language['language_name']; ?>" required><br>

            <label for="proficiency_level">Proficiency Level:</label>
            <select name="proficiency_level" required>
                <option value="Basic" <?php if ($language['proficiency_level'] == 'Basic') echo 'selected'; ?>>Basic</option>
                <option value="Intermediate" <?php if ($language['proficiency_level'] == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
                <option value="Advanced" <?php if ($language['proficiency_level'] == 'Advanced') echo 'selected'; ?>>Advanced</option>
                <option value="Fluent" <?php if ($language['proficiency_level'] == 'Fluent') echo 'selected'; ?>>Fluent</option>
            </select><br>

            <button type="submit" name="edit_language">Update Language Skill</button>
            <button type="submit" name="delete_language" onclick="return confirm('Are you sure you want to delete this language skill?');">Delete Language Skill</button>
        </form>
        <hr>
    <?php endforeach; ?>
    <div class="sql-time">
        <p>SQL execution time: <?php echo number_format($sql_time, 6); ?> seconds.</p>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
