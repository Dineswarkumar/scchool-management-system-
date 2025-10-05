<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is a student
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php?error=Access denied");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get student information
$stmt = $conn->prepare("SELECT s.*, u.username FROM students s 
                        JOIN users u ON s.user_id = u.id 
                        WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: login.php?error=Student record not found");
    exit();
}

$student_id = $student['id'];
$student_name = $student['name'];

// Get enrolled courses count
$stmt = $conn->prepare("SELECT COUNT(*) as course_count FROM enrollments WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$course_count = $stmt->get_result()->fetch_assoc()['course_count'];
$stmt->close();

// Get pending assignments count (for demo, using random number)
$pending_assignments = 3;

// Calculate average grade
$stmt = $conn->prepare("SELECT AVG(marks) as avg_marks FROM grades WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$avg_grade = $result['avg_marks'] ? round($result['avg_marks'], 0) : 0;
$stmt->close();

// Calculate attendance percentage
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
    FROM attendance 
    WHERE student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attendance_data = $stmt->get_result()->fetch_assoc();
$attendance_percent = $attendance_data['total'] > 0 
    ? round(($attendance_data['present'] / $attendance_data['total']) * 100) 
    : 94;
$stmt->close();

// Get enrolled courses with details
$stmt = $conn->prepare("
    SELECT c.course_name, c.course_code, t.name as teacher_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    WHERE e.student_id = ?
    ORDER BY c.course_name
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get recent notifications
$notifications = [];
$stmt = $conn->prepare("SELECT title, message, type, created_at 
                        FROM notifications 
                        ORDER BY created_at DESC 
                        LIMIT 3");
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Dashboard - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="d.css">
</head>
<body>
    <header>
        <<div class="h-container">
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
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <p>Here's what's happening with your courses today.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📚 Active Courses</h3>
                <div class="stat-number"><?php echo $course_count; ?></div>
                <p>Currently enrolled</p>
            </div>

            <div class="card">
                <h3>✅ Assignments Due</h3>
                <div class="stat-number"><?php echo $pending_assignments; ?></div>
                <p>This week</p>
            </div>

            <div class="card">
                <h3>📊 Average Grade</h3>
                <div class="stat-number"><?php echo $avg_grade; ?>%</div>
                <p>Overall performance</p>
            </div>

            <div class="card">
                <h3>🎯 Attendance</h3>
                <div class="stat-number"><?php echo $attendance_percent; ?>%</div>
                <p>This semester</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card" style="grid-column: span 1;">
                <h3>📅 My Courses</h3>
                <ul class="upcoming-list">
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($course['course_name']); ?></strong>
                                <br><small><?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['teacher_name']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No courses enrolled yet</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="card" style="grid-column: span 1;">
                <h3>🔔 Recent Announcements</h3>
                <ul class="upcoming-list">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <li>
                                <span class="date-badge"><?php echo date('M d', strtotime($notif['created_at'])); ?></span>
                                <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                <br><small><?php echo htmlspecialchars(substr($notif['message'], 0, 60)) . '...'; ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No recent announcements</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>