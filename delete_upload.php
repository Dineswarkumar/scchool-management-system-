<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    header("Location: login.php?error=Access denied");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: tuploads.php?error=Invalid file");
    exit();
}

$file_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

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

// Get file information and verify ownership
$stmt = $conn->prepare("SELECT file_path FROM uploads WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $file_id, $teacher_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$file) {
    $conn->close();
    header("Location: tuploads.php?error=File not found or access denied");
    exit();
}

// Delete file from filesystem
if (file_exists($file['file_path'])) {
    unlink($file['file_path']);
}

// Delete from database
$stmt = $conn->prepare("DELETE FROM uploads WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $file_id, $teacher_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: tuploads.php?success=File deleted successfully");
exit();
?>