document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('jobList');

    if (container) {
        container.addEventListener('click', function(event) {
            // Traverse the event path to find the element with the class 'job-overview'
            let targetElement = event.target;
            while (targetElement && !targetElement.classList.contains('job-overview')) {
                targetElement = targetElement.parentElement;
            }
            
            if (targetElement && targetElement.classList.contains('job-overview')) {
                id = targetElement.id;
                fetchDetails(id);
            }
        });
    }

    // Define the function to fetch job details
    function fetchDetails(jobId) {
        // const test_str = document.getElementById("test");
        // test_str.textContent = `/job_platform/utils/job_details.php?id=${id}`;
        // Fetch job data from the server with query parameters
        fetch(`/job_platform/utils/job_details.php?id=${jobId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json(); // Parse the JSON from the response
            })
            .then(data => {
                showJobDetails(data); // Call function to populate the job list

                
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }

    // Define the function to populate the job list
    function showJobDetails(details) {
        const jobDetails = document.getElementById('jobDetails');

        const oldContainer = document.getElementById('detail-container');
        if (oldContainer) {
            oldContainer.remove();
        }

        const detailContainer = document.createElement('div');
        detailContainer.id = 'detail-container'
        detailContainer.innerHTML = `
                <h2 class="job-title">${details.title}</h2>
                <p class="job-detail-text">
                <b>Job Type:</b> ${details.jobType}<br><br>
                <b>Employer:</b> ${details.employer}<br><br>
                <b>Location:</b> <i>${details.location}</i><br><br>
                <b>Salary Range:</b> $${details.minSalary} -- $${details.maxSalary}
                </p>
                <p>
                <b>Description:</b><br>
                ${details.description}<br><br>
                <b>Requirements:</b><br>
                ${details.requirements}<br><br>
                <b>Benefits:</b><br>
                ${details.benefits}<br><br>
                </p>
                <button onclick="window.location.href='/job_platform/communicate?chat_id=${details.employerId}&job_id=${details.jobId}'">Contact Employer</button>
                <p>SQL time used for details: ${details.sqlTime}s</p>
            `;
        jobDetails.appendChild(detailContainer);

    }

    // For job details in communicate.php
    var params = new URLSearchParams(window.location.search);
    var jobId = params.get('job_id');

    if (jobId) {
        fetchDetails("job-" + jobId)
    }

});
