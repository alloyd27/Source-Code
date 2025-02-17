<?php
// Database connection
require_once 'db_connection.php';

// Handle edit form submission
if (isset($_POST['update'])) {
    if (isset($_POST['leave_id'])) {
        $leave_id = $_POST['leave_id'];
        $leave_type = $_POST['leave_type'];
        $reason = $_POST['reason'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];

        // Fetch previous leave details
        $fetch_old_sql = "SELECT employee_number, DATEDIFF(end_date, start_date) + 1 AS old_days FROM leave_requests WHERE leave_id = ?";
        $fetch_old_stmt = $conn->prepare($fetch_old_sql);
        $fetch_old_stmt->bind_param("i", $leave_id);
        $fetch_old_stmt->execute();
        $old_leave_result = $fetch_old_stmt->get_result();

        if ($old_leave_result->num_rows > 0) {
            $old_leave = $old_leave_result->fetch_assoc();
            $employee_number = $old_leave['employee_number'];
            $old_days = (int) $old_leave['old_days'];

            // Calculate new leave days
            $new_days = (int) ((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24)) + 1;

            // Fetch employee's current leave balance
            $fetch_balance_sql = "SELECT remaining_leave_balance FROM employees WHERE employee_number = ?";
            $fetch_balance_stmt = $conn->prepare($fetch_balance_sql);
            $fetch_balance_stmt->bind_param("s", $employee_number);
            $fetch_balance_stmt->execute();
            $balance_result = $fetch_balance_stmt->get_result();

            if ($balance_result->num_rows > 0) {
                $employee_data = $balance_result->fetch_assoc();
                $current_balance = (int) $employee_data['remaining_leave_balance'];

                // Adjust leave balance
                $adjusted_balance = $current_balance + $old_days - $new_days;

                // Ensure balance doesn't go negative
                if ($adjusted_balance < 0) {
                    $message = "Error: Insufficient leave balance!";
                } else {
                    // Update Leave Request
                    $update_sql = "UPDATE leave_requests SET leave_type = ?, reason = ?, start_date = ?, end_date = ?, status = ? WHERE leave_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("sssssi", $leave_type, $reason, $start_date, $end_date, $status, $leave_id);

                    if ($update_stmt->execute()) {
                        // Update Employee Leave Balance
                        $update_balance_sql = "UPDATE employees SET remaining_leave_balance = ? WHERE employee_number = ?";
                        $update_balance_stmt = $conn->prepare($update_balance_sql);
                        $update_balance_stmt->bind_param("is", $adjusted_balance, $employee_number);
                        $update_balance_stmt->execute();

                        $message = "Leave request updated successfully!";
                    } else {
                        $message = "Error updating leave request!";
                    }
                }
            }
        }
    } else {
        $message = "Error: Leave Request ID is missing.";
    }
}

// Fetch all leave requests
$sql = "SELECT lr.leave_id, lr.leave_type, lr.reason, lr.start_date, lr.end_date, lr.status, CONCAT(e.first_name, ' ', e.last_name) AS full_name 
        FROM leave_requests lr 
        LEFT JOIN employees e ON lr.employee_number = e.employee_number";
$result = $conn->query($sql);

// Fetch leave request details if editing
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_sql = "SELECT * FROM leave_requests WHERE leave_id = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();

    if ($edit_result->num_rows > 0) {
        $edit_leave = $edit_result->fetch_assoc();
    } else {
        $message = "Leave request not found.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>Manage Leave Requests</title>
</head>
<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="index.php" class="brand">
            <div class="logo-image">
                <img src="img/logo.png" alt="Logo">
            </div>
            <span class="text">Municipality of Lucban</span>
        </a>
        <ul class="side-menu top">
            <li><a href="index.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li><a href="rfid.php"><i class='bx bx-scan'></i><span class="text">RFID</span></a></li>
            <li><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
            <li><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
            <li class="active"><a href="manage-leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
            <li><a href="attendance.php"><i class='bx bx-copy-alt'></i><span class="text">Attendance</span></a></li>
            <li><a href="absent.php"><i class='bx bx-user-x'></i></i><span class="text">Absent</span></a></li>
            
            <li><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <!-- CONTENT -->
    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="#">
                <div class="form-input">
                </div>
            </form>
            <strong><span id="current-time"></span></strong>
            <strong><span id="current-date"></span></strong>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Manage Leave</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="leave.php">Leave Request</a></li>
                    </ul>
                </div>

                <a href="add-leave.php" class="btn-download">
                    <i class='bx bxs-group'></i><span class="text">Add Leave</span>
                </a>
            </div>

            <br>

            <?php if (isset($message)) { ?>
                <div style="color: red; text-align: center; margin-bottom: 20px;" class="message"><?php echo $message; ?></div>
            <?php } ?>

            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['full_name'] . "</td>";
                                    echo "<td>" . $row['leave_type'] . "</td>";
                                    echo "<td>" . $row['start_date'] . "</td>";
                                    echo "<td>" . $row['end_date'] . "</td>";
                                    echo "<td>" . $row['status'] . "</td>";
                                    echo "<td><a href='?edit_id=" . $row['leave_id'] . "'>Edit</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No leave requests found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (isset($_GET['edit_id'])) { ?>
                <div class="form-container">
                    <h2>Edit Leave Request</h2>
                    <form action="manage-leave.php" method="POST">
                        <input type="hidden" name="leave_id" value="<?php echo $edit_leave['leave_id']; ?>">

                        <label for="leave_type">Leave Type</label>
                        <select id="leave_type" name="leave_type" required>
                            <option value="Sick" <?php echo ($edit_leave['leave_type'] == 'Sick') ? 'selected' : ''; ?>>Sick</option>
                            <option value="Vacation" <?php echo ($edit_leave['leave_type'] == 'Vacation') ? 'selected' : ''; ?>>Vacation</option>
                            <option value="Emergency" <?php echo ($edit_leave['leave_type'] == 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
                            <option value="Maternity" <?php echo ($edit_leave['leave_type'] == 'Maternity') ? 'selected' : ''; ?>>Maternity</option>
                            <option value="Other" <?php echo ($edit_leave['leave_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>

                        <label for="reason">Reason</label>
                        <textarea id="reason" name="reason" required><?php echo $edit_leave['reason']; ?></textarea>

                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $edit_leave['start_date']; ?>" required>

                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $edit_leave['end_date']; ?>" required>

                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Pending" <?php echo ($edit_leave['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo ($edit_leave['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Denied" <?php echo ($edit_leave['status'] == 'Denied') ? 'selected' : ''; ?>>Denied</option>
                        </select>

                        <button type="submit" name="update">Update Leave Request</button>
                    </form>
                </div>
            <?php } ?>

        </main>
    </section>

    <script src="script.js"></script>
</body>
</html>
