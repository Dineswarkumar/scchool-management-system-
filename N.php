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

// Get student ID
$stmt = $conn->prepare("SELECT id, name FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student_data) {
    header("Location: login.php?error=Student record not found");
    exit();
}

$student_id = $student_data['id'];

// Get notifications for student's enrolled courses
$stmt = $conn->prepare("
    SELECT DISTINCT
        n.id,
        n.title,
        n.message,
        n.type,
        n.created_at,
        c.course_name,
        c.course_code,
        t.name as teacher_name
    FROM notifications n
    LEFT JOIN courses c ON n.course_id = c.id
    LEFT JOIN teachers t ON n.teacher_id = t.id
    WHERE n.course_id IS NULL 
       OR n.course_id IN (SELECT course_id FROM enrollments WHERE student_id = ?)
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count notifications by type
$type_counts = [
    'all' => count($notifications),
    'assignment' => 0,
    'grade' => 0,
    'announcement' => 0,
    'unread' => 0
];

foreach ($notifications as $notif) {
    if (isset($type_counts[$notif['type']])) {
        $type_counts[$notif['type']]++;
    }
}

$conn->close();

function getNotificationIcon($type) {
    $icons = [
        'assignment' => '📝',
        'exam' => '📝',
        'grade' => '📊',
        'announcement' => '📢',
        'reminder' => '⏰',
        'urgent' => '🚨'
    ];
    return $icons[$type] ?? '📋';
}

function getIconClass($type) {
    $classes = [
        'assignment' => 'icon-assignment',
        'exam' => 'icon-assignment',
        'grade' => 'icon-grade',
        'announcement' => 'icon-announcement',
        'reminder' => 'icon-reminder',
        'urgent' => 'icon-announcement'
    ];
    return $classes[$type] ?? 'icon-announcement';
}

function getCategoryClass($type) {
    $classes = [
        'assignment' => 'category-assignment',
        'exam' => 'category-assignment',
        'grade' => 'category-grade',
        'announcement' => 'category-announcement',
        'reminder' => 'category-reminder',
        'urgent' => 'category-announcement'
    ];
    return $classes[$type] ?? 'category-announcement';
}

function getTimeAgo($datetime) {
    $now = time();
    $ago = strtotime($datetime);
    $diff = $now - $ago;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    return date('M d, Y', $ago);
}
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Notifications - My School</title>
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
        <a href="grades.php">Grades</a><br>
        <a href="materials.php">Materials</a><br>
        <a href="settings.html">Settings</a><br><br><br><br><br>
        <a href="logout.php"><img src="logout.jpg"style="height:40px; width: 40px;">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="page-title">Notifications</h2>
        <p style="color: #666;">Stay updated with your academic activities</p>

        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterNotifications('all')">All (<?php echo $type_counts['all']; ?>)</button>
            <button class="filter-tab" onclick="filterNotifications('assignment')">Assignments (<?php echo $type_counts['assignment']; ?>)</button>
            <button class="filter-tab" onclick="filterNotifications('grade')">Grades (<?php echo $type_counts['grade']; ?>)</button>
            <button class="filter-tab" onclick="filterNotifications('announcement')">Announcements (<?php echo $type_counts['announcement']; ?>)</button>
        </div>

        <div class="notification-list">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="notification-item" data-type="<?php echo $notif['type']; ?>">
                    <div class="notification-icon <?php echo getIconClass($notif['type']); ?>">
                        <?php echo getNotificationIcon($notif['type']); ?>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <div>
                                <span class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                            </div>
                            <span class="notification-time"><?php echo getTimeAgo($notif['created_at']); ?></span>
                        </div>
                        <div class="notification-message">
                            <?php echo htmlspecialchars($notif['message']); ?>
                        </div>
                        <div style="margin-top: 8px; display: flex; gap: 10px; align-items: center;">
                            <span class="notification-category <?php echo getCategoryClass($notif['type']); ?>">
                                <?php echo strtoupper($notif['type']); ?>
                            </span>
                            <?php if ($notif['course_name']): ?>
                                <span style="color: #888; font-size: 13px;">
                                    📚 <?php echo htmlspecialchars($notif['course_name']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($notif['teacher_name']): ?>
                                <span style="color: #888; font-size: 13px;">
                                    👤 <?php echo htmlspecialchars($notif['teacher_name']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px; margin-top: 30px; box-shadow: 0 3px 15px rgba(0,0,0,0.1);">
                    <div style="font-size: 64px; margin-bottom: 20px;">🔔</div>
                    <h3 style="color: rgb(230, 139, 21); margin-bottom: 10px;">No Notifications</h3>
                    <p style="color: #666;">You don't have any notifications yet. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterNotifications(type) {
            const items = document.querySelectorAll('.notification-item');
            const tabs = document.querySelectorAll('.filter-tab');
            
            // Update active tab
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter notifications
            items.forEach(item => {
                if (type === 'all' || item.dataset.type === type) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>