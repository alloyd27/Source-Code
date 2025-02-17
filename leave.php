<?php
// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Database connection
require_once 'db_connection.php';
// Search query for leave requests
$search_query = isset($_GET['search_query']) ? mysqli_real_escape_string($conn, $_GET['search_query']) : '';

$sql = "SELECT lr.leave_id, lr.employee_number, lr.leave_type, lr.reason, lr.start_date, lr.end_date, lr.status, lr.date_logged, 
        e.leave_balance, CONCAT(e.last_name, ', ', e.first_name) AS full_name
        FROM leave_requests lr
        LEFT JOIN employees e ON lr.employee_number = e.employee_number";

if (!empty($search_query)) {
    $sql .= " WHERE lr.employee_number LIKE '%$search_query%' 
              OR e.first_name LIKE '%$search_query%' 
              OR e.last_name LIKE '%$search_query%' 
              OR lr.leave_type LIKE '%$search_query%' 
              OR lr.reason LIKE '%$search_query%'";
}

$result = $conn->query($sql);

// Handle leave status update (Approval/Denial)
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $leave_id = $_GET['update_status'];
    $new_status = $_GET['status'];

    // Get leave request details
    $leave_query = "SELECT employee_number, start_date, end_date FROM leave_requests WHERE leave_id = ?";
    $stmt = $conn->prepare($leave_query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $leave_result = $stmt->get_result()->fetch_assoc();

    if ($leave_result) {
        $employee_number = $leave_result['employee_number'];
        $start_date = new DateTime($leave_result['start_date']);
        $end_date = new DateTime($leave_result['end_date']);
        $leave_days = $start_date->diff($end_date)->days + 1;

        // Get employee's current leave balance
        $balance_query = "SELECT leave_balance FROM employees WHERE employee_number = ?";
        $stmt = $conn->prepare($balance_query);
        $stmt->bind_param("i", $employee_number);
        $stmt->execute();
        $balance_result = $stmt->get_result()->fetch_assoc();
        $current_balance = $balance_result['leave_balance'];

        if ($new_status === 'Approved' && $current_balance < $leave_days) {
            echo "<script>alert('Not enough leave balance!'); window.location.href = 'leave.php';</script>";
            exit;
        }

        // Update leave status
        $update_sql = "UPDATE leave_requests SET status = ? WHERE leave_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_status, $leave_id);
        $stmt->execute();

        // Deduct leave balance if approved
        if ($new_status === 'Approved') {
            $deduct_leave_sql = "UPDATE employees SET leave_balance = leave_balance - ? WHERE employee_number = ?";
            $stmt = $conn->prepare($deduct_leave_sql);
            $stmt->bind_param("ii", $leave_days, $employee_number);
            $stmt->execute();
        }

        echo "<script>alert('Status updated successfully'); window.location.href = 'leave.php';</script>";
    }
}

// Handle leave deletion
if (isset($_GET['delete_id'])) {
    $leave_id = $_GET['delete_id'];

    // Delete leave request
    $leave_sql = "DELETE FROM leave_requests WHERE leave_id = ?";
    $stmt = $conn->prepare($leave_sql);
    $stmt->bind_param("i", $leave_id);

    if ($stmt->execute()) {
        header("Location: leave.php?message=Leave Request deleted successfully");
        exit;
    } else {
        header("Location: leave.php?message=Error deleting Leave Request");
        exit;
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
    <title>AdminHub</title>
    <script type="text/javascript">
        function confirmDelete(id) {
            var confirmAction = confirm('Are you sure you want to delete this leave request?');
            if (confirmAction) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
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
            <li>
                <a href="index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="rfid.php">
                    <i class='bx bx-scan'></i>
                    <span class="text">RFID</span>
                </a>
            </li>
            <li>
                <a href="Department.php">
                    <i class='bx bxs-buildings'></i>
                    <span class="text">Department</span>
                </a>
            </li>
            <li>
                <a href="Employee.php">
                    <i class='bx bx-face'></i>
                    <span class="text">Employees</span>
                </a>
            </li>
            <li class="active">
                <a href="leave.php">
                    <i class='bx bx-archive-in'></i>
                    <span class="text">Leave Request</span>
                </a>
            </li>
            <li>
                <a href="attendance.php">
                    <i class='bx bx-copy-alt'></i>
                    <span class="text">Attendance</span>
                </a>
            </li>
            <li>
                <a href="absent.php">
                    <i class='bx bx-user-x'></i></i>
                    <span class="text">Absent</span>
                </a>
            </li>
            
            <li>
                <a href="report.php">
                    <i class='bx bxs-notepad'></i>
                    <span class="text">Report</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="leave.php" method="GET">
                <div class="form-input">
                    <input type="search" name="search_query" placeholder="Search..." value="<?php echo isset($_GET['search_query']) ? $_GET['search_query'] : ''; ?>">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <strong><span id="current-time"></span></strong>
            <strong><span id="current-date"></span></strong>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="" class=""></label>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Manage Leave</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="index.php">Home</a>
                        </li>
                    </ul>
                </div>

                <a href="add-leave.php" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add Leave</span>
                </a>
            </div>

            <!-- Notification Message -->
            <div id="notification-message" style="color: red; text-align: center;">
                <?php
                    if (isset($_GET['message'])) {
                        echo $_GET['message'];
                    }
                    if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
                        echo "Showing results for: " . htmlspecialchars($_GET['search_query']);
                    }
                ?>
            </div>

            <div class="table-data">
                <div class="order">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Employee Number</th>
                                <th style="text-align: center;">Full Name</th>
                                <th style="text-align: center;">Type of Leave</th>
                                <th style="text-align: center;">Reason</th>
                                <th style="text-align: center;">Start Date</th>
                                <th style="text-align: center;">End Date</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Date Logged</th> 
                                <th style="text-align: center;">Remaining Leave</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    // Convert the stored date_logged to Manila timezone
                                    $date_logged = new DateTime($row['date_logged'], new DateTimeZone('UTC'));
                                    $date_logged->setTimezone(new DateTimeZone('Asia/Manila'));
                                    $formatted_date_logged = $date_logged->format('Y-m-d H:i:s');

                                    echo "<tr>
                                            <td style='text-align: center;'>{$row['employee_number']}</td>
                                            <td style='text-align: center;'>{$row['full_name']}</td>
                                            <td style='text-align: center;'>{$row['leave_type']}</td>
                                            <td style='text-align: center;'>{$row['reason']}</td>
                                            <td style='text-align: center;'>{$row['start_date']}</td>
                                            <td style='text-align: center;'>{$row['end_date']}</td>
                                            <td style='text-align: center;'>{$row['status']}</td>
                                            <td style='text-align: center;'>{$formatted_date_logged}</td>
                                            <td style='text-align: center;'>{$row['leave_balance']}</td>
                                            <td style='text-align: center;'>
                                                <a href='?update_status={$row['leave_id']}&status=Approved'>Approve</a> |
                                                <a href='?update_status={$row['leave_id']}&status=Denied'>Deny</a> |
                                                <a href='manage-leave.php?id={$row['leave_id']}'>Edit</a> |
                                                <a href='leave.php?delete_id={$row['leave_id']}' onclick='return confirm(\"Are you sure you want to delete this Leave Request?\")'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align: center;'>No leave requests found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="script.js"></script>
</body>
</html>
