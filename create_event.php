<?php
require 'db.php'; ensure_session();
if (empty($_SESSION['uid'])) js_redirect('login.php');
 
$uid = $_SESSION['uid'];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create_event'])) {
  $title = trim($_POST['title'] ?? 'Intro Call');
  $slug  = strtolower(preg_replace('/[^a-z0-9\-]+/','-', trim($_POST['slug'] ?? 'intro-call')));
  $duration = (int)($_POST['duration'] ?? 30);
  $location = trim($_POST['location'] ?? 'Online Meeting');
  $desc = trim($_POST['description'] ?? '');
 
  $pdo->prepare("INSERT INTO event_types (user_id,title,slug,duration_min,location,description) VALUES (?,?,?,?,?,?)")
      ->execute([$uid,$title,$slug,$duration,$location,$desc]);
  $event_id = $pdo->lastInsertId();
 
  // Save weekly availability rows (0-6)
  for ($d=0;$d<7;$d++){
    if (!empty($_POST["day_$d"])) {
      $start = $_POST["start_$d"] ?? '09:00';
      $end   = $_POST["end_$d"] ?? '17:00';
      $pdo->prepare("INSERT INTO availability (event_id,weekday,start_time,end_time) VALUES (?,?,?,?)")
          ->execute([$event_id,$d,$start.":00",$end.":00"]);
    }
  }
  echo "<script>alert('Event created! Share your link: schedule.php?u=".$_SESSION['username']."&event=$slug');</script>";
  js_redirect('dashboard.php');
}
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Event</title>
<style>
  body{margin:0;background:#0f172a;font-family:system-ui}
  .wrap{max-width:1000px;margin:30px auto;color:#e5e7eb}
  .card{background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:20px;margin-bottom:18px}
  input,textarea,select{width:100%;padding:10px;border-radius:10px;border:1px solid #334155;background:#0a1628;color:#e5e7eb;margin:6px 0}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .grid7{display:grid;grid-template-columns:repeat(7,1fr);gap:10px}
  .btn{padding:12px 16px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;cursor:pointer}
  a{color:#94a3b8}
  label{font-size:12px;color:#94a3b8}
</style></head><body>
<div class="wrap">
  <div class="card">
    <h2>Create Event Type</h2>
    <form method="post">
      <div class="row">
        <div><label>Title</label><input name="title" placeholder="Intro Call"></div>
        <div><label>URL Slug</label><input name="slug" placeholder="intro-call"></div>
      </div>
      <div class="row">
        <div><label>Duration (minutes)</label><input type="number" name="duration" value="30" min="5"></div>
        <div><label>Location</label><input name="location" placeholder="Online Meeting"></div>
      </div>
      <label>Description</label><textarea name="description" rows="3" placeholder="What this meeting is about..."></textarea>
 
      <div class="card" style="margin-top:16px">
        <h3 style="margin-top:0">Weekly Availability</h3>
        <div class="grid7">
          <?php
          $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
          for($i=0;$i<7;$i++): ?>
            <div style="border:1px solid #1f2937;border-radius:12px;padding:10px">
              <label><input type="checkbox" name="day_<?php echo $i;?>"> <?php echo $days[$i];?></label>
              <label>Start</label><input type="time" name="start_<?php echo $i;?>" value="09:00">
              <label>End</label><input type="time" name="end_<?php echo $i;?>" value="17:00">
            </div>
          <?php endfor; ?>
        </div>
      </div>
 
      <button class="btn" type="submit" name="create_event">Save Event</button>
      <a href="dashboard.php" style="margin-left:10px">Back to Dashboard</a>
    </form>
  </div>
</div>
</body></html>
