<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    header("Location: login.php?error=Access denied");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get teacher ID and info
$stmt = $conn->prepare("SELECT id, name FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher_data) {
    header("Location: login.php?error=Teacher record not found");
    exit();
}

$teacher_id = $teacher_data['id'];

// Get teacher's courses
$stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses WHERE teacher_id = ? ORDER BY course_name");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get uploaded files
$stmt = $conn->prepare("
    SELECT u.*, c.course_name, c.course_code
    FROM uploads u
    LEFT JOIN courses c ON u.course_id = c.id
    WHERE u.teacher_id = ?
    ORDER BY u.uploaded_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$uploads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_uploads FROM uploads WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_uploads = $stmt->get_result()->fetch_assoc()['total_uploads'];
$stmt->close();

// Calculate storage used
$stmt = $conn->prepare("SELECT SUM(LENGTH(file_path)) as storage FROM uploads WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$storage_result = $stmt->get_result()->fetch_assoc();
$storage_mb = round(($storage_result['storage'] ?: 0) / 1024 / 1024, 2);
$stmt->close();

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
    <title>Upload Materials - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="t.css">
</head>
<body>
    <header>
        <div class="h-container">
            <img id="logo" src="logo.jpg" alt="Logo">
            <h1>My school</h1>
            <nav class="nav-links">
             <a href="TN.php"><img src="n.jpg" style="height:40px; width: 40px; border-radius: 50%;"><br>Notify</a>
             <a href="A.php"><img src="a.jpg" style="height:40px; width: 40px; border-radius: 50%;"><br>Profile</a>
            </nav>
        </div>
    </header>
    <hr style="border: 5px solid rgb(5, 5, 5);">
    <div class="v-line" style="border-left: 5px solid rgb(5, 5, 5); height: 100%; position: absolute; left: 200px;"></div>
    <div class="sidenav">
        <a href="teacher_dash.php">Dashboard</a><br>
        <a href="tuploads.php">Upload Materials</a><br>
        <br><br><br><br>
        <a href="logout.php"><img src="logout.jpg"style="height:40px; width: 40px;">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="page-title">Upload Course Materials</h2>
        <p style="color: #666;">Share lecture notes, assignments, and study materials with your students</p>

        <?php if(isset($_GET['success'])): ?>
        <div class="success-message" style="display: block;">
            ✓ <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
        <div class="error-message" style="display: block;">
            ✗ <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_uploads; ?></div>
                <div class="stat-label">Total Uploads</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $storage_mb; ?> MB</div>
                <div class="stat-label">Storage Used</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count($courses); ?></div>
                <div class="stat-label">Courses</div>
            </div>
        </div>

        <div class="upload-section">
            <div class="upload-card">
                <h3 class="card-title">📤 Upload New Material</h3>
                
                <form id="uploadForm" action="upload_process.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select Course *</label>
                        <select name="course_id" required>
                            <option value="">Choose a course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?> (<?php echo htmlspecialchars($course['course_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Material Type *</label>
                        <select name="material_type" required>
                            <option value="">Select type</option>
                            <option value="lecture_notes">Lecture Notes</option>
                            <option value="assignment">Assignment</option>
                            <option value="study_guide">Study Guide</option>
                            <option value="solution">Solutions</option>
                            <option value="syllabus">Syllabus</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" placeholder="e.g., Chapter 5 - Derivatives" required>
                    </div>

                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea name="description" placeholder="Add a brief description..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload File *</label>
                        <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                            <div class="upload-icon">📁</div>
                            <p class="upload-text"><strong>Click to browse</strong> or drag and drop files here</p>
                            <p style="color: #999; font-size: 12px;">Supported: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX (Max 50MB)</p>
                        </div>
                        <input type="file" id="fileInput" name="file" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" required onchange="displayFileName()">
                        <div id="selectedFile" style="margin-top: 10px; color: #666; font-size: 14px;"></div>
                    </div>

                    <button type="submit" class="btn-upload">📤 Upload Material</button>
                </form>
            </div>

            <div class="upload-card">
                <h3 class="card-title">📚 Recently Uploaded</h3>
                
                <div class="uploaded-files-grid">
                    <?php if (count($uploads) > 0): ?>
                        <?php foreach ($uploads as $upload): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <div class="file-icon"><?php echo getFileIcon($upload['file_type']); ?></div>
                                <div class="file-details">
                                    <h4><?php echo htmlspecialchars($upload['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($upload['course_name']); ?> • <span class="file-type-badge"><?php echo strtoupper($upload['material_type']); ?></span></p>
                                    <p style="font-size: 12px;">Uploaded: <?php echo date('M d, Y', strtotime($upload['uploaded_at'])); ?> • <?php echo formatFileSize($upload['file_path']); ?></p>
                                </div>
                            </div>
                            <div class="file-actions">
                                <a href="download.php?id=<?php echo $upload['id']; ?>" class="btn-icon" title="Download">💾</a>
                                <a href="delete_upload.php?id=<?php echo $upload['id']; ?>" class="btn-icon btn-delete" title="Delete" onclick="return confirm('Delete this file?')">🗑️</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px; color: #666;">No files uploaded yet. Upload your first material above!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            displayFileName();
        }, false);

        function displayFileName() {
            const file = fileInput.files[0];
            const selectedFileDiv = document.getElementById('selectedFile');
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                selectedFileDiv.innerHTML = `<strong>Selected:</strong> ${file.name} (${fileSize} MB)`;
            }
        }
    </script>
</body>
</html>