<?php
// Include this file at the top of protected pages
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html?error=Please login to access this page");
    exit();
}

// Optional: Check if user has specific role
function checkRole($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: login.html?error=You do not have permission to access this page");
        exit();
    }
}

// Optional: Get logged in user details
function getLoggedInUser() {
    if (isset($_SESSION['user_id'])) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $user;
    }
    return null;
}
?>