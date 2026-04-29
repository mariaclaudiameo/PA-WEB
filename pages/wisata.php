<?php
$page = 'wisata';
$pageTitle = 'Wisata — Kebun Ndesa Tanah Merah';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';
$conn = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selected = null;
if ($id) {
    $res = mysqli_query($conn, "SELECT * FROM wisata WHERE id=$id AND status='aktif'");
    $selected = mysqli_fetch_assoc($res);
}
?>

<?php if($selected): ?>

<div style="padding-top:30px;">
<section class="detail-section">
  <div class="detail-layout">
    <div>
      <div class="detail-img-grid">
        <?php if(!empty($selected['foto'])): ?>
          <img src="/assets/wisata/<?= htmlspecialchars($selected['foto']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">
        <?php else: ?>
          <div class="dim-main" style="background:var(--forest);display:flex;align-items:center;justify-content:center;font-size:60px;border-radius:16px;">🌿</div>
        <?php endif; ?>
      </div>
    </div>
    <div class="detail-content">
      <a href="wisata.php" class="back-link">← Semua Wisata</a>
      <div class="rating-row">
        <span class="stars-gold">★★★★★</span>
        <span class="rating-num"><?= $selected['rating'] ?? '4.9' ?></span>
      </div>
      <h1 class="detail-title"><?= htmlspecialchars($selected['nama']) ?></h1>
      <p class="detail-desc"><?= htmlspecialchars($selected['deskripsi']) ?></p>

      <?php if(!empty($selected['fasilitas'])): ?>
      <div class="fac-title">Fasilitas</div>
      <div class="facilities">
        <?php
        $fasilitas = json_decode($selected['fasilitas'], true) ?? [];
        foreach($fasilitas as $f): ?>
        <div class="fac-chip"><span>✓</span> <?= htmlspecialchars($f) ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="ticket-box">
        <div class="ticket-heading">Harga Tiket</div>
        <div class="ticket-prices">
          <div>
            <div class="ticket-type-label">Harga</div>
            <div class="ticket-price-big">Rp <?= number_format($selected['harga_tiket'],0,',','.') ?></div>
            <div class="ticket-per">per orang</div>
          </div>
          <div>
            <div class="ticket-type-label">Jam Buka</div>
            <div class="ticket-price-big" style="font-size:18px;"><?= htmlspecialchars($selected['jam_buka']) ?></div>
          </div>
        </div>
        <a href="/pesan.php?id=<?= $selected['id'] ?>" class="btn-book" style="display:block;text-align:center;text-decoration:none;">
          Pesan Sekarang
        </a>
      </div>
    </div>
  </div>
</section>
</div>

<?php else: ?>

<div style="padding-top: 30px; background: #EAF7EC;">
    <section class="wisata-section">
  <div class="wisata-header">
    <div>
      <div class="section-eyebrow">Semua Destinasi</div>
      <h1 class="section-title">Wisata <em>Kami</em></h1>
      <p class="section-sub">Temukan pengalaman alam terbaik di Kebun Ndesa Tanah Merah</p>
    </div>
  </div>

<div class="wisata-grid" style="grid-template-columns:repeat(<?= min($jumlah, 4) ?>,1fr);">
      <?php
    $wisatas = mysqli_query($conn, "SELECT * FROM wisata WHERE status='aktif' ORDER BY id ASC");
    $wisataList = [];
    while($w = mysqli_fetch_assoc($wisatas)) $wisataList[] = $w;
    foreach($wisataList as $w): ?>
    <a href="wisata.php?id=<?= $w['id'] ?>">
      <div class="wisata-card" style="max-height:380px;">
        <?php if(!empty($w['foto'])): ?>
          <div class="card-bg" style="height:220px;background-image:url('/assets/wisata/<?= htmlspecialchars($w['foto']) ?>');background-size:cover;background-position:center;"></div>
        <?php else: ?>
          <div class="card-bg" style="height:220px;background:var(--forest);"></div>
        <?php endif; ?>
        <span class="card-icon">🌿</span>
        <div class="card-overlay">
          <span class="card-tag">Aktif</span>
          <div class="card-title"><?= htmlspecialchars($w['nama']) ?></div>
          <div class="card-price">Rp <?= number_format($w['harga_tiket'],0,',','.') ?> / orang</div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>


<section style="padding:0 60px 72px;">
  <div style="background:linear-gradient(135deg,var(--forest),#1a3a08);border-radius:8px;padding:48px;color:var(--cream);position:relative;overflow:hidden;">
    <div style="position:absolute;right:-30px;bottom:-40px;font-size:200px;opacity:.06;"></div>

    <div style="font-size:10px;letter-spacing:4px;text-transform:uppercase;color:var(--gold);margin-bottom:12px;">— Paket Bundling</div>
    <h2 style="font-family:'Playfair Display',serif;font-size:32px;margin-bottom:10px;">Hemat Lebih dengan Bundling </h2>
    <p style="font-size:14px;color:rgba(245,237,216,.7);margin-bottom:28px;max-width:520px;line-height:1.7;">Pilih kombinasi wahana yang kamu inginkan dan dapatkan harga spesial. Semakin banyak wahana, semakin hemat!</p>

    
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;" id="bundlingOptions">
      <?php foreach($wisataList as $w): ?>
      <div class="bund-opt" id="bopt-<?= $w['id'] ?>"
           onclick="toggleBundling(<?= $w['id'] ?>, <?= $w['harga_tiket'] ?>, '<?= addslashes(htmlspecialchars($w['nama'])) ?>')"
           style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:4px;padding:20px;cursor:pointer;transition:all .2s;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
          <span class="bund-check" id="bcheck-<?= $w['id'] ?>"
                style="width:20px;height:20px;border:2px solid rgba(255,255,255,.3);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:12px;"></span>
        </div>
        <h4 style="font-size:14px;font-weight:700;margin-bottom:4px;"><?= htmlspecialchars($w['nama']) ?></h4>
        <div style="font-size:12px;color:var(--gold);">Rp <?= number_format($w['harga_tiket'],0,',','.') ?>/org</div>
      </div>
      <?php endforeach; ?>
    </div>

    
    <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:4px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div>
        <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;color:rgba(245,237,216,.6);margin-bottom:4px;">Total Harga Bundling (per orang)</div>
        <div id="bundlingTotal" style="font-family:'Playfair Display',serif;font-size:28px;color:var(--gold);">Rp 0</div>
        <div id="bundlingNote" style="font-size:12px;color:rgba(245,237,216,.5);margin-top:2px;">Pilih minimal 2 wahana untuk bundling</div>
      </div>
      <button id="bundlingBtn" onclick="pesanBundling()"
        style="display:inline-flex;align-items:center;gap:10px;background:var(--gold);color:var(--forest);border:none;padding:14px 32px;font-size:13px;letter-spacing:2px;text-transform:uppercase;border-radius:2px;cursor:pointer;font-weight:700;transition:.2s;"
        onmouseover="this.style.background='#e0c060'" onmouseout="this.style.background='var(--gold)'">
        Pesan Sekarang
      </button>
    </div>

  </div>
</section>
</div>

<script>
const selectedBundling = new Map(); // id => {harga, nama}

function toggleBundling(id, harga, nama) {
  const opt   = document.getElementById('bopt-'   + id);
  const check = document.getElementById('bcheck-' + id);

  if (selectedBundling.has(id)) {
    selectedBundling.delete(id);
    opt.style.background    = 'rgba(255,255,255,.08)';
    opt.style.borderColor   = 'rgba(255,255,255,.15)';
    check.style.background  = '';
    check.style.borderColor = 'rgba(255,255,255,.3)';
    check.style.color       = '';
    check.textContent       = '';
  } else {
    selectedBundling.set(id, {harga, nama});
    opt.style.background    = 'rgba(196,149,106,.25)';
    opt.style.borderColor   = 'var(--gold)';
    check.style.background  = 'var(--gold)';
    check.style.borderColor = 'var(--gold)';
    check.style.color       = '#fff';
    check.textContent       = '✓';
  }
  updateBundlingTotal();
}

function updateBundlingTotal() {
  const n = selectedBundling.size;
  let total = 0;
  selectedBundling.forEach(v => { total += v.harga; });

  document.getElementById('bundlingTotal').textContent =
    n === 0 ? 'Rp 0' : 'Rp ' + total.toLocaleString('id-ID');
  document.getElementById('bundlingNote').textContent =
    n === 0 ? 'Pilih minimal 2 wahana untuk bundling'
            : n + ' wahana dipilih — klik Pesan Sekarang untuk lanjut';
}

function pesanBundling() {
  if (selectedBundling.size < 2) {
    alert('Pilih minimal 2 wahana untuk paket bundling!');
    return;
  }
  const ids   = [...selectedBundling.keys()].join(',');
  const names = [...selectedBundling.values()].map(v => v.nama).join(',');
  const total = [...selectedBundling.values()].reduce((s, v) => s + v.harga, 0);

  window.location.href = '/pesan_bundling.php'
    + '?ids='   + encodeURIComponent(ids)
    + '&names=' + encodeURIComponent(names)
    + '&total=' + total;
}
</script>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>