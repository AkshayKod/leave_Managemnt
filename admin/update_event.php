<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $event_id = intval($_POST['event_id']);
    $admin_comment = isset($_POST['admin_comment']) ? mysqli_real_escape_string($conn, $_POST['admin_comment']) : null;

    $q = mysqli_query($conn, "SELECT * FROM leave_requests WHERE id=$event_id LIMIT 1");
    if ($q && mysqli_num_rows($q) === 1) {
        $row = mysqli_fetch_assoc($q);
        if ($action === 'approve' && $row['status'] === 'pending') {
            mysqli_query($conn, "UPDATE leave_requests SET status='approved', admin_comment='$admin_comment', updated_at=NOW() WHERE id=$event_id");
            
            $m = intval(date('m', strtotime($row['start_date'])));
            $y = intval(date('Y', strtotime($row['start_date'])));
            $uid = intval($row['user_id']);
            $total_days = floatval($row['total_days']);
            if ($total_days <= 0) $total_days = ($row['leave_type']==='half'?0.5:1.0);
            $exists = mysqli_query($conn, "SELECT id FROM leave_balance WHERE user_id=$uid AND month=$m AND year=$y");
            if (mysqli_num_rows($exists) == 0) {
                mysqli_query($conn, "INSERT INTO leave_balance (user_id, month, year, leaves_taken, created_at) VALUES ($uid,$m,$y,0,NOW())");
            }
            mysqli_query($conn, "UPDATE leave_balance SET leaves_taken = leaves_taken + $total_days WHERE user_id=$uid AND month=$m AND year=$y");
        } elseif ($action === 'reject' && $row['status'] === 'pending') {
            mysqli_query($conn, "UPDATE leave_requests SET status='rejected', admin_comment='$admin_comment', updated_at=NOW() WHERE id=$event_id");
        }
    }
}

header("Location: index.php");
exit;
?>