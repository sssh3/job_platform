# DMS Project - Job-seeking Platform


## Project Structure
```
index.php: Entry point for the application.
.htaccess: For URL rewriting and other Apache configurations.
/assets: Contains static files like CSS, JavaScript, and images.
    /css: Stylesheets.
    /js: JavaScript files.
    /images: Images.
/views: HTML templates and views.
/config: Configuration files for database connection, settings, etc.
    database.php
    config.php
```

## Version Control Instruction
### Set Up Git
**Install Git:** All team members should have Git installed. You can download it from `git-scm.com`.
```
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### Project Clone
```
git clone https://github.com/sssh3/dms_job_platform
```

### Make Your Own Change
Please keep the `main` branch as the stable version.  
Each team member should create their own branch for new features or bug fixes. This can be done using:
```
git checkout -b feature-branch-name
git pull # keep updated with the main branch
```

### Push Your Change
1. Make sure you are on your branch. Use `git branch` to check, `git checkout your-branch-name` to switch branch.

2. Commit changes using 
```
git add .
git commit -m "Your descriptive commit message"
```
You can see the status with `git status`.

3. Push your branch to remote by `git push origin your-branch-name`

