<?php
// Database Connection Configuration

$servername = "localhost";
$username = "u766310616_attendance";  // Your database username
$password = ">yaMqWuB4";  // Your database password
$dbname = "u766310616_attendance";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
