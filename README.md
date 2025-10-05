# School Management System

A full-stack web application for managing educational institutions with role-based access control for students, teachers, and administrators.

## Features

### Student Portal
- Personal dashboard with real-time statistics
- Course enrollment tracking
- Grade viewing and GPA calculation
- Download course materials
- Receive notifications from teachers
- Attendance monitoring

### Teacher Portal
- Course management dashboard
- Upload and manage course materials (PDF, DOC, PPTX, etc.)
- Send targeted announcements to students
- View enrolled students by course
- Track upload history

### Admin Panel
- Comprehensive user management
- Course creation and teacher assignment
- Student enrollment system
- Grade entry and management
- System-wide analytics
- Role-based access control

## Tech Stack

**Frontend:**
- HTML5, CSS3, JavaScript
- Responsive design
- Dynamic UI with real-time updates

**Backend:**
- PHP 7.4+
- MySQL Database
- Session-based authentication
- Prepared statements (SQL injection protection)

**Security:**
- Password hashing (bcrypt)
- Role-based authorization
- Input validation and sanitization
- Protected file uploads

## Database Schema

8 interconnected tables:
- Users (authentication)
- Students & Teachers (profiles)
- Courses & Enrollments
- Grades & Attendance
- Notifications & Uploads

## Installation

1. Install XAMPP/WAMP/LAMP
2. Import `school.sql` into MySQL
3. Configure `config.php` with database credentials
4. Place files in `htdocs/` directory
5. Access via `localhost/school/`
6. 'sample_data.sql' is some sample data  

## Default Credentials

**Admin:** admin / password  
**Sample Student:** john.doe / password  
**Sample Teacher:** dr.smith / password

## Key Functionalities

- Automatic student ID generation
- File upload with type/size validation
- Real-time grade calculation
- Cross-referenced foreign key relationships
- Transaction-based data integrity
- Attendance tracking system

## Future Enhancements

- Email notification integration
- Assignment submission portal
- Interactive timetable
- PDF report generation
- Mobile app version
- API endpoints for third-party integration
