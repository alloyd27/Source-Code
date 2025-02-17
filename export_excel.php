<?php
// Include the database connection
require_once 'db_connection.php';

/**
 * Function to calculate the total hours worked based on time-in and time-out records
 *
 * @param string $time_in_am - Time the employee checked in during the AM shift
 * @param string $time_out_am - Time the employee checked out during the AM shift
 * @param string $time_in_pm - Time the employee checked in during the PM shift
 * @param string $time_out_pm - Time the employee checked out during the PM shift
 * @return float - Total hours worked (morning + afternoon)
 */
function calculateTotalHours($time_in_am, $time_out_am, $time_in_pm, $time_out_pm)
{
    // Convert time to timestamps for calculations
    $time_in_am = strtotime($time_in_am);
    $time_out_am = strtotime($time_out_am);
    $time_in_pm = strtotime($time_in_pm);
    $time_out_pm = strtotime($time_out_pm);

    // Calculate the hours worked during the morning and afternoon shifts
    $morning_hours = ($time_out_am - $time_in_am) / 3600; // Calculate hours in the AM shift
    $afternoon_hours = ($time_out_pm - $time_in_pm) / 3600; // Calculate hours in the PM shift

    // Return the total hours worked
    return round($morning_hours + $afternoon_hours, 2); // Rounded to 2 decimal places for consistency
}

if (isset($_GET['selected_ids'])) {
    // Convert the selected IDs into an array
    $selected_ids = explode(',', $_GET['selected_ids']);  

    // Prepare the SQL query to fetch data for the selected records
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $sql = "SELECT id, full_name, position, time_in_am, time_out_am, time_in_pm, time_out_pm, date_logged
            FROM report
            WHERE id IN ($placeholders)";

    // Prepare and execute the query with the selected IDs
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);  // Bind the IDs as integers
    $stmt->execute();
    $result = $stmt->get_result();

    // Set the headers for the Excel export
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="attendance_report.csv"');
    header('Cache-Control: max-age=0');

    // Open the output stream for CSV file generation
    $output = fopen('php://output', 'w');

    // Output the column headers for the CSV file
    fputcsv($output, [
        'Date Logged', 
        'Full Name', 
        'Position', 
        'Time In AM', 
        'Time Out AM', 
        'Time In PM', 
        'Time Out PM',
        'Total Hours'
    ]);

    // Iterate over the result set and output the data for each record
    while ($row = $result->fetch_assoc()) {
        // Calculate total hours for the current record
        $total_hours = calculateTotalHours(
            $row['time_in_am'], 
            $row['time_out_am'], 
            $row['time_in_pm'], 
            $row['time_out_pm']
        );

        // Format date for consistency in "YYYY-MM-DD" format
        $formatted_date = date('Y-m-d', strtotime($row['date_logged']));

        // Write the record data to the CSV file, ensuring that each value is in its own column
        fputcsv($output, [
             
            $formatted_date, 
            $row['full_name'], 
            $row['position'], 
            $row['time_in_am'], 
            $row['time_out_am'], 
            $row['time_in_pm'], 
            $row['time_out_pm'],
            $total_hours
        ]);
    }

    // Close the output stream and terminate the script
    fclose($output);
    exit;
} else {
    // Display a message if no records are selected for export
    echo "No records selected for export.";
}
?>
