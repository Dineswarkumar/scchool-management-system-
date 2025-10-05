<!DOCTYPE html>
<html>  
<head>
    <title>Courses - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="d.css">
</head>
<body>
    <header>
    <div class="h-container">
            <img id="logo" src="logo.jpg" alt="Logo">
            <h1>My school</h1>
            <nav class="nav-links">
             <a href="N.php"><img src="n.jpg" style="height:40px; width: 40px; border-radius: 50%;"><br>Notify</a>
             <a href="A.php"><img src="a.jpg" style="height:40px; width: 40px; border-radius: 50%;"><br>User</a>
            </nav>
        </div>
    </header>
    <hr style="border: 5px solid rgb(5, 5, 5);">
    <div class="sidenav">
        <a href="dash.php">Dashboard</a><br>
        <a href="subjects.php">Courses</a><br>
        <a href="materials.php">Materials</a><br>
        <a href="grades.php">Grades</a><br>
        <a href="settings.html">Settings</a><br><br><br><br><br><br><br><br><br>
        <a href="logout.php"><img src="logout.jpg"style="height:40px; width: 40px;">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="page-title">My Courses</h2>
        <p style="color: #666;">Manage and track your enrolled courses</p>

        <div class="courses-grid">
            <div class="course-card">
                <div class="course-header">
                    <div class="course-icon">📐</div>
                    <div>
                        <h3 class="course-title">Mathematics</h3>
                        <p class="course-code">MATH-101</p>
                    </div>
                </div>
                <div class="course-info">
                    <div class="info-row">
                        <span class="info-label">Instructor:</span>
                        <span class="info-value">Dr. Smith</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Schedule:</span>
                        <span class="info-value">Mon, Wed 10:00 AM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Progress:</span>
                        <span class="info-value">75%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 75%;"></div>
                </div>
                <div class="course-actions">
                    <button class="btn btn-primary">View Details</button>
                    <button class="btn btn-secondary">Materials</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-header">
                    <div class="course-icon">⚗️</div>
                    <div>
                        <h3 class="course-title">Physics</h3>
                        <p class="course-code">PHYS-201</p>
                    </div>
                </div>
                <div class="course-info">
                    <div class="info-row">
                        <span class="info-label">Instructor:</span>
                        <span class="info-value">Prof. Johnson</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Schedule:</span>
                        <span class="info-value">Tue, Thu 2:00 PM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Progress:</span>
                        <span class="info-value">68%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 68%;"></div>
                </div>
                <div class="course-actions">
                    <button class="btn btn-primary">View Details</button>
                    <button class="btn btn-secondary">Materials</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-header">
                    <div class="course-icon">📚</div>
                    <div>
                        <h3 class="course-title">English Literature</h3>
                        <p class="course-code">ENG-301</p>
                    </div>
                </div>
                <div class="course-info">
                    <div class="info-row">
                        <span class="info-label">Instructor:</span>
                        <span class="info-value">Ms. Williams</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Schedule:</span>
                        <span class="info-value">Mon, Fri 1:00 PM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Progress:</span>
                        <span class="info-value">82%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 82%;"></div>
                </div>
                <div class="course-actions">
                    <button class="btn btn-primary">View Details</button>
                    <button class="btn btn-secondary">Materials</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-header">
                    <div class="course-icon">🧪</div>
                    <div>
                        <h3 class="course-title">Chemistry</h3>
                        <p class="course-code">CHEM-102</p>
                    </div>
                </div>
                <div class="course-info">
                    <div class="info-row">
                        <span class="info-label">Instructor:</span>
                        <span class="info-value">Dr. Brown</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Schedule:</span>
                        <span class="info-value">Wed, Fri 9:00 AM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Progress:</span>
                        <span class="info-value">70%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 70%;"></div>
                </div>
                <div class="course-actions">
                    <button class="btn btn-primary">View Details</button>
                    <button class="btn btn-secondary">Materials</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-header">
                    <div class="course-icon">🌍</div>
                    <div>
                        <h3 class="course-title">Geography</h3>
                        <p class="course-code">GEO-105</p>
                    </div>
                </div>
                <div class="course-info">
                    <div class="info-row">
                        <span class="info-label">Instructor:</span>
                        <span class="info-value">Mr. Davis</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Schedule:</span>
                        <span class="info-value">Tue, Thu 11:00 AM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Progress:</span>
                        <span class="info-value">90%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 90%;"></div>
                </div>
                <div class="course-actions">
                    <button class="btn btn-primary">View Details</button>
                    <button class="btn btn-secondary">Materials</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-header">
                    <div class="course-icon">💻</div>
                    <div>
                        <h3 class="course-title">Computer Science</h3>
                        <p class="course-code">CS-201</p>
                    </div>
                </div>
                <div class="course-info">
                    <div class="info-row">
                        <span class="info-label">Instructor:</span>
                        <span class="info-value">Prof. Anderson</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Schedule:</span>
                        <span class="info-value">Mon, Wed 3:00 PM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Progress:</span>
                        <span class="info-value">85%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 85%;"></div>
                </div>
                <div class="course-actions">
                    <button class="btn btn-primary">View Details</button>
                    <button class="btn btn-secondary">Materials</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>