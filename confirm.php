<?php
require 'db.php';
$id = (int)($_GET['id'] ?? 0);
$bk = null;
if ($id){
  $stmt = $pdo->prepare("SELECT b.*, e.title, e.location, u.username FROM bookings b
    JOIN event_types e ON b.event_id=e.id
    JOIN users u ON b.host_user_id=u.id
    WHERE b.id=?");
  $stmt->execute([$id]); $bk = $stmt->fetch();
}
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Booking Confirmed</title>
<style>
 body{margin:0;background:#0f172a;font-family:system-ui}
 .wrap{max-width:680px;margin:40px auto;background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:24px;color:#e5e7eb;text-align:center}
 .btn{display:inline-block;margin-top:12px;padding:12px 16px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;text-decoration:none}
 code{background:#0a1628;border:1px solid #334155;padding:2px 8px;border-radius:8px}
</style></head><body>
<div class="wrap">
  <?php if($bk): ?>
    <h2>✅ Your meeting is booked!</h2>
    <p><strong><?php echo htmlspecialchars($bk['title']); ?></strong></p>
    <p><?php echo htmlspecialchars($bk['date']); ?> · <?php echo substr($bk['start_time'],0,5); ?>–<?php echo substr($bk['end_time'],0,5); ?></p>
    <p>Host: @<?php echo htmlspecialchars($bk['username']); ?> · Location: <?php echo htmlspecialchars($bk['location']); ?></p>
    <a class="btn" href="index.html">Back to Home</a>
  <?php else: ?>
    <h2>Booking not found.</h2>
    <a class="btn" href="index.html">Home</a>
  <?php endif; ?>
</div>
</body></html>
 
