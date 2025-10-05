<?php
require_once 'config.php';
require_once 'auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Admin access required");
    exit();
}

$conn = getDBConnection();

// Handle grade entry/update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grades'])) {
    $course_id = intval($_POST['course_id']);
    $success_count = 0;
    
    foreach ($_POST['grades'] as $student_id => $marks) {
        if ($marks !== '') {
            $marks = floatval($marks);
            $grade = '';
            
            // Calculate letter grade
            if ($marks >= 93) $grade = 'A';
            elseif ($marks >= 90) $grade = 'A-';
            elseif ($marks >= 87) $grade = 'B+';
            elseif ($marks >= 83) $grade = 'B';
            elseif ($marks >= 80) $grade = 'B-';
            elseif ($marks >= 77) $grade = 'C+';
            elseif ($marks >= 73) $grade = 'C';
            elseif ($marks >= 70) $grade = 'C-';
            elseif ($marks >= 60) $grade = 'D';
            else $grade = 'F';
            
            // Check if grade exists
            $stmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
            $stmt->bind_param("ii", $student_id, $course_id);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            
            if ($exists) {
                // Update
                $stmt = $conn->prepare("UPDATE grades SET marks = ?, grade = ? WHERE student_id = ? AND course_id = ?");
                $stmt->bind_param("dsii", $marks, $grade, $student_id, $course_id);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO grades (student_id, course_id, marks, grade, semester) VALUES (?, ?, ?, ?, 'Fall 2025')");
                $stmt->bind_param("iids", $student_id, $course_id, $marks, $grade);
            }
            
            if ($stmt->execute()) {
                $success_count++;
            }
            $stmt->close();
        }
    }
    
    $message = "Saved $success_count grade(s) successfully!";
}

// Get all courses
$courses = $conn->query("SELECT id, course_name, course_code FROM courses ORDER BY course_name")->fetch_all(MYSQLI_ASSOC);

// Get students enrolled in selected course
$enrolled_students = [];
if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $enrolled_students = $conn->query("
        SELECT s.id, s.student_id, s.name, g.marks, g.grade
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        LEFT JOIN grades g ON g.student_id = s.id AND g.course_id = e.course_id
        WHERE e.course_id = $course_id
        ORDER BY s.name
    ")->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grade Entry - Admin</title>
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
        
        .container { max-width: 1200px; margin: 0 auto; padding: 30px; }
        
        .success { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        
        .form-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        .form-group select { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        
        .table-container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 15px; text-align: left; font-weight: bold; color: #333; border-bottom: 2px solid #ddd; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        
        .grade-input { width: 80px; padding: 8px; border: 2px solid #ddd; border-radius: 5px; text-align: center; }
        .grade-input:focus { border-color: rgb(230, 139, 21); outline: none; }
        
        .grade-display { padding: 4px 12px; border-radius: 12px; font-weight: bold; font-size: 14px; display: inline-block; }
        .grade-a { background: #4CAF50; color: white; }
        .grade-b { background: #8BC34A; color: white; }
        .grade-c { background: #FFC107; color: white; }
        .grade-d { background: #FF9800; color: white; }
        .grade-f { background: #f44336; color: white; }
        
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
        .btn-primary { background: rgb(230, 139, 21); color: white; }
        .btn-primary:hover { background: rgb(200, 110, 15); }
    </style>
</head>
<body>
    <div class="header">
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Grade Entry System</h1>
    </div>
    
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="GET">
                <div class="form-group">
                    <label>Select Course to Enter Grades:</label>
                    <select name="course_id" onchange="this.form.submit()" required>
                        <option value="">Choose a course...</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" 
                                    <?php echo (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?> 
                                (<?php echo htmlspecialchars($course['course_code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        
        <?php if (count($enrolled_students) > 0): ?>
        <div class="table-container">
            <h3 style="margin-bottom: 20px; color: rgb(230, 139, 21);">
                Enrolled Students (<?php echo count($enrolled_students); ?>)
            </h3>
            
            <form method="POST">
                <input type="hidden" name="course_id" value="<?php echo $_GET['course_id']; ?>">
                
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Current Marks</th>
                            <th>Current Grade</th>
                            <th>Enter/Update Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolled_students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                            <td><?php echo $student['marks'] ? number_format($student['marks'], 2) . '%' : 'Not Graded'; ?></td>
                            <td>
                                <?php if ($student['grade']): ?>
                                    <?php 
                                    $grade_class = 'grade-' . strtolower($student['grade'][0]);
                                    ?>
                                    <span class="grade-display <?php echo $grade_class; ?>">
                                        <?php echo $student['grade']; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" 
                                       name="grades[<?php echo $student['id']; ?>]" 
                                       class="grade-input" 
                                       min="0" 
                                       max="100" 
                                       step="0.01"
                                       value="<?php echo $student['marks'] ?: ''; ?>"
                                       placeholder="0-100">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" name="save_grades" class="btn btn-primary">
                        Save All Grades
                    </button>
                </div>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 5px; border-left: 4px solid #2196F3;">
                <strong>Grading Scale:</strong><br>
                A (93-100) | A- (90-92) | B+ (87-89) | B (83-86) | B- (80-82) | 
                C+ (77-79) | C (73-76) | C- (70-72) | D (60-69) | F (0-59)
            </div>
        </div>
        <?php elseif (isset($_GET['course_id'])): ?>
        <div class="table-container">
            <p style="text-align: center; padding: 40px; color: #666;">
                No students enrolled in this course yet.
            </p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>