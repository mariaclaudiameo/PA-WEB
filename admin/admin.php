<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Admin — ' . APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Jost:wght@200;300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/style.css">
</head>
<body style="cursor:auto;">

<div class="admin-wrap">
 
  <aside class="admin-sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-name">Kebun Ndesa<br>Tanah Merah</div>
      <div class="sidebar-logo-sub">Admin Panel</div>
    </div>
    <?php $cur = strtok(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '?'); ?>
    <ul class="sidebar-nav">
      <li class="<?= str_ends_with($cur,'dashboard') ? 'active' : '' ?>">
        <a href="<?= APP_URL ?>/admin/dashboard"><span></span> Dashboard</a></li>
      <li class="<?= str_contains($cur,'/admin/wisata') ? 'active' : '' ?>">
        <a href="<?= APP_URL ?>/admin/wisata"><span></span> Data Wisata</a></li>
      <li class="<?= str_contains($cur,'/admin/event') ? 'active' : '' ?>">
        <a href="<?= APP_URL ?>/admin/event"><span></span> Data Event</a></li>
      <li class="<?= str_contains($cur,'/admin/galeri') ? 'active' : '' ?>">
        <a href="<?= APP_URL ?>/admin/galeri"><span></span> Galeri</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="<?= APP_URL ?>/admin/logout">
        <button class="btn-logout"><span></span> Logout</button>
      </a>
    </div>
  </aside>

 
  <main class="admin-content">
    <?php if (!empty($flash)): ?>
      <div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom:28px;">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </main>
</div>

<script src="<?= APP_URL ?>/public/assets/js/main.js"></script>
</body>
</html>