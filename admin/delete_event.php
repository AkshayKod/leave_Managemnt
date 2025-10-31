<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = intval($_POST['event_id']);
    mysqli_query($conn, "DELETE FROM leave_requests WHERE id=$id AND status='pending'"); 
}

header("Location: index.php");
exit;
?>