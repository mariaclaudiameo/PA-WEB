<?php
$page      = 'musim';
$pageTitle = 'Musim Buah — Kebun Ndesa Tanah Merah';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';

$conn = getDB();


$musims = mysqli_fetch_all(mysqli_query($conn, "
    SELECT * FROM musim_buah 
    ORDER BY (status='panen') DESC, nama_buah ASC
"), MYSQLI_ASSOC);

$bulan_ini  = (int)date('n');
$bulanNama  = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
?>

<style>

.musim-foto-section {
  background: var(--cream-dark);
  padding: 100px 8vw;
}

.musim-foto-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 16px;
}

.musim-foto-sub {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 48px;
  font-weight: 300;
}

.musim-foto-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.musim-foto-card {
  background: var(--white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(26,51,40,.08);
  transition: transform .3s, box-shadow .3s;
}

.musim-foto-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(26,51,40,.14);
}

.musim-foto-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
  background: var(--forest-mid);
}

.musim-foto-img-placeholder {
  width: 100%;
  height: 200px;
  background: linear-gradient(145deg, var(--forest-mid), var(--forest));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 56px;
  color: rgba(255,255,255,.15);
}

.musim-foto-body { padding: 20px; }

.musim-foto-tag {
  font-size: 10px;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 6px;
}

.musim-foto-nama {
  font-family: 'Cormorant Garamond', serif;
  font-size: 22px;
  color: var(--forest);
  font-weight: 500;
  margin-bottom: 4px;
}

.musim-foto-period {
  font-size: 12px;
  color: var(--text-muted);
  font-weight: 300;
  margin-bottom: 14px;
}

.musim-foto-status {
  display: inline-block;
  padding: 3px 12px;
  border-radius: 100px;
  font-size: 10px;
  font-weight: 500;
  letter-spacing: .06em;
  text-transform: uppercase;
  margin-bottom: 16px;
}

.status-panen {
  background: rgba(61,107,56,.12);
  color: #2d6b45;
  border: 1px solid rgba(61,107,56,.25);
}

.status-tdk-panen {
  background: rgba(0,0,0,.05);
  color: var(--text-muted);
  border: 1px solid rgba(0,0,0,.1);
}


.musim-filter-bar {
  display: flex;
  gap: 10px;
  margin-bottom: 40px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 8px 20px;
  border-radius: 100px;
  border: 1.5px solid rgba(61,107,56,.25);
  background: transparent;
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 500;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--text-muted);
  cursor: pointer;
  transition: all .2s;
}

.filter-btn:hover,
.filter-btn.active {
  background: var(--forest);
  color: var(--cream);
  border-color: var(--forest);
}

@media (max-width: 1024px) {
  .musim-foto-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
  .musim-foto-grid { grid-template-columns: repeat(2, 1fr); }
  .musim-foto-section { padding: 60px 5vw; }
}

@media (max-width: 480px) {
  .musim-foto-grid { grid-template-columns: 1fr; }
}
</style>

<div style="padding-top: 30px; background: #EAF7EC;">
    <section class="musim-foto-section">

  <div class="musim-foto-header">
    <div>
      <div class="section-eyebrow">Kalender Musiman</div>
      <h1 class="section-title">Musim <em>Panen Ini</em></h1>
    </div>
  </div>

  <p class="musim-foto-sub">
    Nikmati kesegaran buah langsung dari pohon sesuai musimnya
  </p>

  
  <div class="musim-filter-bar">
    <button class="filter-btn active" data-filter="semua">Semua</button>
    <button class="filter-btn" data-filter="panen">Sedang Panen</button>
    <button class="filter-btn" data-filter="tidak">Tidak Panen</button>
  </div>

  <div class="musim-foto-grid" id="musim-grid">

    <?php if (!empty($musims)):
      foreach ($musims as $m):
        $isPanen = $m['status'] === 'panen';
    ?>
    <div class="musim-foto-card" data-status="<?= $isPanen ? 'panen' : 'tidak' ?>">

      <?php if (!empty($m['foto'])): ?>
        <img src="/assets/musim/<?= htmlspecialchars($m['foto']) ?>"
             alt="<?= htmlspecialchars($m['nama_buah']) ?>"
             class="musim-foto-img">
      <?php else: ?>
        <div class="musim-foto-img-placeholder">🍃</div>
      <?php endif; ?>

      <div class="musim-foto-body">
        <div class="musim-foto-tag">Tersedia Sekarang</div>
        <div class="musim-foto-nama"><?= htmlspecialchars($m['nama_buah']) ?></div>
        <div class="musim-foto-period">
          <?= $bulanNama[$m['bulan_mulai']] ?> – <?= $bulanNama[$m['bulan_akhir']] ?>
        </div>
        <span class="musim-foto-status <?= $isPanen ? 'status-panen' : 'status-tdk-panen' ?>">
          <?= $isPanen ? 'Sedang Panen' : 'Tidak Panen' ?>
        </span>
      </div>

    </div>
    <?php endforeach;
    else: ?>
      <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-muted);">
        Belum ada data musim buah.
      </div>
    <?php endif; ?>

  </div>
</section>
</div>

<script>

(function () {
  var btns  = document.querySelectorAll('.filter-btn');
  var cards = document.querySelectorAll('#musim-grid .musim-foto-card');

  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      btns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');

      var filter = btn.dataset.filter;
      cards.forEach(function (card) {
        if (filter === 'semua' || card.dataset.status === filter) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>