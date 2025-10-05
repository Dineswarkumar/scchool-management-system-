-- Complete Sample Data for School Management System
-- Run this script in phpMyAdmin to populate your database

USE school_db;

-- Clear existing data (optional - comment out if you want to keep existing data)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM attendance;
DELETE FROM grades;
DELETE FROM enrollments;
DELETE FROM uploads;
DELETE FROM notifications;
DELETE FROM courses;
DELETE FROM teachers;
DELETE FROM students;
DELETE FROM users;
-- Reset auto-increment counters
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE teachers AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;
ALTER TABLE enrollments AUTO_INCREMENT = 1;
ALTER TABLE grades AUTO_INCREMENT = 1;
ALTER TABLE attendance AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE uploads AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. CREATE USERS (Password: password123 for all)
-- =====================================================

-- Admin user
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Teacher users
INSERT INTO users (username, password, role) VALUES 
('dr.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('prof.johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('ms.williams', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('dr.brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('mr.davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('prof.anderson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

-- Student users
INSERT INTO users (username, password, role) VALUES 
('john.doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('jane.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('michael.brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('emily.davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('david.wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('sarah.johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('james.taylor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('lisa.anderson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('robert.thomas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('jennifer.white', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- =====================================================
-- 2. CREATE TEACHERS
-- =====================================================

INSERT INTO teachers (user_id, name, subject, email, phone) VALUES 
(2, 'Dr. Robert Smith', 'Mathematics', 'robert.smith@myschool.edu', '555-1001'),
(3, 'Prof. Jennifer Johnson', 'Physics', 'jennifer.johnson@myschool.edu', '555-1002'),
(4, 'Ms. Patricia Williams', 'English Literature', 'patricia.williams@myschool.edu', '555-1003'),
(5, 'Dr. Michael Brown', 'Chemistry', 'michael.brown@myschool.edu', '555-1004'),
(6, 'Mr. James Davis', 'Geography', 'james.davis@myschool.edu', '555-1005'),
(7, 'Prof. Linda Anderson', 'Computer Science', 'linda.anderson@myschool.edu', '555-1006');

-- =====================================================
-- 3. CREATE STUDENTS
-- =====================================================

INSERT INTO students (user_id, student_id, name, dob, gender, email, phone, address, emergency_contact) VALUES 
(8, 'STU-2025-00001', 'John Michael Doe', '2005-01-15', 'Male', 'john.doe@student.myschool.edu', '555-2001', '123 Main St, Springfield, USA', 'Mary Doe - 555-2101'),
(9, 'STU-2025-00002', 'Jane Elizabeth Smith', '2005-03-22', 'Female', 'jane.smith@student.myschool.edu', '555-2002', '456 Oak Ave, Springfield, USA', 'John Smith - 555-2102'),
(10, 'STU-2025-00003', 'Michael Robert Brown', '2004-11-08', 'Male', 'michael.brown@student.myschool.edu', '555-2003', '789 Pine Rd, Springfield, USA', 'Sarah Brown - 555-2103'),
(11, 'STU-2025-00004', 'Emily Rose Davis', '2005-06-30', 'Female', 'emily.davis@student.myschool.edu', '555-2004', '321 Elm St, Springfield, USA', 'David Davis - 555-2104'),
(12, 'STU-2025-00005', 'David James Wilson', '2005-02-14', 'Male', 'david.wilson@student.myschool.edu', '555-2005', '654 Maple Dr, Springfield, USA', 'Lisa Wilson - 555-2105'),
(13, 'STU-2025-00006', 'Sarah Ann Johnson', '2004-09-19', 'Female', 'sarah.johnson@student.myschool.edu', '555-2006', '987 Cedar Ln, Springfield, USA', 'Robert Johnson - 555-2106'),
(14, 'STU-2025-00007', 'James Christopher Taylor', '2005-04-25', 'Male', 'james.taylor@student.myschool.edu', '555-2007', '147 Birch Way, Springfield, USA', 'Mary Taylor - 555-2107'),
(15, 'STU-2025-00008', 'Lisa Marie Anderson', '2004-12-03', 'Female', 'lisa.anderson@student.myschool.edu', '555-2008', '258 Spruce St, Springfield, USA', 'William Anderson - 555-2108'),
(16, 'STU-2025-00009', 'Robert Daniel Thomas', '2005-07-11', 'Male', 'robert.thomas@student.myschool.edu', '555-2009', '369 Willow Ct, Springfield, USA', 'Jennifer Thomas - 555-2109'),
(17, 'STU-2025-00010', 'Jennifer Lynn White', '2004-10-28', 'Female', 'jennifer.white@student.myschool.edu', '555-2010', '741 Ash Blvd, Springfield, USA', 'Michael White - 555-2110');

-- =====================================================
-- 4. CREATE COURSES
-- =====================================================

INSERT INTO courses (course_name, course_code, teacher_id, credits, semester) VALUES 
('Mathematics 101', 'MATH-101', 1, 3, 'Fall 2025'),
('Advanced Calculus', 'MATH-201', 1, 4, 'Fall 2025'),
('Physics 201', 'PHYS-201', 2, 4, 'Fall 2025'),
('Quantum Physics', 'PHYS-301', 2, 4, 'Fall 2025'),
('English Literature', 'ENG-301', 3, 3, 'Fall 2025'),
('Creative Writing', 'ENG-201', 3, 3, 'Fall 2025'),
('Chemistry 102', 'CHEM-102', 4, 4, 'Fall 2025'),
('Organic Chemistry', 'CHEM-202', 4, 4, 'Fall 2025'),
('Geography 105', 'GEO-105', 5, 3, 'Fall 2025'),
('World Geography', 'GEO-201', 5, 3, 'Fall 2025'),
('Computer Science 201', 'CS-201', 6, 3, 'Fall 2025'),
('Data Structures', 'CS-301', 6, 4, 'Fall 2025');

-- =====================================================
-- 5. CREATE ENROLLMENTS
-- =====================================================

-- John Doe enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(1, 1, '2025-09-01'), (1, 3, '2025-09-01'), (1, 5, '2025-09-01'), 
(1, 7, '2025-09-01'), (1, 9, '2025-09-01'), (1, 11, '2025-09-01');

-- Jane Smith enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(2, 1, '2025-09-01'), (2, 3, '2025-09-01'), (2, 5, '2025-09-01'), 
(2, 7, '2025-09-01'), (2, 11, '2025-09-01');

-- Michael Brown enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(3, 2, '2025-09-01'), (3, 4, '2025-09-01'), (3, 8, '2025-09-01'), 
(3, 11, '2025-09-01'), (3, 12, '2025-09-01');

-- Emily Davis enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(4, 1, '2025-09-01'), (4, 5, '2025-09-01'), (4, 6, '2025-09-01'), 
(4, 9, '2025-09-01'), (4, 11, '2025-09-01');

-- David Wilson enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(5, 1, '2025-09-01'), (5, 3, '2025-09-01'), (5, 7, '2025-09-01'), 
(5, 9, '2025-09-01'), (5, 12, '2025-09-01');

-- Sarah Johnson enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(6, 2, '2025-09-01'), (6, 4, '2025-09-01'), (6, 8, '2025-09-01'), 
(6, 10, '2025-09-01'), (6, 11, '2025-09-01');

-- James Taylor enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(7, 1, '2025-09-01'), (7, 3, '2025-09-01'), (7, 5, '2025-09-01'), 
(7, 11, '2025-09-01'), (7, 12, '2025-09-01');

-- Lisa Anderson enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(8, 1, '2025-09-01'), (8, 7, '2025-09-01'), (8, 9, '2025-09-01'), 
(8, 11, '2025-09-01');

-- Robert Thomas enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(9, 2, '2025-09-01'), (9, 4, '2025-09-01'), (9, 8, '2025-09-01'), 
(9, 12, '2025-09-01');

-- Jennifer White enrollments
INSERT INTO enrollments (student_id, course_id, date_enrolled) VALUES 
(10, 1, '2025-09-01'), (10, 5, '2025-09-01'), (10, 7, '2025-09-01'), 
(10, 9, '2025-09-01'), (10, 11, '2025-09-01');

-- =====================================================
-- 6. CREATE GRADES
-- =====================================================

-- Generate grades for all enrollments
INSERT INTO grades (student_id, course_id, marks, grade, semester) VALUES 
-- John Doe grades
(1, 1, 88.50, 'B+', 'Fall 2025'), (1, 3, 82.00, 'B', 'Fall 2025'), 
(1, 5, 92.00, 'A-', 'Fall 2025'), (1, 7, 85.00, 'B', 'Fall 2025'), 
(1, 9, 95.00, 'A', 'Fall 2025'), (1, 11, 90.00, 'A-', 'Fall 2025'),

-- Jane Smith grades
(2, 1, 91.00, 'A-', 'Fall 2025'), (2, 3, 87.50, 'B+', 'Fall 2025'), 
(2, 5, 94.00, 'A', 'Fall 2025'), (2, 7, 89.00, 'B+', 'Fall 2025'), 
(2, 11, 93.00, 'A', 'Fall 2025'),

-- Michael Brown grades
(3, 2, 95.00, 'A', 'Fall 2025'), (3, 4, 91.00, 'A-', 'Fall 2025'), 
(3, 8, 88.00, 'B+', 'Fall 2025'), (3, 11, 96.00, 'A', 'Fall 2025'), 
(3, 12, 92.00, 'A-', 'Fall 2025'),

-- Emily Davis grades
(4, 1, 86.00, 'B', 'Fall 2025'), (4, 5, 93.00, 'A', 'Fall 2025'), 
(4, 6, 91.00, 'A-', 'Fall 2025'), (4, 9, 88.00, 'B+', 'Fall 2025'), 
(4, 11, 89.00, 'B+', 'Fall 2025'),

-- David Wilson grades
(5, 1, 84.00, 'B', 'Fall 2025'), (5, 3, 80.00, 'B-', 'Fall 2025'), 
(5, 7, 87.00, 'B+', 'Fall 2025'), (5, 9, 85.00, 'B', 'Fall 2025'), 
(5, 12, 90.00, 'A-', 'Fall 2025'),

-- Sarah Johnson grades
(6, 2, 92.00, 'A-', 'Fall 2025'), (6, 4, 89.00, 'B+', 'Fall 2025'), 
(6, 8, 91.00, 'A-', 'Fall 2025'), (6, 10, 94.00, 'A', 'Fall 2025'), 
(6, 11, 88.00, 'B+', 'Fall 2025'),

-- James Taylor grades
(7, 1, 79.00, 'C+', 'Fall 2025'), (7, 3, 82.00, 'B', 'Fall 2025'), 
(7, 5, 85.00, 'B', 'Fall 2025'), (7, 11, 87.00, 'B+', 'Fall 2025'), 
(7, 12, 84.00, 'B', 'Fall 2025'),

-- Lisa Anderson grades
(8, 1, 90.00, 'A-', 'Fall 2025'), (8, 7, 93.00, 'A', 'Fall 2025'), 
(8, 9, 91.00, 'A-', 'Fall 2025'), (8, 11, 95.00, 'A', 'Fall 2025'),

-- Robert Thomas grades
(9, 2, 88.00, 'B+', 'Fall 2025'), (9, 4, 86.00, 'B', 'Fall 2025'), 
(9, 8, 90.00, 'A-', 'Fall 2025'), (9, 12, 92.00, 'A-', 'Fall 2025'),

-- Jennifer White grades
(10, 1, 87.00, 'B+', 'Fall 2025'), (10, 5, 90.00, 'A-', 'Fall 2025'), 
(10, 7, 86.00, 'B', 'Fall 2025'), (10, 9, 89.00, 'B+', 'Fall 2025'), 
(10, 11, 91.00, 'A-', 'Fall 2025');

-- =====================================================
-- 7. CREATE ATTENDANCE RECORDS (Last 30 days)
-- =====================================================

-- Sample attendance for each enrollment (20 records per enrollment)
-- This is a simplified version - you can expand this

INSERT INTO attendance (student_id, course_id, date, status) 
SELECT e.student_id, e.course_id, DATE_SUB(CURDATE(), INTERVAL n DAY),
       CASE WHEN RAND() > 0.1 THEN 'present' ELSE 'absent' END
FROM enrollments e
CROSS JOIN (
    SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
    UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
    UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
) numbers;

-- =====================================================
-- 8. CREATE NOTIFICATIONS
-- =====================================================

INSERT INTO notifications (title, message, type, teacher_id, course_id) VALUES 
-- General announcements
('Welcome to Fall 2025 Semester', 'Welcome to the new academic year! We wish you all success in your studies.', 'announcement', NULL, NULL),
('Library Extended Hours', 'The library will have extended hours (8 AM - 10 PM) starting next week to support exam preparation.', 'announcement', NULL, NULL),

-- Course-specific notifications
('Midterm Exam Schedule - Mathematics', 'The midterm exam will be held on October 15, 2025, at 10:00 AM in Room A-204. Please arrive 15 minutes early.', 'exam', 1, 1),
('Assignment Due - Calculus', 'Problem Set 6 is due this Friday at 11:59 PM. Submit via the portal.', 'assignment', 1, 1),
('Lab Report Grades Posted', 'Your grades for Lab Report 3 have been posted. Check your grades page.', 'grade', 2, 3),
('Physics Quiz Next Week', 'Quiz on Thermodynamics will be held next Tuesday. Covers chapters 5-7.', 'exam', 2, 3),
('Essay Assignment - Literature', 'Essay on Modernism is due in two weeks. Minimum 1500 words. Check materials for guidelines.', 'assignment', 3, 5),
('New Reading Material', 'I have uploaded the reading material for next week. Please review before class.', 'announcement', 3, 5),
('Chemistry Lab Safety', 'Reminder: Lab safety equipment is mandatory for all lab sessions. No exceptions.', 'reminder', 4, 7),
('Midterm Results Available', 'Midterm exam results are now available. Average score: 82%. Great work!', 'grade', 5, 9),
('Programming Project Due', 'Final programming project is due October 20. Make sure to test your code thoroughly.', 'assignment', 6, 11);

-- =====================================================
-- 9. SUMMARY
-- =====================================================

SELECT 'Database populated successfully!' AS Status;
SELECT COUNT(*) AS 'Total Users' FROM users;
SELECT COUNT(*) AS 'Total Teachers' FROM teachers;
SELECT COUNT(*) AS 'Total Students' FROM students;
SELECT COUNT(*) AS 'Total Courses' FROM courses;
SELECT COUNT(*) AS 'Total Enrollments' FROM enrollments;
SELECT COUNT(*) AS 'Total Grades' FROM grades;
SELECT COUNT(*) AS 'Total Attendance Records' FROM attendance;
SELECT COUNT(*) AS 'Total Notifications' FROM notifications;

-- Display login credentials
SELECT '=== LOGIN CREDENTIALS (Password: password123 for all) ===' AS Info;
SELECT username, role FROM users ORDER BY role, username;