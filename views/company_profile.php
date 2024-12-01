<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /job_platform/login');
    exit;
}

$companyId = $_SESSION['user_id']; // company ID
// connection
$host = 'localhost';
$db = 'job_platform_db';  
$user = 'root'; 
$pass = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // company data
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE u_id = :companyId");
    $stmt->execute(['companyId' => $companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($company) {
        $company_name = $company['company_name'];
        $industry = $company['industry'];
        $location = $company['location'];
        $company_size = $company['company_size'];
        $website = $company['website'];
        $social_media = $company['social_media'];
        $company_description = $company['company_description'];
        $company_exists = true;
    } else {
        $company_exists = false; // No company profile found
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// 更新公司信息功能
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updated_company_name = $_POST['company_name'];
    $updated_industry = $_POST['industry'];
    $updated_location = $_POST['location'];
    $updated_company_size = $_POST['company_size'];
    $updated_website = $_POST['website'];
    $updated_social_media = $_POST['social_media'];
    $updated_company_description = $_POST['company_description'];

    if ($company_exists) {
        // Update existing company profile
        $updateStmt = $pdo->prepare("UPDATE companies SET company_name = :company_name, industry = :industry, location = :location, company_size = :company_size, website = :website, social_media = :social_media, company_description = :company_description WHERE u_id = :companyId");
        $updateStmt->execute([
            'company_name' => $updated_company_name,
            'industry' => $updated_industry,
            'location' => $updated_location,
            'company_size' => $updated_company_size,
            'website' => $updated_website,
            'social_media' => $updated_social_media,
            'company_description' => $updated_company_description,
            'companyId' => $companyId
        ]);
    } else {
        // Insert new company profile
        $insertStmt = $pdo->prepare("INSERT INTO companies (u_id, company_name, industry, location, company_size, website, social_media, company_description) VALUES (:companyId, :company_name, :industry, :location, :company_size, :website, :social_media, :company_description)");
        $insertStmt->execute([
            'companyId' => $companyId,
            'company_name' => $updated_company_name,
            'industry' => $updated_industry,
            'location' => $updated_location,
            'company_size' => $updated_company_size,
            'website' => $updated_website,
            'social_media' => $updated_social_media,
            'company_description' => $updated_company_description
        ]);
    }
    header('Location: company_profile.php'); // 更新后重新加载页面
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $company_exists ? $company_name : 'Create Company Profile'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- For icons -->
    <link rel="stylesheet" href="/job_platform/assets/css/company_styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    
<?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>

    <div class="profile-container">
        <!-- Company Profile Header -->
        <div class="profile-header">
            <?php if ($company_exists): ?>
                <h1><?php echo $company_name; ?></h1>
                <p class="industry"><?php echo $industry; ?></p>
                <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo $location; ?></p>
            <?php else: ?>
                <h1>Create Your Company Profile</h1>
                <p>Please fill out the details to create your company profile.</p>
            <?php endif; ?>
        </div>

        <!-- Company Profile Body -->
        <div class="profile-body">
            <?php if ($company_exists): ?>
                <!-- Company Details Section -->
                <div class="profile-section">
                    <h3>About Us</h3>
                    <p><?php echo $company_description; ?></p>
                </div>

                <div class="profile-section">
                    <h3>Company Details</h3>
                    <ul>
                        <li><strong>Company Size:</strong> <?php echo $company_size; ?></li>
                        <li><strong>Website:</strong> <a href="<?php echo $website; ?>" target="_blank"><?php echo $website; ?></a></li>
                        <li><strong>Social Media:</strong> <a href="https://twitter.com/<?php echo $social_media; ?>" target="_blank">Twitter</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Edit or Create Company Profile Form -->
            <div class="profile-section edit-profile">
                <h3><?php echo $company_exists ? 'Edit Profile' : 'Create Profile'; ?></h3>
                <form action="company_profile.php" method="POST">
                    <label for="company_name">Company Name:</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo $company_exists ? $company_name : ''; ?>" required><br>

                    <label for="industry">Industry:</label>
                    <input type="text" id="industry" name="industry" value="<?php echo $company_exists ? $industry : ''; ?>" required><br>

                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" value="<?php echo $company_exists ? $location : ''; ?>" required><br>

                    <label for="company_size">Company Size:</label>
                    <input type="text" id="company_size" name="company_size" value="<?php echo $company_exists ? $company_size : ''; ?>" required><br>

                    <label for="website">Website:</label>
                    <input type="text" id="website" name="website" value="<?php echo $company_exists ? $website : ''; ?>" required><br>

                    <label for="social_media">Social Media:</label>
                    <input type="text" id="social_media" name="social_media" value="<?php echo $company_exists ? $social_media : ''; ?>" required><br>

                    <label for="company_description">Description:</label>
                    <textarea id="company_description" name="company_description" required><?php echo $company_exists ? $company_description : ''; ?></textarea><br>

                    <button type="submit"><?php echo $company_exists ? 'Save Changes' : 'Create Profile'; ?></button>
                </form>
            </div>
        </div>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo $company_exists ? $company_name : 'Your Company'; ?>. All rights reserved.</p>
        </footer>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
