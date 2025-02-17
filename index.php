<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection details
require_once 'db_connection.php';


$timeout_duration = 60; // 60 seconds (1 minute)
 // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, "/"); // Delete session cookie
    header("Location: login.php");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity timestamp

// Logout when browser closes by setting a session that expires when the browser is closed
session_set_cookie_params(0); // Cookie expires when browser closes

// Get the search query from the URL
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// SQL query for fetching data with a search filter
$sql = "SELECT a.employee_number, a.full_name, a.time_in_am, a.time_out_am, a.time_in_pm, a.time_out_pm, a.date_logged, e.photo 
        FROM attendance a 
        LEFT JOIN employees e ON a.employee_number = e.employee_number"; // Changed rfid to employee_number
        
// Add search filter to the SQL query
if ($search_query) {
    $sql .= " WHERE a.employee_number LIKE '%$search_query%' OR a.full_name LIKE '%$search_query%' OR e.photo LIKE '%$search_query%'";
}

$attendanceResult = $conn->query($sql);

// Query to get total departments
$deptSql = "SELECT COUNT(*) AS total_departments FROM departments";
$deptResult = $conn->query($deptSql);
$department = $deptResult->fetch_assoc();

// Query to get total employees
$empSql = "SELECT COUNT(*) AS total_employees FROM employees";
$empResult = $conn->query($empSql);
$employee = $empResult->fetch_assoc();

// Query to get total attendance today
$attendanceTodaySql = "SELECT COUNT(*) AS total_attendance_today FROM attendance WHERE date_logged = CURDATE()";
$attendanceTodayResult = $conn->query($attendanceTodaySql);
$attendanceToday = $attendanceTodayResult->fetch_assoc();
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

    <title>Admin Dashboard</title>
</head>
<style>
    .employee-photo {
    width: 50px;  /* Adjust the width */
    height: 50px;  /* Adjust the height */
    object-fit: cover;  /* Ensures the image fits well */
}

</style>
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
            <li class="active">
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
                    <i class='bx bxs-buildings' style='color:#181818'></i>
                    <span class="text">Department</span>
                </a>
            </li>
            <li>
                <a href="Employee.php">
                    <i class='bx bx-face' style='color:#181818'></i>
                    <span class="text">Employees</span>
                </a>
            </li>
            <li>
                <a href="leave.php"><i class='bx bx-archive-in'></i>
                    <span class="text">Leave Request</span>
                </a>
            </li>
            <li>
                <a href="attendance.php">
                    <i class='bx bx-copy-alt' style='color:#181818'></i>
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
                    <i class='bx bxs-notepad' style='color:#181818'></i>
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
            <form action="index.php" method="GET">
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
                    <h1>Admin Dashboard</h1>
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
             
            </div>

            <ul class="box-info">
                <li onclick="window.location.href='Department.php';" style="cursor: pointer;">
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3><?php echo $department['total_departments']; ?></h3>
                        <p>Total Department</p>
                    </span>
                </li>


                <li onclick="window.location.href='Employee.php';" style="cursor: pointer;">
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3><?php echo $employee['total_employees']; ?></h3>
                        <p>Total Employees</p>
                    </span>
                </li>

            </ul>

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
