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
$stmt = $conn->prepare("SELECT id, name FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher_data) {
    header("Location: login.php?error=Teacher record not found");
    exit();
}

$teacher_id = $teacher_data['id'];

// Get teacher's courses
$stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses WHERE teacher_id = ? ORDER BY course_name");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get sent notifications
$stmt = $conn->prepare("
    SELECT n.*, c.course_name, c.course_code,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = n.course_id) as recipient_count
    FROM notifications n
    LEFT JOIN courses c ON n.course_id = c.id
    WHERE n.teacher_id = ?
    ORDER BY n.created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$sent_notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Notifications - My School</title>
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
        <a href="tuploads.php">📤 Upload Materials</a>
        <a href="TN.php">🔔 Notifications</a>
        <a href="logout.php" style="margin-top:auto;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2 class="page-title">Notifications & Announcements</h2>
        <p style="color: var(--text-secondary);">Send announcements to students and manage notifications</p>

        <?php if(isset($_GET['success'])): ?>
        <div class="success-message" style="display: block; background-color: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            ✓ <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
        <div class="error-message" style="display: block; background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            ✗ <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('send')">📤 Send Announcement</button>
            <button class="tab-btn" onclick="showTab('sent')">📨 Sent Notifications</button>
        </div>

        <div id="send" class="tab-content active">
            <div class="form-card">
                <h3 style="color: var(--primary); margin-top: 0;">Create New Announcement</h3>
                <form action="send_notification.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">Notification Type *</label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="announcement">General Announcement</option>
                            <option value="assignment">Assignment</option>
                            <option value="exam">Exam/Quiz Notice</option>
                            <option value="reminder">Reminder</option>
                            <option value="urgent">Urgent Notice</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Send To *</label>
                        <select name="send_to" class="form-control" required onchange="toggleCourseSelect(this)">
                            <option value="">Select Recipients</option>
                            <option value="all_students">All My Students</option>
                            <option value="specific_course">Specific Course</option>
                        </select>
                    </div>

                    <div class="form-group" id="course_select" style="display: none;">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" class="form-control">
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?> (<?php echo htmlspecialchars($course['course_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter notification title" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" placeholder="Enter your message here..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notification Methods</label>
                        <div class="checkbox-group">
                            <label style="color: var(--text-secondary); margin-right: 15px;">
                                <input type="checkbox" name="notify_portal" checked>
                                Portal Notification
                            </label>
                            <label style="color: var(--text-secondary);">
                                <input type="checkbox" name="notify_email">
                                Email Notification
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">📤 Send Announcement</button>
                </form>
            </div>
        </div>

        <div id="sent" class="tab-content">
            <div class="notification-list">
                <?php if (count($sent_notifications) > 0): ?>
                    <?php foreach ($sent_notifications as $notif): ?>
                    <div class="notification-item">
                        <div class="notification-header">
                            <div>
                                <span class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                                <span class="type-badge"><?php echo strtoupper($notif['type']); ?></span>
                            </div>
                            <span class="notification-time"><?php echo date('M d, Y g:i A', strtotime($notif['created_at'])); ?></span>
                        </div>
                        <div class="notification-message">
                            <?php echo htmlspecialchars($notif['message']); ?>
                        </div>
                        <div class="notification-meta">
                            <?php if ($notif['course_name']): ?>
                                <span>📚 <?php echo htmlspecialchars($notif['course_name']); ?></span>
                            <?php else: ?>
                                <span>👥 All Students</span>
                            <?php endif; ?>
                            <span>👥 <?php echo $notif['recipient_count'] ?: 'All'; ?> recipients</span>
                            <span>📱 Portal</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        No notifications sent yet. Create your first announcement above!
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function toggleCourseSelect(select) {
            const courseSelect = document.getElementById('course_select');
            if (select.value === 'specific_course') {
                courseSelect.style.display = 'block';
            } else {
                courseSelect.style.display = 'none';
            }
        }
    </script>
</body>
</html>