<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
header('Content-Type: application/json');

$events = [];

// Fetch approved leave requests from the view
$sql_leave = "SELECT event_id AS id, employee_name AS title, start_date AS start, end_date AS end, color 
              FROM view_calendar_events
              WHERE status='approved'";
$res_leave = mysqli_query($conn, $sql_leave);

while($row = mysqli_fetch_assoc($res_leave)){
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => date('Y-m-d', strtotime($row['end'] . ' +1 day')), // FullCalendar treats end date as exclusive
        'color' => $row['color']
    ];
}

// Fetch holidays
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

echo json_encode($events);

?>