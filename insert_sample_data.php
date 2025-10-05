<?php
/**
 * Sample Data Insertion Script
 * Run this once to populate your database with test data
 */

require_once 'config.php';

$conn = getDBConnection();

echo "<h2>Inserting Sample Data...</h2>";

// Sample Teachers
$teachers_data = [
    ['Dr. Smith', 'Mathematics', 'smith@myschool.edu', '555-1001'],
    ['Prof. Johnson', 'Physics', 'johnson@myschool.edu', '555-1002'],
    ['Ms. Williams', 'English', 'williams@myschool.edu', '555-1003'],
    ['Dr. Brown', 'Chemistry', 'brown@myschool.edu', '555-1004'],
    ['Mr. Davis', 'Geography', 'davis@myschool.edu', '555-1005']
];

echo "<p>Inserting Teachers...</p>";
$teacher_ids = [];
foreach ($teachers_data as $teacher) {
    $stmt = $conn->prepare("INSERT INTO teachers (name, subject, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $teacher[0], $teacher[1], $teacher[2], $teacher[3]);
    $stmt->execute();
    $teacher_ids[] = $conn->insert_id;
    $stmt->close();
    echo "- Added: {$teacher[0]}<br>";
}

// Sample Courses
$courses_data = [
    ['Mathematics 101', 'MATH-101', $teacher_ids[0], 3, 'Fall 2025'],
    ['Physics 201', 'PHYS-201', $teacher_ids[1], 4, 'Fall 2025'],
    ['English Literature', 'ENG-301', $teacher_ids[2], 3, 'Fall 2025'],
    ['Chemistry 102', 'CHEM-102', $teacher_ids[3], 4, 'Fall 2025'],
    ['Geography 105', 'GEO-105', $teacher_ids[4], 3, 'Fall 2025'],
    ['Computer Science 201', 'CS-201', $teacher_ids[0], 3, 'Fall 2025']
];

echo "<p>Inserting Courses...</p>";
$course_ids = [];
foreach ($courses_data as $course) {
    $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, teacher_id, credits, semester) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $course[0], $course[1], $course[2], $course[3], $course[4]);
    $stmt->execute();
    $course_ids[] = $conn->insert_id;
    $stmt->close();
    echo "- Added: {$course[0]}<br>";
}

// Get all students to enroll them
$result = $conn->query("SELECT id FROM students");
$student_ids = [];
while ($row = $result->fetch_assoc()) {
    $student_ids[] = $row['id'];
}

if (count($student_ids) > 0) {
    echo "<p>Enrolling Students in Courses...</p>";
    
    // Enroll each student in random courses
    foreach ($student_ids as $student_id) {
        // Enroll in 4-6 random courses
        $num_courses = rand(4, 6);
        $enrolled_courses = array_rand(array_flip($course_ids), $num_courses);
        
        if (!is_array($enrolled_courses)) {
            $enrolled_courses = [$enrolled_courses];
        }
        
        foreach ($enrolled_courses as $course_id) {
            $date_enrolled = date('Y-m-d', strtotime('-' . rand(1, 60) . ' days'));
            
            // Check if already enrolled
            $check = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
            $check->bind_param("ii", $student_id, $course_id);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();
            
            if (!$exists) {
                $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $student_id, $course_id, $date_enrolled);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "- Enrolled Student ID: {$student_id}<br>";
    }
    
    // Insert sample grades
    echo "<p>Inserting Sample Grades...</p>";
    foreach ($student_ids as $student_id) {
        $enrollments = $conn->query("SELECT course_id FROM enrollments WHERE student_id = {$student_id}");
        
        while ($enrollment = $enrollments->fetch_assoc()) {
            $marks = rand(70, 98);
            $grade = '';
            if ($marks >= 93) $grade = 'A';
            elseif ($marks >= 90) $grade = 'A-';
            elseif ($marks >= 87) $grade = 'B+';
            elseif ($marks >= 83) $grade = 'B';
            elseif ($marks >= 80) $grade = 'B-';
            elseif ($marks >= 77) $grade = 'C+';
            elseif ($marks >= 73) $grade = 'C';
            else $grade = 'D';
            
            // Check if grade exists
            $check = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
            $check->bind_param("ii", $student_id, $enrollment['course_id']);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();
            
            if (!$exists) {
                $stmt = $conn->prepare("INSERT INTO grades (student_id, course_id, marks, grade, semester) VALUES (?, ?, ?, ?, 'Fall 2025')");
                $stmt->bind_param("iids", $student_id, $enrollment['course_id'], $marks, $grade);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "- Added grades for Student ID: {$student_id}<br>";
    }
    
    // Insert sample attendance
    echo "<p>Inserting Sample Attendance...</p>";
    foreach ($student_ids as $student_id) {
        $enrollments = $conn->query("SELECT course_id FROM enrollments WHERE student_id = {$student_id}");
        
        while ($enrollment = $enrollments->fetch_assoc()) {
            // Add 20 attendance records per course
            for ($i = 0; $i < 20; $i++) {
                $date = date('Y-m-d', strtotime('-' . $i . ' days'));
                $status = rand(1, 100) > 10 ? 'present' : 'absent'; // 90% present
                
                // Check if attendance exists
                $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND course_id = ? AND date = ?");
                $check->bind_param("iis", $student_id, $enrollment['course_id'], $date);
                $check->execute();
                $exists = $check->get_result()->num_rows > 0;
                $check->close();
                
                if (!$exists) {
                    $stmt = $conn->prepare("INSERT INTO attendance (student_id, course_id, date, status) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiss", $student_id, $enrollment['course_id'], $date, $status);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        echo "- Added attendance for Student ID: {$student_id}<br>";
    }
}

// Insert sample notifications
echo "<p>Inserting Sample Notifications...</p>";
$notifications = [
    ['Midterm Exam Schedule', 'The midterm exam schedule has been released. Please check your course pages for details.', 'announcement'],
    ['Library Extended Hours', 'The library will have extended hours this week to support exam preparation.', 'announcement'],
    ['Assignment Due Reminder', 'Reminder: Problem Set 5 is due this Friday at 11:59 PM.', 'reminder'],
    ['New Study Materials', 'New study materials have been uploaded for Chapter 7.', 'announcement'],
    ['Grade Posted', 'Your grade for Lab Report 3 has been posted.', 'grade']
];

foreach ($notifications as $notif) {
    $stmt = $conn->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $notif[0], $notif[1], $notif[2]);
    $stmt->execute();
    $stmt->close();
    echo "- Added: {$notif[0]}<br>";
}

$conn->close();

echo "<h3 style='color: green; margin-top: 30px;'>✓ Sample data inserted successfully!</h3>";
echo "<p><a href='dash.php'>Go to Dashboard</a></p>";
?>