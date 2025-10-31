<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Calendar - LMS</title>
<link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/main.min.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav ml-auto">
    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
  </ul>
</nav>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="index.php" class="brand-link"><span class="brand-text font-weight-light"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></a>
  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column">
        <li class="nav-item"><a href="index.php" class="nav-link">Dashboard</a></li>
        <li class="nav-item"><a href="apply_leave.php" class="nav-link">Apply Leave</a></li>
        <li class="nav-item"><a href="my_leaves.php" class="nav-link">My Leaves</a></li>
        <li class="nav-item"><a href="calendar.php" class="nav-link active">Calendar</a></li>
      </ul>
    </nav>
  </div>
</aside>

<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid mt-4">
      <div class="card">
        <div class="card-header"><h4>Calendar View (My Leaves & General Holidays)</h4></div>
        <div class="card-body">
          <div id="calendar"></div>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply for Leave</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST" action="apply_leave.php">
                <div class="modal-body">
                    <div class="col-md-12 form-group"><label>Leave Type</label>
                        <select name="leave_type" class="form-control" required>
                            <option value="full">Full</option>
                            <option value="half">Half</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group"><label>Start Date</label><input type="date" name="start_date" id="modal_start_date" class="form-control" required></div>
                    <div class="col-md-12 form-group"><label>End Date</label><input type="date" name="end_date" id="modal_end_date" class="form-control" required></div>
                    <div class="col-md-12 form-group"><label>Reason</label><textarea name="reason" class="form-control" required rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="apply" class="btn btn-success">Submit Leave</button>
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
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        events: 'fetch_employee_events.php',
        height: 'auto',
        selectable: true, 
        // Update: Show modal instead of redirecting
        dateClick: function(info) {
            $('#modal_start_date').val(info.dateStr);
            $('#modal_end_date').val(info.dateStr);
            $('#applyLeaveModal').modal('show');
        }
    });
    calendar.render();
});
</script>
</body>
</html>