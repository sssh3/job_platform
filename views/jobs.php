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
            <h2 style="text-align: center;">Filters</h2>
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
                

                <label for="job-type">Job Type:</label>
                <select id="job-type" name="job-type">
                    <option value="">select type</option>
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

            <p id="matches"></p>
            <p id="filters-sql-time"></p>
        </div>

        <ul id="jobList" class="job-list">
            <!-- example style-->
            <!-- <div class="job-overview">
                <h3 class="job-title">Software Engineer</h3>
                <p class="job-overview-text">
                FULLTIME<br>
                <b>Tailored Environments Corporation</b><br>
                <i>San Francisco, CA</i><br>
                $50,000 -- $80,000
                </p>
            </div> -->
            <script src="/job_platform/assets/js/jobList.js"></script>
        </ul>

        <div id="jobDetails" class="job-details">
            <h3 id="temp-details">Job Details</h3>
            <!-- example style-->
            <!-- <div class="details">
                <h3>Software Engineer</h3>
                <p>Full description of the job goes here...</p>
                <button>Apply Now</button>
                <button>Save for Later</button>
            </div> -->
            <script src="/job_platform/assets/js/jobDetails.js"></script>
            <p id="test"></p>
        </div>
    </div>
</body>
</html>


    <?php include 'footer.php'; ?>
</body>
</html>