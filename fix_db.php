<?php
require_once 'config.php';
$conn = getDBConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS uploads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT,
  course_id INT,
  file_name VARCHAR(255),
  file_type VARCHAR(50),
  file_path VARCHAR(500),
  title VARCHAR(255),
  description TEXT,
  material_type VARCHAR(50),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'uploads' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
?>
