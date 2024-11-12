<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>README</title>
    <link rel="stylesheet" href="/job_platform/assets/css/style.css">
</head>
<body>
    <?php include 'header.html'; ?>

    <div id="markdown-content">
        <!-- The converted markdown content will be inserted here -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        console.log(marked.parse('# Hello World'));
        // Fetch the README.md file
        fetch('/job_platform/README.md')
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(markdown => {
                console.log('Markdown content:', markdown);
                // Convert Markdown to HTML
                const htmlContent = marked.parse(markdown);
                // Insert the HTML into the page
                document.getElementById('markdown-content').innerHTML = htmlContent;
            })
            .catch(error => {
                console.error('Error fetching README:', error);
                document.getElementById('markdown-content').innerHTML = '<p>Error loading content.</p>';
            });
    </script>
    <?php include 'footer.html'; ?>
</body>
</html>