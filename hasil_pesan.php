<?php
$page = 'pesan';
$title = 'Tiket Pesanan – Kebun Ndesa';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/header.php';
$conn = getDB();

$kode = trim($_GET['kode'] ?? '');
$pesanan = null;

if ($kode) {
    $kode_safe = mysqli_real_escape_string($conn, $kode);
    $res = mysqli_query($conn, "
        SELECT p.*, w.nama AS nama_wisata, w.harga_tiket AS harga
        FROM pesanan p
        JOIN wisata w ON p.id_wisata = w.id
        WHERE p.kode_pesan = '$kode_safe'
    ");
    $pesanan = mysqli_fetch_assoc($res);
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap');
:root { --forest:#1e3a2f; --leaf:#3d7a5a; --gold:#c9a84c; --cream:#f5f0e8; --white:#ffffff; }

.hasil-wrap {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 130px 20px 60px;
  font-family: 'DM Sans', sans-serif;
  background: #f5f0e8;
}

.tiket-card {
  width: 100%;
  max-width: 560px;
  background: var(--white);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 40px rgba(30,58,47,.13);
  margin-bottom: 40px;
}

.tiket-head { background:var(--forest); padding:30px 36px 24px; text-align:center; }
.tiket-head h2 { font-family:'Playfair Display',serif; color:var(--white); font-size:1.6rem; margin:0 0 4px; }
.tiket-head p { color:rgba(255,255,255,.6); font-size:.88rem; margin:0; }

.status-badge { display:inline-block; margin-top:12px; padding:5px 18px; border-radius:30px; font-size:.8rem; font-weight:500; letter-spacing:.06em; text-transform:uppercase; }
.status-pending   { background:rgba(201,168,76,.25); color:var(--gold); }
.status-verified  { background:rgba(61,122,90,.25);  color:#5fc98a; }
.status-cancelled { background:rgba(224,85,85,.2);   color:#e07070; }

.gold-bar { height:4px; background:linear-gradient(90deg,var(--gold),transparent); }

.tiket-body { padding:28px 36px; }

.qr-center { display:flex; flex-direction:column; align-items:center; margin:10px 0 26px; }
.qr-center img { border:3px solid var(--forest); border-radius:12px; padding:6px; background:var(--white); }
.qr-center small { margin-top:8px; font-size:.78rem; color:#999; letter-spacing:.05em; text-transform:uppercase; }

.kode-box { background:var(--cream); border-radius:10px; padding:12px 18px; text-align:center; font-family:'Courier New',monospace; font-size:1.3rem; font-weight:700; color:var(--forest); letter-spacing:.15em; margin-bottom:24px; }

.detail-list { list-style:none; padding:0; margin:0 0 24px; }
.detail-list li { display:flex; justify-content:space-between; align-items:flex-start; padding:11px 0; border-bottom:1px solid #f0ebe0; font-size:.92rem; color:#444; }
.detail-list li:last-child { border-bottom:none; }
.detail-list li .lbl { color:#888; font-weight:400; min-width:140px; }
.detail-list li .val { font-weight:500; color:var(--forest); text-align:right; }

.total-row { background:rgba(30,58,47,.05); border-radius:10px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.total-row .lbl { font-size:.85rem; color:#666; text-transform:uppercase; letter-spacing:.06em; }
.total-row .val { font-size:1.25rem; font-weight:700; color:var(--forest); }

.info-box { background:#fff8e6; border:1px dashed var(--gold); border-radius:10px; padding:13px 16px; font-size:.85rem; color:#7a6030; line-height:1.6; margin-bottom:20px; }

.btn-back { display:block; text-align:center; padding:13px; background:var(--forest); color:var(--white); border-radius:12px; text-decoration:none; font-weight:500; font-size:.95rem; transition:background .2s; }
.btn-back:hover { background:var(--leaf); }

.not-found { text-align:center; padding:60px 20px; color:#888; }

@media(max-width:520px){
  .tiket-body { padding:20px 18px; }
  .tiket-head { padding:24px 18px 18px; }
}
</style>

<div class="hasil-wrap">
  <?php if (!$pesanan): ?>
    <div class="tiket-card">
      <div class="not-found">
        <div style="font-size:3rem;margin-bottom:16px"></div>
        <h3>Pesanan tidak ditemukan</h3>
        <a href="/pages/pesan.php" style="color:var(--leaf)">← Kembali ke Form Pesan</a>
      </div>
    </div>

  <?php else: ?>
    <div class="tiket-card">
      <div class="tiket-head">
        <h2> Tiket Berhasil Dibuat!</h2>
        <p>Tunjukkan QR ini kepada petugas saat tiba</p>
        <span class="status-badge status-<?= $pesanan['status'] ?>">
          <?= ucfirst($pesanan['status']) ?>
        </span>
      </div>
      <div class="gold-bar"></div>
      <div class="tiket-body">

        <div class="qr-center">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($pesanan['kode_pesan']) ?>"
               alt="QR Code" width="180" height="180">
          <small>scan untuk verifikasi</small>
        </div>

        <div class="kode-box"><?= htmlspecialchars($pesanan['kode_pesan']) ?></div>

        <ul class="detail-list">
          <li><span class="lbl">Nama</span>             <span class="val"><?= htmlspecialchars($pesanan['nama']) ?></span></li>
          <li><span class="lbl">No. HP</span>           <span class="val"><?= htmlspecialchars($pesanan['no_hp']) ?></span></li>
          <li><span class="lbl">Wisata</span>           <span class="val"><?= htmlspecialchars($pesanan['nama_wisata']) ?></span></li>
          <li><span class="lbl">Tanggal Kunjungan</span><span class="val"><?= date('d M Y', strtotime($pesanan['tanggal_kunjungan'])) ?></span></li>
          <li><span class="lbl">Jumlah Tiket</span>     <span class="val"><?= $pesanan['jumlah_tiket'] ?> orang</span></li>
          <li><span class="lbl">Harga/tiket</span>      <span class="val">Rp <?= number_format($pesanan['harga'],0,',','.') ?></span></li>
        </ul>

        <div class="total-row">
          <span class="lbl">Total Bayar</span>
          <span class="val">Rp <?= number_format($pesanan['total_harga'],0,',','.') ?></span>
        </div>

        <div class="info-box">
          ⚠️ Simpan halaman ini atau screenshot QR code kamu. Admin akan memverifikasi pesanan sebelum kamu masuk.
        </div>

        <a href="/pages/wisata.php" class="btn-back">← Kembali ke Halaman Wisata</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>