<?php
require 'db.php'; ensure_session();
 
// Resolve host and event
$u = strtolower(trim($_GET['u'] ?? ''));
$slug = strtolower(trim($_GET['event'] ?? ''));
 
if (!$u || !$slug) die('Missing user or event.');
 
$usr = $pdo->prepare("SELECT * FROM users WHERE username=?"); $usr->execute([$u]); $host = $usr->fetch();
if (!$host) die('Host not found.');
 
$evt = $pdo->prepare("SELECT * FROM event_types WHERE user_id=? AND slug=?");
$evt->execute([$host['id'], $slug]); $event = $evt->fetch();
if (!$event) die('Event not found.');
 
$duration = (int)$event['duration_min'];
 
// Helper: get availability rows
$av = $pdo->prepare("SELECT * FROM availability WHERE event_id=?"); $av->execute([$event['id']]);
$avail = $av->fetchAll();
 
// Get chosen date (default today)
$today = new DateTime('today');
$chosen = isset($_GET['date']) ? DateTime::createFromFormat('Y-m-d', $_GET['date']) : $today;
if (!$chosen) $chosen = $today;
 
// Fetch existing bookings for that date
$b = $pdo->prepare("SELECT start_time,end_time FROM bookings WHERE event_id=? AND date=? AND status='scheduled'");
$b->execute([$event['id'], $chosen->format('Y-m-d')]);
$booked = $b->fetchAll();
$bookedSet = [];
foreach($booked as $bk){ $bookedSet[$bk['start_time']] = true; }
 
// Build slots for the chosen weekday
$weekday = (int)$chosen->format('w'); // 0-6
$dayRows = array_values(array_filter($avail, fn($r)=> (int)$r['weekday']===$weekday));
 
function build_slots($start, $end, $dur){
  $slots=[];
  $s=strtotime($start); $e=strtotime($end);
  while($s + $dur*60 <= $e){ $slots[] = date('H:i:s',$s); $s += $dur*60; }
  return $slots;
}
$slots=[];
foreach($dayRows as $r){
  $slots = array_merge($slots, build_slots($r['start_time'], $r['end_time'], $duration));
}
sort($slots);
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book: <?php echo htmlspecialchars($event['title']); ?></title>
<style>
  body{margin:0;background:#0f172a;font-family:system-ui}
  .wrap{max-width:1000px;margin:30px auto;color:#e5e7eb;display:grid;grid-template-columns:1.1fr .9fr;gap:18px}
  .card{background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:20px}
  .slot{display:inline-block;margin:6px;padding:10px 12px;border:1px solid #334155;border-radius:12px;cursor:pointer}
  .slot.disabled{opacity:.35;cursor:not-allowed;text-decoration:line-through}
  input{width:100%;padding:10px;border-radius:10px;border:1px solid #334155;background:#0a1628;color:#e5e7eb;margin:6px 0}
  .btn{padding:12px 16px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;cursor:pointer}
  a{color:#94a3b8}
  .date-nav{display:flex;gap:8px;align-items:center}
  .date-nav a{border:1px solid #334155;border-radius:10px;padding:8px 10px;text-decoration:none;color:#e5e7eb}
  h2,h3{margin-top:0}
  @media(max-width:900px){.wrap{grid-template-columns:1fr}}
</style>
</head><body>
<div class="wrap">
  <div class="card">
    <h2><?php echo htmlspecialchars($event['title']); ?> · <?php echo (int)$event['duration_min'];?> min</h2>
    <p><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></p>
    <p><strong>Host:</strong> @<?php echo htmlspecialchars($u); ?> · <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
 
    <?php
      $prev = (clone $chosen)->modify('-1 day')->format('Y-m-d');
      $next = (clone $chosen)->modify('+1 day')->format('Y-m-d');
    ?>
    <div class="date-nav">
      <a href="?u=<?php echo urlencode($u) ?>&event=<?php echo urlencode($slug) ?>&date=<?php echo $prev ?>">◀ Prev</a>
      <div style="padding:8px 12px;border:1px solid #334155;border-radius:10px"><?php echo $chosen->format('D, M j, Y'); ?></div>
      <a href="?u=<?php echo urlencode($u) ?>&event=<?php echo urlencode($slug) ?>&date=<?php echo $next ?>">Next ▶</a>
    </div>
 
    <h3 style="margin-top:14px">Select a time</h3>
    <div id="slots">
      <?php if (!$slots): ?>
        <p>No availability on this day.</p>
      <?php else:
        foreach($slots as $s):
          $disabled = isset($bookedSet[$s]);
          $label = substr($s,0,5);
      ?>
        <span class="slot <?php echo $disabled?'disabled':''; ?>" data-time="<?php echo $s; ?>"><?php echo $label; ?></span>
      <?php endforeach; endif; ?>
    </div>
  </div>
 
  <div class="card">
    <h3>Enter your details</h3>
    <form method="post" action="book.php" onsubmit="return checkPicked()">
      <input type="hidden" name="event_id" value="<?php echo (int)$event['id']; ?>">
      <input type="hidden" name="host_user_id" value="<?php echo (int)$host['id']; ?>">
      <input type="hidden" name="date" value="<?php echo htmlspecialchars($chosen->format('Y-m-d')); ?>">
      <input type="hidden" name="start_time" id="start_time">
      <label>Your name</label><input name="guest_name" required>
      <label>Your email</label><input type="email" name="guest_email" required>
      <button class="btn" type="submit">Confirm Booking</button>
      <p style="margin-top:8px"><small>You will receive an email confirmation.</small></p>
    </form>
    <p><a href="index.html">Back to home</a></p>
  </div>
</div>
 
<script>
  let picked=null;
  document.querySelectorAll('.slot').forEach(el=>{
    el.addEventListener('click', ()=>{
      if (el.classList.contains('disabled')) return;
      document.querySelectorAll('.slot').forEach(s=>s.style.outline='none');
      el.style.outline='2px solid #22d3ee';
      picked = el.dataset.time;
      document.getElementById('start_time').value = picked;
    });
  });
  function checkPicked(){
    if(!picked){ alert('Please select a time slot.'); return false; }
    return true;
  }
</script>
</body></html>
 
