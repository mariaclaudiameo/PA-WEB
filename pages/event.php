<?php
$page = 'event';
$pageTitle = 'Event & Pengumuman — Kebun Ndesa Tanah Merah';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';
$conn = getDB();

$result = mysqli_query($conn, "SELECT * FROM event ORDER BY tanggal DESC");
$events = mysqli_fetch_all($result, MYSQLI_ASSOC);

$statusLabel = ['akan_datang'=>'Akan Datang','berlangsung'=>'Berlangsung','selesai'=>'Selesai'];
?>

<section class="event-section" style="padding-top:140px;">
  <div class="event-header">
    <div>
      <div class="section-eyebrow">Kegiatan</div>
      <h1 class="section-title">Event & <em>Pengumuman</em></h1>
      <p class="section-sub">Ikuti berbagai kegiatan seru di Kebun Ndesa Tanah Merah</p>
    </div>
    <a href="<?= SITE_URL ?>/pages/kontak.php" class="btn-primary">Daftar Kegiatan</a>
  </div>

  <div class="event-list">
    <?php if(empty($events)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:40px;">Tidak ada event tersedia saat ini.</p>
    <?php endif; ?>


    <?php foreach($events as $e): ?>
<div class="event-item">
  <?php if(!empty($e['foto'])): ?>
  <img 
    src="<?= SITE_URL ?>/assets/event/<?= htmlspecialchars($e['foto']) ?>" 
    alt="<?= htmlspecialchars($e['judul']) ?>"
    style="width:120px;height:90px;object-fit:cover;border-radius:10px;flex-shrink:0;">
  <?php endif; ?>

  <div class="event-date">
    <div class="event-day"><?= date('d', strtotime($e['tanggal'])) ?></div>
    <div class="event-month"><?= date('M', strtotime($e['tanggal'])) ?></div>
  </div>
  <div style="flex:1;">
    <div class="event-name"><?= htmlspecialchars($e['judul']) ?></div>
    <div class="event-desc"><?= htmlspecialchars($e['deskripsi']) ?></div>
    <div class="event-time">🕐 <?= htmlspecialchars($e['jam']) ?> &nbsp;📍 <?= htmlspecialchars($e['lokasi']) ?></div>
  </div>
  <div class="event-status">
    <span class="status-pill status-<?= $e['status'] ?>">
      <?= $statusLabel[$e['status']] ?? $e['status'] ?>
    </span>
  </div>
</div>
<?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>