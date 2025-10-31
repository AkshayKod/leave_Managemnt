<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $start = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end = mysqli_real_escape_string($conn, $_POST['end_date']);
    if (empty($end)) {
        $end = $start; // Set end date to start date if not provided
    }
    mysqli_query($conn, "INSERT INTO holidays (title,start_date,end_date) VALUES ('$title','$start','$end')");
}

header("Location: index.php");
exit;
?>