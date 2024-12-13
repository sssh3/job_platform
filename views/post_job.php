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


    // Create a new job postition
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_job'])) {
        $job_title = $_POST['job_title'];
        $description = $_POST['description'];
        $requirements = $_POST['requirements'];
        $benefits = $_POST['benefits'];
        $min_salary = $_POST['min_salary'];
        $max_salary = $_POST['max_salary'];
        $employer_id = $_SESSION['user_id'];


        $search_country = $_POST['search-country'];
        $search_province = $_POST['search-province'];
        $search_city = $_POST['search-city'];
        $stmt = $pdo->prepare("SELECT address_id
        FROM cities
        JOIN provinces ON provinces.admin1_code = cities.admin1_code AND provinces.admin1_name = :province
        JOIN countries ON countries.code = cities.country_code AND countries.name = :country
        WHERE cities.city_name = :city
        ");
        $stmt->bindParam(':country', $search_country);
        $stmt->bindParam(':province', $search_province);
        $stmt->bindParam(':city', $search_city);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $address_id = $result['address_id'];


        $job_type = $_POST['job_type'];
        $stmt = $pdo->prepare("SELECT job_type_id FROM job_types WHERE job_type_name = :job_type_name");
        $stmt->bindParam(':job_type_name', $job_type);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $job_type_id = $result['job_type_id'];

        $stmt = $pdo->prepare("INSERT INTO jobs 
                (job_title, `description`, requirements, benefits, min_salary, max_salary, address_id, employer_id, job_type_id) 
                VALUES 
                (:job_title, :description, :requirements, :benefits, :min_salary, :max_salary, :address_id, :employer_id, :job_type_id)");
        $stmt->bindParam(':job_title', $job_title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':requirements', $requirements);
        $stmt->bindParam(':benefits', $benefits);
        $stmt->bindParam(':min_salary', $min_salary);
        $stmt->bindParam(':max_salary', $max_salary);
        $stmt->bindParam(':address_id', $address_id);
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->bindParam(':job_type_id', $job_type_id);
        $stmt->execute();


        // 更新成功，跳转回页面
        echo "<script>alert('Job position posted successfully!'); window.location.href='control_employer.php';</script>";
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
    <link rel="stylesheet" href="/job_platform/assets/css/jobseekerStyle.css">
    <title>Edit Basic Information</title>
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
    margin: 10px 0;
    width: 80%; /* 确保宽度为80% */
    max-width: 600px; /* 最大宽度限制 */
}

label {
    font-weight: bold;
    display: inline-block;
    margin-top: 10px;
}

input[type="text"], input[type="email"], input[type="tel"], textarea {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background-color: #3498db;  /* 统一按钮颜色为蓝色 */
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

.container {
    width: 80%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center; /* 居中对齐内容 */
    justify-content: center; /* 垂直居中内容 */
    position: relative; /* 为了定位按钮 */
   
}

.container .btn-back {
    position: absolute; /* 绝对定位 */
    top: 10px; /* 距离容器顶部10px */
    left: 10px; /* 距离容器左边10px */
    padding: 10px 20px;
    background-color: #3498db;  /* 与其他按钮统一颜色 */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none; /* 去掉链接下划线 */
}

.container .btn-back:hover {
    background-color: #2980b9;  /* 按钮悬停效果 */
}

hr {
    border: 1px solid #ddd;
    margin: 20px 0;
}


    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>

<div class="container">
    <h2>Post A New Job Position</h2>

    <!-- back botton -->
    <a href="control_employer.php" class="btn-back">Back to Control</a>

    <!-- edit basic information -->
    <form method="POST" action="post_job.php">

        <label for="job_title">Job Title:</label>
        <input type="text" name="job_title" required><br>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea><br>

        <label for="requirements">Requirements:</label>
        <textarea name="requirements" required></textarea><br>

        <label for="benefits">Benefits:</label>
        <textarea name="benefits"></textarea><br>

        <label for="min_salary">Minimum Salary ($/Y):</label>
        <input type="number" name="min_salary" min="0" step="100" required><br>

        <label for="max_salary">Maximum Salary ($/Y):</label>
        <input type="number" name="max_salary" min="0" step="100" required><br>

        <label for="job_type">Job Type:</label>
        <select name="job_type" required>
            <option value="">--Select Job Type--</option>
            <option value="Full-time">Full-time</option>
            <option value="Part-time">Part-time</option>
            <option value="Contract">Contract</option>
            <option value="Internship">Internship</option>
        </select><br><br>

        <label>Select Job Location:</label>
        <input type="text" id="search-country" name="search-country" placeholder="select country/region" autocomplete="off" required><br>
        <div id="dropdown-country"></div>
        <script src="/job_platform/assets/js/searchCountry.js"></script>
        <br>
        <input type="text" id="search-province" name="search-province" placeholder="select province/state" autocomplete="off" required><br>
        <div id="dropdown-province"></div>
        <script src="/job_platform/assets/js/searchProvince.js"></script>
        <br>
        <input type="text" id="search-city" name="search-city" placeholder="select city" autocomplete="off" required><br>
        <div id="dropdown-city"></div>
        <script src="/job_platform/assets/js/searchCity.js"></script>
        <br>

        <button type="submit" name="post_job">Post Job Position</button>
    </form>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
