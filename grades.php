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

// Calculate GPA (assuming 4.0 scale)
function calculateGPA($marks) {
    if ($marks >= 90) return 4.0;
    if ($marks >= 80) return 3.0;
    if ($marks >= 70) return 2.0;
    if ($marks >= 60) return 1.0;
    return 0.0;
}

function getLetterGrade($marks) {
    if ($marks >= 93) return 'A';
    if ($marks >= 90) return 'A-';
    if ($marks >= 87) return 'B+';
    if ($marks >= 83) return 'B';
    if ($marks >= 80) return 'B-';
    if ($marks >= 77) return 'C+';
    if ($marks >= 73) return 'C';
    if ($marks >= 70) return 'C-';
    if ($marks >= 60) return 'D';
    return 'F';
}

function getGradeBadgeClass($marks) {
    if ($marks >= 90) return 'grade-a';
    if ($marks >= 80) return 'grade-b';
    return 'grade-c';
}

// Get all grades with course details
$stmt = $conn->prepare("
    SELECT 
        c.course_name, 
        c.course_code,
        t.name as teacher_name,
        g.marks,
        g.grade as letter_grade,
        g.semester,
        g.updated_at
    FROM grades g
    JOIN courses c ON g.course_id = c.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    WHERE g.student_id = ?
    ORDER BY c.course_name
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate statistics
$total_marks = 0;
$total_gpa = 0;
$course_count = count($grades);

foreach ($grades as $grade) {
    $total_marks += $grade['marks'];
    $total_gpa += calculateGPA($grade['marks']);
}

$avg_marks = $course_count > 0 ? round($total_marks / $course_count) : 0;
$gpa = $course_count > 0 ? round($total_gpa / $course_count, 2) : 0.00;

// Get total credits (for demo)
$total_credits = $course_count * 3; // Assuming 3 credits per course

$conn->close();
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Grades - My School</title>
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
        <a href="materials.php">Materials</a><br>
        <a href="grades.php">Grades</a><br>
        <a href="settings.html">Settings</a><br><br><br><br><br><br><br><br><br>
        <a href="logout.php"><img src="logout.jpg"style="height:40px; width: 40px;">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="page-title">My Grades</h2>
        <p style="color: #666;">Track your academic performance across all courses</p>

        <div class="summary-cards">
            <div class="summary-card">
                <h3>Overall GPA</h3>
                <div class="value"><?php echo number_format($gpa, 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Current Average</h3>
                <div class="value"><?php echo $avg_marks; ?>%</div>
            </div>
            <div class="summary-card">
                <h3>Credits Earned</h3>
                <div class="value"><?php echo $total_credits; ?></div>
            </div>
            <div class="summary-card">
                <h3>Courses</h3>
                <div class="value"><?php echo $course_count; ?></div>
            </div>
        </div>

        <div class="grades-table">
            <h3 style="color: rgb(244, 154, 29); margin-top: 0;">Course Grades</h3>
            
            <?php if (count($grades) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Marks</th>
                        <th>Grade</th>
                        <th>Semester</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($grade['course_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($grade['course_code']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($grade['teacher_name'] ?: 'N/A'); ?></td>
                        <td><strong><?php echo round($grade['marks'], 2); ?>%</strong></td>
                        <td>
                            <span class="grade-badge <?php echo getGradeBadgeClass($grade['marks']); ?>">
                                <?php echo $grade['letter_grade'] ?: getLetterGrade($grade['marks']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($grade['semester'] ?: 'Fall 2025'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($grade['updated_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                No grades available yet. Grades will appear here once your instructors post them.
            </p>
            <?php endif; ?>
        </div>

        <?php if (count($grades) > 0): ?>
        <div class="grades-table" style="margin-top: 20px;">
            <h3 style="color: rgb(244, 154, 29); margin-top: 0;">Grade Distribution</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; padding: 20px;">
                <?php
                $grade_dist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
                foreach ($grades as $g) {
                    $letter = getLetterGrade($g['marks']);
                    $first_letter = $letter[0];
                    if (isset($grade_dist[$first_letter])) {
                        $grade_dist[$first_letter]++;
                    }
                }
                foreach ($grade_dist as $letter => $count):
                    if ($count > 0):
                ?>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 10px; text-align: center; border: 2px solid #f7c873;">
                    <div style="font-size: 32px; font-weight: bold; color: rgb(230, 139, 21);"><?php echo $count; ?></div>
                    <div style="font-size: 14px; color: #666;">Grade <?php echo $letter; ?></div>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>