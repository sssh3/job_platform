<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
    <style>
            input[type="submit"] {
                width : 60%;
                visibility: hidden;
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
        <div class="filter-options">
            <h2>Filters</h2>
            <!-- Add your filter options here -->
            
            <form class="filter-form">
                <label>Location:</label>
                <input type="text" id="search-country" name="search-country" placeholder="select country/region" autocomplete="off"><br>
                <div id="dropdown-country"></div>
                <script src="/job_platform/assets/js/searchCountry.js"></script>
                <br>
                <input type="text" id="search-province" name="search-province" placeholder="select province/state" autocomplete="off"><br>
                <div id="dropdown-province"></div>
                <script src="/job_platform/assets/js/searchProvince.js"></script>
                <br>
                <input type="text" id="search-city" name="search-city" placeholder="select city" autocomplete="off"><br>
                <div id="dropdown-city"></div>
                <script src="/job_platform/assets/js/searchCity.js"></script>
                <br>
                <p id="test"></p>

                <label for="job-type">Job Type:</label>
                <select id="job-type" name="job-type">
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                    <option value="internship">Internship</option>
                </select>
                <br>
                <br>

                <label>Job Title:</label>
                <input type="text" id="search-title" name="search-title" placeholder="search in job titles" autocomplete="off"><br>
                <input type="submit" value="Apply Filters"></input>
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
            <script src="/job_platform/assets/js/jobList.js"></script>
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