document.addEventListener("DOMContentLoaded", function() {

    function fetchJobs(companyId = '') {
        // const test_str = document.getElementById("test");
        // test_str.textContent = `/job_platform/utils/filter_jobs.php?${queryString}`;
        // Fetch job data from the server with query parameters
        fetch(`/job_platform/utils/jobs_by_employer.php?company_id=${companyId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json(); // Parse the JSON from the response
            })
            .then(data => {
                populateJobList(data); // Call function to populate the job list

                // Record status
                const sqlTime = document.getElementById('filters-sql-time');
                const matches = document.getElementById('matches');

                sqlTime.textContent = 'Filter SQL time used: ' + data[0].sqlTime + 's';
                matches.textContent = 'Found ' + data[0].count + ' jobs posted by ' + data[0].employer;
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }

    // Define the function to populate the job list
    function populateJobList(jobs) {
        const jobList = document.getElementById('jobs-management-list');
        
        // Clear existing job items
        jobList.innerHTML = `
        <h2>Posted Jobs Management &nbsp;&nbsp;&nbsp;&nbsp;
        <button onclick="window.location.href='/job_platform/views/post_job.php'">Post A New Job</button>
        </h2>
        `;

        // Iterate over each job and create an <div> element
        jobs.forEach(job => {
            const jobItem = document.createElement('div');
            jobItem.className = "job-overview"
            jobItem.id = job.jobId
            jobItem.innerHTML = `
                <h3 class="job-title">${job.title}</h3>
                <p class="job-overview-text">
                ${job.jobType}<br>
                <b>${job.employer}</b><br>
                <i>${job.location}</i><br>
                $${job.minSalary} -- $${job.maxSalary}
                </p>
                <button onclick="window.location.href='/job_platform/utils/delete_job.php?job_id=${job.jobId}'">Delete Post</button>
            `;
            jobList.appendChild(jobItem);
        });

    }

    const obj = document.getElementById('companyId');
    if (obj) {
        const companyId = obj.getAttribute('value');
        // Initial fetch of jobs when the page is loaded
        fetchJobs(companyId);
    } else {
        console.error("Element with ID 'companyId' not found.");
    }
});
