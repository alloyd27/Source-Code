<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to store success/error messages
session_start();

// Database connection
$host = 'localhost';
$dbname = 'u766310616_attendance';
$username = 'u766310616_attendance';
$password = '>yaMqWuB4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Handle the delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $delete_sql = "DELETE FROM employees WHERE id = :id";
        $stmt = $pdo->prepare($delete_sql);
        $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt->execute();

        // Set success message in session
        $_SESSION['message'] = "Employee deleted successfully!";
        header("Location: Employee.php");
        exit();
    } catch (PDOException $e) {
        echo 'Error deleting employee: ' . $e->getMessage();
    }
}

// Capture the search query from the form submission
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Updated query with search functionality
$sql = "SELECT * FROM employees";

// Apply search filter if a search query is provided
if ($search_query !== '') {
    $sql .= " WHERE first_name LIKE :search_query 
              OR last_name LIKE :search_query 
              OR rfid LIKE :search_query 
              OR email LIKE :search_query 
              OR position LIKE :search_query";
}

// Add sorting by last_name (A-Z)
$sql .= " ORDER BY last_name ASC";


try {
    $stmt = $pdo->prepare($sql);
    if ($search_query !== '') {
        $search_pattern = '%' . $search_query . '%';
        $stmt->bindParam(':search_query', $search_pattern, PDO::PARAM_STR);
    }
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error fetching employees: ' . $e->getMessage();
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
    <title>Employee</title>
</head>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 8px;
        text-align: center; /* Center-align text in header and data cells */
    }

    td img {
        display: block;
        margin: 0 auto; /* Centers the image inside its cell */
    }

    th {
        background-color: #f4f4f4; /* Optional: background color for headers */
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
            <li><a href="index.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li><a href="rfid.php"><i class='bx bx-scan'></i><span class="text">RFID</span></a></li>
            <li><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
            <li class="active"><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
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
            <form action="Employee.php" method="GET">
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
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Manage Employee</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="index.php">Home</a></li>
                    </ul>
                </div>

                <a href="add-employee.php" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add Employee</span>
                </a>
            </div>

            <br>

            <!-- Display success or error messages -->
            <div id="notification-message" style="color: red; text-align: center;">
                <?php
                    // Display success or delete messages
                    if (isset($_SESSION['message'])) {
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);  // Clear the message after displaying
                    }

                    // Display search notification
                    if (!empty($search_query)) {
                        if (count($employees) > 0) {
                            echo "Showing results for: " . htmlspecialchars($search_query);
                        } else {
                            echo "No results found for: " . htmlspecialchars($search_query);
                        }
                    }
                ?>
            </div>

            <br>

            <div class="table-data" style="text-align: center; width: 100%; margin: 0 auto;">
                <div class="order">
                    <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Photo</th>
                                <th style="text-align: center;">Employee Number</th>
                                <th style="text-align: center;">Last Name</th>
                                <th style="text-align: center;">First Name</th>
                                <th style="text-align: center;">Middle Name</th>
                                <th style="text-align: center;">Date of Birth</th>
                                <th style="text-align: center;">Place of Birth</th>
                                <th style="text-align: center;">Civil Status</th>
                                <th style="text-align: center;">Contact Number</th>
                                <th style="text-align: center;">Email Address</th>
                                <th style="text-align: center;">Street</th>
                                <th style="text-align: center;">Barangay</th>
                                <th style="text-align: center;">City</th>
                                <th style="text-align: center;">Province</th>
                                <th style="text-align: center;">Postal Code</th>
                                <th style="text-align: center;">Date Hired</th>
                                <th style="text-align: center;">Position</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($employees) > 0): ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><img src="<?= $employee['photo']; ?>" alt="Employee Photo" width="50"></td>
                                        <td><?= $employee['employee_number']; ?></td>
                                        <td><?= $employee['last_name']; ?></td>
                                        <td><?= $employee['first_name']; ?></td>
                                        <td><?= $employee['middle_name']; ?></td>
                                        <td><?= $employee['dob']; ?></td>
                                        <td><?= $employee['birthplace']; ?></td>
                                        <td><?= $employee['civil_status']; ?></td>
                                        <td><?= $employee['contact_number']; ?></td>
                                        <td><?= $employee['email']; ?></td>
                                        <td><?= $employee['street']; ?></td>
                                        <td><?= $employee['barangay']; ?></td>
                                        <td><?= $employee['city']; ?></td>
                                        <td><?= $employee['province']; ?></td>
                                        <td><?= $employee['postal_code']; ?></td>
                                        <td><?= $employee['date_hired']; ?></td>
                                        <td><?= $employee['position']; ?></td>
                                        <td><a href="manage-employee.php?id=<?= $employee['id']; ?>">Edit</a> | 
                                            <a href="Employee.php?delete_id=<?= $employee['id']; ?>" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="19">No employees found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>

    <script src="script.js"></script>
</body>
</html>