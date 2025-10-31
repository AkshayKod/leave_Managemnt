<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_leave'])) {
    $user_id = intval($_POST['user_id']);
    $start = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end = mysqli_real_escape_string($conn, $_POST['end_date']);
    $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    if ($leave_type === 'half') $total_days = 0.5;
    else {
        $d1 = new DateTime($start); $d2 = new DateTime($end);
        $total_days = $d2->diff($d1)->days + 1;
        if ($total_days < 1) $total_days = 1;
    }

    $sql = "INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, total_days, reason, status, created_at)
            VALUES ($user_id, '$leave_type', '$start', '$end', $total_days, '$reason', 'pending', NOW())";
    mysqli_query($conn, $sql);
}

header("Location: index.php");
exit;
?>