<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    header("Location: login.php?error=Access denied");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get teacher information
$stmt = $conn->prepare("SELECT id, name, subject FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    header("Location: login.php?error=Teacher record not found");
    exit();
}

$teacher_id = $teacher['id'];
$teacher_name = $teacher['name'];

// Get total courses taught
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM courses WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_courses = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get total students across all courses
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT e.student_id) as total
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get pending grades (courses with enrolled students but no grades yet)
$stmt = $conn->prepare("
    SELECT COUNT(*) as pending
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN grades g ON g.student_id = e.student_id AND g.course_id = e.course_id
    WHERE c.teacher_id = ? AND g.id IS NULL
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$pending_grades = $stmt->get_result()->fetch_assoc()['pending'];
$stmt->close();

// Get uploaded materials count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM uploads WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_uploads = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get teacher's courses with student counts
$stmt = $conn->prepare("
    SELECT 
        c.id,
        c.course_name,
        c.course_code,
        c.credits,
        c.semester,
        COUNT(e.student_id) as student_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    WHERE c.teacher_id = ?
    GROUP BY c.id
    ORDER BY c.course_name
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get recent notifications sent
$stmt = $conn->prepare("
    SELECT title, message, type, created_at
    FROM notifications
    WHERE teacher_id = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$recent_notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get recent uploads
$stmt = $conn->prepare("
    SELECT u.title, u.uploaded_at, c.course_name
    FROM uploads u
    LEFT JOIN courses c ON u.course_id = c.id
    WHERE u.teacher_id = ?
    ORDER BY u.uploaded_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$recent_uploads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Teacher Dashboard - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="t.css">
</head>
<body>
    <header>
        <div class="h-container">
            <img id="logo" src="logo.jpg" alt="Logo">
            <h1>My school</h1>
            <nav class="nav-links">
             <a href="TN.php"><img src="n.jpg" style="height:40px; width: 40px; border-radius: 50%;"><br>Notify</a>
             <a href="teacher_profile.php"><img src="a.jpg" style="height:40px; width: 40px; border-radius: 50%;"><br>Profile</a>
            </nav>
        </div>
    </header>
    <hr style="border: 5px solid rgb(5, 5, 5);">
    <div class="v-line" style="border-left: 5px solid rgb(5, 5, 5); height: 100%; position: absolute; left: 200px;"></div>
    <div class="sidenav">
        <a href="teacher_dash.php">Dashboard</a><br>
        <a href="teacher_courses.php">My Courses</a><br>
        <a href="tuploads.php">Upload Materials</a><br>
        <a href="TN.php">Notifications</a><br>
        <br><br><br><br>
        <a href="logout.php"><img src="logout.jpg"style="height:40px; width: 40px;">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo htmlspecialchars(explode(' ', $teacher_name)[0]); ?>!</h2>
            <p>Here's an overview of your classes and activities today.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Courses</h3>
                <div class="stat-number"><?php echo $total_courses; ?></div>
                <p class="stat-label">Active this semester</p>
            </div>

            <div class="stat-card">
                <h3>Total Students</h3>
                <div class="stat-number"><?php echo $total_students; ?></div>
                <p class="stat-label">Across all courses</p>
            </div>

            <div class="stat-card">
                <h3>Pending Grades</h3>
                <div class="stat-number"><?php echo $pending_grades; ?></div>
                <p class="stat-label">Students without grades</p>
            </div>

            <div class="stat-card">
                <h3>Materials Uploaded</h3>
                <div class="stat-number"><?php echo $total_uploads; ?></div>
                <p class="stat-label">Total files</p>
            </div>
        </div>

        <div class="quick-actions">
            <a href="tuploads.php" class="quick-action-btn">
                <span>📤</span>
                Upload Materials
            </a>
            <a href="TN.php" class="quick-action-btn">
                <span>📢</span>
                Send Announcement
            </a>
            <a href="teacher_courses.php" class="quick-action-btn">
                <span>📚</span>
                View Courses
            </a>
            <a href="teacher_students.php" class="quick-action-btn">
                <span>👥</span>
                View Students
            </a>
        </div>

        <div class="content-grid" style="margin-top: 30px;">
            <div class="content-card">
                <h3 class="card-title">My Courses</h3>
                
                <?php if (count($courses) > 0): ?>
                    <?php foreach ($courses as $course): ?>
                    <div class="class-item">
                        <h4>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                            <span class="student-count"><?php echo $course['student_count']; ?> students</span>
                        </h4>
                        <p>📚 <?php echo htmlspecialchars($course['course_code']); ?></p>
                        <p>🎓 <?php echo $course['credits']; ?> credits • <?php echo htmlspecialchars($course['semester']); ?></p>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="window.location.href='course_details.php?id=<?php echo $course['id']; ?>'">View Details</button>
                            <button class="btn btn-secondary" onclick="window.location.href='tuploads.php'">Upload Materials</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #666;">
                        No courses assigned yet. Contact administration to get courses assigned.
                    </p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <h3 class="card-title">Recent Activity</h3>
                
                <?php if (count($recent_notifications) > 0 || count($recent_uploads) > 0): ?>
                    
                    <?php if (count($recent_uploads) > 0): ?>
                        <h4 style="color: rgb(230, 139, 21); font-size: 16px; margin: 15px 0 10px 0;">📤 Recent Uploads</h4>
                        <?php foreach ($recent_uploads as $upload): ?>
                        <div class="schedule-item">
                            <h4><?php echo htmlspecialchars($upload['title']); ?></h4>
                            <p>📚 <?php echo htmlspecialchars($upload['course_name'] ?: 'General'); ?></p>
                            <p style="font-size: 12px; color: #888;">
                                <?php echo date('M d, Y g:i A', strtotime($upload['uploaded_at'])); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (count($recent_notifications) > 0): ?>
                        <h4 style="color: rgb(230, 139, 21); font-size: 16px; margin: 15px 0 10px 0;">📢 Recent Announcements</h4>
                        <?php foreach ($recent_notifications as $notif): ?>
                        <div class="schedule-item">
                            <h4><?php echo htmlspecialchars($notif['title']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($notif['message'], 0, 80)) . '...'; ?></p>
                            <p style="font-size: 12px; color: #888;">
                                <?php echo date('M d, Y g:i A', strtotime($notif['created_at'])); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #666;">
                        No recent activity. Start by uploading materials or sending announcements!
                    </p>
                <?php endif; ?>

                <div style="margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4CAF50;">
                    <h4 style="margin: 0 0 5px 0; color: #2e7d32;">💡 Quick Tips</h4>
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        Upload study materials regularly to keep students engaged. Send announcements for important updates.
                    </p>
                </div>
            </div>
        </div>

        <?php if ($pending_grades > 0): ?>
        <div class="content-card" style="margin-top: 20px; border-left: 4px solid #FF9800;">
            <h3 class="card-title">⚠️ Action Required</h3>
            <p style="color: #666;">
                You have <strong><?php echo $pending_grades; ?> student(s)</strong> without grades. 
                Consider entering grades to keep students updated on their progress.
            </p>
            <button class="btn btn-primary" style="margin-top: 10px;">Enter Grades</button>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>