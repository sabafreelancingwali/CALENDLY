<?php
require 'db.php'; ensure_session();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $username = strtolower(trim($_POST['username'] ?? ''));
  $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
  $stmt->execute([$username]); $u = $stmt->fetch();
  if ($u && password_verify($pass, $u['password_hash'])) {
    $_SESSION['uid']=$u['id']; $_SESSION['username']=$u['username'];
    js_redirect('dashboard.php');
  } else $err="Invalid credentials.";
}
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in</title>
<style>
  body{margin:0;background:#0f172a;font-family:system-ui,Segoe UI,Roboto}
  .wrap{max-width:420px;margin:40px auto;background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:24px;color:#e5e7eb}
  input{width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0a1628;color:#e5e7eb;margin:8px 0}
  .btn{width:100%;padding:12px;border-radius:10px;border:0;background:#22d3ee;color:#06202A;font-weight:800;cursor:pointer}
  a{color:#94a3b8}
  .err{background:#3f1d1d;border:1px solid #7f1d1d;padding:10px;border-radius:10px;margin-bottom:10px}
</style></head><body>
<div class="wrap">
  <h2>Welcome back</h2>
  <?php if (!empty($err)) echo "<div class='err'>".htmlspecialchars($err)."</div>"; ?>
  <form method="post">
    <label>Username</label><input name="username" required>
    <label>Password</label><input type="password" name="password" required>
    <button class="btn" type="submit">Log in</button>
  </form>
  <p style="margin-top:10px"><a href="signup.php">Create an account</a></p>
</div>
</body></html>
 
