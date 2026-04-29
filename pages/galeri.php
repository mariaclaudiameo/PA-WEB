<?php
$page = 'galeri';
$pageTitle = 'Galeri — Kebun Ndesa Tanah Merah';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';
$conn = getDB();

$galeris = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM galeri ORDER BY urutan ASC, id DESC"), MYSQLI_ASSOC);
?>

<div style="padding-top: 30px; background: #EAF7EC;">
      <section class="gallery-section" style="min-height:100vh;">
  <div class="section-eyebrow">Galeri Foto</div>
  <h1 class="section-title">Kebun <em>ndesa</em></h1>
  <p class="section-sub">Keindahan alam dan aktivitas seru di Kebun Ndesa Tanah Merah</p>

  <?php if(empty($galeris)): ?>
  <div style="text-align:center;padding:60px;color:var(--text-muted);">
    <div style="font-size:48px;opacity:.3;">🖼️</div>
    <p style="margin-top:16px;">Belum ada foto galeri.</p>
  </div>
  <?php else: ?>
  <div class="gallery-grid" style="grid-template-rows:280px 280px;">
  <?php foreach($galeris as $i => $g): 
    $span = '';
    if($i === 0) $span = 'grid-column:span 2;grid-row:span 2;';
    elseif($i === 3) $span = 'grid-column:span 2;';
  ?>
  <div class="gallery-item" style="<?= $span ?>background-image:url('/assets/galeri/<?= htmlspecialchars($g['foto']) ?>');background-size:cover;background-position:center;border-radius:12px;overflow:hidden;position:relative;min-height:280px;">
    <div class="gallery-overlay">
      <div style="color:white;font-size:14px;font-weight:500;padding:16px;"><?= htmlspecialchars($g['judul'] ?: '') ?></div>
      <div class="gallery-zoom">+</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
  <?php endif; ?>

</section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>