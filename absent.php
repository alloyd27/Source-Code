<?php
// Database connection
require_once 'db_connection.php';

// Handle deletion of an individual absent employee
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // SQL query to delete the record for the specified employee number
    $delete_sql = "DELETE FROM attendance WHERE employee_number = ? AND DATE(date_logged) = CURDATE()";

    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $delete_id); // Assuming employee_number is a string, adjust the data type if necessary

    if ($stmt->execute()) {
        // Redirect back to the same page after deletion to refresh the list
        header("Location: absent.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle bulk deletion of all absent employees
if (isset($_POST['delete_all_absent'])) {
    $delete_sql = "DELETE FROM attendance WHERE DATE(date_logged) = CURDATE() AND status = 'Absent'";

    if ($conn->query($delete_sql)) {
        echo "<script>alert('All absent records have been deleted.'); window.location='absent.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Get the search query from the URL (if any)
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// Update SQL to include department and position
$sql = "SELECT e.employee_number, 
               CONCAT(e.last_name, ', ', e.first_name) AS full_name, 
               e.department_abbreviation, 
               e.position,
               CURDATE() AS date_logged, 
               'Absent' AS status
        FROM employees e
        LEFT JOIN attendance a 
        ON e.employee_number = a.employee_number AND a.date_logged = CURDATE()
        WHERE a.employee_number IS NULL";

// If a search query is provided, filter by employee_number or full_name
if ($search_query) {
    $sql .= " AND (e.employee_number LIKE '%$search_query%' OR CONCAT(e.first_name, ' ', e.last_name) LIKE '%$search_query%')";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="short icon" type="x-icon" href="img/logo.png">
    <link rel="stylesheet" href="style.css">
    <title>Absent Employees</title>
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
            <li><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
            <li><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
            <li><a href="leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
            <li><a href="attendance.php"><i class='bx bx-copy-alt'></i><span class="text">Attendance</span></a></li>
            <li class="active"><a href="absent.php"><i class='bx bx-user-x'></i><span class="text">Absent</span></a></li>
            <li><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <section id="content">
         <nav>
            <i class='bx bx-menu'></i>
            <form action="absent.php" method="GET">
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
                <h1>Manage Absent</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="index.php">Home</a></li>
                </ul>
            </div>
        </div>



        <div class="table-data">
            <div class="order">
                <table>
                   <thead>
    <tr>
        <th>Date Logged</th>
        <th>Employee Number</th>
        <th>Full Name</th>
        <th>Department Name</th>
        <th>Position</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['date_logged']}</td>
                <td>{$row['employee_number']}</td>
                <td>{$row['full_name']}</td>
                <td>{$row['department_abbreviation']}</td>
                <td>{$row['position']}</td>
                <td><strong>{$row['status']}</strong></td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No absentees found</td></tr>";
    }
    ?>
</tbody>

                </table>
            </div>
        </div>
    </section>
</main>

    <script src="script.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
