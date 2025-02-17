<?php
// Database connection
require_once 'db_connection.php';

// Fetch RFID data from the database
$sql = "SELECT * FROM rfid";
$result = $conn->query($sql);

// Handle Delete action
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_sql = "DELETE FROM rfid WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        // Redirect after deleting
        header("Location: rfid.php?message=RFID deleted successfully");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Check if the RFID is already used by any employee
function isRfidUsed($rfid) {
    global $conn;
    $check_sql = "SELECT COUNT(*) FROM employees WHERE rfid = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>Manage RFID</title>
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
            <li class="active"><a href="rfid.php"><i class='bx bx-scan'></i><span class="text">RFID</span></a></li>
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
            <input type="checkbox" id="switch-mode" hidden>
            <label for="" class=""></label>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Manage RFID</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="index.php">Home</a></li>
                    </ul>
                </div>

                <a href="add-rfid.php" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add RFID</span>
                </a>
            </div>

            <!-- Display success or error message -->
            <?php
            if (isset($_GET['message'])) {
                echo "<div style='color: red; text-align: center; margin-bottom: 20px;'>";
                echo htmlspecialchars($_GET['message']);
                echo "</div>";
            }
            ?>

            <!-- Success Message -->
            <?php if (!empty($success_message)): ?>
                <div style="color: red; text-align: center; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- RFID Table -->
            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center;">ID</th>
                                <th style="text-align: center;">RFID Code</th>
                                <th style="text-align: center;">Action</th>
                            </tr>                         
                        </thead>
                        <tbody>
                            <?php
                            // Check if there are any rows returned by the query
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $rfid = $row['rfid'];
                                    $rfid_style = isRfidUsed($rfid) ? 'color: red;' : '';  // Apply red color if RFID is used
                                    echo "<tr>";
                                    echo "<td align='center'>" . $row['id'] . "</td>";
                                    echo "<td align='center' style='$rfid_style'>" . $rfid . "</td>";
                                    echo "<td align='center'>
                                            <a href='rfid.php?delete=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this record?\")'>Delete</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No RFID records found</td></tr>";
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

<?php
// Close the database connection
$conn->close();
?>
