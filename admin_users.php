<?php
require_once 'config.php';
require_once 'auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Admin access required");
    exit();
}

$conn = getDBConnection();

// Handle user deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Don't allow deleting yourself
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "User deleted successfully";
    } else {
        $error = "Cannot delete your own account";
    }
}

// Get all users with details
$users = $conn->query("
    SELECT u.id, u.username, u.role, u.created_at,
           COALESCE(s.name, t.name) as name,
           COALESCE(s.email, t.email) as email,
           COALESCE(s.phone, t.phone) as phone,
           s.student_id
    FROM users u
    LEFT JOIN students s ON u.id = s.user_id
    LEFT JOIN teachers t ON u.id = t.user_id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        
        .header {
            background: linear-gradient(135deg, rgb(230, 139, 21), #f7c873);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 { font-size: 28px; }
        .back-link { color: white; text-decoration: none; display: inline-block; margin-bottom: 10px; opacity: 0.9; }
        .back-link:hover { opacity: 1; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        
        .success { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        
        .stats { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); flex: 1; text-align: center; }
        .stat-box .number { font-size: 32px; font-weight: bold; color: rgb(230, 139, 21); }
        .stat-box .label { color: #666; margin-top: 5px; }
        
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 15px; text-align: left; font-weight: bold; color: #333; border-bottom: 2px solid #ddd; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f9f9f9; }
        
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
        
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; display: inline-block; }
        .btn-danger { background: #f44336; color: white; }
        .btn-danger:hover { background: #d32f2f; }
        .btn-primary { background: rgb(230, 139, 21); color: white; }
        .btn-primary:hover { background: rgb(200, 110, 15); }
    </style>
</head>
<body>
    <div class="header">
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Manage Users</h1>
    </div>
    
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-box">
                <div class="number"><?php echo count(array_filter($users, fn($u) => $u['role'] == 'student')); ?></div>
                <div class="label">Students</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo count(array_filter($users, fn($u) => $u['role'] == 'teacher')); ?></div>
                <div class="label">Teachers</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo count(array_filter($users, fn($u) => $u['role'] == 'admin')); ?></div>
                <div class="label">Admins</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo count($users); ?></div>
                <div class="label">Total Users</div>
            </div>
        </div>
        
        <div class="table-container">
            <h3 style="margin-bottom: 20px; color: rgb(230, 139, 21);">All Users</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['name'] ?: 'N/A'); ?></strong>
                            <?php if ($user['student_id']): ?>
                                <br><small style="color: #888;"><?php echo htmlspecialchars($user['student_id']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=1&id=<?php echo $user['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                    Delete
                                </a>
                            <?php else: ?>
                                <span style="color: #999; font-size: 12px;">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>