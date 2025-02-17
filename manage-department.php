<?php
// Database connection
require_once 'db_connection.php';

// Get the department ID from the URL
if (isset($_GET['edit_id'])) {
    $department_id = $_GET['edit_id'];

    // Fetch the department data from the database
        $sql = "SELECT * FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $department_name = $row['department_name'];
            $department_abbreviation = $row['department_abbreviation'];  // Fetch abbreviation
            $department_head = $row['department_head'];
            $department_photo = $row['department_photo'];  // Assuming this column exists
        } else {
            header("Location: department.php?message=Department not found");
            exit();
        }


    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department_name = $_POST['department_name'];
    $department_abbreviation = $_POST['department_abbreviation'];
    $department_head = $_POST['department_head'];

    // Validate inputs
    if (!preg_match("/^[A-Za-z\s]+$/", $department_name)) {
        $error_message = "Department Name should only contain letters and spaces.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $department_head)) {
        $error_message = "Department Head should only contain letters and spaces.";
    }

    if (empty($error_message)) {
        $update_sql = "UPDATE departments SET department_name = ?, department_abbreviation = ?, department_head = ? WHERE department_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $department_name, $department_abbreviation, $department_head, $department_id);

        if ($update_stmt->execute()) {
            header("Location: Department.php?message=Department updated successfully");
            exit();
        } else {
            $error_message = "Error updating department.";
        }

        $update_stmt->close();
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

    <title>Edit Department</title>
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
            <li class="active"><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
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
                    <h1>Manage Department</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="Department.php">Department</a></li>
                    </ul>
                </div>
                <a href="add-department.php" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add Department</span>
                </a>
            </div>


            <?php if (!empty($error_message)) : ?>
            <div style="color: red; text-align: center; margin-bottom: 20px;">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

           <div class="form-container">
                <form action="manage-department.php?edit_id=<?php echo $department_id; ?>" method="POST">
                    <label for="department_name">Department Name</label>
                    <input type="text" id="department_name" name="department_name" value="<?php echo htmlspecialchars($department_name); ?>" required>

                    <label for="department_abbreviation">Department Abbreviation</label>
                    <input type="text" id="department_abbreviation" name="department_abbreviation" value="<?php echo htmlspecialchars($department_abbreviation); ?>" required>

                    <label for="department_head">Department Head</label>
                    <input type="text" id="department_head" name="department_head" value="<?php echo htmlspecialchars($department_head); ?>" required>

                    <button type="submit">Update Department</button>
                </form>
            </div>

        </main>
    </section>
    <script src="script.js"></script>
</body>
</html>
