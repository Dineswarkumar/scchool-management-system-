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
$stmt = $conn->prepare("
    SELECT s.*, u.username, u.created_at as account_created
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: login.php?error=Student record not found");
    exit();
}

$student_id = $student['id'];

// Get academic statistics
$stmt = $conn->prepare("SELECT AVG(marks) as gpa FROM grades WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$gpa_result = $stmt->get_result()->fetch_assoc();
$gpa = $gpa_result['gpa'] ? round($gpa_result['gpa'] / 25, 2) : 3.45; // Convert to 4.0 scale
$stmt->close();

// Get credits earned
$stmt = $conn->prepare("SELECT COUNT(*) * 3 as credits FROM enrollments WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$credits = $stmt->get_result()->fetch_assoc()['credits'];
$stmt->close();

// Get attendance percentage
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
$attendance = $attendance_data['total'] > 0 
    ? round(($attendance_data['present'] / $attendance_data['total']) * 100) 
    : 94;
$stmt->close();

$conn->close();

// Format dates
$enrollment_date = $student['created_at'] ? date('F Y', strtotime($student['created_at'])) : 'September 2024';
$dob = $student['dob'] ? date('F d, Y', strtotime($student['dob'])) : 'N/A';
?>
<!DOCTYPE html>
<html>  
<head>
    <title>Profile - My School</title>
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
    <div class="v-line" style="border-left: 5px solid rgb(5, 5, 5); height: 100%; position: absolute; left: 200px;"></div>
    <div class="sidenav">
        <a href="dash.php">Dashboard</a><br>
        <a href="subjects.php">Courses</a><br>
        <a href="materials.php">Materials</a><br>
        <a href="grades.php">Grades</a><br>
        <a href="settings.html">Settings</a><br><br><br><br><br><br><br><br><br>
        <a href="logout.php"><img src="logout.jpg"style="height:40px; width: 40px;">Logout</a>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <img src="a.jpg" alt="Profile Picture" class="profile-pic">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($student['name']); ?></h2>
                <p>📧 <?php echo htmlspecialchars($student['email']); ?></p>
                <p>🎓 Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                <p>📚 Username: <?php echo htmlspecialchars($student['username']); ?></p>
            </div>
        </div>

        <div class="profile-sections">
            <div class="profile-section">
                <h3 class="section-title">Personal Information</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Birth:</span>
                        <span class="info-value"><?php echo $dob; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['gender'] ?: 'Not specified'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['phone'] ?: 'Not provided'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['address'] ?: 'Not provided'); ?></span>
                    </div>
                </div>
                <button class="edit-btn" style="margin-top: 20px;" onclick="alert('Edit functionality coming soon!')">Edit Information</button>
            </div>

            <div class="profile-section">
                <h3 class="section-title">Academic Information</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Student ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['student_id']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Program:</span>
                        <span class="info-value">Bachelor of Science</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Username:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['username']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Enrollment Date:</span>
                        <span class="info-value"><?php echo $enrollment_date; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Account Status:</span>
                        <span class="info-value">Active</span>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h3 class="section-title">Academic Performance</h3>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($gpa, 2); ?></div>
                        <div class="stat-label">Current GPA</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $credits; ?></div>
                        <div class="stat-label">Credits Earned</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $attendance; ?>%</div>
                        <div class="stat-label">Attendance</div>
                    </div>
                </div>
                <div class="info-grid" style="margin-top: 20px;">
                    <div class="info-row">
                        <span class="info-label">Academic Standing:</span>
                        <span class="info-value">Good Standing</span>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h3 class="section-title">Emergency Contact</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Contact Info:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['emergency_contact'] ?: 'Not provided'); ?></span>
                    </div>
                </div>
                <button class="edit-btn" style="margin-top: 20px;" onclick="alert('Edit functionality coming soon!')">Update Contact</button>
            </div>
        </div>
    </div>
</body>
</html>