<?php
// Database connection
require_once 'db_connection.php';

// Get the search query from the URL (if any)
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// Base query to fetch departments along with department head and employee count
$sql = "SELECT d.department_id, d.department_name, d.department_abbreviation, d.department_head, 
               (SELECT COUNT(*) FROM employees e WHERE e.department_abbreviation = d.department_abbreviation) AS employee_count
        FROM departments d";

// If search query is provided, add WHERE clause
if ($search_query) {
    $sql .= " WHERE d.department_name LIKE ? OR d.department_head LIKE ? OR d.department_abbreviation LIKE ?";
}

// Add sorting by department_name (A-Z)
$sql .= " ORDER BY d.department_name ASC";


// Prepare and execute SQL statement
$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
if ($search_query) {
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();

// Handle department deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Ensure it's an integer

    // Prepare SQL to delete the department
    $delete_sql = "DELETE FROM departments WHERE department_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        // Redirect to department.php after successful deletion
        header("Location: Department.php?message=Department deleted successfully");
        exit;
    } else {
        // Redirect with an error message
        header("Location: Department.php?message=Error deleting department");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="style.css">

    <title>Department</title>
</head>

<body>
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
            <li  class="active"><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
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

    <!-- CONTENT -->
    <section id="content">

        <nav>
            <i class='bx bx-menu'></i>
            <form action="Department.php" method="GET">
                <div class="form-input">
                    <input type="search" name="search_query" placeholder="Search..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <strong><span id="current-time"></span></strong>
            <strong><span id="current-date"></span></strong>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="" class=""></label>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Manage Department</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="index.php">Home</a></li>
                    </ul>
                </div>
                <a href="add-department.php" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add Department</span>
                </a>
            </div>

            <div id="notification-message" style="color: red; text-align: center;">
                <?php
                    if (isset($_GET['message'])) {
                        echo $_GET['message'];
                    }
                    if (!empty($search_query)) {
                        echo "Showing results for: " . htmlspecialchars($search_query);
                    }
                ?>
            </div>

            <div class="table-data">
                <div class="order">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th style="text-align: center;">Abbreviation</th>
                                <th style="text-align: center;">Department Head</th>
                                <th style="text-align: center;">Number of Employees</th>
                                <th style="text-align: center;">Action</th>
                            </tr>                        
                        </thead>

                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>" . htmlspecialchars($row["department_name"]) . "</td>
                                        <td style='text-align: center;'>" . htmlspecialchars($row["department_abbreviation"]) . "</td>
                                        <td style='text-align: center;'>" . htmlspecialchars($row["department_head"]) . "</td>
                                        <td style='text-align: center;'>" . $row["employee_count"] . "</td>
                                        <td style='text-align: center;'>
                                            <a href='manage-department.php?edit_id=" . $row["department_id"] . "'>Edit</a> | 
                                            <a href='Department.php?delete_id=" . $row["department_id"] . "' onclick='return confirm(\"Are you sure you want to delete this department?\")'>Delete</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center;'>No departments found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script src="script.js"></script>
</body>
</html>
