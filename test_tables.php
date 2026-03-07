<?php
require_once 'config.php';
$conn = getDBConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}
echo "TABLES IN DB:\n";
print_r($tables);

echo "\n\nUPLOADS TABLE SCHEMA:\n";
$res = $conn->query("DESCRIBE uploads");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "ERROR: " . $conn->error;
}
$conn->close();
?>
