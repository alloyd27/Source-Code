<?php
// Database connection
require_once 'db_connection.php';

$error_message = ""; // To store error messages

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $department_name = $_POST['department_name'];
    $department_abbreviation = $_POST['department_abbreviation'];
    $department_head = $_POST['department_head'];

    // Validate inputs
    if (!preg_match("/^[A-Za-z\s]+$/", $department_name)) {
        $error_message = "Department Name should only contain letters and spaces.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $department_head)) {
        $error_message = "Department Head should only contain letters and spaces.";
    } elseif (!preg_match("/^[A-Za-z0-9]+$/", $department_abbreviation) || strlen($department_abbreviation) > 10) {
        $error_message = "Department Abbreviation should be up to 10 characters (letters and numbers only).";
    }

    // Proceed only if there's no validation error
    if (empty($error_message)) {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO departments (department_name, department_abbreviation, department_head) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $department_name, $department_abbreviation, $department_head);

        // Execute query and redirect
        if ($stmt->execute()) {
            header("Location: Department.php?message=Department added successfully");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

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
    <title>Add Department</title>
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
    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="#"></form>
            <strong><span id="current-time"></span></strong>
            <strong><span id="current-date"></span></strong>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="" class=""></label>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Add Department</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li><a class="active" href="Department.php">Department</a></li>
                    </ul>
                </div>
            </div>

            <br>

            <?php if (!empty($error_message)) : ?>
            <div style="color: red; text-align: center; margin-bottom: 20px;">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

           <div class="form-container">
                <h2>Enter Department Details</h2>
                <form action="add-department.php" method="POST">
                    <label for="department_name">Department Name</label>
                    <input type="text" id="department_name" name="department_name" placeholder="Enter Department Name" required>

                    <label for="department_abbreviation">Department Abbreviation</label>
                    <input type="text" id="department_abbreviation" name="department_abbreviation" placeholder="e.g., HR, IT" required>

                    <label for="department_head">Department Head</label>
                    <input type="text" id="department_head" name="department_head" placeholder="Enter Department Head Name" required>

                    <button type="submit">Save Department</button>
                </form>
            </div>

        </main>
    </section>
</body>
</html>
