<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <?php if (isset($_SESSION["msg"])) {
        $msg = $_SESSION["msg"];
        UNSET($_SESSION["msg"]);
        echo "<p> $msg </p>";
    } ?>
    

    <div class="container">
        <div class="filter-options">
            <h2>Filter Options</h2>
            <!-- Add your filter options here -->
            <form>
                <label for="location">Location:</label>
                <input type="text" id="location" name="location"><br>

                <label for="type">Job Type:</label>
                <select id="type" name="type">
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                </select>
                <br>

                <button type="submit">Apply Filters</button>
            </form>
        </div>

        <ul id="jobList" class="job-list">
            <li>
                <div class="job-title">Software Engineer</div>
                <div class="job-details">
                    <p>Location: San Francisco, CA</p>
                    <p>Description: Responsible for developing and maintaining web applications.</p>
                    <a href="#" target="_blank">Apply Now</a>
                </div>
            </li>
            <li>
                <div class="job-title">Data Scientist</div>
                <div class="job-details">
                    <p>Location: New York, NY</p>
                    <p>Description: Analyze data to provide insights and drive business decisions.</p>
                    <a href="#" target="_blank">Apply Now</a>
                </div>
            </li>
            <li>
                <div class="job-title">Product Manager</div>
                <div class="job-details">
                    <p>Location: Remote</p>
                    <p>Description: Lead product development and strategy execution.</p>
                    <a href="#" target="_blank">Apply Now</a>
                </div>
            </li>
            <!-- Add more job positions here -->
        </ul>

        <div class="job-details">
            <h2>Job Details</h2>
            <!-- Add detailed description and buttons here -->
            <div class="details">
                <h3>Software Engineer</h3>
                <p>Full description of the job goes here...</p>
                <button>Apply Now</button>
                <button>Save for Later</button>
            </div>
        </div>
    </div>
</body>
</html>


    <?php include 'footer.php'; ?>
</body>
</html>