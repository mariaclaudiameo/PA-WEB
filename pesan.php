<?php
ob_start();
$page      = 'pesan';
$pageTitle = 'Pesan Tiket – Kebun Ndesa Tanah Merah';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/header.php';
$conn = getDB();

$wisata_list = mysqli_query($conn, "SELECT id, nama, harga_tiket, harga_weekend FROM wisata WHERE status='aktif' ORDER BY nama ASC");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama          = trim($_POST['nama'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $no_hp         = trim($_POST['no_hp'] ?? '');
    $id_wisata     = (int)($_POST['id_wisata'] ?? 0);
    $jumlah        = max(1, (int)($_POST['jumlah_tiket'] ?? 1));
    $tgl_kunjungan = $_POST['tanggal_kunjungan'] ?? '';

    
    if (empty($nama)) {
        $error = 'Nama lengkap wajib diisi.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $nama)) {
        $error = 'Nama hanya boleh berisi huruf dan spasi, tanpa angka atau simbol.';
    } elseif (mb_strlen(trim($nama)) < 2) {
        $error = 'Nama terlalu pendek.';

    
    } elseif (empty($no_hp)) {
        $error = 'Nomor HP wajib diisi.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $error = 'Nomor HP hanya boleh berisi angka, 10–15 digit.';

    
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';

    
    } elseif ($id_wisata <= 0) {
        $error = 'Pilih destinasi wisata terlebih dahulu.';

    
    } elseif (empty($tgl_kunjungan)) {
        $error = 'Tanggal kunjungan wajib diisi.';
    } elseif (strtotime($tgl_kunjungan) < strtotime('today')) {
        $error = 'Tanggal kunjungan tidak boleh di masa lalu.';

    } else {
        $res_wisata  = mysqli_query($conn, "SELECT harga_tiket, harga_weekend FROM wisata WHERE id=$id_wisata AND status='aktif'");
        $data_wisata = mysqli_fetch_assoc($res_wisata);

        if (!$data_wisata) {
            $error = 'Wisata tidak ditemukan.';
        } else {
            $hari_ke    = (int)date('N', strtotime($tgl_kunjungan));
            $is_weekend = ($hari_ke >= 6);
            $tipe_hari  = $is_weekend ? 'weekend' : 'weekday';

            $harga_weekend = (float)$data_wisata['harga_weekend'];
            $harga_weekday = (float)$data_wisata['harga_tiket'];
            $harga = ($is_weekend && $harga_weekend > 0) ? $harga_weekend : $harga_weekday;
            $total = $harga * $jumlah;

            $kode       = 'KN-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
            $nama_safe  = mysqli_real_escape_string($conn, $nama);
            $email_safe = mysqli_real_escape_string($conn, $email);
            $hp_safe    = mysqli_real_escape_string($conn, $no_hp);
            $tgl_safe   = mysqli_real_escape_string($conn, $tgl_kunjungan);

            $sql = "INSERT INTO pesanan
                        (kode_pesan, nama, email, no_hp, id_wisata, jumlah_tiket, tanggal_kunjungan, total_harga, tipe_hari, status, created_at)
                    VALUES
                        ('$kode','$nama_safe','$email_safe','$hp_safe',$id_wisata,$jumlah,'$tgl_safe',$total,'$tipe_hari','pending',NOW())";

            if (mysqli_query($conn, $sql)) {
                header("Location: hasil_pesan.php?kode=" . urlencode($kode));
                exit;
            } else {
                $error = 'Gagal menyimpan pesanan: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<link rel="stylesheet" href="../assets/style.css">

<div class="pesan-wrap">
  <div class="pesan-card">
    <div class="pesan-header">
      <h1>Pesan Tiket</h1>
      <p>Kebun Ndesa Tanah Merah — isi form di bawah untuk memesan tiket kunjungan</p>
    </div>
    <div class="gold-bar"></div>
    <div class="pesan-body">

      <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="" id="formPesan" novalidate>

        <div class="form-group">
          <label>Nama Lengkap <span>*</span></label>
          <input type="text" name="nama" id="nama" class="form-control"
                 placeholder="Masukkan nama lengkap"
                 value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
          <div class="field-error" id="err-nama"></div>
        </div>

        <div class="row-2">
          <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" id="email" class="form-control"
                   placeholder="email@contoh.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <div class="field-error" id="err-email"></div>
          </div>
          <div class="form-group">
            <label>No. HP / WA <span>*</span></label>
            <input type="tel" name="no_hp" id="no_hp" class="form-control"
                   placeholder="08xxxxxxxxxx"
                   value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
            <div class="field-error" id="err-hp"></div>
          </div>
        </div>

        <div class="form-group">
          <label>Pilih Wisata <span>*</span></label>
          <select name="id_wisata" id="id_wisata" class="form-control" required>
            <option value="">-- Pilih Destinasi --</option>
            <?php while ($w = mysqli_fetch_assoc($wisata_list)): ?>
              <option value="<?= $w['id'] ?>"
                      data-harga-weekday="<?= (float)$w['harga_tiket'] ?>"
                      data-harga-weekend="<?= (float)$w['harga_weekend'] ?>"
                      <?= (isset($_POST['id_wisata']) && $_POST['id_wisata'] == $w['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($w['nama']) ?> — Rp <?= number_format($w['harga_tiket'], 0, ',', '.') ?>/orang
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="row-2">
          <div class="form-group">
            <label>Jumlah Tiket <span>*</span></label>
            <input type="number" name="jumlah_tiket" id="jumlah_tiket" class="form-control"
                   min="1" max="50" value="<?= (int)($_POST['jumlah_tiket'] ?? 1) ?>" required>
          </div>
          <div class="form-group">
            <label>Tanggal Kunjungan <span>*</span></label>
            <input type="date" name="tanggal_kunjungan" id="tanggal_kunjungan" class="form-control"
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($_POST['tanggal_kunjungan'] ?? '') ?>" required>
          </div>
        </div>

        <div class="harga-info" id="harga-info" style="display:none;">
          <div id="info-harga-label"></div>
          Total yang harus dibayar: <strong id="total-harga">Rp 0</strong>
        </div>

        <button type="submit" class="btn-pesan">Buat Pesanan</button>
      </form>
    </div>
  </div>
</div>

<script>
const selectWisata = document.getElementById('id_wisata');
const inputJumlah  = document.getElementById('jumlah_tiket');
const inputTgl     = document.getElementById('tanggal_kunjungan');
const infoBox      = document.getElementById('harga-info');
const totalEl      = document.getElementById('total-harga');
const hargaLabel   = document.getElementById('info-harga-label');

function isWeekend(dateStr) {
  if (!dateStr) return false;
  const day = new Date(dateStr + 'T00:00:00').getDay();
  return day === 0 || day === 6;
}

function updateTotal() {
  const opt    = selectWisata.options[selectWisata.selectedIndex];
  const jumlah = parseInt(inputJumlah.value) || 1;
  const tgl    = inputTgl.value;

  const hargaWeekday = parseFloat(opt?.dataset?.hargaWeekday || 0);
  const hargaWeekend = parseFloat(opt?.dataset?.hargaWeekend || 0);

  if (hargaWeekday <= 0) { infoBox.style.display = 'none'; return; }

  const weekend = isWeekend(tgl);
  const harga   = (weekend && hargaWeekend > 0) ? hargaWeekend : hargaWeekday;
  const total   = harga * jumlah;

  totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');

  if (weekend && hargaWeekend > 0) {
    hargaLabel.innerHTML = '🗓️ Harga <span class="badge-weekend">Weekend</span> — Rp '
      + harga.toLocaleString('id-ID') + '/orang<br>';
  } else if (tgl) {
    hargaLabel.innerHTML = '🗓️ Harga Weekday — Rp ' + harga.toLocaleString('id-ID') + '/orang<br>';
  } else {
    hargaLabel.innerHTML = '';
  }

  infoBox.style.display = 'block';
}

selectWisata.addEventListener('change', updateTotal);
inputJumlah.addEventListener('input', updateTotal);
inputTgl.addEventListener('change', updateTotal);
updateTotal();


function showErr(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.style.display = 'block';
}
function hideErr(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = '';
  el.style.display = 'none';
}
function setInvalid(el, invalid) {
  el.classList.toggle('invalid', invalid);
}


const namaInput = document.getElementById('nama');
namaInput.addEventListener('input', function () {
  const adaKarakterTerlarang = /[^\p{L}\s'\-\.]/gu.test(this.value);

  if (!adaKarakterTerlarang) {
    hideErr('err-nama');
    setInvalid(this, false);
  } else {
    this.value = this.value.replace(/[^\p{L}\s'\-\.]/gu, '');
    showErr('err-nama', 'Nama tidak boleh mengandung angka atau simbol.');
    setInvalid(this, true);
    setTimeout(() => {
      hideErr('err-nama');
      setInvalid(namaInput, false);
    }, 2000);
  }
});


const hpInput = document.getElementById('no_hp');
hpInput.addEventListener('input', function () {
  this.value = this.value.replace(/[^0-9]/g, '');
  if (this.value.length > 15) this.value = this.value.slice(0, 15);
  if (this.value.length === 0) {
    hideErr('err-hp');
    setInvalid(this, false);
  }
});
hpInput.addEventListener('blur', function () {
  if (this.value.length === 0) return;
  if (this.value.length < 10) {
    showErr('err-hp', 'Nomor HP minimal 10 digit.');
    setInvalid(this, true);
  } else {
    hideErr('err-hp');
    setInvalid(this, false);
  }
});
hpInput.addEventListener('focus', function () {
  hideErr('err-hp');
  setInvalid(this, false);
});


const emailInput = document.getElementById('email');
emailInput.addEventListener('blur', function () {
  if (!this.value) { hideErr('err-email'); setInvalid(this, false); return; }
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
  setInvalid(this, !ok);
  ok ? hideErr('err-email') : showErr('err-email', 'Format email tidak valid.');
});
emailInput.addEventListener('input', function () {
  if (!this.value) { hideErr('err-email'); setInvalid(this, false); }
});


document.getElementById('formPesan').addEventListener('submit', function (e) {
  let valid = true;

  const nama = namaInput.value.trim();
  if (!nama) {
    showErr('err-nama', 'Nama lengkap wajib diisi.');
    setInvalid(namaInput, true);
    valid = false;
  } else if (nama.length < 2) {
    showErr('err-nama', 'Nama minimal 2 karakter.');
    setInvalid(namaInput, true);
    valid = false;
  }

  if (!hpInput.value) {
    showErr('err-hp', 'Nomor HP wajib diisi.');
    setInvalid(hpInput, true);
    valid = false;
  } else if (!/^[0-9]{10,15}$/.test(hpInput.value)) {
    showErr('err-hp', 'Nomor HP hanya angka, 10–15 digit.');
    setInvalid(hpInput, true);
    valid = false;
  }

  if (emailInput.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
    showErr('err-email', 'Format email tidak valid.');
    setInvalid(emailInput, true);
    valid = false;
  }

  if (!valid) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>