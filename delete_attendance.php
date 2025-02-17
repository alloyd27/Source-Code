<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance_monitoring";

// Create connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Perform the deletion of records if the conditions match (6 PM)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete attendance records where time_in_pm and time_out_pm are null
    $sql = "DELETE FROM attendance WHERE time_in_pm IS NULL AND time_out_pm IS NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Attendance records deleted successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
</head>
<body>

    <h1>Attendance Management System</h1>

    <script>
        // Check every minute if it's 6 PM
        setInterval(function() {
            var currentTime = new Date();
            if (currentTime.getHours() === 14 && currentTime.getMinutes() === 0) {
                // Send POST request to delete the records
                fetch(window.location.href, {
                    method: 'POST',
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data); // Log success message
                })
                .catch(error => {
                    console.log("Error deleting attendance: ", error);
                });
            }
        }, 60000); // Check every minute
    </script>

</body>
</html>
