<?php
// Include the TCPDF library
require_once __DIR__ . '/tcpdf/TCPDF-main/tcpdf.php';

// Database connection
require_once 'db_connection.php';

// Fetch attendance data based on search and date filters
$selectedRecords = isset($_POST['selected_records']) ? $_POST['selected_records'] : [];
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if (empty($selectedRecords)) {
    echo "No records selected!";
    exit;
}

// Prepare SQL query
$placeholders = implode(',', array_fill(0, count($selectedRecords), '?'));
$sql = "SELECT employee_number, full_name, time_in_am, time_out_am, time_in_pm, time_out_pm, date_logged 
        FROM attendance WHERE 1=1";

$params = [];
$types = '';

// Filter by search query
if (!empty($search_query)) {
    $sql .= " AND (employee_number LIKE ? OR full_name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Filter by date range
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND DATE(date_logged) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Create PDF object
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Municipality of Lucban');
$pdf->SetTitle('Attendance Report');
$pdf->SetSubject('Monthly Attendance Report');
$pdf->SetKeywords('TCPDF, Attendance, Report');

// Add a page
$pdf->AddPage();

// Set title
$pdf->SetFont('helvetica', 'B', 15); // Reduced font size for title
$pdf->Cell(0, 10, 'Municipality of Lucban - Attendance Report', 0, 1, 'C');

// Add current date below the title
$current_date = date('F j, Y');  // Current date in 'Month Day, Year' format
$pdf->SetFont('helvetica', '', 10); // Smaller font for the current date
$pdf->Cell(0, 10, '' . $current_date, 0, 1, 'C');

// Add some space
$pdf->Ln(4);

// Set table column widths
$table_widths = [25, 40, 30, 20, 20, 20, 20];
$width_total = array_sum($table_widths); // Total width of all columns
$page_width = $pdf->getPageWidth(); // Get page width

// Calculate the left margin to center the table
$left_margin = ($page_width - $width_total) / 2;

// Table header
$pdf->SetFont('helvetica', 'B', 7); // Further reduced font for header
$pdf->SetX($left_margin); // Set the X position to center the table
$pdf->Cell($table_widths[0], 6, 'Emp. No.', 1, 0, 'C');
$pdf->Cell($table_widths[1], 6, 'Full Name', 1, 0, 'C');
$pdf->Cell($table_widths[2], 6, 'Date', 1, 0, 'C');
$pdf->Cell($table_widths[3], 6, 'In AM', 1, 0, 'C');
$pdf->Cell($table_widths[4], 6, 'Out AM', 1, 0, 'C');
$pdf->Cell($table_widths[5], 6, 'In PM', 1, 0, 'C');
$pdf->Cell($table_widths[6], 6, 'Out PM', 1, 1, 'C');

// Table body (attendance records)
$pdf->SetFont('helvetica', '', 7); // Further reduced font for data rows
while ($row = $result->fetch_assoc()) {
    $pdf->SetX($left_margin); // Set X to center each row of the table
    $pdf->Cell($table_widths[0], 5, $row['employee_number'], 1, 0, 'C');
    $pdf->Cell($table_widths[1], 5, $row['full_name'], 1, 0, 'L');
    $pdf->Cell($table_widths[2], 5, $row['date_logged'], 1, 0, 'C');
    $pdf->Cell($table_widths[3], 5, $row['time_in_am'], 1, 0, 'C');
    $pdf->Cell($table_widths[4], 5, $row['time_out_am'], 1, 0, 'C');
    $pdf->Cell($table_widths[5], 5, $row['time_in_pm'], 1, 0, 'C');
    $pdf->Cell($table_widths[6], 5, $row['time_out_pm'], 1, 1, 'C');
}

// Close connection
$conn->close();

// Output the PDF
$pdf->Output('attendance_report_' . date('Ymd') . '.pdf', 'I'); // 'I' for inline display in browser
?>
