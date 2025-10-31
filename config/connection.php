<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "leave_maagement"; 

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Kolkata');
?>
