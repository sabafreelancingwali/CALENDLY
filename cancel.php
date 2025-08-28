
<?php
require 'db.php'; ensure_session(); if (empty($_SESSION['uid'])) js_redirect('login.php');
$id = (int)($_GET['id'] ?? 0);
$pdo->prepare("UPDATE bookings SET status='canceled' WHERE id=? AND host_user_id=?")->execute([$id,$_SESSION['uid']]);
echo "<script>alert('Booking canceled.');</script>";
js_redirect('dashboard.php');
 
