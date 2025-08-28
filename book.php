<?php
require 'db.php'; ensure_session();
 
$event_id = (int)($_POST['event_id'] ?? 0);
$host_user_id = (int)($_POST['host_user_id'] ?? 0);
$date = $_POST['date'] ?? '';
$start = $_POST['start_time'] ?? '';
$guest_name = trim($_POST['guest_name'] ?? '');
$guest_email = trim($_POST['guest_email'] ?? '');
 
if(!$event_id || !$host_user_id || !$date || !$start || !$guest_name || !$guest_email) die('Missing fields.');
 
$evt = $pdo->prepare("SELECT * FROM event_types WHERE id=?"); $evt->execute([$event_id]); $event = $evt->fetch();
if(!$event) die('Event not found');
 
$dur = (int)$event['duration_min'];
$end = date('H:i:s', strtotime($start) + $dur*60);
 
// Insert booking (unique constraint protects double booking)
try{
  $pdo->prepare("INSERT INTO bookings (event_id,host_user_id,guest_name,guest_email,date,start_time,end_time) VALUES (?,?,?,?,?,?,?)")
      ->execute([$event_id,$host_user_id,$guest_name,$guest_email,$date,$start,$end]);
  $bid = $pdo->lastInsertId();
} catch (Exception $e) {
  echo "<script>alert('Sorry, that slot was just taken. Pick another time.');</script>";
  js_redirect($_SERVER['HTTP_REFERER'] ?? 'index.html');
}
 
// Send emails (requires mail() configured on server)
$subject = "Meeting confirmed: {$event['title']} on $date at ".substr($start,0,5);
$bodyGuest = "Hi $guest_name,\n\nYour meeting is booked.\n\nEvent: {$event['title']}\nDate: $date\nTime: ".substr($start,0,5)." - ".substr($end,0,5)."\nLocation: {$event['location']}\n\nIf you need to cancel or reschedule, use the link sent by your host.\n";
@mail($guest_email, $subject, $bodyGuest, "From: no-reply@yourdomain.com");
 
$host = $pdo->prepare("SELECT email,username FROM users WHERE id=?"); $host->execute([$host_user_id]); $h = $host->fetch();
$bodyHost = "New booking!\n\nEvent: {$event['title']}\nGuest: $guest_name <$guest_email>\nDate: $date\nTime: ".substr($start,0,5)." - ".substr($end,0,5);
@mail($h['email'], "New booking: {$event['title']}", $bodyHost, "From: no-reply@yourdomain.com");
 
// Redirect to confirmation via JS
js_redirect('confirm.php?id='.$bid);
