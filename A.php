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
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <header>
        <div class="h-container">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <img id="logo" src="logo.jpg" alt="Logo">
            <h1>My School</h1>
            <nav class="nav-links">
                <a href="N.php"><img src="n.jpg" style="height:36px;width:36px;border-radius:50%;" alt="Notify"><br>Notify</a>
                <a href="A.php"><img src="a.jpg" style="height:36px;width:36px;border-radius:50%;" alt="Profile"><br>Profile</a>
            </nav>
        </div>
    </header>
    <div class="sidenav" id="sidenav">
        <a href="dash.php">🏠 Dashboard</a>
        <a href="subjects.php">📚 Courses</a>
        <a href="materials.php">📁 Materials</a>
        <a href="grades.php">📊 Grades</a>
        <a href="settings.html">⚙️ Settings</a>
        <a href="logout.php" style="margin-top:auto;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-banner" style="display:flex; align-items:center; gap:20px; padding:25px; margin-bottom:30px;">
            <img src="a.jpg" alt="Profile Picture" style="width:80px;height:80px;border-radius:50%;border:3px solid rgba(255,255,255,0.2);">
            <div>
                <h2 style="margin:0 0 5px 0; font-size:28px;"><?php echo htmlspecialchars($student['name']); ?></h2>
                <p style="margin:0; color:rgba(255,255,255,0.75);">📧 <?php echo htmlspecialchars($student['email']); ?></p>
                <div style="display:flex; gap:15px; margin-top:8px;">
                    <span style="font-size:13px; background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px; color:rgba(255,255,255,0.8);">🎓 ID: <?php echo htmlspecialchars($student['student_id']); ?></span>
                    <span style="font-size:13px; background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px; color:rgba(255,255,255,0.8);">👤 @<?php echo htmlspecialchars($student['username']); ?></span>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:25px;">
            <!-- Personal Information -->
            <div style="background: rgba(255,255,255,0.06); backdrop-filter:blur(12px); border: 1px solid rgba(255,255,255,0.1); padding:25px; border-radius:16px;">
                <h3 style="color:#f97316; margin-top:0; margin-bottom:20px; font-size:18px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Personal Information</h3>
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.6);">Full Name</span>
                        <span style="color:white; font-weight:500; text-align:right;"><?php echo htmlspecialchars($student['name']); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.6);">Date of Birth</span>
                        <span style="color:white; font-weight:500; text-align:right;"><?php echo $dob; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.6);">Gender</span>
                        <span style="color:white; font-weight:500; text-align:right;"><?php echo htmlspecialchars($student['gender'] ?: 'Not specified'); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.6);">Phone</span>
                        <span style="color:white; font-weight:500; text-align:right;"><?php echo htmlspecialchars($student['phone'] ?: 'Not provided'); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.6);">Email</span>
                        <span style="color:white; font-weight:500; text-align:right;"><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:rgba(255,255,255,0.6);">Address</span>
                        <span style="color:white; font-weight:500; text-align:right; max-width:60%;"><?php echo htmlspecialchars($student['address'] ?: 'Not provided'); ?></span>
                    </div>
                </div>
                <button class="btn-primary" style="margin-top: 25px; width:100%;" onclick="alert('Edit functionality coming soon!')">Edit Information</button>
            </div>

            <!-- Academic & Account Information -->
            <div style="display:flex; flex-direction:column; gap:25px;">
                <div style="background: rgba(255,255,255,0.06); backdrop-filter:blur(12px); border: 1px solid rgba(255,255,255,0.1); padding:25px; border-radius:16px;">
                    <h3 style="color:#f97316; margin-top:0; margin-bottom:20px; font-size:18px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Academic Profile</h3>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                            <span style="color:rgba(255,255,255,0.6);">Program</span>
                            <span style="color:white; font-weight:500; text-align:right;">Bachelor of Science</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                            <span style="color:rgba(255,255,255,0.6);">Enrollment Date</span>
                            <span style="color:white; font-weight:500; text-align:right;"><?php echo $enrollment_date; ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">
                            <span style="color:rgba(255,255,255,0.6);">Account Status</span>
                            <span style="color:#4ade80; font-weight:500; text-align:right;">● Active</span>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.06); backdrop-filter:blur(12px); border: 1px solid rgba(255,255,255,0.1); padding:25px; border-radius:16px;">
                    <h3 style="color:#f97316; margin-top:0; margin-bottom:20px; font-size:18px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Performance Stats</h3>
                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px; text-align:center;">
                        <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                            <div style="font-size:24px; font-weight:700; color:white;"><?php echo number_format($gpa, 2); ?></div>
                            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:5px;">Current GPA</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                            <div style="font-size:24px; font-weight:700; color:white;"><?php echo $credits; ?></div>
                            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:5px;">Credits</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                            <div style="font-size:24px; font-weight:700; color:white;"><?php echo $attendance; ?>%</div>
                            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:5px;">Attendance</div>
                        </div>
                    </div>
                    
                    <div style="margin-top:20px; padding-top:15px; border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between;">
                        <span style="color:rgba(255,255,255,0.6);">Academic Standing</span>
                        <span style="color:#4ade80; font-weight:500;">Good Standing</span>
                    </div>
                </div>
            </div>
            
            <!-- Emergency Contact -->
            <div style="background: rgba(255,255,255,0.06); backdrop-filter:blur(12px); border: 1px solid rgba(255,255,255,0.1); padding:25px; border-radius:16px; grid-column:1/-1;">
                <h3 style="color:#f97316; margin-top:0; margin-bottom:20px; font-size:18px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Emergency Contact</h3>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="margin:0 0 5px 0; color:rgba(255,255,255,0.6);">Contact Name & Number</p>
                        <p style="margin:0; color:white; font-weight:500; font-size:18px;"><?php echo htmlspecialchars($student['emergency_contact'] ?: 'Not provided'); ?></p>
                    </div>
                    <button class="btn-secondary" onclick="alert('Edit functionality coming soon!')">Update Contact</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidenav = document.getElementById('sidenav');
        const overlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            hamburgerBtn.classList.toggle('open');
            sidenav.classList.toggle('open');
            overlay.classList.toggle('active');
            // Prevent scrolling on body when sidebar is open on mobile
            document.body.style.overflow = sidenav.classList.contains('open') ? 'hidden' : '';
        }

        hamburgerBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>