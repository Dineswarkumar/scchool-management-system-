<?php
require_once 'config.php';
require_once 'auth_check.php';

// Only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Admin access required");
    exit();
}

$conn = getDBConnection();

// Handle enrollment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll'])) {
    $student_id = $_POST['student_id'];
    $course_ids = $_POST['courses'] ?? [];
    
    $success_count = 0;
    foreach ($course_ids as $course_id) {
        // Check if already enrolled
        $stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            
            // Enroll student
            $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES (?, ?, CURDATE())");
            $stmt->bind_param("ii", $student_id, $course_id);
            $stmt->execute();
            $success_count++;
        }
        $stmt->close();
    }
    
    $message = "Successfully enrolled student in $success_count course(s)";
}

// Get all students
$students = $conn->query("SELECT id, student_id, name, email FROM students ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get all courses
$courses = $conn->query("SELECT c.id, c.course_name, c.course_code, t.name as teacher_name 
                         FROM courses c 
                         LEFT JOIN teachers t ON c.teacher_id = t.id 
                         ORDER BY c.course_name")->fetch_all(MYSQLI_ASSOC);

// Get enrolled courses for selected student
$enrolled_courses = [];
$student_info = null;
if (isset($_GET['student'])) {
    $student_id = intval($_GET['student']);
    
    // Get student info
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get enrolled courses
    $stmt = $conn->prepare("SELECT course_id FROM enrollments WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $enrolled_courses[] = $row['course_id'];
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enroll Students - Admin</title>
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
        
        .container { max-width: 1200px; margin: 0 auto; padding: 30px; }
        
        .success { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        
        .form-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #333; font-weight: bold; margin-bottom: 8px; font-size: 14px; }
        .form-group select { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        
        .student-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid rgb(230, 139, 21);
        }
        
        .student-info h3 { color: rgb(230, 139, 21); margin-bottom: 10px; }
        .student-info p { color: #666; margin: 5px 0; }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .course-item {
            background: #fff;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .course-item:hover { border-color: rgb(230, 139, 21); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        .course-item.enrolled {
            background: #e8f5e9;
            border-color: #4CAF50;
        }
        
        .course-item label {
            display: flex;
            align-items: start;
            gap: 12px;
            cursor: pointer;
            font-weight: normal;
        }
        
        .course-item input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .course-details h4 { color: #333; margin-bottom: 5px; }
        .course-details .code { color: #888; font-size: 13px; }
        .course-details .teacher { color: #666; font-size: 13px; margin-top: 5px; }
        
        .enrolled-badge {
            background: #4CAF50;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 8px;
            display: inline-block;
        }
        
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
        .btn-primary { background: rgb(230, 139, 21); color: white; width: 100%; }
        .btn-primary:hover { background: rgb(200, 110, 15); }
        .btn-primary:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="header">
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Enroll Students in Courses</h1>
    </div>
    
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="GET">
                <div class="form-group">
                    <label>Select Student:</label>
                    <select name="student" onchange="this.form.submit()" required>
                        <option value="">Choose a student...</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>" 
                                    <?php echo (isset($_GET['student']) && $_GET['student'] == $student['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['name']); ?> 
                                (<?php echo htmlspecialchars($student['student_id']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        
        <?php if ($student_info): ?>
            <div class="student-info">
                <h3><?php echo htmlspecialchars($student_info['name']); ?></h3>
                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student_info['student_id']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student_info['email']); ?></p>
                <p><strong>Currently Enrolled:</strong> <?php echo count($enrolled_courses); ?> course(s)</p>
            </div>
            
            <div class="form-card">
                <h3 style="color: rgb(230, 139, 21); margin-bottom: 20px;">Available Courses</h3>
                
                <form method="POST">
                    <input type="hidden" name="student_id" value="<?php echo $_GET['student']; ?>">
                    
                    <div class="courses-grid">
                        <?php foreach ($courses as $course): ?>
                            <?php $is_enrolled = in_array($course['id'], $enrolled_courses); ?>
                            <div class="course-item <?php echo $is_enrolled ? 'enrolled' : ''; ?>">
                                <label>
                                    <input type="checkbox" 
                                           name="courses[]" 
                                           value="<?php echo $course['id']; ?>"
                                           <?php echo $is_enrolled ? 'checked disabled' : ''; ?>>
                                    <div class="course-details">
                                        <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                        <div class="code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                                        <div class="teacher">Teacher: <?php echo htmlspecialchars($course['teacher_name'] ?: 'Not Assigned'); ?></div>
                                        <?php if ($is_enrolled): ?>
                                            <span class="enrolled-badge">Already Enrolled</span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" name="enroll" class="btn btn-primary">
                        Enroll in Selected Courses
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>