<?php
// Database connection details
require_once 'db_connection.php';

// Handle search query and date filters here
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Default to no result initially
$result = null;

if (!empty($search_query)) {
   $sql = "SELECT id, full_name, position, time_in_am, time_out_am, time_in_pm, time_out_pm, date_logged 
        FROM report
        WHERE full_name LIKE ?";


    // Apply date filter if present
    if ($start_date && $end_date) {
        $sql .= " AND date_logged BETWEEN ? AND ?";
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Bind parameters based on filters
    if ($start_date && $end_date) {
        $like_query = "%" . $search_query . "%";
        $stmt->bind_param("sss", $like_query, $start_date, $end_date);
    } else {
        $like_query = "%" . $search_query . "%";
        $stmt->bind_param("s", $like_query);
    }

    // Execute statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    // Check if query execution was successful
    if (!$result) {
        die("Error executing query: " . $stmt->error);
    }
}

// Function to calculate total hours worked
function calculateTotalHours($time_in_am, $time_out_am, $time_in_pm, $time_out_pm) {
    $time_in_am = strtotime($time_in_am);
    $time_out_am = strtotime($time_out_am);
    $time_in_pm = strtotime($time_in_pm);
    $time_out_pm = strtotime($time_out_pm);

    // Calculate morning and afternoon hours
    $morning_hours = ($time_out_am - $time_in_am) / 3600; // in hours
    $afternoon_hours = ($time_out_pm - $time_in_pm) / 3600; // in hours

    // Return total hours
    return $morning_hours + $afternoon_hours;
}

// Initialize variable for total hours
$total_hours_all = 0;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_records_hidden'])) {
    $selected_records = $_POST['selected_records_hidden'];

    if (!empty($selected_records)) {
        // Sanitize the input
        $selected_records = array_map('intval', $selected_records);

        // Convert the array to a comma-separated string for SQL IN clause
        $ids = implode(',', $selected_records);

        // Fetch records from the database based on selected IDs
        $sql = "SELECT * FROM report WHERE id IN ($ids)";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            // Generate PDF logic here...
        } else {
            echo "No records found for the selected IDs.";
        }
    } else {
        echo "No records selected.";
    }
} else {
}

// Close the database connection
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
    <title>Report</title>
    <style type="text/css">
        .full-name-container {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .full-name-container h2 {
            color: black; 
            font-size: 25px;
        }

        .error-message {
            color: red;
            font-size: 20px;
            text-align: center;
        }

        /* Search Bar and Filters */
.filter-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
    margin-bottom: 20px;
     margin-top: 50px;
}

/* Input fields for search and date filters */
.input-group {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 250px;
}

.input-group label {
    font-size: 14px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.input-group input {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    transition: border 0.3s ease;
}

.input-group input:focus {
    border-color: #4CAF50;
}

/* Buttons for apply and reset */
.button-group {
    display: flex;
    gap: 15px;
    align-items: center;
    justify-content: flex-start;
}

.btn-apply,
.btn-reset {
     margin-top: 20px;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-apply {
    background-color: #4CAF50;
    color: white;
}

.btn-apply:hover {
    background-color: #45a049;
}

.btn-reset {
    background-color: #f44336;
    color: white;
}

.btn-reset:hover {
    background-color: #e53935;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .input-group {
        width: 100%;
    }

    .button-group {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }

    .btn-apply,
    .btn-reset {
        width: 100%;
    }
}

.button-container {
    display: flex;
    flex-direction: column; /* Stack buttons vertically */
    gap: 10px; /* Add spacing between buttons */
    align-items: flex-start; /* Align buttons to the left */
}

    </style>
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
        <li><a href="Department.php"><i class='bx bxs-buildings' style='color:#181818'></i><span class="text">Department</span></a></li>
        <li><a href="Employee.php"><i class='bx bx-face' style='color:#181818'></i><span class="text">Employees</span></a></li>
        <li><a href="leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
        <li><a href="attendance.php"><i class='bx bx-copy-alt' style='color:#181818'></i><span class="text">Attendance</span></a></li>
        <li><a href="absent.php"><i class='bx bx-user-x'></i></i><span class="text">Absent</span></a></li>
        <li class="active"><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
    </ul>
    <ul class="side-menu">
        <li><a href="login.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <i class='bx bx-menu'></i>
        <a href="#" class="nav-link"></a>
        <form action="attendance.php" method="GET">
                <div class="form-input">
                </div>
            </form>
    
        <strong><span id="current-time"></span></strong>
        <strong><span id="current-date"></span></strong>
    </nav>

    <main>
       <div class="head-title">
    <div class="left">
        <h1>Attendance Report</h1>
        <ul class="breadcrumb">
            <li><a href="#">Dashboard</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="index.php">Home</a></li>
        </ul>
    </div>

    <!-- Button container -->
    <div class="button-container">
        <!-- Download PDF Button (Top) -->
        <a href="attendace_pdf.php" class="btn-download" id="download-pdf">
            <i class='bx bxs-group'></i>
            <span class="text">Download PDF</span>
        </a>

      <!-- Export to Excel Button (Updated) -->
        <a href="export_excel.php" class="btn-download" id="export-excel-btn">
            <i class='bx bx-spreadsheet'></i>
            <span class="text">Export Excel</span>
        </a>

    </div>
</div>






        <!-- Date Filters and Search -->
<form action="report.php" method="GET" onsubmit="return validateForm()">
    <div class="filter-container">
        <div class="input-group">
            <label for="search-query">Search by Name:</label>
            <input type="search" id="search-query" name="search_query" placeholder="Enter full name" value="<?php echo htmlspecialchars($search_query); ?>">
        </div>

        <!-- Date Filters -->
        <div class="input-group">
            <label for="start-date">Start Date:</label>
            <input type="date" id="start-date" name="start_date" value="<?php echo $start_date; ?>">
        </div>

        <div class="input-group">
            <label for="end-date">End Date:</label>
            <input type="date" id="end-date" name="end_date" value="<?php echo $end_date; ?>">
        </div>

        <!-- Action Buttons -->
        <div class="button-group">
            <button type="submit" class="btn-apply">Apply</button>
            <button type="button" class="btn-reset" onclick="resetFilters()">Reset</button>
        </div>
    </div>
</form>

         <div id="error-message" class="error-message"></div>

         <div class="table-data">
            <div class="order">
                <form action="generate_pdf.php" method="POST" id="pdf-form">
                   <table>
    <thead>
        <?php 
        // Kunin ang unang pangalan para mailagay sa header
        $first_name = '';
        if (isset($result) && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $first_name = $row['full_name'];
            $result->data_seek(0); // I-reset ang pointer ng result set
        }
        ?>
                   <tr>
                <th colspan="8" style="text-align: center; font-size: 25px; font-weight: bold; padding: 10px; background-color: #f2f2f2;">
                    <?= !empty($first_name) ? htmlspecialchars($first_name) : 'Employee Name'; ?>
                </th>
            </tr>
            <!-- Extra row for spacing -->
            <tr>
                <td colspan="8" style="height: 30px;"></td>
            </tr>

        <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th style="text-align: center;">Select All</th>
            <th style="text-align: center;">Date Logged</th>
            <th style="text-align: center;">Time In AM</th>
            <th style="text-align: center;">Time Out AM</th>
            <th style="text-align: center;">Time In PM</th>
            <th style="text-align: center;">Time Out PM</th>
            <th style="text-align: center;">Total Hours</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if (empty($search_query) && empty($start_date) && empty($end_date)) : ?>
            <tr>
                
                <td colspan="8" style="text-align: center; font-size: 18px; color: red; padding: 20px;">
                    Please search by name and date first to see data.
                </td>
                
            </tr>
        <?php elseif (isset($result) && $result->num_rows > 0) : 
            $total_hours_all = 0;

            while ($row = $result->fetch_assoc()) :
                if (function_exists('calculateTotalHours')) {
                    $total_hours = calculateTotalHours($row['time_in_am'], $row['time_out_am'], $row['time_in_pm'], $row['time_out_pm']);
                } else {
                    $total_hours = 0; 
                }
                $total_hours_all += $total_hours; 
        ?>
            <tr>
                <td></td>
                <td style="text-align: center;">
                    <input type="checkbox" name="selected_records[]" value="<?= $row['id']; ?>" class="select-record">
                </td>
                <td style="text-align: center;"><?= htmlspecialchars($row['date_logged']); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['time_in_am']); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['time_out_am']); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['time_in_pm']); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['time_out_pm']); ?></td>
                <td style="text-align: center;"><?= number_format($total_hours, 2); ?> hours</td>
            </tr>
        <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="8" style="text-align: center;">No records found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

                </form>
            </div>
        </div>

        <br>
<div style="text-align: right; font-weight: bold; color: red; font-size: 20px; margin-top: 25px; margin-right: 30px;">
    Total Hours: <?php echo number_format($total_hours_all, 2); ?> hours
</div>


    </main>
</section>

<script>
// JavaScript to handle export to Excel for selected records
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('select-all');
    const recordCheckboxes = document.querySelectorAll('input[name="selected_records[]"]');
    const exportExcelButton = document.getElementById('export-excel-btn');

    // Select All Checkbox Functionality
    selectAllCheckbox.addEventListener('change', function () {
        recordCheckboxes.forEach(function (checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });

    // Update Select All Checkbox Status Based on Individual Checkbox
    recordCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            if (!checkbox.checked) {
                selectAllCheckbox.checked = false;
            } else if ([...recordCheckboxes].every(cb => cb.checked)) {
                selectAllCheckbox.checked = true;
            }
        });
    });

    // Export to Excel Logic
    exportExcelButton.addEventListener('click', function (event) {
        const selectedRecords = Array.from(recordCheckboxes).filter(checkbox => checkbox.checked);

        if (selectedRecords.length === 0) {
            event.preventDefault(); // Prevents navigation if no records are selected
            alert("Please select at least one record before exporting to Excel.");
        } else {
            // Collect selected record IDs to append to the export URL
            let selectedIds = selectedRecords.map(checkbox => checkbox.value).join(',');

            // Append selected record IDs to the export Excel URL
            exportExcelButton.href = `export_excel.php?selected_ids=${selectedIds}`;
        }
    });
});



          document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('select-all');
    const recordCheckboxes = document.querySelectorAll('input[name="selected_records[]"]');
    const downloadButton = document.getElementById('download-pdf');
    const form = document.getElementById('pdf-form');

    // Select All Checkbox Functionality
    selectAllCheckbox.addEventListener('change', function () {
        recordCheckboxes.forEach(function (checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });

    // Update Select All Checkbox Status Based on Individual Checkbox
    recordCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            if (!checkbox.checked) {
                selectAllCheckbox.checked = false;
            } else if ([...recordCheckboxes].every(cb => cb.checked)) {
                selectAllCheckbox.checked = true;
            }
        });
    });

    // Download Button Logic
    downloadButton.addEventListener('click', function (event) {
        event.preventDefault();

        // Clear existing hidden inputs
        const hiddenInputs = document.querySelectorAll('input[name="selected_records_hidden[]"]');
        hiddenInputs.forEach(input => input.remove());

        // Get selected checkboxes
        const selectedRecords = Array.from(recordCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedRecords.length > 0) {
            // Add hidden input fields to the form for each selected record
            selectedRecords.forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_records_hidden[]';
                input.value = id;
                form.appendChild(input);
            });

            // Submit the form
            form.submit();
        } else {
            alert("Please select at least one record.");
        }
    });
});


   function validateForm() {
    const searchQuery = document.getElementById('search-query').value.trim();
    const errorMessage = document.getElementById('error-message');

    // Validate if the search query is a complete name (has at least two words)
    if (searchQuery.split(' ').length < 2) {
        errorMessage.textContent = "Please enter both first and last name.";
        return false;
    } else {
        errorMessage.textContent = ''; // Clear error message
        return true;
    }
}

function resetFilters() {
    const form = document.querySelector('form');
    form.reset(); // Reset form fields

    // Clear the error message
    const errorMessage = document.getElementById('error-message');
    errorMessage.textContent = ''; // Clear any displayed error messages

    // Reload the page to remove query parameters from the URL
    window.location.href = "report.php"; // This will refresh the page and reset the filters
}


function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = now.toLocaleDateString('en-US', options);
        const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        
        document.getElementById('current-date').textContent = dateString;
        document.getElementById('current-time').textContent = timeString;
    }

    // Update time every second
    setInterval(updateDateTime, 1000);
    // Initial call to set the date and time immediately
    updateDateTime();


   const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
    const li = item.parentElement;

    item.addEventListener('click', function () {
        allSideMenu.forEach(i=> {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
    sidebar.classList.toggle('hide');
})

</script>

</body>
</html>
