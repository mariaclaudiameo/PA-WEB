<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$page      = 'pesan';
$pageTitle = 'Pesan Paket Bundling – Kebun Ndesa';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/header.php';
$conn = getDB();


$ids_raw   = $_GET['ids']   ?? '';
$names_raw = $_GET['names'] ?? '';
$total_url = (int)($_GET['total'] ?? 0);

$ids   = array_filter(array_map('intval', explode(',', $ids_raw)));
$names = array_filter(explode(',', $names_raw));


$error   = '';
$pesanan = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
    $no_hp     = trim(mysqli_real_escape_string($conn, $_POST['no_hp'] ?? ''));
    $email     = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $jumlah    = max(1, (int)($_POST['jumlah_orang'] ?? 1));
    $tgl       = $_POST['tanggal_kunjungan'] ?? '';
    $wahana_nm = $_POST['wahana_names'] ?? '';
    $harga_org = (int)($_POST['harga_per_orang'] ?? 0);
    $total     = $harga_org * $jumlah;

    if (empty($nama) || empty($no_hp) || empty($tgl)) {
        $error = 'Harap isi semua field yang wajib.';
    } elseif (strtotime($tgl) < strtotime('today')) {
        $error = 'Tanggal kunjungan tidak boleh di masa lalu.';
    } else {
        $kode     = 'KB-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
        $wahana_j = mysqli_real_escape_string($conn, $wahana_nm);
        $hari_ke  = (int)date('N', strtotime($tgl));
        $tipe     = ($hari_ke >= 6) ? 'weekend' : 'weekday';

        $sql = "INSERT INTO pesanan
                  (kode_pesan, nama,  email, no_hp, id_wisata, jumlah_tiket, tanggal_kunjungan, total_harga, tipe_hari, status, created_at)
                VALUES
                  ('$kode','$nama','$email','$no_hp', Null,$jumlah,'$tgl',$total,'$tipe','pending',NOW())";

        if (mysqli_query($conn, $sql)) {
            $res     = mysqli_query($conn, "SELECT * FROM pesanan WHERE kode_pesan='$kode'");
            $pesanan = mysqli_fetch_assoc($res);
            $_SESSION['bundling_wahana_' . $kode] = $wahana_nm;
        } else {
            $error = 'Gagal menyimpan: ' . mysqli_error($conn);
        }
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap');
:root { --forest:#1e3a2f; --leaf:#3d7a5a; --gold:#c9a84c; --cream:#f5f0e8; --white:#ffffff; }

.pb-wrap {
  min-height: 100vh;
  background: #EAF7EC;
  padding: 130px 20px 60px;
  font-family: 'DM Sans', sans-serif;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}
.pb-card {
  width: 100%; max-width: 600px;
  background: var(--white);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 40px rgba(30,58,47,.13);
  margin-bottom: 40px;
}
.pb-head {
  background: linear-gradient(135deg, var(--forest), #1a3a08);
  padding: 32px 36px 26px;
}
.pb-head .eyebrow { font-size:.75rem; letter-spacing:.18em; text-transform:uppercase; color:var(--gold); margin-bottom:8px; }
.pb-head h2 { font-family:'Playfair Display',serif; color:var(--white); font-size:1.6rem; margin:0 0 6px; }
.pb-head p  { color:rgba(255,255,255,.6); font-size:.88rem; margin:0; }
.gold-bar { height:4px; background:linear-gradient(90deg,var(--gold),transparent); }
.pb-body { padding:30px 36px 36px; }

.wahana-chips { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px; }
.wahana-chip {
  display:inline-flex; align-items:center; gap:6px;
  background:rgba(30,58,47,.07); border:1px solid rgba(30,58,47,.15);
  border-radius:30px; padding:5px 14px; font-size:.82rem; color:var(--forest); font-weight:500;
}
.total-box {
  background: linear-gradient(135deg, var(--forest), #1a3a08);
  border-radius:12px; padding:16px 20px;
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:26px; color:var(--white);
}
.total-box .lbl { font-size:.8rem; text-transform:uppercase; letter-spacing:.07em; color:rgba(255,255,255,.6); }
.total-box .val { font-family:'Playfair Display',serif; font-size:1.5rem; color:var(--gold); }

.form-group { margin-bottom:18px; }
.form-group label { display:block; font-size:.8rem; font-weight:500; color:var(--forest); text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
.form-group label span { color:#e05555; }
.form-control { width:100%; padding:11px 15px; border:1.5px solid #ddd; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:.95rem; color:#222; background:#fafafa; transition:.2s; box-sizing:border-box; }
.form-control:focus { outline:none; border-color:var(--leaf); box-shadow:0 0 0 3px rgba(61,122,90,.1); background:var(--white); }
.row-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

.alert-error { background:#fff0f0; border-left:4px solid #e05555; border-radius:8px; padding:12px 16px; color:#b83232; font-size:.88rem; margin-bottom:20px; }

.btn-pesan { width:100%; padding:14px; background:var(--forest); color:var(--white); font-family:'DM Sans',sans-serif; font-size:.95rem; font-weight:500; border:none; border-radius:12px; cursor:pointer; letter-spacing:.04em; transition:.2s; }
.btn-pesan:hover { background:var(--leaf); }

.status-badge { display:inline-block; margin-top:10px; padding:4px 16px; border-radius:30px; font-size:.78rem; font-weight:500; letter-spacing:.06em; text-transform:uppercase; }
.status-pending  { background:rgba(201,168,76,.25); color:var(--gold); }

.qr-center { display:flex; flex-direction:column; align-items:center; margin:10px 0 22px; }
.qr-center img { border:3px solid var(--forest); border-radius:12px; padding:6px; }
.qr-center small { margin-top:8px; font-size:.75rem; color:#999; text-transform:uppercase; letter-spacing:.05em; }

.kode-box { background:var(--cream); border-radius:10px; padding:11px 16px; text-align:center; font-family:'Courier New',monospace; font-size:1.25rem; font-weight:700; color:var(--forest); letter-spacing:.15em; margin-bottom:22px; }

.detail-list { list-style:none; padding:0; margin:0 0 20px; }
.detail-list li { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0ebe0; font-size:.9rem; }
.detail-list li:last-child { border-bottom:none; }
.detail-list li .lbl { color:#888; min-width:130px; }
.detail-list li .val { font-weight:500; color:var(--forest); text-align:right; }

.info-box { background:#fff8e6; border:1px dashed var(--gold); border-radius:10px; padding:12px 15px; font-size:.83rem; color:#7a6030; line-height:1.6; margin-bottom:18px; }

.btn-back { display:block; text-align:center; padding:12px; background:var(--forest); color:var(--white); border-radius:12px; text-decoration:none; font-weight:500; font-size:.93rem; transition:.2s; }
.btn-back:hover { background:var(--leaf); }

@media(max-width:520px){ .pb-body{padding:22px 18px 26px;} .pb-head{padding:26px 18px 20px;} .row-2{grid-template-columns:1fr;} }
</style>

<div class="pb-wrap">
  <div class="pb-card">

    <?php if ($pesanan): ?>
    
    <div class="pb-head">
      <div class="eyebrow">Paket Bundling</div>
      <h2>Tiket Berhasil Dibuat!</h2>
      <p>Tunjukkan QR ini kepada petugas saat tiba</p>
      <span class="status-badge status-pending">Pending</span>
    </div>
    <div class="gold-bar"></div>
    <div class="pb-body">
      <div class="qr-center">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($pesanan['kode_pesan']) ?>"
             width="180" height="180" alt="QR">
        <small>scan untuk verifikasi</small>
      </div>
      <div class="kode-box"><?= htmlspecialchars($pesanan['kode_pesan']) ?></div>
      <ul class="detail-list">
        <li><span class="lbl">Nama</span><span class="val"><?= htmlspecialchars($pesanan['nama']) ?></span></li>
        <li><span class="lbl">No. HP</span><span class="val"><?= htmlspecialchars($pesanan['no_hp']) ?></span></li>
        <li><span class="lbl">Wahana</span><span class="val"><?= htmlspecialchars($_SESSION['bundling_wahana_' . $pesanan['kode_pesan']] ?? '') ?></span></li>
        <li><span class="lbl">Jumlah Orang</span><span class="val"><?= $pesanan['jumlah_tiket'] ?> orang</span></li>
        <li><span class="lbl">Tanggal Kunjungan</span><span class="val"><?= date('d M Y', strtotime($pesanan['tanggal_kunjungan'])) ?></span></li>
      </ul>
      <div class="total-box">
        <span class="lbl">Total Bayar</span>
        <span class="val">Rp <?= number_format($pesanan['total_harga'],0,',','.') ?></span>
      </div>
      <div class="info-box">⚠️ Simpan halaman ini atau screenshot QR. Admin akan memverifikasi pesanan sebelum kamu masuk.</div>
      <a href="/pages/wisata.php" class="btn-back">← Kembali ke Wisata</a>
    </div>

    <?php else: ?>
    
    <div class="pb-head">
      <div class="eyebrow">Paket Bundling</div>
      <h2>Form Pemesanan</h2>
      <p>Isi data di bawah untuk konfirmasi paket bundling kamu</p>
    </div>
    <div class="gold-bar"></div>
    <div class="pb-body">

      <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div style="margin-bottom:6px;font-size:.8rem;font-weight:500;color:var(--forest);text-transform:uppercase;letter-spacing:.08em;">Wahana Dipilih</div>
      <div class="wahana-chips" style="margin-bottom:16px;">
        <?php foreach ($names as $nm): ?>
          <span class="wahana-chip">✓ <?= htmlspecialchars(trim($nm)) ?></span>
        <?php endforeach; ?>
      </div>

      <div class="total-box" style="margin-bottom:24px;">
        <div>
          <div class="lbl">Total per orang</div>
          <div class="val">Rp <?= number_format($total_url,0,',','.') ?></div>
        </div>
        <div style="font-size:.82rem;color:rgba(255,255,255,.5);text-align:right;"><?= count($names) ?> wahana</div>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="wahana_ids"      value="<?= htmlspecialchars($ids_raw) ?>">
        <input type="hidden" name="wahana_names"    value="<?= htmlspecialchars(implode(', ', $names)) ?>">
        <input type="hidden" name="harga_per_orang" value="<?= $total_url ?>">

        <div class="form-group">
          <label>Nama Lengkap <span>*</span></label>
          <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap"
                 value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
        </div>

        <div class="row-2">
          <div class="form-group">
            <label>No. HP / WA <span>*</span></label>
            <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx"
                   value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="opsional"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <div class="row-2">
          <div class="form-group">
            <label>Jumlah Orang <span>*</span></label>
            <input type="number" name="jumlah_orang" id="jumlah_orang" class="form-control"
                   min="1" max="200" value="<?= (int)($_POST['jumlah_orang'] ?? 1) ?>" required>
          </div>
          <div class="form-group">
            <label>Tanggal Kunjungan <span>*</span></label>
            <input type="date" name="tanggal_kunjungan" class="form-control"
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($_POST['tanggal_kunjungan'] ?? '') ?>" required>
          </div>
        </div>

        <div style="background:rgba(30,58,47,.05);border-radius:10px;padding:13px 16px;margin-bottom:22px;font-size:.9rem;color:var(--forest);">
          Total: <strong id="preview-total">Rp <?= number_format($total_url,0,',','.') ?></strong>
          <span style="color:#888;font-size:.8rem;"> (<?= number_format($total_url,0,',','.') ?>/org × <span id="preview-jml">1</span> orang)</span>
        </div>

        <button type="submit" class="btn-pesan">Buat Pesanan Bundling</button>
      </form>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
const hargaOrg = <?= $total_url ?>;
const jmlInput = document.getElementById('jumlah_orang');
if (jmlInput) {
  jmlInput.addEventListener('input', function() {
    const j = parseInt(this.value) || 1;
    document.getElementById('preview-total').textContent = 'Rp ' + (hargaOrg * j).toLocaleString('id-ID');
    document.getElementById('preview-jml').textContent = j;
  });
}


const namaB = document.querySelector('input[name="nama"]');
if (namaB) {
  namaB.addEventListener('input', function() {
    this.value = this.value.replace(/[^\p{L}\s'\-\.]/gu, '');
  });
}


const hpB = document.querySelector('input[name="no_hp"]');
if (hpB) {
  hpB.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 15) this.value = this.value.slice(0, 15);
  });
}


document.querySelector('form').addEventListener('submit', function(e) {
  const nama = namaB.value.trim();
  const hp   = hpB.value.trim();
  
  if (!nama || nama.length < 2) {
    alert('Nama hanya boleh huruf dan spasi, minimal 2 karakter.');
    e.preventDefault(); return;
  }
  if (!hp || !/^[0-9]{10,15}$/.test(hp)) {
    alert('Nomor HP hanya angka, 10–15 digit.');
    e.preventDefault(); return;
  }
});

</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>