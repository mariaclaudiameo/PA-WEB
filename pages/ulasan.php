<?php
$page      = 'ulasan';
$pageTitle = 'Tulis Ulasan – Kebun Ndesa Tanah Merah';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';
$conn = getDB();

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $asal     = trim($_POST['asal'] ?? '');
    $bintang  = (int)($_POST['bintang'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');
    $ip       = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ip       = mysqli_real_escape_string($conn, $ip);

    
    if (empty($nama)) {
        $error = 'Nama wajib diisi.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $nama)) {
        $error = 'Nama hanya boleh berisi huruf dan spasi, tanpa angka atau simbol.';
    } elseif (mb_strlen($nama) < 2 || mb_strlen($nama) > 60) {
        $error = 'Nama harus antara 2–60 karakter.';

    
    } elseif ($bintang < 1 || $bintang > 5) {
        $error = 'Pilih rating bintang (1–5).';

    
    } elseif (empty($komentar)) {
        $error = 'Komentar wajib diisi.';
    } elseif (mb_strlen($komentar) < 10) {
        $error = 'Komentar terlalu pendek, minimal 10 karakter.';
    } elseif (mb_strlen($komentar) > 500) {
        $error = 'Komentar maksimal 500 karakter.';

    
    } else {
        $cek_ip = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) AS n FROM ulasan
             WHERE ip_address='$ip' AND DATE(tanggal) = CURDATE()"
        ));
        if ($cek_ip['n'] >= 3) {
            $error = 'Kamu sudah mengirim terlalu banyak ulasan hari ini. Coba lagi besok.';
        }
    }

    if (!$error) {
        $nama_safe     = mysqli_real_escape_string($conn, $nama);
        $email_safe    = mysqli_real_escape_string($conn, $email);
        $asal_safe     = mysqli_real_escape_string($conn, $asal);
        $komentar_safe = mysqli_real_escape_string($conn, $komentar);

        $sql = "INSERT INTO ulasan (nama, email, asal, bintang, komentar, ditampilkan, ip_address, tanggal)
                VALUES ('$nama_safe','$email_safe','$asal_safe',$bintang,'$komentar_safe',0,'$ip',NOW())";

        if (mysqli_query($conn, $sql)) {
            $success = true;
        } else {
            $error = 'Gagal menyimpan ulasan. Coba lagi.';
        }
    }
}
?>

<style>
.ulasan-wrap {
  min-height: 100vh;
  background: var(--cream);
  padding: 110px 20px 60px;
  font-family: 'Jost', sans-serif;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}
.ulasan-card {
  width: 100%; max-width: 620px;
  background: var(--white);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 40px rgba(26,51,40,.1);
  margin-bottom: 40px;
}
.ulasan-head {
  background: var(--forest);
  padding: 32px 40px 26px;
  position: relative; overflow: hidden;
}
.ulasan-head::before {
  content: ''; position: absolute;
  width: 200px; height: 200px; background: var(--forest-light);
  border-radius: 50%; top: -70px; right: -50px; opacity: .2;
}
.ulasan-head h1 { font-family:'Playfair Display',serif; color:var(--white); font-size:1.7rem; margin:0 0 6px; }
.ulasan-head p  { color:rgba(255,255,255,.6); font-size:.88rem; margin:0; font-weight:300; }

.gold-bar { height:4px; background:linear-gradient(90deg,var(--gold),transparent); }
.ulasan-body { padding:32px 40px 40px; }

.form-group { margin-bottom:20px; }
.form-group label { display:block; font-size:.78rem; font-weight:500; color:var(--forest); text-transform:uppercase; letter-spacing:.08em; margin-bottom:7px; }
.form-group label span { color:#e05555; }
.form-control { width:100%; padding:11px 15px; border:1.5px solid #ddd; border-radius:10px; font-family:'Jost',sans-serif; font-size:.95rem; color:#222; background:#fafafa; transition:.2s; box-sizing:border-box; }
.form-control:focus { outline:none; border-color:var(--forest-light); box-shadow:0 0 0 3px rgba(61,107,56,.1); background:var(--white); }
.form-control.invalid { border-color:#e05555 !important; }
.field-error { color:#e05555; font-size:.76rem; margin-top:5px; display:none; }

.row-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }


.star-group { display:flex; gap:6px; flex-direction:row-reverse; justify-content:flex-end; }
.star-group input { display:none; }
.star-group label {
  font-size:2rem; color:#ddd; cursor:pointer;
  transition:color .15s; line-height:1;
}
.star-group input:checked ~ label,
.star-group label:hover,
.star-group label:hover ~ label { color:var(--gold); }

.char-count { font-size:.74rem; color:#aaa; text-align:right; margin-top:4px; }

.alert-error { background:#fff0f0; border-left:4px solid #e05555; border-radius:8px; padding:12px 16px; color:#b83232; font-size:.88rem; margin-bottom:18px; }

.success-box {
  text-align:center; padding:40px 20px;
}
.success-box .icon { font-size:3rem; margin-bottom:16px; }
.success-box h3 { font-family:'Playfair Display',serif; color:var(--forest); font-size:1.4rem; margin-bottom:8px; }
.success-box p { color:#666; font-size:.9rem; line-height:1.6; margin-bottom:24px; }
.btn-back-home { display:inline-block; padding:12px 28px; background:var(--forest); color:var(--white); border-radius:10px; font-size:.88rem; font-weight:500; text-decoration:none; transition:.2s; }
.btn-back-home:hover { background:var(--forest-light); }

.btn-submit { width:100%; padding:14px; background:var(--forest); color:var(--white); font-family:'Jost',sans-serif; font-size:.95rem; font-weight:400; border:none; border-radius:12px; cursor:pointer; letter-spacing:.04em; transition:.2s; }
.btn-submit:hover { background:var(--forest-light); transform:translateY(-1px); }

@media(max-width:520px){ .ulasan-body{padding:22px 18px 28px;} .ulasan-head{padding:26px 18px 20px;} .row-2{grid-template-columns:1fr;} }
</style>

<div class="ulasan-wrap">
  <div class="ulasan-card">
    <div class="ulasan-head">
      <h1>Tulis Ulasan</h1>
      <p>Ceritakan pengalamanmu di Kebun Ndesa Tanah Merah</p>
    </div>
    <div class="gold-bar"></div>
    <div class="ulasan-body">

      <?php if ($success): ?>
        <div class="success-box">
          <div class="icon">🎉</div>
          <h3>Ulasan Terkirim!</h3>
          <p>Terima kasih atas ulasanmu. Ulasan akan ditampilkan setelah disetujui oleh admin.</p>
          <a href="/index.php" class="btn-back-home">Kembali ke Beranda</a>
        </div>

      <?php else: ?>

        <?php if ($error): ?>
          <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="formUlasan" novalidate>

          <div class="row-2">
            <div class="form-group">
              <label>Nama <span>*</span></label>
              <input type="text" name="nama" id="nama" class="form-control"
                     placeholder="Nama lengkap"
                     value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
              <div class="field-error" id="err-nama"></div>
            </div>
            <div class="form-group">
              <label>Asal Kota</label>
              <input type="text" name="asal" class="form-control"
                     placeholder="cth: Samarinda"
                     value="<?= htmlspecialchars($_POST['asal'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label>Email <span style="color:#aaa;font-weight:300;">(opsional)</span></label>
            <input type="email" name="email" class="form-control"
                   placeholder="email@contoh.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Rating <span>*</span></label>
            <div class="star-group">
              <?php for($i=5;$i>=1;$i--): ?>
              <input type="radio" name="bintang" id="star<?=$i?>" value="<?=$i?>"
                     <?= (isset($_POST['bintang']) && $_POST['bintang']==$i) ? 'checked' : '' ?>>
              <label for="star<?=$i?>" title="<?=$i?> bintang">&#9733;</label>
              <?php endfor; ?>
            </div>
          </div>

          <div class="form-group">
            <label>Komentar <span>*</span></label>
            <textarea name="komentar" id="komentar" class="form-control" rows="4"
                      placeholder="Ceritakan pengalamanmu..." maxlength="500"><?= htmlspecialchars($_POST['komentar'] ?? '') ?></textarea>
            <div class="char-count"><span id="char-num">0</span>/500</div>
            <div class="field-error" id="err-komentar"></div>
          </div>

          <button type="submit" class="btn-submit">Kirim Ulasan</button>
        </form>

      <?php endif; ?>
    </div>
  </div>
</div>

<script>

const komentarEl = document.getElementById('komentar');
const charNum    = document.getElementById('char-num');
if (komentarEl) {
  komentarEl.addEventListener('input', function() {
    charNum.textContent = this.value.length;
  });
  charNum.textContent = komentarEl.value.length;
}


const namaEl = document.getElementById('nama');
if (namaEl) {
  namaEl.addEventListener('input', function() {
    this.value = this.value.replace(/[^\p{L}\s'\-\.]/gu, '');
    const ok = /^[\p{L}\s'\-\.]{2,}$/u.test(this.value.trim());
    const err = document.getElementById('err-nama');
    this.classList.toggle('invalid', !ok && this.value.length > 0);
    if (!ok && this.value.length > 0) {
      err.textContent = 'Nama hanya boleh huruf dan spasi.';
      err.style.display = 'block';
    } else {
      err.style.display = 'none';
    }
  });
}


const form = document.getElementById('formUlasan');
if (form) {
  form.addEventListener('submit', function(e) {
    let valid = true;
    const nama = namaEl.value.trim();
    if (!nama || !/^[\p{L}\s'\-\.]{2,}$/u.test(nama)) {
      const err = document.getElementById('err-nama');
      err.textContent = 'Nama hanya boleh huruf dan spasi, minimal 2 karakter.';
      err.style.display = 'block';
      namaEl.classList.add('invalid');
      valid = false;
    }
    const bintang = form.querySelector('input[name="bintang"]:checked');
    if (!bintang) {
      alert('Pilih rating bintang terlebih dahulu!');
      valid = false;
    }
    if (!valid) e.preventDefault();
  });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>