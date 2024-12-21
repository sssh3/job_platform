<?php

// This file is for creating use profiles related tables

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 数据库配置
$servername = "localhost";
$username = "root"; // 默认 XAMPP MySQL 用户名
$password = ""; // 默认 XAMPP MySQL 密码为空

try {
    // 创建与 MySQL 的连接
    $conn = new PDO("mysql:host=$servername", $username, $password);
    // 设置 PDO 错误模式为异常
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the table 'companies' has existed
    $stmt = $conn->query("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = 'job_platform_db' AND TABLE_NAME = 'companies'
    ");
    if ($stmt->fetchColumn() > 0) {
        // Skip creating tables
        // echo "Tables for user profile already exist.<br>";
    } else {
        // // 创建数据库
        // $sql = "CREATE DATABASE IF NOT EXISTS job_platform_db";
        // $conn->exec($sql);
        // echo "Database 'job_platform_db' created successfully<br>";
    

        // 选择数据库
        $conn->exec("USE job_platform_db");

        
        // 创建 companies 表
        $sql = "CREATE TABLE IF NOT EXISTS companies (
            u_id INT NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            industry VARCHAR(100),
            `location` INT,
            company_size ENUM('1-50', '51-200', '201-500', '500+'),
            website VARCHAR(255),
            social_media VARCHAR(255),
            company_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (u_id)
        )";
        $conn->exec($sql);
        echo "Table 'companies' created successfully<br>";
        
       // 4. 添加 CHECK 约束来限制 company_size 的值
        $alterSql = "ALTER TABLE companies
        ADD CONSTRAINT chk_company_size CHECK (company_size IN ('1-50', '51-200', '201-500', '500+'))";
        $conn->exec($alterSql);
        echo "Check constraint for company_size added successfully<br>";

        
        

        // 插入 companies 数据
        $sql = "INSERT INTO companies (u_id, company_name, industry, location, company_size, website, social_media, company_description)
        VALUES
            (1, 'Tech Innovators', 'Technology', 4262, '51-200', 'https://www.techinnovators.com', 'https://twitter.com/techinnovators', 'Innovative tech solutions for a better future.')
        ";
        $conn->exec($sql);
        echo "Inserted companies data successfully<br>";

        // create jobseekers table
        $sql = "CREATE TABLE IF NOT EXISTS jobseekers (
            u_id INT PRIMARY KEY,
            avatar BLOB,
            resume VARCHAR(255),
            short_intro TEXT,
            phone VARCHAR(20),
            email VARCHAR(255) NOT NULL,
            GPA DECIMAL(3, 2) DEFAULT NULL ,  
            first_name VARCHAR(100) NOT NULL,
            family_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (u_id) REFERENCES users(u_id) 
        )";

        $conn->exec($sql);
        echo "Table 'jobseekers' created successfully<br>";

        // 添加 CHECK 约束来确保 GPA 的范围为 0 <= GPA <= 4
        $alterSql = "ALTER TABLE jobseekers
        ADD CONSTRAINT chk_gpa_range CHECK (GPA >= 0 AND GPA <= 4)";
        $conn->exec($alterSql);
        echo "Check constraint for GPA range added successfully<br>";

        // 触发器：当删除 users 表中的记录时，删除 jobseekers 中相关的记录
        $triggerSql = "
        

        CREATE TRIGGER delete_jobseeker_on_user_delete
        AFTER DELETE ON users
        FOR EACH ROW
        BEGIN
        DELETE FROM jobseekers WHERE u_id = OLD.u_id;
        END  ;
        ";
        $conn->exec($triggerSql);
        echo "Trigger for cascading delete on jobseekers added successfully<br>";

        // 插入 jobseekers 数据
        $sql = "INSERT INTO jobseekers (u_id, avatar, resume, short_intro, phone, email, GPA,first_name, family_name)
        VALUES
            (2, 'avatar.jpg', 'resume.pdf', 'A passionate software engineer.', '123-456-7890', 'john.doe@example.com', '3.50','John', 'Doe')
        ";
        $conn->exec($sql);
        echo "Inserted jobseekers data successfully<br>";

        // 创建 certifications 表
        $sql = "CREATE TABLE IF NOT EXISTS certifications (
            certification_id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            certification_name VARCHAR(255),
            certification_date DATE,
            issuing_organization VARCHAR(255),
            FOREIGN KEY (u_id) REFERENCES users(u_id)
        )";
        $conn->exec($sql);
        echo "Table 'certifications' created successfully<br>";
       
        
        $triggerSql = "
        

        CREATE TRIGGER delete_certifications_on_user_delete
        AFTER DELETE ON users
        FOR EACH ROW
        BEGIN
            DELETE FROM certifications WHERE u_id = OLD.u_id;
        END  ;
        ";
        $conn->exec($triggerSql);


        echo "Trigger for cascading delete on certifications added successfully<br>";



        // 插入 certifications 数据
        $sql = "INSERT INTO certifications (u_id, certification_name, certification_date, issuing_organization)
        VALUES
            (2, 'Certified Python Developer', '2023-05-01', 'Python Institute'),
            (2, 'AWS Certified Solutions Architect', '2024-02-15', 'Amazon Web Services')
        ";
        $conn->exec($sql);
        echo "Inserted certifications data successfully<br>";

        // 创建 language_skills 表
        $sql = "CREATE TABLE IF NOT EXISTS language_skills (
            language_skill_id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            language_name VARCHAR(100),
            proficiency_level ENUM('Basic', 'Intermediate', 'Advanced', 'Fluent'),
            FOREIGN KEY (u_id) REFERENCES users(u_id) ON DELETE CASCADE
        )";
        $conn->exec($sql);
        echo "Table 'language_skills' created successfully<br>";
        
        $triggerSql = "
       

        CREATE TRIGGER delete_language_skills_on_user_delete
        AFTER DELETE ON users
        FOR EACH ROW
        BEGIN
            DELETE FROM language_skills WHERE u_id = OLD.u_id;
        END ;
        ";


        $conn->exec($triggerSql);


        echo "Trigger for cascading delete on language_skills added successfully<br>";


        // 插入 language_skills 数据
        $sql = "INSERT INTO language_skills (u_id, language_name, proficiency_level)
        VALUES
            (2, 'English', 'Fluent'),
            (2, 'Chinese', 'Advanced')
        ";
        $conn->exec($sql);
        echo "Inserted language_skills data successfully<br>";

        // 创建 internships 表
        $sql = "CREATE TABLE IF NOT EXISTS internships (
            internship_id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            company_name VARCHAR(255),
            job_title VARCHAR(255),
            start_date DATE,
            end_date DATE,
            job_description TEXT,
            FOREIGN KEY (u_id) REFERENCES users(u_id) 
        )";
        $conn->exec($sql);
        echo "Table 'internships' created successfully<br>";
       
       // 触发器：当删除 users 表中的记录时，删除 internships 中相关的记录
        $triggerSql = "
        

        CREATE TRIGGER delete_internships_on_user_delete
        AFTER DELETE ON users
        FOR EACH ROW
        BEGIN
            DELETE FROM internships WHERE u_id = OLD.u_id;
        END ;
        ";
        $conn->exec($triggerSql);
        echo "Trigger for cascading delete on internships added successfully<br>";


        // 插入 internships 数据
        $sql = "INSERT INTO internships (u_id, company_name, job_title, start_date, end_date, job_description)
        VALUES
            (2, 'ABC Corp', 'Software Developer Intern', '2023-06-01', '2023-08-31', 'Worked on full-stack development for a customer-facing application.')
        ";
        $conn->exec($sql);
        echo "Inserted internships data successfully<br>";

        // 创建 extracurricular_activities 表
        $sql = "CREATE TABLE IF NOT EXISTS extracurricular_activities (
            activity_id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            activity_name VARCHAR(255),
            position VARCHAR(100),
            start_date DATE,
            end_date DATE,
            activity_description TEXT,
            FOREIGN KEY (u_id) REFERENCES users(u_id)
        )";
        $conn->exec($sql);
        echo "Table 'extracurricular_activities' created successfully<br>";

        // 触发器：当删除 users 表中的记录时，删除 extracurricular_activities 中相关的记录
        $triggerSql = "
        

        CREATE TRIGGER delete_extracurricular_activities_on_user_delete
        AFTER DELETE ON users
        FOR EACH ROW
        BEGIN
            DELETE FROM extracurricular_activities WHERE u_id = OLD.u_id;
        END ;
        ";
        $conn->exec($triggerSql);
        echo "Trigger for cascading delete on extracurricular_activities added successfully<br>";

        //  extracurricular_activities 
        $sql = "INSERT INTO extracurricular_activities (u_id, activity_name, position, start_date, end_date, activity_description)
        VALUES
            (2, 'Tech Club', 'President', '2021-09-01', '2023-06-30', 'Led a team organizing workshops and coding competitions.')
        ";
        $conn->exec($sql);
        echo "Inserted extracurricular_activities data successfully<br>";

        // applications table:
        $sql = "CREATE TABLE applications (
            application_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,  -- 外键，指向用户（求职者）
            job_id INT NOT NULL,   -- 外键，指向职位表
            resume_url VARCHAR(255),   -- 求职者简历的 URL（改为存储 URL）
            status ENUM('applied', 'resume_viewed', 'interview', 'offer', 'rejected'),
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(u_id) ON DELETE CASCADE,
            FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
        ) ";
        $conn->exec($sql);
        echo "Table 'Applications' created successfully<br>";


        $sql = "
        CREATE TRIGGER before_insert_application
        BEFORE INSERT ON applications
        FOR EACH ROW
        BEGIN
             -- Validate if the user is a job seeker (assuming user type 3 or 0 represents a job seeker)
            DECLARE user_type INT;
            DECLARE resume_url VARCHAR(255);

            -- Get the user type and resume URL
            SELECT user_type_id, resume INTO user_type, resume_url
            FROM users
            LEFT JOIN jobSeekers ON users.u_id = jobSeekers.u_id
            WHERE users.u_id = NEW.user_id;

            -- Validate user type
            IF user_type NOT IN (3, 0) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'You are not a job seeker, and therefore cannot apply for jobs.';
            END IF;

            -- Validate if the resume is uploaded
            IF resume_url IS NULL OR resume_url = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'You have not uploaded your resume yet. Please update your profile and upload your resume before applying for jobs.';
            END IF;

            -- Check if the user has already applied for the job
            IF EXISTS (SELECT 1 FROM applications WHERE job_id = NEW.job_id AND user_id = NEW.user_id) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'You have already applied for this job.';
            END IF;

        END;";

    $conn->exec($sql);
    echo "Trigger 'before_insert_application' created successfully<br>";
        }
     
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// 关闭连接
$conn = null;
?>
