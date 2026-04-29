<?php
session_start();

if(!empty($_SESSION['admin_logged_in'])){
  header('Location: dashboard.php');
  exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
 
  $captcha = $_POST['g-recaptcha-response'] ?? '';
  if(empty($captcha)){
    $error = 'Harap centang captcha terlebih dahulu.';
  } else {
    $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=6LeJj84sAAAAAC0XnPWPOVcdVUDP7T4xzpA1Xr9R&response=' . $captcha);
    $result = json_decode($verify);
    if(!$result->success){
      $error = 'Verifikasi captcha gagal. Coba lagi.';
    }
  }

  if(!$error){
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if($user === 'admin' && $pass === 'admin123'){
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_user'] = $user;
      header('Location: dashboard.php');
      exit;
    } else {
      $error = 'Username atau password salah.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin — Kebun Ndesa Tanah Merah</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Jost:wght@200;300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<style>
  .admin-login-section {
    height: 100vh;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 0 8vw;
  }
  .admin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
    width: 100%;
  }
  .brand-side {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .admin-brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 40px;
    color: var(--cream);
    font-weight: 400;
    line-height: 1.1;
    margin: 0;
  }
  .admin-brand-tag {
    font-size: 13px;
    font-weight: 300;
    color: rgba(240,247,244,0.4);
  }
  .login-box {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(82,183,136,0.2);
    border-radius: 24px;
    padding: 36px 40px;
  }
  .login-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: var(--cream);
    margin-bottom: 6px;
  }
  .login-sub {
    font-size: 13px;
    font-weight: 300;
    color: rgba(240,247,244,0.4);
    margin-bottom: 24px;
  }
  .f-label {
    font-size: 11px;
    font-weight: 400;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(240,247,244,0.5);
    display: block;
    margin-bottom: 8px;
  }
  .f-input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(82,183,136,0.2);
    border-radius: 4px;
    color: var(--cream);
    font-size: 14px;
    font-weight: 300;
    font-family: 'Jost', sans-serif;
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
  }
  .f-input:focus {
    border-color: rgba(82,183,136,0.5);
    background: rgba(255,255,255,0.09);
  }
  .f-input::placeholder { color: rgba(240,247,244,0.2); }
  .form-group { margin-bottom: 16px; }
  .captcha-wrap { margin-bottom: 16px; transform: scale(0.95); transform-origin: left; }
  .btn-login {
    width: 100%;
    padding: 14px;
    background: var(--gold);
    color: var(--forest);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background .2s;
    margin-bottom: 16px;
  }
  .btn-login:hover { background: var(--gold-light); }
  .back-link-login {
    display: block;
    text-align: center;
    font-size: 13px;
    font-weight: 300;
    color: rgba(240,247,244,0.35);
    text-decoration: none;
  }
  .back-link-login:hover { color: rgba(240,247,244,0.6); }
</style>
</head>
<body>
<div class="noise-overlay"></div>
<div id="cursor-dot"></div>
<div id="cursor-ring"></div>

<div class="admin-login-section">
  <div class="admin-grid">

    
    <div class="brand-side">
      <div class="admin-brand-name">Kebun Ndesa<br>Tanah Merah</div>
      <div class="admin-brand-tag">Sistem Manajemen Admin</div>
      <svg width="52" height="52" viewBox="0 0 52 52" fill="none" style="margin-top:16px;">
        <rect width="52" height="52" rx="12" fill="rgba(201,169,110,0.12)" stroke="rgba(201,169,110,0.3)" stroke-width="1"/>
        <circle cx="26" cy="26" r="10" fill="none" stroke="#C9A96E" stroke-width="1.5"/>
        <circle cx="26" cy="26" r="4" fill="rgba(201,169,110,0.4)" stroke="#C9A96E" stroke-width="1.5"/>
        <line x1="26" y1="14" x2="26" y2="18" stroke="#C9A96E" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="26" y1="34" x2="26" y2="38" stroke="#C9A96E" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="14" y1="26" x2="18" y2="26" stroke="#C9A96E" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="34" y1="26" x2="38" y2="26" stroke="#C9A96E" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </div>

   
    <div class="login-box">
      <div class="login-title">Selamat Datang</div>
      <div class="login-sub">Masuk untuk mengelola sistem kebun ndesa</div>

      <?php if($error): ?>
        <div class="alert alert-danger" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="f-label">Username</label>
          <input class="f-input" type="text" name="username" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="f-label">Password</label>
          <input class="f-input" type="password" name="password" placeholder="••••••••">
        </div>

        
        <div class="captcha-wrap">
          <div class="g-recaptcha" data-sitekey="6LeJj84sAAAAAChPfeUbfB3wR76d0yH9QENUNZ79" data-theme="dark"></div>
        </div>

        <button type="submit" class="btn-login">Masuk</button>
      </form>
      <a class="back-link-login" href="/index.php">← Kembali ke Beranda</a>
    </div>

  </div>
</div>

<script src="/assets/js/script.js"></script>
</body>
</html>