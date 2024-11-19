<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database configuration
$servername = "localhost";
$username = "root"; // Default XAMPP MySQL username
$password = ""; // Default XAMPP MySQL password is empty

try {
    // Create a connection to MySQL
    $conn = new PDO("mysql:host=$servername", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SHOW DATABASES LIKE 'job_platform_db'");
    if ($stmt->rowCount() > 0) {
        // echo "Database 'job_platform_db' already exists. Exiting script.<br>";
    }
    else {

        // SQL to create the database
        $sql = "CREATE DATABASE IF NOT EXISTS job_platform_db";
        $conn->exec($sql);
        echo "Database 'job_platform_db' created successfully<br>";

        // Connect to the newly created database
        $conn->exec("USE job_platform_db");

        // Create table user_types
        $sql = "CREATE TABLE IF NOT EXISTS user_types (
            user_type_id INT PRIMARY KEY,
            user_type_name VARCHAR(10) NOT NULL
        )";
        $conn->exec($sql);

        // Insert user_type instances
        $sql = "INSERT INTO user_types (user_type_id, user_type_name) VALUES
            (0, 'admin'),
            (1, 'visitor'),
            (2, 'employer'),
            (3, 'job-seeker')
        ";
        $conn->exec($sql);
        echo "user_types creation success<br>";

        // SQL to create the users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            u_id INT AUTO_INCREMENT PRIMARY KEY,
            user_name VARCHAR(50) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            user_type_id INT NOT NULL,
            FOREIGN KEY (user_type_id) REFERENCES user_types(user_type_id)
        )";
        $conn->exec($sql);
        echo "Table 'users' created successfully<br>";

        // Create admin and visitor
        $sql = "INSERT INTO users (user_name, `password`, user_type_id) VALUES 
                    ('admin', 'admin', 0),
                    ('visitor', 'visitor', 1)";
        $conn->exec($sql);

        // Create 30,000 employer users, 40 words for each, which makes a cap of 64,000 instances
        $adjectives = [
            'Global', 'Dynamic', 'Innovative', 'Efficient', 'Reliable', 'Advanced', 'Strategic', 'Professional', 'Pioneering', 'Creative',
            'Robust', 'CuttingEdge', 'Modern', 'Versatile', 'Agile', 'Sustainable', 'Revolutionary', 'Leading', 'Comprehensive', 'Progressive',
            'Visionary', 'Resourceful', 'Ambitious', 'Diverse', 'Flexible', 'Secure', 'Trusted', 'Responsive', 'Synergetic', 'Collaborative',
            'FutureProof', 'CustomerCentric', 'Driven', 'Focused', 'Holistic', 'Sophisticated', 'Tailored', 'Expert', 'Specialized', 'InnovativeSolutions'
        ];
        
        $nouns = [
            'Tech', 'Solutions', 'Industries', 'Systems', 'Networks', 'Enterprises', 'Technologies', 'Partners', 'Consulting', 'Services',
            'Holdings', 'Analytics', 'Platforms', 'Technologies', 'Ventures', 'Innovations', 'Development', 'Dynamics', 'Concepts', 'Designs',
            'Resources', 'Processes', 'Management', 'Operations', 'Tools', 'Infrastructure', 'Devices', 'Applications', 'Modules', 'Components',
            'Interfaces', 'Frameworks', 'Facilities', 'Connections', 'Environments', 'Structures', 'Configurations', 'Assemblies', 'Programs', 'SolutionsHub'
        ];
        
        $suffixes = [
            'Inc', 'Corp', 'LLC', 'Ltd', 'Group', 'PLC', 'GmbH', 'S.A.', 'Pty', 'Co.',
            'Incorporated', 'Corporation', 'Limited', 'Enterprises', 'Associates', 'Consortium', 'Partners', 'Holdings', 'Company', 'Firm',
            'Network', 'Alliance', 'Collective', 'Federation', 'Syndicate', 'Cooperative', 'Conglomerate', 'Union', 'Society', 'Organization',
            'Institution', 'Division', 'Agency', 'Office', 'Bureau', 'B.V.', 'S.R.L.', 'Inc.', 'LLP', 'Foundation'
        ];
        function generateName($adjectives, $nouns, $suffixes) {
            $adjective = $adjectives[array_rand($adjectives)];
            $noun = $nouns[array_rand($nouns)];
            $suffix = $suffixes[array_rand($suffixes)];
            return implode(' ', [$adjective, $noun, $suffix]);
        }

        $stmt = $conn->prepare("INSERT INTO users (user_name, password, user_type_id) VALUES (:username, :password, 2)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);

        $uniqueUsernames = [];
        for ($i = 0; $i < 30000; $i++) {
            do {
                $username = generateName($adjectives, $nouns, $suffixes);
            } while (isset($uniqueUsernames[$username])); // Check for uniqueness
            
            $uniqueUsernames[$username] = true;
        
            $password = "defaultPassword"; // Use a default password for testing
        
            // Execute the statement
            $stmt->execute();
        }
        echo "30,000 employer users have been generated!<br>";

        // Create 'countries' table for iso3166 two-letter country code
        $sql = file_get_contents(__DIR__ . '/../assets/data/iso3166.sql');
        $conn->exec($sql);

        // Get valid country codes
        $validCountryCodes = [];
        $stmt = $conn->query("SELECT code FROM countries");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $validCountryCodes[] = $row['code'];
        }

        // Create 'provinces' table
        $sql = "CREATE TABLE IF NOT EXISTS provinces (
            country_code CHAR(2),
            admin1_code VARCHAR(20),
            admin1_name VARCHAR(50) NOT NULL,
            PRIMARY KEY (country_code, admin1_code),
            FOREIGN KEY (country_code) REFERENCES countries(code)
        )";
        $conn->exec($sql);
        echo "Table 'provinces' created successfully<br>";

        // Insert provinces instances
        $provincesFile = fopen(__DIR__ . '/../assets/data/admin1CodesASCII.txt', 'r');
        if ($provincesFile) {
            // Prepare an SQL statement for inserting data
            $stmt = $conn->prepare("INSERT INTO provinces (country_code, admin1_code, admin1_name) VALUES (:country_code, :admin1_code, :admin1_name)");
    
            while (($line = fgetcsv($provincesFile, 0, "\t")) !== false) {
                // Extract necessary fields
                $arr = explode('.', $line[0]);

                $countryCode = $arr[0];
                $admin1Code = $arr[1];
                $admin1Name = $line[2];

                if (in_array($countryCode, $validCountryCodes)) {
                    // Bind parameters and execute the statement
                    $stmt->bindParam(':country_code', $countryCode);
                    $stmt->bindParam(':admin1_code', $admin1Code);
                    $stmt->bindParam(':admin1_name', $admin1Name);
                    $stmt->execute();
                }
            }
            fclose($provincesFile);
            echo "provinces insertion success<br>";
        }

        // Create cities table
        $sql = "CREATE TABLE IF NOT EXISTS cities (
            address_id INT AUTO_INCREMENT PRIMARY KEY,
            city_name VARCHAR(50) NOT NULL,
            country_code CHAR(2),
            admin1_code VARCHAR(20),
            `population` BIGINT NOT NULL,
            FOREIGN KEY (country_code) REFERENCES countries(code)
        )";
        $conn->exec($sql);
        echo "Table 'cities' created successfully<br>";

        // Insert real world cities instances from GeoNames (The file contains cities with more than 15,000 people)
        $citiesFile = fopen(__DIR__ . '/../assets/data/cities15000.txt', 'r');
        if ($citiesFile) {
            // Prepare an SQL statement for inserting data
            $stmt = $conn->prepare("INSERT INTO cities (city_name, country_code, admin1_code, `population`) VALUES 
                                        (:city_name, :country_code, :admin1_code, :population)");
    
            while (($line = fgetcsv($citiesFile, 0, "\t")) !== false) {
                // Extract necessary fields
                $cityName = $line[2]; // name
                $countryCode = $line[8]; // country code
                $admin1Code = $line[10]; // state or province code
                $population = $line[14]; // population

                // Check if the country code is valid
                if (in_array($countryCode, $validCountryCodes)) {
                    // Bind parameters and execute the statement
                    $stmt->bindParam(':city_name', $cityName);
                    $stmt->bindParam(':country_code', $countryCode);
                    $stmt->bindParam(':population', $population);
                    $stmt->bindParam(':admin1_code', $admin1Code);
                    $stmt->execute();
                }
            }
            fclose($citiesFile);
            echo "cities insertion success<br>";
        }

        // Create table job_types
        $sql = "CREATE TABLE IF NOT EXISTS job_types (
            job_type_id INT PRIMARY KEY,
            job_type_name VARCHAR(10) NOT NULL
        )";
        $conn->exec($sql);

        // Insert job_type instances
        $sql = "INSERT INTO job_types (job_type_id, job_type_name) VALUES
            (0, 'FULLTIME'),
            (1, 'PARTTIME'),
            (2, 'CONTRACT'),
            (3, 'INTERNSHIP')
        ";
        $conn->exec($sql);
        echo "job_types creation success<br>";

        // Create Jobs table
        $sql = "CREATE TABLE IF NOT EXISTS jobs (
            job_id INT AUTO_INCREMENT PRIMARY KEY,
            job_title VARCHAR(255) NOT NULL,
            job_type_id INT NOT NULL,
            requirements VARCHAR(255),
            benefits VARCHAR(255),
            min_salary INT NOT NULL,
            max_salary INT NOT NULL,
            address_id INT,
            employer_id INT,
            FOREIGN KEY (address_id) REFERENCES cities(address_id),
            FOREIGN KEY (job_type_id) REFERENCES job_types(job_type_id),
            FOREIGN KEY (employer_id) REFERENCES users(u_id)
        )";
        $conn->exec($sql);
        echo "Table 'jobs' created successfully<br>";

        // Insert 90,000 job instances according to the population in each city
        // Dics for job_title
        $adjectives = array(
            "Lead", "Senior", "Global", "Dynamic", "Creative", "Innovative",
            "Strategic", "Expert", "Proactive", "Visionary"
        );
        $nouns = array(
            "Marketing", "Development", "Operations", "Engineering", "Design",
            "Sales", "Finance", "Human Resources", "Product", "Customer Success"
        );
        $suffixes = array(
            "Manager", "Specialist", "Coordinator", "Director", "Consultant",
            "Analyst", "Executive", "Administrator", "Strategist", "Officer"
        );
        // For job_type_id
        $stmt = $conn->query("SELECT job_type_id AS id FROM job_types");
        $jobTypeIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), "id");

        // Array of job requirements
        $jobRequirements = [
            "Bachelor's degree in Computer Science or related field",
            "5+ years of software development experience",
            "Proficiency in JavaScript, HTML, and CSS",
            "Experience with PHP and MySQL",
            "Strong understanding of version control systems like Git",
            "Familiarity with Agile methodologies",
            "Excellent problem-solving skills",
            "Strong communication and teamwork abilities",
            "Experience with cloud platforms such as AWS or Azure",
            "Knowledge of RESTful API design",
            "Ability to work independently and manage time effectively",
            "Experience with automated testing frameworks",
            "Understanding of CI/CD processes",
            "Proficiency in Python or Java",
            "Experience with front-end frameworks like React or Angular",
            "Knowledge of database design and optimization",
            "Strong analytical and critical thinking skills",
            "Experience with Docker and containerization",
            "Ability to learn new technologies quickly",
            "Proficiency in mobile development (iOS/Android)"
        ];

        // Array of job benefits
        $jobBenefits = [
            "Competitive salary package", "Health insurance coverage",
            "Dental and vision benefits", "401(k) retirement plan with matching",
            "Paid time off and holidays", "Flexible working hours",
            "Remote work opportunities", "Professional development programs",
            "Gym membership or wellness benefits", "Life insurance",
            "Employee stock purchase plan", "Commuter benefits",
            "Tuition reimbursement", "Childcare support",
            "Generous parental leave policy", "Team building and company events",
            "Diversity and inclusion initiatives", "Casual dress code",
            "Annual performance bonuses", "Employee assistance programs"
        ];

        // For employer_id
        $stmt = $conn->query("SELECT u_id AS id FROM users WHERE user_type_id = 2");
        $employerIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), "id");

        // Retrieve population data
        $stmt = $conn->query("SELECT address_id, `population` FROM cities");
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total population
        $totalPopulation = 0;
        foreach ($cities as $city) {
            $totalPopulation += $city['population'];
        }

        // Distribute jobs
        $totalJobs = 90000;
        $jobDistribution = [];
        foreach ($cities as $city) {
            $addressId = $city['address_id'];
            $population = $city['population'];
            $numJobs = round(($population / $totalPopulation) * $totalJobs);
            
            for ($i = 0; $i < $numJobs; $i++) {
                $stmt = $conn->prepare("INSERT INTO jobs 
                (job_title, job_type_id, requirements, benefits, min_salary, max_salary, employer_id, address_id) VALUES 
                (:jobTitle, :jobTypeId, :requirements, :benefits, :minSalary, :maxSalary, :employerId, :addressId)");

                $jobTitle = generateName($adjectives, $nouns, $suffixes);
                $jobTypeId = $jobTypeIds[array_rand($jobTypeIds)];
                // requirements
                $randomKeys = array_rand($jobRequirements, 2);
                $requirements = '1. ' . $jobRequirements[$randomKeys[0]] . '\n2. ' . $jobRequirements[$randomKeys[1]];
                // benefits
                $randomKeys = array_rand($jobBenefits, 2);
                $benefits = '1. ' . $jobBenefits[$randomKeys[0]] . '\n2. ' . $jobBenefits[$randomKeys[1]];

                $minSalary = rand(10, 100) * 1000;
                $maxSalary = $minSalary + rand(10, 100) *1000;

                $employerId = $employerIds[array_rand($employerIds)];

                $stmt->bindParam(":jobTitle", $jobTitle);
                $stmt->bindParam(":jobTypeId", $jobTypeId);
                $stmt->bindParam(":requirements", $requirements);
                $stmt->bindParam(":benefits", $benefits);
                $stmt->bindParam(":minSalary", $minSalary);
                $stmt->bindParam(":maxSalary", $maxSalary);
                $stmt->bindParam(":employerId", $employerId);
                $stmt->bindParam(":addressId", $addressId);

                $stmt->execute();
            }
        }
        echo "jobs generation success<br>";


    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
