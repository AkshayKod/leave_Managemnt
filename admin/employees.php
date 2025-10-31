<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: login.php"); exit; }

//POST/GET LOGIC
if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    mysqli_query($conn, "INSERT INTO users (name,email,password,role,gender,address,contact) VALUES ('$name','$email','$password','employee','$gender','$address','$contact')");
    header("Location: employees.php"); exit;
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='employee'");
    header("Location: employees.php"); exit;
}

//FILTERING AND SORTING LOGIC
$search_name = empty($_GET['search_name']) ? '' : $_GET['search_name'];
$search_email = empty($_GET['search_email']) ? '' : $_GET['search_email'];
$order_by = empty($_GET['order_by']) ? 'name_asc' : $_GET['order_by'];

$root_sql = "SELECT * FROM users WHERE role='employee'";

if (isset($_GET['search'])) {
    if (!empty($search_name)) {
        $keywords = "%" . mysqli_real_escape_string($conn, $search_name) . "%";
        $root_sql .= " AND name LIKE '$keywords'";
    }
    if (!empty($search_email)) {
        $keywords = "%" . mysqli_real_escape_string($conn, $search_email) . "%";
        $root_sql .= " AND email LIKE '$keywords'";
    }
}

if ($order_by == 'name_asc') {
    $root_sql .= " ORDER BY name ASC";
} else if ($order_by == 'name_desc') {
    $root_sql .= " ORDER BY name DESC";
} else if ($order_by == 'id_asc') {
    $root_sql .= " ORDER BY id ASC";
} else {
    $root_sql .= " ORDER BY id DESC";
}

$res = mysqli_query($conn, $root_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Employees</title>
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
      <li class="nav-item"><a href="employees.php" class="nav-link active">Manage Users</a></li>
      <li class="nav-item"><a href="holidays.php" class="nav-link">Manage Holidays</a></li>
      <li class="nav-item"><a href="leave_requests.php" class="nav-link">Leave Requests</a></li>
    </ul></nav></div>
</aside>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid mt-4">
            
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-title">Filter Users</h4></div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search by Name</label>
                            <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" class="form-control" placeholder="Employee Name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search by Email</label>
                            <input type="email" name="search_email" value="<?php echo htmlspecialchars($search_email); ?>" class="form-control" placeholder="Employee Email">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Order By</label>
                            <select name="order_by" class="form-control">
                                <option value="name_asc" <?php if($order_by == 'name_asc') echo 'selected'; ?>>Name Ascending</option>
                                <option value="name_desc" <?php if($order_by == 'name_desc') echo 'selected'; ?>>Name Descending</option>
                                <option value="id_desc" <?php if($order_by == 'id_desc') echo 'selected'; ?>>ID Descending</option>
                                <option value="id_asc" <?php if($order_by == 'id_asc') echo 'selected'; ?>>ID Ascending</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary me-1" name="search">Apply Filter</button>
                            <a class="btn btn-danger" href="employees.php">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Employee List (Role: Employee)</h5>
                    <a href="export_employees.php" class="btn btn-info btn-sm float-right mr-2"><i class="fas fa-file-excel"></i> Export</a>

                    <button type="button" class="btn btn-success btn-sm float-right" data-toggle="modal" data-target="#addEmployeeModal">
                        <i class="fas fa-plus"></i> Add New Employee
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($res) > 0) {
                                    while($r=mysqli_fetch_assoc($res)){ 
                                        echo "<tr>";
                                        echo "<td>{$r['id']}</td>";
                                        echo "<td>".htmlspecialchars($r['name'])."</td>";
                                        echo "<td>".htmlspecialchars($r['email'])."</td>";
                                        echo "<td><a href='?delete={$r['id']}' onclick=\"return confirm('Delete this employee?')\" class='btn btn-danger btn-sm'>Delete</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No employee records found.</td></tr>";
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

<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                    <div class="form-group"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="form-group"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                    <div class="form-group">
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group"><textarea name="address" class="form-control" placeholder="Address" required rows="2"></textarea></div>
                    <div class="form-group"><input type="text" name="contact" class="form-control" placeholder="Contact Number" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button name="add" class="btn btn-success">Add Employee</button>
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