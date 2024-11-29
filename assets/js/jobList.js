document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('.filter-form');

    // Attach event listeners to the form for input changes
    const filterInput1 = document.querySelector('#dropdown-country');
    filterInput1.addEventListener('click', handleFormChange);

    const filterInput2 = document.querySelector('#dropdown-province');
    filterInput2.addEventListener('click', handleFormChange);

    const filterInput3 = document.querySelector('#dropdown-city');
    filterInput3.addEventListener('click', handleFormChange);

    const filterInput4 = document.querySelector('#job-type');
    filterInput4.addEventListener('input', handleFormChange);

    const filterInput5 = document.querySelector('#search-title');
    filterInput5.addEventListener('input', handleFormChange);


    // Function to handle form change and fetch updated job list
    function handleFormChange() {
        // Serialize the form data
        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString();

        // Fetch filtered job data based on form inputs
        fetchJobs(queryString);
    }

    // Define the function to fetch job data
    function fetchJobs(queryString = '') {
        // const test_str = document.getElementById("test");
        // test_str.textContent = `/job_platform/utils/filter_jobs.php?${queryString}`;
        // Fetch job data from the server with query parameters
        fetch(`/job_platform/utils/filter_jobs.php?${queryString}`)
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
                matches.textContent = 'Found ' + data[0].count + ' jobs';
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }

    // Define the function to populate the job list
    function populateJobList(jobs) {
        const jobList = document.getElementById('jobList');
        
        // Clear existing job items
        jobList.innerHTML = '';

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
            `;
            jobList.appendChild(jobItem);
        });
    }

    // Initial fetch of jobs when the page is loaded
    fetchJobs();
});
