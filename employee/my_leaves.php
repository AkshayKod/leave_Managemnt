<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') { header("Location: login.php"); exit; }
date_default_timezone_set('Asia/Kolkata');

$user_id = intval($_SESSION['user_id']);

if (isset($_GET['cancel_id'])) {
    $id = intval($_GET['cancel_id']);
    
    $q = mysqli_query($conn, "SELECT * FROM leave_requests WHERE id=$id AND user_id=$user_id AND (status='pending' OR status='approved') LIMIT 1");
    if ($q && mysqli_num_rows($q) == 1) {
        $row = mysqli_fetch_assoc($q);
        $status = $row['status'];
        
        // Delete the request
        mysqli_query($conn, "DELETE FROM leave_requests WHERE id=$id");
        
        // Reverse balance if approved
        if ($status === 'approved') {
            $m = intval(date('m', strtotime($row['start_date'])));
            $y = intval(date('Y', strtotime($row['start_date'])));
            $total_days = floatval($row['total_days']);
            // Deduct the leaves taken (reverse the addition from admin/update_event.php)
            mysqli_query($conn, "UPDATE leave_balance SET leaves_taken = leaves_taken - $total_days WHERE user_id=$user_id AND month=$m AND year=$y");
        }
    }
    header("Location: my_leaves.php"); exit;
}

// --- FILTERING AND SORTING LOGIC ---
$from_date = empty($_GET['from_date']) ? '' : $_GET['from_date'];
$to_date = empty($_GET['to_date']) ? '' : $_GET['to_date'];
$search_status = empty($_GET['search_status']) ? '' : $_GET['search_status'];
$order_by = empty($_GET['order_by']) ? 'date_desc' : $_GET['order_by'];

$root_sql = "SELECT * FROM leave_requests WHERE user_id=$user_id";

if (isset($_GET['search'])) {
    if (!empty($from_date) && !empty($to_date)) {
        $root_sql .= " AND start_date BETWEEN '{$from_date}' AND '{$to_date}'";
    } else if (!empty($from_date)) {
        $root_sql .= " AND start_date >= '{$from_date}'";
    } else if (!empty($to_date)) {
        $root_sql .= " AND end_date <= '{$to_date}'";
    }
    if(!empty($search_status)) {
        $status = mysqli_real_escape_string($conn, $search_status);
        $root_sql .= " AND status = '$status'";
    }
}

if ($order_by == 'date_desc') {
    $root_sql .= " ORDER BY start_date DESC";
} else if ($order_by == 'date_asc') {
    $root_sql .= " ORDER BY start_date ASC";
} else {
    $root_sql .= " ORDER BY id DESC";
}

$res = mysqli_query($conn, $root_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Leaves - LMS</title>
<link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li></ul></nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4"><a href="index.php" class="brand-link"><span class="brand-text font-weight-light"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></a><div class="sidebar"><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column"><li class="nav-item"><a href="index.php" class="nav-link">Dashboard</a></li><li class="nav-item"><a href="apply_leave.php" class="nav-link">Apply Leave</a></li><li class="nav-item"><a href="my_leaves.php" class="nav-link active">My Leaves</a></li><li class="nav-item"><a href="calendar.php" class="nav-link">Calendar</a></li></ul></nav></div></aside>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid mt-4">
            <h4>My Leave History</h4>

            <div class="card mb-4">
                <div class="card-header"><h4 class="card-title">Filter Leave History</h4></div>
                <div class="card-body">
                    <form method="GET" class="row g-3" name="search">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="form-control">
                        </div>
                        <div class="col-md-3">  
                            <label class="form-label">End Date</label>
                            <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="search_status" class="form-control">
                                <option value="">Choose</option>
                                <option value="pending" <?php if($search_status == 'pending') echo 'selected'; ?>>Pending</option>
                                <option value="approved" <?php if($search_status == 'approved') echo 'selected'; ?>>Approved</option>
                                <option value="rejected" <?php if($search_status == 'rejected') echo 'selected'; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Order By</label>
                            <select name="order_by" class="form-control">
                                <option value="date_desc" <?php if($order_by == 'date_desc') echo 'selected'; ?>>Date Descending</option>
                                <option value="date_asc" <?php if($order_by == 'date_asc') echo 'selected'; ?>>Date Ascending</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary me-1" name="search">Apply Filter</button>
                            <a class="btn btn-danger" href="my_leaves.php">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title">Leave History</h4></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="thead-dark"><tr><th>SI.NO</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php
                            if ($res && mysqli_num_rows($res) > 0) {
                                $i=1;
                                while ($r = mysqli_fetch_assoc($res)) {
                                    $status = $r['status'];
                                    echo "<tr>";
                                    echo "<td>{$i}</td>";
                                    echo "<td>".htmlspecialchars($r['leave_type'])."</td>";
                                    echo "<td>".htmlspecialchars($r['start_date'])."</td>";
                                    echo "<td>".htmlspecialchars($r['end_date'])."</td>";
                                    echo "<td>".htmlspecialchars($r['total_days'])."</td>";
                                    echo "<td>".htmlspecialchars($r['reason'])."</td>";
                                    $badge = $status=='approved' ? 'bg-success' : ($status=='rejected' ? 'bg-danger' : 'bg-warning');
                                    echo "<td><span class='badge {$badge}'>".htmlspecialchars(ucfirst($status))."</span></td>";
                                    echo "<td>";
                                    if ($r['status'] === 'pending' || $r['status'] === 'approved') {
                                        echo "<a href='?cancel_id={$r['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Cancel this leave? (If approved, your leave balance will be credited back.)');\">Cancel</a>";
                                    } else {
                                        echo "-";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                    $i++;
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No records found.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
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