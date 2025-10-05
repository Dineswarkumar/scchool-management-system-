<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    header("Location: login.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get teacher ID
    $stmt = $conn->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $teacher_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$teacher_data) {
        header("Location: tuploads.php?error=Teacher record not found");
        exit();
    }
    
    $teacher_id = $teacher_data['id'];
    $course_id = $_POST['course_id'];
    $material_type = $_POST['material_type'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Validate inputs
    if (empty($course_id) || empty($material_type) || empty($title)) {
        header("Location: tuploads.php?error=All required fields must be filled");
        exit();
    }
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
        
        if (!in_array($file_ext, $allowed)) {
            header("Location: tuploads.php?error=Invalid file type. Only PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX allowed");
            exit();
        }
        
        // Check file size (50MB max)
        if ($file_size > 50 * 1024 * 1024) {
            header("Location: tuploads.php?error=File size must be less than 50MB");
            exit();
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO uploads (teacher_id, course_id, file_name, file_type, file_path, title, description, material_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssss", $teacher_id, $course_id, $file_name, $file_ext, $file_path, $title, $description, $material_type);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: tuploads.php?success=File uploaded successfully");
                exit();
            } else {
                $stmt->close();
                $conn->close();
                // Delete uploaded file if database insert fails
                unlink($file_path);
                header("Location: tuploads.php?error=Database error. Please try again");
                exit();
            }
        } else {
            $conn->close();
            header("Location: tuploads.php?error=Failed to upload file");
            exit();
        }
    } else {
        $conn->close();
        header("Location: tuploads.php?error=No file selected or upload error");
        exit();
    }
} else {
    header("Location: tuploads.php");
    exit();
}
?>