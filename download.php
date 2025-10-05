<?php
require_once 'config.php';
require_once 'auth_check.php';

if (!isset($_GET['id'])) {
    header("Location: dash.php?error=Invalid file");
    exit();
}

$file_id = $_GET['id'];
$conn = getDBConnection();

// Get file information
$stmt = $conn->prepare("SELECT file_name, file_path, file_type FROM uploads WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$file) {
    header("Location: dash.php?error=File not found");
    exit();
}

$file_path = $file['file_path'];

if (!file_exists($file_path)) {
    header("Location: dash.php?error=File does not exist");
    exit();
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($file_path);
exit();
?>