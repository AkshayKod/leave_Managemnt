<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

$user_id = intval($_SESSION['user_id']);
$name = $_SESSION['user_name'];

// Pre-fill dates from GET parameters (used for direct navigation or when the modal posts back here)
$prefill_start_date = isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '';
$prefill_end_date   = isset($_GET['end_date'])   ? htmlspecialchars($_GET['end_date'])   : '';

if (isset($_POST['apply'])) {
    $start = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end   = mysqli_real_escape_string($conn, $_POST['end_date']);
    $type  = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    if ($type === 'half') {
        $total_days = 0.50;
    } else {
        $d1 = new DateTime($start);
        $d2 = new DateTime($end);
        $interval = $d2->diff($d1)->days + 1;
        $total_days = max(1, $interval);
    }

    $ins = "INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, total_days, reason, status, created_at) VALUES ($user_id, '$type', '$start', '$end', $total_days, '$reason', 'pending', NOW())";
    if (mysqli_query($conn, $ins)) $success = "Leave applied successfully.";
    else $error = "DB error: ".mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Apply Leave - LMS</title>
<link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li></ul></nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4"><a href="index.php" class="brand-link"><span class="brand-text font-weight-light"><?php echo htmlspecialchars($name); ?></span></a><div class="sidebar"><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column"><li class="nav-item"><a href="index.php" class="nav-link">Dashboard</a></li><li class="nav-item"><a href="apply_leave.php" class="nav-link active">Apply Leave</a></li><li class="nav-item"><a href="my_leaves.php" class="nav-link">My Leaves</a></li><li class="nav-item"><a href="calendar.php" class="nav-link">Calendar</a></li></ul></nav></div></aside>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header"><h4 class="card-title">Apply for Leave</h4></div>
        <div class="card-body">
            <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form method="POST" class="row g-3">
              <div class="col-md-6 form-group"><label>Leave Type</label>
                <select name="leave_type" class="form-control" required>
                  <option value="full">Full</option>
                  <option value="half">Half</option>
                </select>
              </div>
              <div class="col-md-6"></div>
              <div class="col-md-6 form-group"><label>Start Date</label><input type="date" name="start_date" class="form-control" value="<?php echo $prefill_start_date; ?>" required></div>
              <div class="col-md-6 form-group"><label>End Date</label><input type="date" name="end_date" class="form-control" value="<?php echo $prefill_end_date; ?>" required></div>
              <div class="col-12 form-group"><label>Reason</label><textarea name="reason" class="form-control" required rows="3"></textarea></div>
              <div class="col-12"><button type="submit" name="apply" class="btn btn-success">Submit</button></div>
            </form>
        </div>
    </div>
</div>
</section>
</div>

<footer class="main-footer text-center"><strong>Leave Management System © <?php echo date('Y'); ?></strong></footer>
</div>

<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>