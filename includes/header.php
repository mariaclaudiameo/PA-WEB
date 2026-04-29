<?php
require_once __DIR__ . '/../config.php';
$page = $page ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div id="cursor-dot"></div>
<div id="cursor-ring"></div>

<nav id="main-nav">
  <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
    Kebun Ndesa
    <small>Tanah Merah</small>
  </a>
  <ul class="nav-links">
    <li><a href="<?= SITE_URL ?>/index.php"        class="<?= $page==='beranda'?'active':'' ?>">Beranda</a></li>
    <li><a href="<?= SITE_URL ?>/pages/wisata.php" class="<?= $page==='wisata' ?'active':'' ?>">Wisata</a></li>
    <li><a href="<?= SITE_URL ?>/pages/musim.php"  class="<?= $page==='musim'  ?'active':'' ?>">Musim Buah</a></li>
    <li><a href="<?= SITE_URL ?>/pages/event.php"  class="<?= $page==='event'  ?'active':'' ?>">Event</a></li>
    <li><a href="<?= SITE_URL ?>/pages/galeri.php" class="<?= $page==='galeri' ?'active':'' ?>">Galeri</a></li>
    <li><a href="<?= SITE_URL ?>/pages/kontak.php" class="<?= $page==='kontak' ?'active':'' ?>">Kontak</a></li>
  </ul>
  <a href="<?= SITE_URL ?>/pesan.php" class="nav-cta nav-cta-desktop">Pesan</a>
  <div class="nav-burger" id="nav-burger" onclick="toggleMobileMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>


<div id="nav-mobile-menu">
  <ul>
    <li><a href="<?= SITE_URL ?>/index.php"        class="<?= $page==='beranda'?'active':'' ?>">Beranda</a></li>
    <li><a href="<?= SITE_URL ?>/pages/wisata.php" class="<?= $page==='wisata' ?'active':'' ?>">Wisata</a></li>
    <li><a href="<?= SITE_URL ?>/pages/musim.php"  class="<?= $page==='musim'  ?'active':'' ?>">Musim Buah</a></li>
    <li><a href="<?= SITE_URL ?>/pages/event.php"  class="<?= $page==='event'  ?'active':'' ?>">Event</a></li>
    <li><a href="<?= SITE_URL ?>/pages/galeri.php" class="<?= $page==='galeri' ?'active':'' ?>">Galeri</a></li>
    <li><a href="<?= SITE_URL ?>/pages/kontak.php" class="<?= $page==='kontak' ?'active':'' ?>">Kontak</a></li>
    <li><a href="<?= SITE_URL ?>/pesan.php" class="nav-cta">Pesan</a></li>
  </ul>
</div>

<style>
.nav-burger { display: none; }
.nav-cta-desktop { display: inline-block; }

#nav-mobile-menu {
  display: none;
  position: fixed;
  top: 64px; left: 0; right: 0;
  background: #fff;
  z-index: 199;
  padding: 16px 24px 24px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
#nav-mobile-menu.open { display: block; }
#nav-mobile-menu ul { list-style: none; display: flex; flex-direction: column; gap: 4px; }
#nav-mobile-menu ul li a { display: block; padding: 12px 0; font-size: 14px; color: #1B4332; border-bottom: 1px solid rgba(0,0,0,0.06); text-decoration: none; }
#nav-mobile-menu ul li:last-child a { border-bottom: none; margin-top: 12px; text-align: center; background: #1B4332; color: #fff; border-radius: 10px; padding: 12px; }

.nav-burger { flex-direction: column; gap: 5px; cursor: pointer; padding: 4px; }
.nav-burger span { display: block; width: 22px; height: 2px; background: #1B4332; border-radius: 2px; transition: .3s; }
.nav-burger.open span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.nav-burger.open span:nth-child(2) { opacity: 0; }
.nav-burger.open span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

@media (max-width: 768px) {
  .nav-burger { display: flex !important; }
  .nav-cta-desktop { display: none !important; }
}
</style>

<script>
window.addEventListener('scroll', function() {
  var nav = document.getElementById('main-nav');
  if (window.scrollY > 40) nav.classList.add('scrolled');
  else nav.classList.remove('scrolled');
}, { passive: true });


function toggleMobileMenu() {
  document.getElementById('nav-burger').classList.toggle('open');
  document.getElementById('nav-mobile-menu').classList.toggle('open');
}


document.addEventListener('click', function (e) {
  var burger = document.getElementById('nav-burger');
  var menu   = document.getElementById('nav-mobile-menu');
  if (burger && menu && !burger.contains(e.target) && !menu.contains(e.target)) {
    burger.classList.remove('open');
    menu.classList.remove('open');
  }
});
</script>