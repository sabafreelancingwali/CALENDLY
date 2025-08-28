<?php
require 'db.php'; ensure_session(); if (empty($_SESSION['uid'])) js_redirect('login.php');
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, e.duration_min, e.id AS eid, e.title FROM bookings b JOIN event_types e ON b.event_id=e.id WHERE b.id=? AND b.host_user_id=?");
$stmt->execute([$id,$_SESSION['uid']]); $bk=$stmt->fetch();
if(!$bk) die('Booking not found.');
 
if($_SERVER['REQUEST_METHOD']==='POST'){
  $date = $_POST['date']; $start = $_POST['start_time'];
  $end = date('H:i:s', strtotime($start) + ((int)$bk['duration_min'])*60);
  try{
    $pdo->prepare("UPDATE bookings SET date=?, start_time=?, end_time=? WHERE id=? AND host_user_id=?")
        ->execute([$date,$start,$end,$id,$_SESSION['uid']]);
    echo "<script>alert('Rescheduled!');</script>";
    js_redirect('dashboard.php');
  }catch(Exception $e){
    echo "<script>alert('That slot is taken. Choose another.');</script>";
    js_redirect($_SERVER['REQUEST_URI']);
  }
}
 
// Build slots for chosen date (today default)
$av = $pdo->prepare("SELECT * FROM availability WHERE event_id=?"); $av->execute([$bk['eid']]); $avail=$av->fetchAll();
$chosen = isset($_GET['date']) ? DateTime::createFromFormat('Y-m-d', $_GET['date']) : new DateTime('today');
$weekday = (int)$chosen->format('w');
$dayRows = array_values(array_filter($avail, fn($r)=> (int)$r['weekday']===$weekday));
$slots=[]; function slots($s,$e,$d){$o=[];$S=strtotime($s);$E=strtotime($e);while($S+$d*60<=$E){$o[]=date('H:i:s',$S);$S+=$d*60;}return $o;}
foreach($dayRows as $r){ $slots=array_merge($slots, slots($r['start_time'],$r['end_time'],(int)$bk['duration_min'])); }
sort($slots);
// remove already booked in that date
$b = $pdo->prepare("SELECT start_time FROM bookings WHERE event_id=? AND date=? AND status='scheduled' AND id<>?");
$b->execute([$bk['eid'],$chosen->format('Y-m-d'),$id]); $taken=$b->fetchAll(PDO::FETCH_COLUMN,0); $takenSet=array_flip($taken);
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reschedule</title>
<style>
 body{margin:0;background:#0f172a;font-family:system-ui;color:#e5e7eb}
 .wrap{max-width:800px;margin:30px auto}
 .card{background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:18px}
 .slot{display:inline-block;margin:6px;padding:10px 12px;border:1px solid #334155;border-radius:12px;cursor:pointer}
 .slot.disabled{opacity:.35;cursor:not-allowed;text-decoration:line-through}
 input{width:100%;padding:10px;border-radius:10px;border:1px solid #334155;background:#0a1628;color:#e5e7eb;margin:6px 0}
 .btn{padding:12px 16px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;cursor:pointer}
 a{color:#94a3b8}
</style></head><body>
<div class="wrap">
  <div class="card">
    <h2>Reschedule: <?php echo htmlspecialchars($bk['title']); ?></h2>
    <p>Current: <?php echo htmlspecialchars($bk['date']); ?> · <?php echo substr($bk['start_time'],0,5); ?></p>
 
    <?php $prev=(clone $chosen)->modify('-1 day')->format('Y-m-d'); $next=(clone $chosen)->modify('+1 day')->format('Y-m-d'); ?>
    <p>
      <a href="?id=<?php echo $id; ?>&date=<?php echo $prev; ?>">◀ Prev</a>
      <span style="border:1px solid #334155;border-radius:10px;padding:6px 10px;margin:0 8px"><?php echo $chosen->format('D, M j, Y'); ?></span>
      <a href="?id=<?php echo $id; ?>&date=<?php echo $next; ?>">Next ▶</a>
    </p>
 
    <form method="post" onsubmit="return pickTime()">
      <input type="hidden" name="date" value="<?php echo $chosen->format('Y-m-d'); ?>">
      <input type="hidden" name="start_time" id="start_time">
      <div>
        <?php if(!$slots) echo "<p>No availability on this day.</p>";
        foreach($slots as $s): $disabled = isset($takenSet[$s]); ?>
          <span class="slot <?php echo $disabled?'disabled':''; ?>" data-time="<?php echo $s; ?>"><?php echo substr($s,0,5); ?></span>
        <?php endforeach; ?>
      </div>
      <button class="btn" type="submit">Save New Time</button>
      <a style="margin-left:8px" href="dashboard.php">Cancel</a>
    </form>
  </div>
</div>
<script>
let chosen=null;
document.querySelectorAll('.slot').forEach(el=>{
  el.addEventListener('click',()=>{
    if(el.classList.contains('disabled')) return;
    document.querySelectorAll('.slot').forEach(s=>s.style.outline='none');
    el.style.outline='2px solid #22d3ee';
    chosen = el.dataset.time;
    document.getElementById('start_time').value = chosen;
  });
});
function pickTime(){ if(!chosen){ alert('Pick a time'); return false; } return true; }
</script>
</body></html>
