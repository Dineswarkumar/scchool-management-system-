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
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <header>
        <div class="h-container">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <img id="logo" src="logo.jpg" alt="Logo">
            <h1>My School</h1>
            <nav class="nav-links">
                <a href="TN.php"><img src="n.jpg" style="height:36px;width:36px;border-radius:50%;" alt="Notify"><br>Notify</a>
                <a href="teacher_profile.php"><img src="a.jpg" style="height:36px;width:36px;border-radius:50%;" alt="Profile"><br>Profile</a>
            </nav>
        </div>
    </header>
    <div class="sidenav" id="sidenav">
        <a href="teacher_dash.php">🏠 Dashboard</a>
        <a href="teacher_courses.php">📚 My Courses</a>
        <a href="tuploads.php">📤 Upload Materials</a>
        <a href="TN.php">🔔 Notifications</a>
        <a href="logout.php" style="margin-top:auto;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo htmlspecialchars(explode(' ', $teacher_name)[0]); ?>!</h2>
            <p>Here's an overview of your classes and activities today.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>🏫 Total Courses</h3>
                <div class="stat-number"><?php echo $total_courses; ?></div>
                <p>Active this semester</p>
            </div>
            <div class="card">
                <h3>👥 Total Students</h3>
                <div class="stat-number"><?php echo $total_students; ?></div>
                <p>Across all courses</p>
            </div>
            <div class="card">
                <h3>📝 Pending Grades</h3>
                <div class="stat-number"><?php echo $pending_grades; ?></div>
                <p>Students without grades</p>
            </div>
            <div class="card">
                <h3>📁 Materials</h3>
                <div class="stat-number"><?php echo $total_uploads; ?></div>
                <p>Total files uploaded</p>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
            <a href="tuploads.php" class="btn-primary">📤 Upload Materials</a>
            <a href="TN.php" class="btn-primary">📢 Send Announcement</a>
            <a href="teacher_courses.php" class="btn-secondary">📚 View Courses</a>
            <a href="teacher_students.php" class="btn-secondary">👥 View Students</a>
        </div>

        <div class="dashboard-grid" style="margin-top:1.5rem;">
            <div class="content-card">
                <h3 class="card-title">📚 My Courses</h3>
                <?php if (count($courses) > 0): ?>
                    <ul class="upcoming-list">
                    <?php foreach ($courses as $course): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($course['course_name']); ?>
                                <span class="status-badge status-active" style="float:right;"><?php echo $course['student_count']; ?> students</span>
                            </strong>
                            <small>📚 <?php echo htmlspecialchars($course['course_code']); ?> · <?php echo $course['credits']; ?> credits · <?php echo htmlspecialchars($course['semester']); ?></small>
                            <div style="margin-top:0.6rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <button class="btn-primary" style="padding:0.35rem 0.9rem;font-size:0.82rem;" onclick="window.location.href='course_details.php?id=<?php echo $course['id']; ?>'">👁 View Details</button>
                                <button class="btn-secondary" style="padding:0.35rem 0.9rem;font-size:0.82rem;" onclick="window.location.href='tuploads.php'">📤 Upload</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                        No courses assigned yet. Contact administration.
                    </p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <h3 class="card-title">📋 Recent Activity</h3>
                <?php if (count($recent_notifications) > 0 || count($recent_uploads) > 0): ?>
                    <?php if (count($recent_uploads) > 0): ?>
                        <h4 style="color:var(--primary);font-size:0.9rem;margin:0.8rem 0 0.5rem;">📤 Recent Uploads</h4>
                        <ul class="upcoming-list">
                        <?php foreach ($recent_uploads as $upload): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($upload['title']); ?></strong>
                                <small>📚 <?php echo htmlspecialchars($upload['course_name'] ?: 'General'); ?> · <?php echo date('M d, Y g:i A', strtotime($upload['uploaded_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (count($recent_notifications) > 0): ?>
                        <h4 style="color:var(--primary);font-size:0.9rem;margin:0.8rem 0 0.5rem;">📢 Recent Announcements</h4>
                        <ul class="upcoming-list">
                        <?php foreach ($recent_notifications as $notif): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                <small><?php echo htmlspecialchars(substr($notif['message'], 0, 80)) . '...'; ?></small>
                                <small style="display:block;color:var(--text-muted);margin-top:0.15rem;"><?php echo date('M d, Y g:i A', strtotime($notif['created_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                        No recent activity. Start by uploading materials or sending announcements!
                    </p>
                <?php endif; ?>

                <div style="margin-top:1.2rem;padding:1rem 1.2rem;background:rgba(34,197,94,0.08);border-radius:12px;border-left:4px solid #15803d;">
                    <h4 style="margin:0 0 4px 0;color:#15803d;font-size:0.95rem;">💡 Quick Tips</h4>
                    <p style="margin:0;color:var(--text-secondary);font-size:0.86rem;">Upload study materials regularly to keep students engaged. Send announcements for important updates.</p>
                </div>
            </div>
        </div>

        <?php if ($pending_grades > 0): ?>
        <div class="content-card" style="margin-top:1.5rem; border-left:4px solid #b45309;">
            <h3 class="card-title" style="color:#b45309;">⚠️ Action Required</h3>
            <p style="color:var(--text-secondary);">You have <strong><?php echo $pending_grades; ?> student(s)</strong> without grades. Consider entering grades to keep students updated.</p>
            <button class="btn-primary" style="margin-top:0.75rem;">Enter Grades</button>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidenav = document.getElementById('sidenav');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() {
            hamburgerBtn.classList.toggle('open');
            sidenav.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidenav.classList.contains('open') ? 'hidden' : '';
        }
        hamburgerBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>