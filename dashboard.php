<?php
require 'db.php'; ensure_session(); if (empty($_SESSION['uid'])) js_redirect('login.php');
$uid = $_SESSION['uid'];
// Fetch my events
$ev = $pdo->prepare("SELECT * FROM event_types WHERE user_id=? ORDER BY created_at DESC"); $ev->execute([$uid]); $events = $ev->fetchAll();
 
// Fetch upcoming & past
$up = $pdo->prepare("SELECT b.*, e.title FROM bookings b JOIN event_types e ON b.event_id=e.id WHERE b.host_user_id=? AND b.status='scheduled' AND b.date >= CURDATE() ORDER BY b.date, b.start_time");
$up->execute([$uid]); $upcoming = $up->fetchAll();
 
$pa = $pdo->prepare("SELECT b.*, e.title FROM bookings b JOIN event_types e ON b.event_id=e.id WHERE b.host_user_id=? AND b.date < CURDATE() ORDER BY b.date DESC, b.start_time DESC");
$pa->execute([$uid]); $past = $pa->fetchAll();
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>
<style>
 body{margin:0;background:#0f172a;font-family:system-ui;color:#e5e7eb}
 .container{max-width:1100px;margin:24px auto;padding:0 12px}
 .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
 .card{background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:18px}
 .btn{display:inline-block;padding:10px 12px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;text-decoration:none}
 code{background:#0a1628;border:1px solid #334155;padding:1px 6px;border-radius:8px}
 table{width:100%;border-collapse:collapse;margin-top:10px}
 th,td{border-bottom:1px solid #1f2937;padding:10px;text-align:left}
 a{color:#94a3b8}
 @media(max-width:900px){.row{grid-template-columns:1fr}}
</style></head><body>
<div class="container">
  <h2>Welcome, @<?php echo htmlspecialchars($_SESSION['username']); ?></h2>
  <div class="row">
    <div class="card">
      <h3>Your Event Types</h3>
      <p><a class="btn" href="create_event.php">+ New Event</a> <a class="btn" style="margin-left:8px;background:#14b8a6" href="logout.php">Logout</a></p>
      <table>
        <tr><th>Title</th><th>Duration</th><th>Share Link</th></tr>
        <?php foreach($events as $e): ?>
          <tr>
            <td><?php echo htmlspecialchars($e['title']); ?></td>
            <td><?php echo (int)$e['duration_min']; ?> min</td>
            <td>
              <?php $link = "schedule.php?u=".$_SESSION['username']."&event=".$e['slug']; ?>
              <code><?php echo htmlspecialchars($link); ?></code>
              <a href="<?php echo htmlspecialchars($link); ?>" class="btn" style="margin-left:8px">Open</a>
            </td>
          </tr>
        <?php endforeach; if(!$events) echo "<tr><td colspan='3'>No events yet.</td></tr>"; ?>
      </table>
    </div>
 
    <div class="card">
      <h3>Upcoming Meetings</h3>
      <table>
        <tr><th>Event</th><th>Date</th><th>Time</th><th>Guest</th><th>Actions</th></tr>
        <?php foreach($upcoming as $b): ?>
          <tr>
            <td><?php echo htmlspecialchars($b['title']); ?></td>
            <td><?php echo htmlspecialchars($b['date']); ?></td>
            <td><?php echo substr($b['start_time'],0,5); ?>–<?php echo substr($b['end_time'],0,5); ?></td>
            <td><?php echo htmlspecialchars($b['guest_name']); ?> (<?php echo htmlspecialchars($b['guest_email']); ?>)</td>
            <td>
              <a class="btn" href="reschedule.php?id=<?php echo (int)$b['id']; ?>">Reschedule</a>
              <a class="btn" style="background:#f43f5e;color:#fff" href="cancel.php?id=<?php echo (int)$b['id']; ?>">Cancel</a>
            </td>
          </tr>
        <?php endforeach; if(!$upcoming) echo "<tr><td colspan='5'>No upcoming meetings.</td></tr>"; ?>
      </table>
    </div>
  </div>
 
  <div class="card" style="margin-top:16px">
    <h3>Past Meetings</h3>
    <table>
      <tr><th>Event</th><th>Date</th><th>Time</th><th>Guest</th><th>Status</th></tr>
      <?php foreach($past as $b): ?>
        <tr>
          <td><?php echo htmlspecialchars($b['title']); ?></td>
          <td><?php echo htmlspecialchars($b['date']); ?></td>
          <td><?php echo substr($b['start_time'],0,5); ?>–<?php echo substr($b['end_time'],0,5); ?></td>
          <td><?php echo htmlspecialchars($b['guest_name']); ?></td>
          <td><?php echo htmlspecialchars($b['status']); ?></td>
        </tr>
      <?php endforeach; if(!$past) echo "<tr><td colspan='5'>No past meetings.</td></tr>"; ?>
    </table>
  </div>
</div>
</body></html>
 
