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

// Get uploaded materials for enrolled courses
$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.file_name,
        u.file_type,
        u.file_path,
        u.title,
        u.description,
        u.material_type,
        u.uploaded_at,
        c.course_name,
        c.course_code,
        t.name as teacher_name
    FROM uploads u
    JOIN courses c ON u.course_id = c.id
    LEFT JOIN teachers t ON u.teacher_id = t.id
    WHERE u.course_id IN (
        SELECT course_id FROM enrollments WHERE student_id = ?
    )
    ORDER BY u.uploaded_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group materials by course
$materials_by_course = [];
foreach ($materials as $material) {
    $course_name = $material['course_name'];
    if (!isset($materials_by_course[$course_name])) {
        $materials_by_course[$course_name] = [];
    }
    $materials_by_course[$course_name][] = $material;
}

$conn->close();

function getFileIcon($file_type) {
    $icons = [
        'pdf' => '📄',
        'doc' => '📝',
        'docx' => '📝',
        'ppt' => '📊',
        'pptx' => '📊',
        'xls' => '📈',
        'xlsx' => '📈'
    ];
    return $icons[$file_type] ?? '📁';
}

function formatFileSize($filepath) {
    if (file_exists($filepath)) {
        $size = filesize($filepath);
        if ($size < 1024) return $size . ' B';
        if ($size < 1048576) return round($size / 1024, 2) . ' KB';
        return round($size / 1048576, 2) . ' MB';
    }
    return 'N/A';
}
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Course Materials - My School</title>
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
        <h2 class="page-title">Course Materials</h2>
        <p style="color: #666;">Access lecture notes, assignments, and study materials from your courses</p>

        <?php if (count($materials) > 0): ?>
            <?php foreach ($materials_by_course as $course_name => $course_materials): ?>
            <div class="grades-table" style="margin-top: 30px;">
                <h3 style="color: rgb(230, 139, 21); margin-top: 0;">📚 <?php echo htmlspecialchars($course_name); ?></h3>
                <p style="color: #666; margin-bottom: 20px;">
                    <?php echo count($course_materials); ?> file(s) available
                </p>
                
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($course_materials as $material): ?>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 12px; border-left: 4px solid rgb(230, 139, 21); display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                                <span style="font-size: 32px;"><?php echo getFileIcon($material['file_type']); ?></span>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 5px 0; color: #3a3f58; font-size: 18px;">
                                        <?php echo htmlspecialchars($material['title']); ?>
                                    </h4>
                                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                                        <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; background-color: rgb(230, 139, 21); color: white;">
                                            <?php echo strtoupper(str_replace('_', ' ', $material['material_type'])); ?>
                                        </span>
                                        <span style="color: #888; font-size: 13px;">
                                            📅 <?php echo date('M d, Y', strtotime($material['uploaded_at'])); ?>
                                        </span>
                                        <span style="color: #888; font-size: 13px;">
                                            👤 <?php echo htmlspecialchars($material['teacher_name']); ?>
                                        </span>
                                        <span style="color: #888; font-size: 13px;">
                                            📦 <?php echo formatFileSize($material['file_path']); ?>
                                        </span>
                                    </div>
                                    <?php if ($material['description']): ?>
                                    <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">
                                        <?php echo htmlspecialchars($material['description']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="download.php?id=<?php echo $material['id']; ?>" 
                               class="btn btn-primary" 
                               style="text-decoration: none; display: inline-block; white-space: nowrap;">
                                💾 Download
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px; margin-top: 30px; box-shadow: 0 3px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 64px; margin-bottom: 20px;">📚</div>
                <h3 style="color: rgb(230, 139, 21); margin-bottom: 10px;">No Materials Available</h3>
                <p style="color: #666;">Your instructors haven't uploaded any course materials yet. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>