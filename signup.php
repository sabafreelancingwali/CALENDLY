<?php
require 'db.php'; ensure_session();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $username = strtolower(trim($_POST['username'] ?? ''));
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  if (!$username || !$email || !$pass) $err = "All fields required.";
  if (!isset($err)) {
    $stmt = $pdo->prepare("INSERT INTO users (username,email,password_hash) VALUES (?,?,?)");
    try {
      $stmt->execute([$username, $email, password_hash($pass, PASSWORD_BCRYPT)]);
      $_SESSION['uid'] = $pdo->lastInsertId();
      $_SESSION['username'] = $username;
      echo "<script>alert('Welcome! Let’s create your first event.');</script>";
      js_redirect('create_event.php');
    } catch (Exception $e) { $err = "Username or email already exists."; }
  }
}
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign up</title>
<style>
  body{margin:0;background:#0f172a;font-family:system-ui,Segoe UI,Roboto}
  .wrap{max-width:480px;margin:40px auto;background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:24px;color:#e5e7eb}
  input{width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0a1628;color:#e5e7eb;margin:8px 0}
  .btn{width:100%;padding:12px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;cursor:pointer}
  a{color:#94a3b8}
  .err{background:#3f1d1d;border:1px solid #7f1d1d;padding:10px;border-radius:10px;margin-bottom:10px}
</style></head><body>
<div class="wrap">
  <h2>Create your account</h2>
  <?php if (!empty($err)) echo "<div class='err'>".htmlspecialchars($err)."</div>"; ?>
  <form method="post">
    <label>Username</label><input name="username" required>
    <label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" required minlength="6">
    <button class="btn" type="submit">Sign Up</button>
  </form>
  <p style="margin-top:10px">Already have an account? <a href="login.php">Log in</a></p>
</div>
</body></html>
