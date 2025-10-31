<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }

// Fetch all employee details, including new fields
$sql = "SELECT id, name, email, gender, address, contact, created_at FROM users WHERE role='employee' ORDER BY name ASC";
$res = mysqli_query($conn, $sql);

if (!$res) {
    die("Database query failed: " . mysqli_error($conn));
}

$filename = "employee_list_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Set column headers
fputcsv($output, array('ID', 'Name', 'Email', 'Gender', 'Address', 'Contact', 'Created At'));

// Fetch data rows
while ($row = mysqli_fetch_assoc($res)) {
    // Add data row to the CSV
    fputcsv($output, $row);
}

fclose($output);
exit;
?>