<?php
require_once 'config.php';
require_once 'auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Admin access required");
    exit();
}

$conn = getDBConnection();

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $teacher_id = $_POST['teacher_id'] !== '' ? intval($_POST['teacher_id']) : null;
    $credits = intval($_POST['credits']);
    $semester = trim($_POST['semester']);
    
    $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, teacher_id, credits, semester) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $course_name, $course_code, $teacher_id, $credits, $semester);
    if ($stmt->execute()) {
        $message = "Course added successfully!";
    } else {
        $error = "Failed to add course: " . $stmt->error;
    }
    $stmt->close();
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    $message = "Course deleted successfully";
}

// Get all courses with teacher info and enrollment count
$courses = $conn->query("
    SELECT c.*, t.name as teacher_name,
           COUNT(e.id) as enrolled_count
    FROM courses c
    LEFT JOIN teachers t ON c.teacher_id = t.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    GROUP BY c.id
    ORDER BY c.course_name
")->fetch_all(MYSQLI_ASSOC);

// Get all teachers for dropdown
$teachers = $conn->query("SELECT id, name FROM teachers ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses - Admin</title>
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
        
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        
        .success { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        
        .form-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-card h3 { color: rgb(230, 139, 21); margin-bottom: 20px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-primary { background: rgb(230, 139, 21); color: white; }
        .btn-primary:hover { background: rgb(200, 110, 15); }
        .btn-danger { background: #f44336; color: white; padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-block; }
        
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 15px; text-align: left; font-weight: bold; color: #333; border-bottom: 2px solid #ddd; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Manage Courses</h1>
    </div>
    
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-card">
            <h3>Add New Course</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Course Name *</label>
                        <input type="text" name="course_name" placeholder="e.g., Mathematics 101" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Course Code *</label>
                        <input type="text" name="course_code" placeholder="e.g., MATH-101" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Assign Teacher</label>
                        <select name="teacher_id">
                            <option value="">No Teacher Assigned</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Credits *</label>
                        <input type="number" name="credits" value="3" min="1" max="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Semester *</label>
                        <input type="text" name="semester" placeholder="e.g., Fall 2025" value="Fall 2025" required>
                    </div>
                </div>
                
                <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
            </form>
        </div>
        
        <div class="table-container">
            <h3 style="margin-bottom: 20px; color: rgb(230, 139, 21);">All Courses (<?php echo count($courses); ?>)</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Course Code</th>
                        <th>Teacher</th>
                        <th>Credits</th>
                        <th>Semester</th>
                        <th>Enrolled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['teacher_name'] ?: 'Not Assigned'); ?></td>
                            <td><?php echo $course['credits']; ?></td>
                            <td><?php echo htmlspecialchars($course['semester']); ?></td>
                            <td><?php echo $course['enrolled_count']; ?> students</td>
                            <td>
                                <a href="?delete=<?php echo $course['id']; ?>" 
                                   class="btn-danger" 
                                   onclick="return confirm('Delete this course? This will also remove all enrollments and grades.')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                No courses found. Add your first course above!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>