<?php
// Database connection
require_once 'db_connection.php';

// Initialize error message variable
$error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate employee_number input
    if (empty($_POST['employee_number'])) {
        $error_message = "Employee number is required.";
    } else {
        $employee_number = $_POST['employee_number'];
        $leave_type = $_POST['leave_type'];

        // Check if reason is set and not empty
        if (empty($_POST['reason'])) {
            $error_message = "Reason is required.";
        } else {
            $reason = $_POST['reason'];
        }

        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Check if employee_number exists in the employee table
        $employee_check_query = "SELECT * FROM employees WHERE employee_number = ? LIMIT 1";
        $stmt = $conn->prepare($employee_check_query);
        $stmt->bind_param("s", $employee_number); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Employee number does not exist, show error message
            $error_message = "Invalid Employee number. Please enter a valid employee number.";
        } else {
            // Fetch the employee's remaining leave balance
            $employee = $result->fetch_assoc();
            $remaining_leave_balance = $employee['remaining_leave_balance'];

            // Calculate the duration of the leave request (in days)
            $start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
            $leave_duration = ($end_timestamp - $start_timestamp) / (60 * 60 * 24); // Convert seconds to days

            // Check if the employee has enough leave balance
            if ($leave_duration > $remaining_leave_balance) {
                $error_message = "Error: Insufficient leave balance!";
            } else {
                // Check if start date is greater than end date
                if ($start_date > $end_date) {
                    $error_message = "Start date cannot be later than end date.";
                } else {
                    // Default leave status
                    $status = "Pending"; 

                    // Get the current timestamp for date_logged (it will be automatically set to CURRENT_TIMESTAMP)
                    $date_logged = date("Y-m-d H:i:s");

                    // Prepare an SQL statement for execution
                    $stmt = $conn->prepare("INSERT INTO leave_requests (employee_number, leave_type, reason, start_date, end_date, status, date_logged) VALUES (?, ?, ?, ?, ?, ?, ?)");

                    // Bind the parameters to the query
                    $stmt->bind_param("sssssss", $employee_number, $leave_type, $reason, $start_date, $end_date, $status, $date_logged); 

                    // Execute the query
                    if ($stmt->execute()) {
                        // Successfully inserted into the database, update the leave balance
                        $new_leave_balance = $remaining_leave_balance - $leave_duration;
                        $update_balance_query = "UPDATE employees SET remaining_leave_balance = ? WHERE employee_number = ?";
                        $stmt = $conn->prepare($update_balance_query);
                        $stmt->bind_param("ds", $new_leave_balance, $employee_number);
                        $stmt->execute();

                        echo "<script>
                                window.location.href = 'leave.php'; // Redirect to Leave Request page
                              </script>";
                    } else {
                        $error_message = "Error: " . $stmt->error;
                    }

                    // Close the statement
                    $stmt->close();
                }
            }
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <link rel="stylesheet" href="style.css">
    <title>Add Leave Request</title>

    <!-- jQuery (necessary for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <li><a href="rfid.php"><i class='bx bx-scan'></i><span class="text">RFID</span></a></li>
            <li><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
            <li><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
            <li class="active"><a href="leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
            <li><a href="attendance.php"><i class='bx bx-copy-alt'></i><span class="text">Attendance</span></a></li>
            <li><a href="absent.php"><i class='bx bx-user-x'></i></i><span class="text">Absent</span></a></li>
            <li><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="login.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="#"></form>
            <strong><span id="current-time"></span></strong>
            <strong><span id="current-date"></span></strong>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Add Leave Request</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="leave.php">Leave Request</a></li>
                    </ul>
                </div>

                <a href="#" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add Leave</span>
                </a>
            </div>

            <br>

              <?php if (!empty($error_message)): ?>
                     <div style="color: red; text-align: center; margin-bottom: 20px;" class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div id="notification-message" style="color: red; text-align: center; margin-bottom: 20px;">
                    <?php
                        // Display any notification (e.g., success or error messages)
                        if (isset($_GET['message'])) {
                            echo $_GET['message'];
                        }
                    ?>
                </div>

            <!-- Add Leave Form -->
            <div class="form-container">
                <h2>Enter Leave Request Details</h2>

                <!-- Display Error Message for employee_number -->
              

                <form action="add-leave.php" method="POST">
                    <label for="employee_number">Employee Number</label>
                    <input type="text" id="employee_number" name="employee_number" placeholder="Enter your Employee Number" required>

                    <label for="leave_type">Leave Type</label>
                    <select id="leave_type" name="leave_type" required>
                        <option value="Sick">Sick</option>
                        <option value="Vacation">Vacation</option>
                        <option value="Emergency">Emergency</option>
                        <option value="Maternity">Maternity</option>
                        <option value="Other">Other</option>
                    </select>

                    <label for="reason">Reason</label>
                    <input type="text" id="reason" name="reason" placeholder="Enter reason for leave" required>

                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>

                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" required>

                    <button type="submit">Submit Leave Request</button>
                </form>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="script.js"></script>
</body>
</html>
