<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validate inputs
    if (empty($name) || empty($username) || empty($email) || empty($phone) || empty($password) || empty($role)) {
        header("Location: reg.php?error=All fields are required");
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        header("Location: reg.php?error=Passwords do not match");
        exit();
    }
    
    // Validate password length
    if (strlen($password) < 4) {
        header("Location: reg.php?error=Password must be at least 6 characters");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: reg.php?error=Invalid email format");
        exit();
    }
    
    $conn = getDBConnection();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: reg.php?error=Username already exists");
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Insert into appropriate table based on role
        if ($role === 'student') {
            // Generate student ID
            $student_id = 'STU-' . date('Y') . '-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
            
            $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, name, email, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $student_id, $name, $email, $phone);
            $stmt->execute();
            $stmt->close();
        } elseif ($role === 'teacher') {
            $stmt = $conn->prepare("INSERT INTO teachers (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $name, $email, $phone);
            $stmt->execute();
            $stmt->close();
        }
        
        // Commit transaction
        $conn->commit();

        header("Location: login.php?success=Registration successful! Please login.");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        header("Location: reg.php?error=Registration failed. Please try again.");
        exit();
    }
    
    $conn->close();
} else {
    header("Location: reg.php");
    exit();
}
?>