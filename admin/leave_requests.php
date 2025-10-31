<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: login.php"); exit; }

date_default_timezone_set('Asia/Kolkata');

// --- ACTION LOGIC (simplified for direct update) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    $q = mysqli_query($conn, "SELECT * FROM leave_requests WHERE id=$id LIMIT 1");
    if ($q && mysqli_num_rows($q) == 1) {
        $row = mysqli_fetch_assoc($q);
        if ($action === 'approve' && $row['status'] === 'pending') {
            mysqli_query($conn, "UPDATE leave_requests SET status='approved', updated_at=NOW() WHERE id=$id");
        } elseif ($action === 'reject' && $row['status'] === 'pending') {
            mysqli_query($conn, "UPDATE leave_requests SET status='rejected', updated_at=NOW() WHERE id=$id");
        }
    }
    header("Location: leave_requests.php"); exit;
}

// --- FILTERING AND SORTING LOGIC ---
$search_name = empty($_GET['search_name']) ? '' : $_GET['search_name'];
$from_date = empty($_GET['from_date']) ? '' : $_GET['from_date'];
$to_date = empty($_GET['to_date']) ? '' : $_GET['to_date'];
$search_status = empty($_GET['search_status']) ? '' : $_GET['search_status'];
$order_by = empty($_GET['order_by']) ? 'date_desc' : $_GET['order_by'];

$root_sql = "SELECT lr.*, u.name AS employee_name, u.email AS employee_email FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE 1=1";

if (isset($_GET['search'])) {
    if (!empty($search_name)) {
        $keywords = "%" . mysqli_real_escape_string($conn, $search_name) . "%";
        $root_sql .= " AND u.name LIKE '$keywords'";
    }
    if (!empty($from_date) && !empty($to_date)) {
        $root_sql .= " AND lr.start_date BETWEEN '{$from_date}' AND '{$to_date}'";
    } else if (!empty($from_date)) {
        $root_sql .= " AND lr.start_date >= '{$from_date}'";
    } else if (!empty($to_date)) {
        $root_sql .= " AND lr.end_date <= '{$to_date}'";
    }
    if(!empty($search_status)) {
        $status = mysqli_real_escape_string($conn, $search_status);
        $root_sql .= " AND lr.status = '$status'";
    }
}

if ($order_by == 'date_desc') {
    $root_sql .= " ORDER BY lr.start_date DESC";
} else if ($order_by == 'date_asc') {
    $root_sql .= " ORDER BY lr.start_date ASC";
} else if ($order_by == 'name_asc') {
    $root_sql .= " ORDER BY u.name ASC";
} else if ($order_by == 'name_desc') {
    $root_sql .= " ORDER BY u.name DESC";
} else {
    $root_sql .= " ORDER BY lr.id DESC";
}

$res = mysqli_query($conn, $root_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Leave Requests - LMS</title>
<link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li></ul></nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4"><a href="index.php" class="brand-link"><span class="brand-text font-weight-light">LMS Admin</span></a><div class="sidebar"><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column"><li class="nav-item"><a href="index.php" class="nav-link">Calendar</a></li><li class="nav-item"><a href="employees.php" class="nav-link">Manage Users</a></li><li class="nav-item"><a href="holidays.php" class="nav-link">Manage Holidays</a></li><li class="nav-item"><a href="leave_requests.php" class="nav-link active">Leave Requests</a></li></ul></nav></div></aside>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid mt-4">
            
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-title">Filter Leave Requests</h4></div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Employee Name</label>
                            <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" class="form-control" placeholder="Employee Name">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="form-control">
                        </div>
                        <div class="col-md-2">  
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
                        <div class="col-md-3">
                            <label class="form-label">Order By</label>
                            <select name="order_by" class="form-control">
                                <option value="date_desc" <?php if($order_by == 'date_desc') echo 'selected'; ?>>Start Date Descending</option>
                                <option value="date_asc" <?php if($order_by == 'date_asc') echo 'selected'; ?>>Start Date Ascending</option>
                                <option value="name_asc" <?php if($order_by == 'name_asc') echo 'selected'; ?>>Name Ascending</option>
                                <option value="name_desc" <?php if($order_by == 'name_desc') echo 'selected'; ?>>Name Descending</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button class="btn btn-primary me-1" name="search">Apply Filter</button>
                            <a class="btn btn-danger" href="leave_requests.php">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title">Leave Requests</h4></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark"><tr><th>SI.NO</th><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php
                            if ($res && mysqli_num_rows($res) > 0) {
                                $i=1;
                                while ($r = mysqli_fetch_assoc($res)) {
                                    $status = $r['status'];
                                    echo "<tr>";
                                    echo "<td>{$i}</td>";
                                    echo "<td>".htmlspecialchars($r['employee_name'])."</td>";
                                    echo "<td>".htmlspecialchars($r['leave_type'])."</td>";
                                    echo "<td>".htmlspecialchars($r['start_date'])."</td>";
                                    echo "<td>".htmlspecialchars($r['end_date'])."</td>";
                                    echo "<td>".htmlspecialchars($r['total_days'])."</td>";
                                    echo "<td>".htmlspecialchars($r['reason'])."</td>";
                                    $badge = $status=='approved' ? 'bg-success' : ($status=='rejected' ? 'bg-danger' : 'bg-warning');
                                    echo "<td><span class='badge {$badge}'>".htmlspecialchars(ucfirst($status))."</span></td>";
                                    echo "<td>";
                                    if ($status === 'pending') {
                                        echo "<a href='?action=approve&id={$r['id']}' class='btn btn-success btn-sm' onclick=\"return confirm('Approve this leave?');\">Approve</a> ";
                                        echo "<a href='?action=reject&id={$r['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Reject this leave?');\">Reject</a>";
                                    } else {
                                        echo "-";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                    $i++;
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No requests found.</td></tr>";
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