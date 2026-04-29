<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(empty($_SESSION['admin_logged_in'])){
  header('Location: login.php'); exit;
}
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Admin — Kebun Ndesa' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Jost:wght@200;300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="cursor:auto;">
<div class="admin-wrap">
  
  <aside class="admin-sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-name">Kebun Ndesa<br>Tanah Merah</div>
      <div class="sidebar-logo-sub">Admin Panel</div>
    </div>
    <ul class="sidebar-nav">
      <li class="<?= $currentPage==='dashboard' ?'active':'' ?>"><a href="dashboard.php">Dashboard</a></li>
      <li class="<?= $currentPage==='wisata'    ?'active':'' ?>"><a href="wisata.php">Data Wisata</a></li>
      <li class="<?= $currentPage==='event'     ?'active':'' ?>"><a href="event.php">Data Event</a></li>
      <li class="<?= $currentPage==='galeri'    ?'active':'' ?>"><a href="galeri.php">Galeri</a></li>
      <li class="<?= $currentPage==='musim'     ?'active':'' ?>"><a href="musim.php">Musim Buah</a></li>
      <li class="<?= $currentPage==='ulasan' ?'active':'' ?>"><a href="ulasan.php">Kelola Ulasan</a></li>
      <li class="<?= $currentPage==='pesanan'   ?'active':'' ?>"><a href="pesanan.php">Kelola Pesanan</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="logout.php"><button class="btn-logout">Logout</button></a>
    </div>
  </aside>
 
  <main class="admin-content">