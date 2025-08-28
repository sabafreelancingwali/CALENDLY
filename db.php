<?php
// db.php
$DB_HOST = 'localhost';            // change if needed
$DB_NAME = 'dbru8z9ip1rw47';
$DB_USER = 'uei4bkjtcem6s';
$DB_PASS = 'wmhalmspfjgz';
 
try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Exception $e) {
  die("DB Connection failed: " . htmlspecialchars($e->getMessage()));
}
 
function js_redirect($url) {
  echo "<script>window.location.href='".htmlspecialchars($url, ENT_QUOTES)."';</script>";
  exit;
}
 
function ensure_session() { if (session_status() !== PHP_SESSION_ACTIVE) session_start(); }
?>
