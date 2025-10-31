<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['add'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $start = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end = mysqli_real_escape_string($conn, $_POST['end_date']);
    mysqli_query($conn, "INSERT INTO holidays (title,start_date,end_date) VALUES ('$title','$start','$end')");
    header("Location: holidays.php"); exit;
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM holidays WHERE id=$id");
    header("Location: holidays.php"); exit;
}
$res = mysqli_query($conn, "SELECT * FROM holidays ORDER BY start_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Holidays</title>
<link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light"><ul class="navbar-nav ml-auto"><li class="nav-item"><a href="logout.php" class="nav-link text-danger">Logout</a></li></ul></nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link"><span class="brand-text font-weight-light">LMS Admin</span></a>
    <div class="sidebar"><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column">
      <li class="nav-item"><a href="index.php" class="nav-link">Calendar</a></li>
      <li class="nav-item"><a href="employees.php" class="nav-link">Manage Users</a></li>
      <li class="nav-item"><a href="holidays.php" class="nav-link active">Manage Holidays</a></li>
      <li class="nav-item"><a href="leave_requests.php" class="nav-link">Leave Requests</a></li>
    </ul></nav></div>
  </aside>

  <div class="content-wrapper">
    <section class="content">
      <div class="container-fluid mt-4">
        
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Holiday List</h4>
                <button type="button" class="btn btn-success btn-sm float-right" data-toggle="modal" data-target="#addHolidayModal">
                    <i class="fas fa-plus"></i> Add New Holiday
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark"><tr><th>ID</th><th>Title</th><th>From</th><th>To</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php while($h=mysqli_fetch_assoc($res)){ echo "<tr><td>{$h['id']}</td><td>{$h['title']}</td><td>{$h['start_date']}</td><td>{$h['end_date']}</td><td><a href='?delete={$h['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Delete?')\">Delete</a></td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </section>
  </div>

<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Holiday</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" placeholder="Holiday Name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button name="add" class="btn btn-success">Add Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

  <footer class="main-footer text-center"><strong>Leave Management System © <?php echo date('Y'); ?></strong></footer>
</div>

<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>