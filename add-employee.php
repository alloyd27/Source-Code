<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $rfid = $_POST['rfid'];
    $lastName = $_POST['last-name'];
    $firstName = $_POST['first-name'];
    $middleName = $_POST['middle-name'] ?? null;
    $dob = $_POST['dob'];
    $birthplace = $_POST['birthplace'] ?? null;
    $sex = $_POST['sex']; 
    $civilStatus = $_POST['civil-status'];
    $contactNumber = $_POST['contact-number'];
    $email = $_POST['email'];
    $street = $_POST['street'] ?? null;
    $barangay = $_POST['barangay'] ?? null;
    $city = $_POST['city'] ?? null;
    $province = $_POST['province'] ?? null;
    $postalCode = $_POST['postal-code'] ?? null;
    $department = $_POST['department'];
    $dateHired = $_POST['date-hired'];
    $position = $_POST['position'];
    $employeeNumber = $_POST['employee'];

    // Initialize error message variable as an array to collect all errors
    $error_messages = [];

    // Get current date for validation
    $currentDate = date("Y-m-d");

    // Check if the birthdate is in the future
    if ($dob > $currentDate) {
        $error_messages[] = 'Birthdate cannot be in the future. Please enter a valid birthdate.';
    }

    // Check if the date hired is in the future
    if ($dateHired > $currentDate) {
        $error_messages[] = 'Date hired cannot be in the future. Please enter a valid date hired.';
    }

    // Validate first, middle, and last name to ensure they only contain letters and spaces
    if (!preg_match("/^[a-zA-Z\s]+$/", $firstName) || !preg_match("/^[a-zA-Z\s]+$/", $lastName) || ($middleName && !preg_match("/^[a-zA-Z\s]+$/", $middleName))) {
        $error_messages[] = 'Name fields can only contain letters and spaces. Please check your inputs.';
    }

    // Validate phone number (must be 11 digits)
    if (!preg_match("/^\d{11}$/", $contactNumber)) {
        $error_messages[] = 'Contact number must be exactly 11 digits. Please enter a valid phone number.';
    }

    // Validate postal code (must be 4 digits)
    if (!preg_match("/^\d{4}$/", $postalCode)) {
        $error_messages[] = 'Postal code must be exactly 4 digits. Please enter a valid postal code.';
    }

    // Check if the employee number already exists in the database
    $checkEmployeeStmt = $pdo->prepare("SELECT * FROM employees WHERE employee_number = :employee_number");
    $checkEmployeeStmt->bindParam(':employee_number', $employeeNumber);
    $checkEmployeeStmt->execute();

    if ($checkEmployeeStmt->rowCount() > 0) {
        $error_messages[] = 'This employee number is already assigned to another employee. Please enter a unique employee number.';
    }

    // Handle the case when no error messages exist
    if (empty($error_messages)) {
        // Handle file upload
        $photo = $_FILES['photo'];
        $uploadDir = 'uploads/';
        $photoPath = $uploadDir . basename($photo['name']);

        // Check if the uploads directory exists, if not, create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($photo['error'] == UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($photo['type'], $allowedTypes)) {
                if (!move_uploaded_file($photo['tmp_name'], $photoPath)) {
                    $error_messages[] = 'Error uploading photo.';
                }
            } else {
                $error_messages[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
            }
        } else {
            $error_messages[] = 'Error uploading photo. Error Code: ' . $photo['error'];
        }

        // Validate department name exists in the database
        $checkDepartmentStmt = $pdo->prepare("SELECT department_abbreviation FROM departments WHERE department_abbreviation = :department_abbreviation");
        $checkDepartmentStmt->bindParam(':department_abbreviation', $department);
        $checkDepartmentStmt->execute();

        if ($checkDepartmentStmt->rowCount() == 0) {
            $error_messages[] = 'The selected department does not exist in the database.';
        }

        // Check if the RFID is already assigned
        $checkRFIDStmt = $pdo->prepare("SELECT * FROM rfid WHERE rfid = :rfid AND is_assigned = 0");
        $checkRFIDStmt->bindParam(':rfid', $rfid);
        $checkRFIDStmt->execute();

        if ($checkRFIDStmt->rowCount() == 0) {
            $error_messages[] = 'This RFID is already assigned or does not exist.';
        }

        // If no errors, proceed with the insertion
        if (empty($error_messages)) {
            // Prepare SQL insert statement
            $sql = "INSERT INTO employees (rfid, employee_number, last_name, first_name, middle_name, dob, birthplace, sex, civil_status, contact_number, email, street, barangay, city, province, postal_code, department_abbreviation, date_hired, position, photo)
                    VALUES (:rfid, :employee_number, :last_name, :first_name, :middle_name, :dob, :birthplace, :sex, :civil_status, :contact_number, :email, :street, :barangay, :city, :province, :postal_code, :department_abbreviation, :date_hired, :position, :photo)";

            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':rfid', $rfid);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':middle_name', $middleName);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':birthplace', $birthplace);
            $stmt->bindParam(':sex', $sex);
            $stmt->bindParam(':civil_status', $civilStatus);
            $stmt->bindParam(':contact_number', $contactNumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':street', $street);
            $stmt->bindParam(':barangay', $barangay);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':province', $province);
            $stmt->bindParam(':postal_code', $postalCode);
            $stmt->bindParam(':department_abbreviation', $department);
            $stmt->bindParam(':date_hired', $dateHired);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':photo', $photoPath);
            $stmt->bindParam(':employee_number', $employeeNumber);

            if ($stmt->execute()) {
                // Mark the RFID as assigned
                $updateRFIDStmt = $pdo->prepare("UPDATE rfid SET is_assigned = 1 WHERE rfid = :rfid");
                $updateRFIDStmt->bindParam(':rfid', $rfid);
                $updateRFIDStmt->execute();

                header('Location: Employee.php');
                exit();
            } else {
                $error_messages[] = 'There was an error saving the employee data.';
            }
        }
    }
}

// Fetch available RFID codes
$rfidCodes = [];
try {
    $stmt = $pdo->query("SELECT rfid FROM rfid WHERE is_assigned = 0");
    $rfidCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error fetching RFID codes: ' . $e->getMessage();
    exit();
}

// Fetch departments
$departments = [];
try {
    $stmt = $pdo->query("SELECT department_abbreviation FROM departments");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error fetching departments: ' . $e->getMessage();
    exit();
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Add Employee</title>
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
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="#"></form>
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
                    <h1>Add Employee</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="Employee.php">Employees</a></li>
                    </ul>
                </div>
                <a href="Employee.php" class="btn-download">
                    <i class="bx bxs-group"></i>
                    <span class="text">Add employee</span>
                </a>
            </div>

            <!-- Display errors -->
            <?php if (!empty($error_messages)): ?>
            <div class="error-message">
                <p style="color: red; text-align: center;"><?= implode('<br>', $error_messages); ?></p>
            </div>
            <?php endif; ?>


            <!-- Add Employee Form -->
            <div class="form-container">
                <h2>Enter Employee Details</h2>
                <form action="add-employee.php" method="POST" enctype="multipart/form-data">
                    <!-- Photo Upload -->
                    <label for="photo">Upload Photo</label>
                    <input type="file" name="photo" accept="image/*" required>

                    <!-- Employee Fields -->
                    <label for="rfid">Select RFID</label>
                    <select name="rfid" required>
                        <option value="">Select RFID</option>
                        <?php foreach ($rfidCodes as $rfid): ?>
                            <option value="<?= htmlspecialchars($rfid['rfid']); ?>" <?= isset($rfid) && $rfid == $rfid['rfid'] ? 'selected' : '' ?>><?= htmlspecialchars($rfid['rfid']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="employee">Employee Number</label>
                    <input type="text" name="employee" value="<?= isset($employeeNumber) ? htmlspecialchars($employeeNumber) : '' ?>" required>

                    <label for="last-name">Last Name</label>
                    <input type="text" name="last-name" value="<?= isset($lastName) ? htmlspecialchars($lastName) : '' ?>" required>

                    <label for="first-name">First Name</label>
                    <input type="text" name="first-name" value="<?= isset($firstName) ? htmlspecialchars($firstName) : '' ?>" required>

                    <label for="middle-name">Middle Name (Optional)</label>
                    <input type="text" name="middle-name" value="<?= isset($middleName) ? htmlspecialchars($middleName) : '' ?>">

                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" value="<?= isset($dob) ? htmlspecialchars($dob) : '' ?>" required>

                    <label for="birthplace">Place of Birth</label>
                    <input type="text" name="birthplace" value="<?= isset($birthplace) ? htmlspecialchars($birthplace) : '' ?>">

                    <label for="sex">Sex</label>
                    <select name="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male" <?= isset($sex) && $sex == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= isset($sex) && $sex == 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>

                    <label for="civil-status">Civil Status</label>
                    <select name="civil-status" required>
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?= isset($civilStatus) && $civilStatus == 'Single' ? 'selected' : '' ?>>Single</option>
                        <option value="Married" <?= isset($civilStatus) && $civilStatus == 'Married' ? 'selected' : '' ?>>Married</option>
                        <option value="Widowed" <?= isset($civilStatus) && $civilStatus == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                        <option value="Divorced" <?= isset($civilStatus) && $civilStatus == 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                    </select>

                    <label for="contact-number">Contact Number</label>
                    <input type="text" name="contact-number" value="<?= isset($contactNumber) ? htmlspecialchars($contactNumber) : '' ?>" required>

                    <label for="email">Email</label>
                    <input type="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

                    <label for="street">Street (Optional)</label>
                    <input type="text" name="street" value="<?= isset($street) ? htmlspecialchars($street) : '' ?>">

                    <label for="barangay">Barangay (Optional)</label>
                    <input type="text" name="barangay" value="<?= isset($barangay) ? htmlspecialchars($barangay) : '' ?>">

                    <label for="city">City</label>
                    <input type="text" name="city" value="<?= isset($city) ? htmlspecialchars($city) : '' ?>" required>

                    <label for="province">Province</label>
                    <input type="text" name="province" value="<?= isset($province) ? htmlspecialchars($province) : '' ?>" required>

                    <label for="postal-code">Postal Code</label>
                    <input type="text" name="postal-code" value="<?= isset($postalCode) ? htmlspecialchars($postalCode) : '' ?>" required>

                    <label for="department">Department</label>
                    <select name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= htmlspecialchars($department['department_abbreviation']); ?>" <?= isset($department) && $department == $department['department_abbreviation'] ? 'selected' : '' ?>><?= htmlspecialchars($department['department_abbreviation']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="date-hired">Date Hired</label>
                    <input type="date" name="date-hired" value="<?= isset($dateHired) ? htmlspecialchars($dateHired) : '' ?>" required>

                    <label for="position">Position</label>
                    <input type="text" name="position" value="<?= isset($position) ? htmlspecialchars($position) : '' ?>" required>

                    <button type="submit">Submit</button>
                </form>
            </div>
        </main>
    </section>
</body>
</html>
