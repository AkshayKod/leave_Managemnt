<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') {
    // Return empty array if not logged in
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$user_id = intval($_SESSION['user_id']);
$events = [];

// Fetch ALL holidays (general leaves)
$sql_holiday = "SELECT id, title, start_date AS start, end_date AS end FROM holidays";
$res_holiday = mysqli_query($conn, $sql_holiday);

while($row = mysqli_fetch_assoc($res_holiday)){
    $events[] = [
        'id' => 'h'.$row['id'], // prefix so it doesn't conflict with leave IDs
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => date('Y-m-d', strtotime($row['end'] . ' +1 day')), // FullCalendar end date exclusive
        'color' => '#007bff' // blue for holidays
    ];
}

// Fetch current user's leaves (approved, pending, rejected)
$sql_user_leave = "SELECT id, leave_type, start_date AS start, end_date AS end, status FROM leave_requests WHERE user_id=$user_id";
$res_user_leave = mysqli_query($conn, $sql_user_leave);

while($row = mysqli_fetch_assoc($res_user_leave)){
    $status = $row['status'];
    $color = '';
    $title_prefix = ucfirst($status);
    if ($status === 'approved') {
        $color = '#28a745'; // Green for Approved
    } elseif ($status === 'pending') {
        $color = '#ffc107'; // Yellow for Pending
    } else {
        $color = '#dc3545'; // Red for Rejected
    }
    
    $events[] = [
        'id' => $row['id'],
        'title' => $title_prefix . ' Leave',
        'start' => $row['start'],
        'end' => date('Y-m-d', strtotime($row['end'] . ' +1 day')), // FullCalendar end date exclusive
        'color' => $color
    ];
}

echo json_encode($events);
?>