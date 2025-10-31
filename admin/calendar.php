<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}
include '../config/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Calendar</title>
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
  <!-- FullCalendar CSS -->
  <link rel="stylesheet" href="../fullcalendar/main.min.css">
  <!-- jQuery -->
  <script src="../adminlte/plugins/jquery/jquery.min.js"></script>
  <!-- AdminLTE JS -->
  <script src="../adminlte/dist/js/adminlte.min.js"></script>
  <!-- FullCalendar JS -->
  <script src="../fullcalendar/main.min.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Calendar</h1>
    </section>
    <section class="content">
      <div class="container-fluid">
        <div id="calendar"></div>
      </div>
    </section>
  </div>

  <?php include 'footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'fetch_events.php', // Admin events
        editable: false,
        selectable: false,
        navLinks: true,
        eventDisplay: 'block',
        eventColor: '#378006',
        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.title);
        }
    });
    calendar.render();
});
</script>
</body>
</html>
