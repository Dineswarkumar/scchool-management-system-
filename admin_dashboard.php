<?php
require_once 'config.php';
require_once 'auth_check.php';

// Only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Admin access required");
    exit();
}

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total users
$stats['users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$stats['students'] = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$stats['teachers'] = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];

// Total courses
$stats['courses'] = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];

// Total enrollments
$stats['enrollments'] = $conn->query("SELECT COUNT(*) as count FROM enrollments")->fetch_assoc()['count'];

// Students without enrollments
$stats['unenrolled'] = $conn->query("
    SELECT COUNT(*) as count FROM students s
    WHERE NOT EXISTS (SELECT 1 FROM enrollments e WHERE e.student_id = s.id)
")->fetch_assoc()['count'];

// Recent registrations
$recent_users = $conn->query("
    SELECT u.username, u.role, u.created_at, 
           COALESCE(s.name, t.name) as name
    FROM users u
    LEFT JOIN students s ON u.id = s.user_id
    LEFT JOIN teachers t ON u.id = t.user_id
    ORDER BY u.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Courses with no teacher
$unassigned_courses = $conn->query("
    SELECT COUNT(*) as count FROM courses WHERE teacher_id IS NULL
")->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        
        .header {
            background: linear-gradient(135deg, rgb(230, 139, 21), #f7c873);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 { font-size: 32px; margin-bottom: 5px; }
        .header p { opacity: 0.9; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid rgb(230, 139, 21);
        }
        
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; }
        .stat-card .number { font-size: 36px; font-weight: bold; color: rgb(230, 139, 21); }
        .stat-card .label { color: #888; font-size: 14px; margin-top: 5px; }
        
        .warning-card { border-left-color: #FF9800; }
        .warning-card .number { color: #FF9800; }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .action-btn .icon { font-size: 32px; }
        .action-btn .text h4 { color: rgb(230, 139, 21); margin-bottom: 5px; }
        .action-btn .text p { color: #666; font-size: 13px; }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title { color: rgb(230, 139, 21); font-size: 20px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        
        .user-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-item:last-child { border-bottom: none; }
        
        .user-info h4 { color: #333; margin-bottom: 5px; }
        .user-info p { color: #888; font-size: 13px; }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .role-student { background: #e3f2fd; color: #1976d2; }
        .role-teacher { background: #f3e5f5; color: #7b1fa2; }
        .role-admin { background: #fff3e0; color: #f57c00; }
        
        .logout-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #f44336;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
            font-weight: bold;
        }
        
        .logout-btn:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <p>Manage your school management system</p>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $stats['users']; ?></div>
                <div class="label">All system users</div>
            </div>
            
            <div class="stat-card">
                <h3>Students</h3>
                <div class="number"><?php echo $stats['students']; ?></div>
                <div class="label">Registered students</div>
            </div>
            
            <div class="stat-card">
                <h3>Teachers</h3>
                <div class="number"><?php echo $stats['teachers']; ?></div>
                <div class="label">Active teachers</div>
            </div>
            
            <div class="stat-card">
                <h3>Courses</h3>
                <div class="number"><?php echo $stats['courses']; ?></div>
                <div class="label">Total courses</div>
            </div>
            
            <div class="stat-card">
                <h3>Enrollments</h3>
                <div class="number"><?php echo $stats['enrollments']; ?></div>
                <div class="label">Active enrollments</div>
            </div>
            
            <?php if ($stats['unenrolled'] > 0): ?>
            <div class="stat-card warning-card">
                <h3>Unenrolled Students</h3>
                <div class="number"><?php echo $stats['unenrolled']; ?></div>
                <div class="label">Need enrollment</div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="quick-actions">
            <a href="admin_enroll.php" class="action-btn">
                <div class="icon">👥</div>
                <div class="text">
                    <h4>Enroll Students</h4>
                    <p>Assign courses to students</p>
                </div>
            </a>
            
            <a href="admin_users.php" class="action-btn">
                <div class="icon">👤</div>
                <div class="text">
                    <h4>Manage Users</h4>
                    <p>View and edit users</p>
                </div>
            </a>
            
            <a href="admin_courses.php" class="action-btn">
                <div class="icon">📚</div>
                <div class="text">
                    <h4>Manage Courses</h4>
                    <p>Add, edit, delete courses</p>
                </div>
            </a>
            
            <a href="admin_grades.php" class="action-btn">
                <div class="icon">📊</div>
                <div class="text">
                    <h4>Grade Entry</h4>
                    <p>Enter or modify grades</p>
                </div>
            </a>
            
            <a href="admin_reports.php" class="action-btn">
                <div class="icon">📈</div>
                <div class="text">
                    <h4>Reports</h4>
                    <p>View analytics and reports</p>
                </div>
            </a>
            
            <a href="admin_settings.php" class="action-btn">
                <div class="icon">⚙️</div>
                <div class="text">
                    <h4>Settings</h4>
                    <p>System configuration</p>
                </div>
            </a>
        </div>
        
        <div class="content-section">
            <h3 class="section-title">Recent Registrations</h3>
            
            <?php if (count($recent_users) > 0): ?>
                <?php foreach ($recent_users as $user): ?>
                <div class="user-item">
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user['name'] ?: $user['username']); ?></h4>
                        <p>Username: <?php echo htmlspecialchars($user['username']); ?> • 
                           Registered: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <span class="role-badge role-<?php echo $user['role']; ?>">
                        <?php echo $user['role']; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #666;">No recent registrations</p>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="logout.php" class="logout-btn">Logout</a>
</body>
</html>