<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

$user_id = intval($_SESSION['user_id']);
$name = $_SESSION['user_name'];

$approved = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM leave_requests WHERE user_id=$user_id AND status='approved'"))[0];
$pending  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM leave_requests WHERE user_id=$user_id AND status='pending'"))[0];
$rejected = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM leave_requests WHERE user_id=$user_id AND status='rejected'"))[0];
$total_requests = $approved + $pending + $rejected; // New calculation
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Employee Dashboard - LMS</title>
<link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li></ul></nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4"><a href="index.php" class="brand-link"><span class="brand-text font-weight-light"><?php echo htmlspecialchars($name); ?></span></a><div class="sidebar"><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column"><li class="nav-item"><a href="index.php" class="nav-link active">Dashboard</a></li><li class="nav-item"><a href="apply_leave.php" class="nav-link">Apply Leave</a></li><li class="nav-item"><a href="my_leaves.php" class="nav-link">My Leaves</a></li><li class="nav-item"><a href="calendar.php" class="nav-link">Calendar</a></li></ul></nav></div></aside>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $total_requests; ?></h3><p>Total Requests</p></div><div class="icon"><i class="fas fa-list-alt"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $approved; ?></h3><p>Approved</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $pending; ?></h3><p>Pending</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo $rejected; ?></h3><p>Rejected</p></div><div class="icon"><i class="fas fa-times-circle"></i></div></div></div>
  </div>

  <div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Leave Status Distribution</h3></div>
    <div class="card-body">
        <div class="chart-responsive">
            <canvas id="leaveDoughnutChart" height="150" style="height: 150px;"></canvas>
        </div>
        <ul class="chart-legend clearfix text-center mt-3">
            <li><i class="far fa-circle text-success"></i> Approved (<?php echo $approved; ?>)</li>
            <li><i class="far fa-circle text-warning"></i> Pending (<?php echo $pending; ?>)</li>
            <li><i class="far fa-circle text-danger"></i> Rejected (<?php echo $rejected; ?>)</li>
        </ul>
        <?php if ($total_requests == 0): ?>
            <p class="text-center text-muted mt-3">No leave requests recorded yet.</p>
        <?php endif; ?>
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
<script src="../adminlte/plugins/chart.js/Chart.min.js"></script>

<script>
$(function () {
    var total = <?php echo $total_requests; ?>;

    if (total > 0) {
        var pieChartCanvas = $('#leaveDoughnutChart').get(0).getContext('2d');
        
        var approved = <?php echo $approved; ?>;
        var pending = <?php echo $pending; ?>;
        var rejected = <?php echo $rejected; ?>;

        var pieData = {
            labels: [
                'Approved',
                'Pending',
                'Rejected'
            ],
            datasets: [
                {
                    data: [approved, pending, rejected],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'], // Success, Warning, Danger colors
                }
            ]
        };

        var pieOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: false 
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index] || '';
                        if (label) {
                            label += ': ';
                        }
                        label += data.datasets[0].data[tooltipItem.index];
                        var percent = ((data.datasets[0].data[tooltipItem.index] / total) * 100).toFixed(1);
                        label += ' (' + percent + '%)';
                        return label;
                    }
                }
            }
        };

        // Create the Doughnut chart
        new Chart(pieChartCanvas, {
            type: 'doughnut',
            data: pieData,
            options: pieOptions
        });
    }
});
</script>
</body>
</html>