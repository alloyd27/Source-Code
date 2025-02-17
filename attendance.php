
<?php
// Database connection details
require_once 'db_connection.php';

// Handle deletion of records
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Delete only if the date is today AND status is NOT "Absent"
    $delete_query = "DELETE FROM attendance 
                     WHERE id = ? 
                     AND DATE(date_logged) = CURDATE() 
                     AND status != 'Absent'";
                     
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: attendance.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}


if (isset($_GET['reset_filters'])) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle bulk deletion of today's non-absent records
if (isset($_POST['delete_all_today'])) {
    $delete_query = "DELETE FROM attendance WHERE DATE(date_logged) = CURDATE() AND status != 'Absent'";
    if ($conn->query($delete_query)) {
        echo "<script>alert('Deleted all non-absent records from today!'); window.location='attendance.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Get search query
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Base query to fetch attendance data
// Base query to fetch attendance data
$sql = "SELECT employee_number, full_name, time_in_am, time_out_am, time_in_pm, time_out_pm, date_logged, status 
        FROM attendance 
        WHERE 1=1";

$params = [];
$types = "";

// Search and date filters
if (!empty($search_query)) {
    $sql .= " AND (employee_number LIKE ? OR full_name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND DATE(date_logged) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
}


$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <link rel="stylesheet" href="style.css">
    <title>Attendance</title>
</head>
<style>
        /* Search Bar and Filters */
.filter-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
    margin-bottom: 60px;
     margin-top: 50px;;
    
}

/* Input fields for search and date filters */
.input-group {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 300px;
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
            <li><a href="Employee.php"><i class='bx bx-face'></i><span class="text">Employees</span></a></li>
            <li><a href="leave.php"><i class='bx bx-archive-in'></i><span class="text">Leave Request</span></a></li>
            <li class="active"><a href="attendance.php"><i class='bx bx-copy-alt'></i><span class="text">Attendance</span></a></li>
            <li><a href="absent.php"><i class='bx bx-user-x'></i><span class="text">Absent</span></a></li>
            <li><a href="report.php"><i class='bx bxs-notepad'></i><span class="text">Report</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <nav>
             <i class='bx bx-menu'></i>
            <form action="attendance.php" method="GET">
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
                    <h1>Manage Attendance</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="index.php">Home</a></li>
                    </ul>
                </div>
             

                
            </div>


           
 <form action="" method="GET">
                <div class="filter-container">
                    <!-- Date Filters -->
                    <div class="input-group">
                        <label for="start-date">Start Date:</label>
                        <input type="date" id="start-date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                
                    <div class="input-group">
                        <label for="end-date">End Date:</label>
                        <input type="date" id="end-date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>

                    <!-- Action Buttons -->
                    <div class="button-group">
                        <button type="submit" class="btn-apply">Apply</button>
                      <!-- Reset button as a link with the 'reset_filters' query parameter -->
            <button type="submit" class="btn-reset" name="reset_filters" value="1">Reset</button>
        
                    </div>
                </div>
            </form>

            <!-- Search Query Notification -->
            <div id="notification-message" style="color: red; text-align: center;">
                <?php
                    if (!empty($search_query)) {
                        echo "Showing results for: " . htmlspecialchars($search_query);
                    }
                ?>
            </div>

            <!-- Form to delete all today's non-absent records -->
         



            <div class="table-data">
                <div class="order">
                      <form action="download-pdf.php" method="POST" id="pdf-form">
                   <table style="width: 100%; border-collapse: collapse; text-align: center;">
    <thead>
        <tr>
            <th>Employee Number</th>
            <th style="text-align: center;">Full Name</th>
            <th style="text-align: center;">Date Logged</th>
            <th style="text-align: center;">Time In AM</th>
            <th style="text-align: center;">Time Out AM</th>
            <th style="text-align: center;">Time In PM</th>
            <th style="text-align: center;">Time Out PM</th>
            <th style="text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $prev_employee = null; // To track previous employee
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                
                // Display employee number and full name only if it's a new employee
                if ($prev_employee !== $row['employee_number']) {
                    echo "<td>{$row['employee_number']}</td>";
                    echo "<td>{$row['full_name']}</td>";
                    $prev_employee = $row['employee_number']; // Update previous employee
                } else {
                    echo "<td></td>"; // Empty cell to avoid repetition
                    echo "<td></td>";
                }

                echo "<td>{$row['date_logged']}</td>
                      <td>{$row['time_in_am']}</td>
                      <td>{$row['time_out_am']}</td>
                      <td>{$row['time_in_pm']}</td>
                      <td>{$row['time_out_pm']}</td>
                      <td>{$row['status']}</td>
                  </tr>";
            }
        } else {
            echo "<tr><td colspan='8' style='text-align:center;'>No attendance records found.</td></tr>";
        }
        ?>
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


const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
})

</script>

</body>
</html>

<?php
$conn->close();
?>
