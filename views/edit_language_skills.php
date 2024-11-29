<?php
session_start();

// 如果没有登录，跳转到登录页面
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

    // 查询现有的语言技能
    $stmt = $pdo->prepare("SELECT * FROM language_skills WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->execute();
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 更新语言技能
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

    // 新增语言技能
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

    // 删除语言技能
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_language'])) {
        $language_skill_id = $_POST['language_skill_id'];

        $stmt_delete = $pdo->prepare("DELETE FROM language_skills WHERE language_skill_id = :language_skill_id AND u_id = :u_id");
        $stmt_delete->bindParam(':language_skill_id', $language_skill_id);
        $stmt_delete->bindParam(':u_id', $u_id);
        $stmt_delete->execute();

        header('Location: edit_language_skills.php');
        exit;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Language Skills</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 20px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
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
            background-color: #3498db;
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
        }
        .btn-back {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-back:hover {
            background-color: #c0392b;
        }
        hr {
            border: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Language Skills</h2>

    <!-- 返回按钮 -->
    <a href="jobseeker_profile.php" class="btn-back">Back to Profile</a>

    <!-- 新增语言技能表单 -->
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
    <!-- 显示现有语言技能，允许编辑和删除 -->
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
</div>

</body>
</html>
