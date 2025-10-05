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
    $stmt = $conn->prepare("SELECT id, name FROM teachers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $teacher_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$teacher_data) {
        header("Location: TN.php?error=Teacher record not found");
        exit();
    }
    
    $teacher_id = $teacher_data['id'];
    $teacher_name = $teacher_data['name'];
    
    // Get form data
    $type = $_POST['type'];
    $send_to = $_POST['send_to'];
    $course_id = isset($_POST['course_id']) && !empty($_POST['course_id']) ? intval($_POST['course_id']) : null;
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    
    // Validate inputs
    if (empty($type) || empty($send_to) || empty($title) || empty($message)) {
        header("Location: TN.php?error=All required fields must be filled");
        exit();
    }
    
    // If sending to all students, set course_id to NULL
    if ($send_to == 'all_students') {
        $course_id = null;
    }
    
    // Validate course_id exists if provided
    if ($course_id !== null) {
        $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->bind_param("ii", $course_id, $teacher_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            $conn->close();
            header("Location: TN.php?error=Invalid course selected");
            exit();
        }
        $stmt->close();
    }
    
    // Insert notification
    $stmt = $conn->prepare("INSERT INTO notifications (title, message, type, teacher_id, course_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $title, $message, $type, $teacher_id, $course_id);
    
    if ($stmt->execute()) {
        $notification_id = $conn->insert_id;
        $stmt->close();
        
        // Get recipient count
        $recipient_count = 0;
        
        if ($send_to == 'all_students') {
            // Count all students in teacher's courses
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT e.student_id) as count 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.teacher_id = ?
            ");
            $stmt->bind_param("i", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $recipient_count = $result['count'];
            $stmt->close();
        } elseif ($send_to == 'specific_course' && $course_id) {
            // Count students in specific course
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $recipient_count = $result['count'];
            $stmt->close();
        }
        
        $conn->close();
        header("Location: TN.php?success=Notification sent to {$recipient_count} student(s)");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: TN.php?error=Failed to send notification");
        exit();
    }
} else {
    header("Location: TN.php");
    exit();
}
?>