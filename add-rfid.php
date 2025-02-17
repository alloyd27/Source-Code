<?php
// Database connection
require_once 'db_connection.php';

// Initialize error and success messages
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the RFID number from the form
    $rfid_number = $_POST['rfid_number'];

    // Validate RFID number
    if (!empty($rfid_number) && is_numeric($rfid_number)) {
        // Check if RFID number already exists in the database
        $check_sql = "SELECT * FROM rfid WHERE rfid = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("s", $rfid_number);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // RFID already exists
            $error_message = "Error: RFID number already exists!";
        } else {
            // Insert RFID number into the database (without status)
            $sql = "INSERT INTO rfid (rfid) VALUES (?)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                die("Error in preparing the SQL statement: " . $conn->error);
            }

            $stmt->bind_param("s", $rfid_number);

            if ($stmt->execute()) {
                // Redirect to rfid.php with a success message
                header("Location: rfid.php?message=RFID added successfully");
                exit();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
    } else {
        $error_message = "RFID number must be a valid number!";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <link rel="stylesheet" href="style.css">
    <title>Add RFID</title>
   
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="index.php" class="brand">
            <div class="logo-image">
                <img src="img/logo.png" alt="">
            </div>
            <span class="text">Municipality of Lucban</span>
        </a>
        <ul class="side-menu top">
            <li><a href="index.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li class="active"><a href="rfid.php"><i class='bx bxs-buildings'></i><span class="text">RFID</span></a></li>
            <li><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
            <li><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
            <li><a href="leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
            <li><a href="attendance.php"><i class='bx bx-copy-alt'></i><span class="text">Attendance</span></a></li>
            <li><a href="absent.php"><i class='bx bx-user-x'></i></i><span class="text">Absent</span></a></li>
            <li><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <strong><span id="current-time"></span></strong>
            <strong><span id="current-date"></span></strong>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Add RFID</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="rfid.php">RFID</a></li>
                    </ul>
                </div>
            </div>

            <br>

              <!-- Error/Success Message -->
                <?php if ($error_message): ?>
                    <div style="color: red; text-align: center; margin-bottom: 20px;" class="message error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php elseif ($success_message): ?>
                    <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

            <div class="form-container">
                <h2>Enter RFID Details</h2>

                <!-- RFID Form -->
                <form action="" method="POST">
                    <label for="rfid_number">RFID Number:</label>
                    <input type="number" id="rfid_number" name="rfid_number" placeholder="Enter RFID Number" required>
                    
                    <button type="submit">Save RFID</button>
                </form>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="script.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
