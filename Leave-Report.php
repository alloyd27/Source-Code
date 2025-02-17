<?php
// Database connection details
require_once 'db_connection.php';

// Handle search query and other functions here (existing code)
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$sql = "SELECT lr.*, CONCAT(e.last_name, ', ', e.first_name) AS full_name
        FROM leave_requests lr
        LEFT JOIN employees e ON lr.rfid = e.rfid
        WHERE 1=1";

// Apply date filter if present
if ($start_date && $end_date) {
    $sql .= " AND lr.date_logged BETWEEN ? AND ?";
}

// Apply search query filter if present
if (!empty($search_query)) {
    $sql .= " AND (lr.id LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR lr.leave_type LIKE ?)";
}

$stmt = $conn->prepare($sql);

// Bind parameters based on conditions
if ($start_date && $end_date && !empty($search_query)) {
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("ssss", $start_date, $end_date, $like_query, $like_query); // 4 parameters (start_date, end_date, like_query for ID, like_query for first_name)
} elseif ($start_date && $end_date) {
    $stmt->bind_param("ss", $start_date, $end_date); // 2 parameters (start_date, end_date)
} elseif (!empty($search_query)) {
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("ssss", $like_query, $like_query, $like_query, $like_query); // 4 parameters (like_query for ID, first_name, last_name, leave_type)
}

// Execute and get the result
$stmt->execute();
$result = $stmt->get_result();

// Check if no results
$no_results_found = $result->num_rows === 0;

// Close the database connection
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
    <title>Leave Request Report</title>
</head>
<body>

    <section id="sidebar">
        <a href="index.php" class="brand">
            <div class="logo-image">
                <img src="img/logo.png" alt="Logo">
            </div>
            <span class="text">Municipality of Lucban</span>
        </a>
        <ul class="side-menu top">
            <li><a href="index.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li><a href="Department.php"><i class='bx bxs-buildings'></i><span class="text">Department</span></a></li>
            <li><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
            <li><a href="leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
            <li><a href="attendance.php"><i class='bx bx-copy-alt'></i><span class="text">Attendance</span></a></li>
            <li class="active"><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link"></a>
            <form action="Leave-Report.php" method="GET">
                <div class="form-input">
                    <input type="search" name="search_query" placeholder="Search..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <strong><span id="current-date"></span></strong>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Leave Request Report</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="index.php">Home</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="Report.php">Attendance Report</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="Leave-Report.php">Leave Request Report</a></li>
                    </ul>
                </div>
                <a href="generate_pdf.php" class="btn-download" id="download-pdf">
                    <i class='bx bxs-group'></i>
                    <span class="text">Download PDF</span>
                </a>
            </div>

            <br>

            <!-- Date Filters and Search -->
            <div class="filters-container">
                <form action="Leave-Report.php" method="GET">
                    <div class="date-filters">
                        <div class="input-group">
                            <label for="start-date">Start Date:</label>
                            <input type="date" id="start-date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>

                        <div class="input-group">
                            <label for="end-date">End Date:</label>
                            <input type="date" id="end-date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>

                        <div class="button-group">
                            <button class="btn-apply" type="submit" title="Apply">
                                <span>Apply</span>
                            </button>
                            <button class="btn-reset" type="button" onclick="resetFilters()" title="Reset">
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                </form>

                <br>
                <br>

                <!-- Display 'No records found' message below the filters if no records are found -->
                <?php if ($no_results_found): ?>
                    <div style="color: red; text-align: center; margin-top: 10px;">
                        No records found for the given search or date filter.
                    </div>
                <?php endif; ?>
            </div>

            <div class="table-data">
                <div class="order">
                    <form action="generate_leave_pdf.php" method="POST" id="pdf-form">
                        <table>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Full Name</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Date Logged</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><input type="checkbox" class="select-record" value="<?php echo $row['id']; ?>"></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['leave_type']; ?></td>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td><?php echo $row['end_date']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                        <td><?php echo $row['date_logged']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </main>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all');
            const recordCheckboxes = document.querySelectorAll('.select-record');
            const form = document.getElementById('pdf-form');

            // Handle Select All behavior
            selectAllCheckbox.addEventListener('change', function () {
                // Select or unselect all checkboxes based on the Select All checkbox state
                recordCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });

            // Update Select All checkbox status based on individual checkboxes
            recordCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (!checkbox.checked) {
                        selectAllCheckbox.checked = false;
                    } else if ([...recordCheckboxes].every(cb => cb.checked)) {
                        selectAllCheckbox.checked = true;
                    }
                });
            });

            // Handle the "Download PDF" button click event
            const downloadButton = document.getElementById('download-pdf');
            downloadButton.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior

                // Gather selected record IDs
                const selectedRecords = Array.from(document.querySelectorAll('.select-record:checked'))
                                      .map(checkbox => checkbox.value);

                if (selectedRecords.length > 0) {
                    // Create hidden inputs for each selected record ID to pass to the server
                    selectedRecords.forEach(function(id) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'selected_records[]';z
                        input.value = id;  // Pass thze 'id' of the leave request
                        form.appendChild(input);
                    });

                    // Submit the form
                    form.submit();
                } else {
                    alert("Please select at least one record.");
                }
            });
        });

        function resetFilters() {
            document.getElementById('start-date').value = '';
            document.getElementById('end-date').value = '';
            window.location.href = 'Leave-Report.php'; // Reload the page
        }

        // Update date
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('en-US', options);

            document.getElementById('current-date').textContent = dateString;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>

</body>
</html>
