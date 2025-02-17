<?php
// Database connection

$servername = "localhost";
$username = "u766310616_attendance";  // Your database username
$password = ">yaMqWuB4";  // Your database password
$dbname = "u766310616_attendance";  // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
// Handle update request
if (isset($_POST['update'])) {
    // Capture form data
    $id = $_POST['id'];
    $rfid = $_POST['rfid'];
    $employee_number = $_POST['employee_number'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $dob = $_POST['dob'];
    $birthplace = $_POST['birthplace'];
    $sex = $_POST['sex'];
    $civil_status = $_POST['civil_status'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];
     $department_abbreviation = $_POST['department_abbreviation'];
    $date_hired = $_POST['date_hired'];
    $position = $_POST['position'];

    // Handle photo upload
    $photo_path = isset($_POST['existing_photo']) ? $_POST['existing_photo'] : ''; // Default to existing photo if no new one is uploaded

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = $_FILES['photo']['name'];
        $target_dir = "uploads/";  // Folder where images will be stored
        $target_file = $target_dir . basename($photo);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is a valid image
        if ($check = getimagesize($_FILES['photo']['tmp_name'])) {
            $uploadOk = 1;
        } else {
            $uploadOk = 0;
            $message = "File is not an image.";
        }

        // Check file size
        if ($_FILES['photo']['size'] > 500000) {  // Example limit: 500 KB
            $uploadOk = 0;
            $message = "Sorry, your file is too large.";
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadOk = 0;
            $message = "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        }

        // If file upload is successful, move it to the server
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $photo_path = $target_file;
            } else {
                $photo_path = $_POST['existing_photo'];  // If upload fails, use existing photo
            }
        }
    }



    // Update query
       $update_sql = "UPDATE employees SET 
        employee_number = ?, rfid = ?, last_name = ?, first_name = ?, middle_name = ?, 
        dob = ?, birthplace = ?, sex = ?, civil_status = ?, contact_number = ?, 
        email = ?, street = ?, barangay = ?, city = ?, province = ?, postal_code = ?, 
        department_abbreviation = ?, date_hired = ?, position = ?, photo = ? 
        WHERE id = ?";


    $update_stmt = $conn->prepare($update_sql);

    // Ensure the bind_param types are correctly quoted
       $update_stmt->bind_param(
        "sssssssssssssssssssii", // Adjust types
        $employee_number, $rfid, $last_name, $first_name, $middle_name, $dob, 
        $birthplace, $sex, $civil_status, $contact_number, $email, $street, 
        $barangay, $city, $province, $postal_code, $department_abbreviation, $date_hired, 
        $position, $photo_path, $id
    );


    // Execute the update statement
    if ($update_stmt->execute()) {
        $message = "Employee updated successfully!";
        // Redirect to Employee page after successful update
        header("Location: Employee.php");
        exit(); // Make sure to call exit after the header redirect
    } else {
        $message = "Error updating employee!";
    }
}

// Fetch employee data to pre-fill the form (if in edit mode)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Corrected SQL query for fetching the employee by ID
    $sql = "SELECT * FROM employees WHERE id = ?";
    $edit_stmt = $conn->prepare($sql);
    $edit_stmt->bind_param("i", $id);  // binding the id parameter
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    
    // Check if the employee record was found
    if ($edit_result->num_rows > 0) {
        $edit_employee = $edit_result->fetch_assoc();
    } else {
        // No employee found with the given ID, handle accordingly
        $message = "Employee not found!";
    }
}


// Fetch departments
$departments = [];
$dept_result = $conn->query("SELECT department_abbreviation FROM departments");
if ($dept_result) {
    while ($row = $dept_result->fetch_assoc()) {
        $departments[] = $row;
    }
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
    <title>Manage Employees</title>
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

    <!-- CONTENT -->
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
                    <h1>Manage Employees</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="Employee.php">Employees</a></li>
                    </ul>
                </div>
                <a href="add-employee.php" class="btn-download">
                    <i class='bx bxs-group'></i>
                    <span class="text">Add Employee</span>
                </a>
            </div>

            <br>
            <?php if (isset($message)) { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>


            <?php if (isset($edit_employee)) { ?>
    <div class="form-container">
        <h2>Edit Employee</h2>
        <form action="manage-employee.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $edit_employee['id']; ?>">
            
            <label for="employee_number">Employee Number</label>
            <input type="text" name="employee_number" value="<?php echo $edit_employee['employee_number']; ?>" required>


            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" value="<?php echo $edit_employee['last_name']; ?>" required>
            


    <label for="first_name">First Name</label>
    <input type="text" name="first_name" value="<?php echo $edit_employee['first_name']; ?>" required>

    <label for="middle_name">Middle Name</label>
    <input type="text" name="middle_name" value="<?php echo $edit_employee['middle_name']; ?>" required>

    <label for="dob">Date of Birth</label>
    <input type="date" name="dob" value="<?php echo $edit_employee['dob']; ?>" required>

    <label for="birthplace">Birthplace</label>
    <input type="text" name="birthplace" value="<?php echo $edit_employee['birthplace']; ?>" required>

    <label for="sex">Sex</label>
    <select name="sex">
        <option value="Male" <?php echo ($edit_employee['sex'] == 'Male') ? 'selected' : ''; ?>>Male</option>
        <option value="Female" <?php echo ($edit_employee['sex'] == 'Female') ? 'selected' : ''; ?>>Female</option>
    </select>

    <label for="civil_status">Civil Status</label>
    <select name="civil_status">
        <option value="Single" <?php echo ($edit_employee['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
        <option value="Married" <?php echo ($edit_employee['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
    </select>

    <label for="contact_number">Contact Number</label>
    <input type="text" name="contact_number" value="<?php echo $edit_employee['contact_number']; ?>" required>

    <label for="email">Email Address</label>
    <input type="email" name="email" value="<?php echo $edit_employee['email']; ?>" required>

    <label for="street">Street</label>
    <input type="text" name="street" value="<?php echo $edit_employee['street']; ?>" required>

    <label for="barangay">Barangay</label>
    <input type="text" name="barangay" value="<?php echo $edit_employee['barangay']; ?>" required>

    <label for="city">City</label>
    <input type="text" name="city" value="<?php echo $edit_employee['city']; ?>" required>

    <label for="province">Province</label>
    <input type="text" name="province" value="<?php echo $edit_employee['province']; ?>" required>

    <label for="postal_code">Postal Code</label>
    <input type="text" name="postal_code" value="<?php echo $edit_employee['postal_code']; ?>" required>

<?php
if (isset($edit_employee['department_id'])) {
    $selected_department_id = $edit_employee['department_id'];
} else {
    $selected_department_id = ''; // or set a default value
}
?>

  <label for="department_abbreviation">Department</label>
<select name="department_abbreviation" required>
    <option value="">Select Department</option>
    <?php foreach ($departments as $department): ?>
        <option value="<?= $department['department_abbreviation']; ?>"><?= $department['department_abbreviation']; ?></option>
    <?php endforeach; ?>
</select>



    <label for="date_hired">Date Hired</label>
    <input type="date" name="date_hired" value="<?php echo $edit_employee['date_hired']; ?>" required>

    <label for="position">Position</label>
    <input type="text" name="position" value="<?php echo $edit_employee['position']; ?>" required>

    <label for="photo">Employee Photo</label>
    <?php if (!empty($edit_employee['photo'])): ?>
        <img src="<?php echo $edit_employee['photo']; ?>" alt="Current Photo" width="100">
    <?php endif; ?>
    <input type="file" name="photo">
    <input type="hidden" name="existing_photo" value="<?php echo $edit_employee['photo']; ?>">  <!-- Retain the existing photo if no new one is uploaded -->

    <button type="submit" name="update">Update Employee</button>
</form>
    </div>
    <?php } ?>
</main>
</section>
</body>
</html>

<?php
$conn->close();
?>