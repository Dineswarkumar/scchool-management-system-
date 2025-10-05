<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        header("Location: login.php?error=All fields are required");
        exit();
    }
    
    $conn = getDBConnection();
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Redirect based on role
            switch ($user['role']) {
                case 'student':
                    header("Location: dash.php");
                    break;
                case 'teacher':
                    header("Location: teacher_dash.php");
                    break;
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                default:
                    header("Location: dash.php");
            }
            exit();
        } else {
            header("Location: login.php?error=Invalid username or password");
            exit();
        }
    } else {
        header("Location: login.php?error=Invalid username or password");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>