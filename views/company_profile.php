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
        $company_size = $company['company_size'];
        $website = $company['website'];
        $social_media = $company['social_media'];
        $company_description = $company['company_description'];
        $address_id = $company['address_id']; // Fetch address_id
        $company_exists = true;

        // 查询地址信息（国家、省份、城市）
        $stmtAddress = $pdo->prepare("SELECT countries.name AS country, provinces.admin1_name AS province, cities.city_name AS city
                                     FROM cities
                                     JOIN provinces ON provinces.admin1_code = cities.admin1_code
                                     JOIN countries ON countries.code = cities.country_code
                                     WHERE cities.address_id = :address_id");
        $stmtAddress->execute(['address_id' => $address_id]);
        $address = $stmtAddress->fetch(PDO::FETCH_ASSOC);
        if ($address) {
            $country = $address['country'];
            $province = $address['province'];
            $city = $address['city'];
        } else {
            $country = $province = $city = '';
        }
    } else {
        $company_exists = false;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// 更新公司信息功能
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updated_company_name = $_POST['company_name'];
    $updated_industry = $_POST['industry'];
    $updated_company_size = $_POST['company_size'];
    $updated_website = $_POST['website'];
    $updated_social_media = $_POST['social_media'];
    $updated_company_description = $_POST['company_description'];

    // 处理地址选择
    $search_country = $_POST['search-country'];
    $search_province = $_POST['search-province'];
    $search_city = $_POST['search-city'];

    // 获取 address_id
    $stmt = $pdo->prepare("SELECT address_id 
                           FROM cities
                           JOIN provinces ON provinces.admin1_code = cities.admin1_code AND provinces.admin1_name = :province
                           JOIN countries ON countries.code = cities.country_code AND countries.name = :country
                           WHERE cities.city_name = :city");
    $stmt->bindParam(':country', $search_country);
    $stmt->bindParam(':province', $search_province);
    $stmt->bindParam(':city', $search_city);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $address_id = $result['address_id'];

    // 更新现有公司资料
    if ($company_exists) {
        $updateStmt = $pdo->prepare("UPDATE companies 
                                     SET company_name = :company_name, 
                                         industry = :industry, 
                                         company_size = :company_size, 
                                         website = :website, 
                                         social_media = :social_media, 
                                         company_description = :company_description, 
                                         address_id = :address_id 
                                     WHERE u_id = :companyId");
        $updateStmt->execute([
            'company_name' => $updated_company_name,
            'industry' => $updated_industry,
            'company_size' => $updated_company_size,
            'website' => $updated_website,
            'social_media' => $updated_social_media,
            'company_description' => $updated_company_description,
            'address_id' => $address_id,
            'companyId' => $companyId
        ]);
    } else {
        // 插入新公司资料
        $insertStmt = $pdo->prepare("INSERT INTO companies 
                                     (u_id, company_name, industry, company_size, website, social_media, company_description, address_id) 
                                     VALUES 
                                     (:companyId, :company_name, :industry, :company_size, :website, :social_media, :company_description, :address_id)");
        $insertStmt->execute([
            'companyId' => $companyId,
            'company_name' => $updated_company_name,
            'industry' => $updated_industry,
            'company_size' => $updated_company_size,
            'website' => $updated_website,
            'social_media' => $updated_social_media,
            'company_description' => $updated_company_description,
            'address_id' => $address_id
        ]);
    }

    // 更新后重定向
    header('Location: job_platform/views/company_profile.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $company_exists ? $company_name : 'Create Company Profile'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/job_platform/assets/css/company_styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="profile-container">
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

        <div class="profile-body">
            <?php if ($company_exists): ?>
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

            <div class="profile-section edit-profile">
                <h3><?php echo $company_exists ? 'Edit Profile' : 'Create Profile'; ?></h3>
                <form action="company_profile.php" method="POST">
                    <label for="company_name">Company Name:</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo $company_exists ? $company_name : ''; ?>" required><br>

                    <label for="industry">Industry:</label>
                    <input type="text" id="industry" name="industry" value="<?php echo $company_exists ? $industry : ''; ?>" required><br>

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

                    <label for="company_size">Company Size:</label>
                    <input type="text" id="company_size" name="company_size" value="<?php echo $company_exists ? $company_size : ''; ?>"><br>

                    <label for="website">Website:</label>
                    <input type="url" id="website" name="website" value="<?php echo $company_exists ? $website : ''; ?>"><br>

                    <label for="social_media">Social Media:</label>
                    <input type="text" id="social_media" name="social_media" value="<?php echo $company_exists ? $social_media : ''; ?>"><br>

                    <label for="company_description">Description:</label>
                    <textarea id="company_description" name="company_description"><?php echo $company_exists ? $company_description : ''; ?></textarea><br>

                    <button type="submit">Save Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
