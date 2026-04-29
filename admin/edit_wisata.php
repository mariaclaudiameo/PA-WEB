<?php
$pageTitle = 'Edit Wisata — Admin Kebun Ndesa';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';
$conn = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$w  = [];

if ($id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM wisata WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $w = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?? [];
    mysqli_stmt_close($stmt);
}

function makeSlugWisata($text) {
    $text = strtolower(trim($text ?? ''));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uploadFotoWisata($file) {
    if (empty($file['name']) || $file['error'] !== 0) return null;
    $imgType = @exif_imagetype($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
    if (!in_array($imgType, $allowed)) return false;
    $extMap = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    $ext    = $extMap[$imgType];
    $nama   = uniqid('wisata_') . '.' . $ext;
    $folder = __DIR__ . '/../assets/wisata/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);
    if (move_uploaded_file($file['tmp_name'], $folder . $nama)) return $nama;
    return null;
}


function validasiNama($nama) {
    $nama = trim($nama);
    if ($nama === '') return 'Nama wisata tidak boleh kosong.';
    if (!preg_match('/^[a-zA-Z0-9\s\.,\-\'&\(\)]+$/u', $nama)) {
        return 'Nama hanya boleh huruf, angka, dan simbol umum (. , - \' & ( )).';
    }
    return null;
}


function validasiDeskripsi($desc) {
    $desc = trim($desc);
    if ($desc === '') return 'Deskripsi tidak boleh kosong.';
    if (!preg_match('/^[a-zA-Z0-9\s\.,\-\'&\(\)\!\?\:\;\"\+\=\/\%\#\@\*]+$/u', $desc)) {
        return 'Deskripsi mengandung karakter yang tidak diperbolehkan.';
    }
    $jumlahKata = str_word_count(strip_tags($desc));
    if ($jumlahKata < 10) return 'Deskripsi minimal 10 kata (saat ini ' . $jumlahKata . ' kata).';
    if ($jumlahKata > 100) return 'Deskripsi maksimal 100 kata (saat ini ' . $jumlahKata . ' kata).';
    return null;
}


function validasiJamBuka($jam) {
    $jam = trim($jam);
    if ($jam === '') return null; // opsional
    if (!preg_match('/^\d{2}[\.:]?\d{2}\s*-\s*\d{2}[\.:]?\d{2}(\s+WITA|\s+WIB|\s+WIT)?$/i', $jam)) {
        return 'Format jam buka tidak valid. Contoh: 09.00 - 17.00 atau 09:00 - 17:00';
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)$_POST['id'];
    $nama       = trim($_POST['nama'] ?? '');
    $harga      = (int)($_POST['harga_tiket'] ?? 0);
    $harga_wknd = !empty($_POST['harga_weekend']) ? (int)$_POST['harga_weekend'] : null;
    $jam        = trim($_POST['jam_buka'] ?? '');
    $status     = ($_POST['status'] ?? '') === 'aktif' ? 'aktif' : 'nonaktif';
    $desc       = trim($_POST['deskripsi'] ?? '');
    $fasilitas  = $_POST['fasilitas'] ?? '';
    $foto       = $_POST['foto_lama'] ?? '';
    $slug       = makeSlugWisata($nama);

    
    $errors = [];
    $errNama = validasiNama($nama);
    if ($errNama) $errors[] = $errNama;

    $errDesc = validasiDeskripsi($desc);
    if ($errDesc) $errors[] = $errDesc;

    $errJam = validasiJamBuka($jam);
    if ($errJam) $errors[] = $errJam;

    if (!empty($errors)) {
        $errMsg = urlencode(implode(' | ', $errors));
        header('Location: edit_wisata.php?id=' . $id . '&error=' . $errMsg);
        exit;
    }

    
    if (!empty($_FILES['foto']['name'])) {
        $result = uploadFotoWisata($_FILES['foto']);
        if ($result === false) {
            header('Location: edit_wisata.php?id=' . $id . '&error=Hanya+boleh+JPG%2C+PNG%2C+WEBP');
            exit;
        }
        if ($result) $foto = $result;
    }

    if ($id) {
        
        $stmt = mysqli_prepare($conn, "
            UPDATE wisata 
            SET nama=?, slug=?, deskripsi=?, fasilitas=?, harga_tiket=?, harga_weekend=?, jam_buka=?, foto=?, status=? 
            WHERE id=?
        ");
        mysqli_stmt_bind_param($stmt, "ssssiisssi",
            $nama, $slug, $desc, $fasilitas, $harga, $harga_wknd, $jam, $foto, $status, $id
        );
    } else {
        
        $stmt = mysqli_prepare($conn, "
            INSERT INTO wisata (nama, slug, deskripsi, fasilitas, harga_tiket, harga_weekend, jam_buka, foto, status) 
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        mysqli_stmt_bind_param($stmt, "ssssiisss",
            $nama, $slug, $desc, $fasilitas, $harga, $harga_wknd, $jam, $foto, $status
        );
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: Wisata.php');
    exit;
} 
?>



<?php if (!empty($_GET['error'])): ?>
<div style="background:rgba(200,70,70,0.08);border:1px solid rgba(200,70,70,0.25);color:#c84646;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;">
  ⚠️ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<div class="admin-topbar">
  <div class="admin-page-title"><?= $id ? 'Edit Wisata' : 'Tambah Wisata' ?></div>
  <a href="Wisata.php" style="font-size:13px;color:var(--text-muted);text-decoration:none;">← Kembali</a>
</div>

<div style="background:var(--white);border-radius:16px;padding:32px;border:1px solid rgba(201,169,110,0.15);max-width:700px;">
  <form method="POST" enctype="multipart/form-data" id="formWisata">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($w['foto'] ?? '') ?>">

  
    <div style="margin-bottom:20px;">
      <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Nama Wisata *</label>
      <input type="text" name="nama" id="inputNama" value="<?= htmlspecialchars($w['nama'] ?? '') ?>" required
        style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
      <div id="errorNama" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
      <small style="color:var(--text-muted);font-size:11px;">Boleh huruf, angka, dan simbol umum (. , - ' &amp; ( ))</small>
    </div>

    
    <div style="margin-bottom:20px;">
      <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Deskripsi</label>
      <textarea name="deskripsi" id="inputDeskripsi" rows="4"
        style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;resize:vertical;"><?= htmlspecialchars($w['deskripsi'] ?? '') ?></textarea>
      <div id="errorDeskripsi" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
        <small style="color:var(--text-muted);font-size:11px;">Min 10 kata, maks 100 kata. Boleh huruf, angka, dan simbol umum.</small>
        <small id="wordCount" style="color:var(--text-muted);font-size:11px;">0 kata</small>
      </div>
    </div>

    
    <div style="margin-bottom:20px;">
      <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Fasilitas <span style="font-size:11px;text-transform:none;">(pisahkan dengan koma)</span></label>
      <input type="text" name="fasilitas" placeholder="Kolam Renang, Gazebo, Toilet" value="<?= htmlspecialchars(is_array($w['fasilitas'] ?? null) ? implode(', ', $w['fasilitas']) : ($w['fasilitas'] ?? '')) ?>"
        style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
    </div>

    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
      <div>
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Harga Weekday (Rp) *</label>
        <input type="number" name="harga_tiket" value="<?= $w['harga_tiket'] ?? '' ?>" required
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
      </div>
      <div>
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Harga Weekend (Rp) <span style="font-size:11px;text-transform:none;">opsional</span></label>
        <input type="number" name="harga_weekend" value="<?= $w['harga_weekend'] ?? '' ?>"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
      </div>
    </div>

    
    <div style="margin-bottom:20px;">
      <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Jam Buka</label>
      <input type="text" name="jam_buka" id="inputJam" value="<?= htmlspecialchars($w['jam_buka'] ?? '') ?>" placeholder="09.00 - 17.00 WITA"
        style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
      <div id="errorJam" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
      <small style="color:var(--text-muted);font-size:11px;">Format: 09.00 - 17.00 atau 09:00 - 17:00 (tambah WITA/WIB/WIT opsional)</small>
    </div>

    
    <div style="margin-bottom:20px;">
      <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Foto</label>
      <?php if(!empty($w['foto'])): ?>
        <img src="/assets/wisata/<?= htmlspecialchars($w['foto']) ?>" style="height:120px;object-fit:cover;border-radius:8px;margin-bottom:10px;display:block;">
        <small style="color:var(--text-muted);font-size:11px;display:block;margin-bottom:8px;">Upload baru untuk mengganti.</small>
      <?php endif; ?>
      <input type="file" id="inputFotoWisata" name="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
      <div id="errorFotoWisata" style="display:none;color:#c84646;font-size:12px;margin-top:6px;">⚠️ Hanya file JPG, PNG, atau WEBP yang diperbolehkan.</div>
      <small style="color:var(--text-muted);font-size:11px;">Hanya JPG, PNG, WEBP (maks 2MB)</small>
    </div>

    
    <div style="margin-bottom:28px;">
      <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Status</label>
      <select name="status" style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
        <option value="aktif"    <?= ($w['status'] ?? '') == 'aktif'    ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= ($w['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
      </select>
    </div>

    <button type="submit" style="background:var(--forest);color:white;border:none;padding:12px 32px;border-radius:100px;font-size:14px;cursor:pointer;">
      Simpan
    </button>
  </form>
</div>

<script>

function hitungKata(teks) {
  return teks.trim() === '' ? 0 : teks.trim().split(/\s+/).length;
}

const inputDeskripsi = document.getElementById('inputDeskripsi');
const wordCount      = document.getElementById('wordCount');

function updateWordCount() {
  const n = hitungKata(inputDeskripsi.value);
  wordCount.textContent = n + ' kata';
  wordCount.style.color = (n < 10 || n > 100) ? '#c84646' : 'var(--text-muted)';
}
inputDeskripsi.addEventListener('input', updateWordCount);
updateWordCount(); // inisialisasi saat load


function validasiNamaJS(val) {
  val = val.trim();
  if (val === '') return 'Nama wisata tidak boleh kosong.';
  if (!/^[a-zA-Z0-9\s\.,\-'&\(\)]+$/.test(val))
    return "Nama hanya boleh huruf, angka, dan simbol umum (. , - ' & ( )).";
  return null;
}


function validasiDeskripsiJS(val) {
  val = val.trim();
  if (val === '') return 'Deskripsi tidak boleh kosong.';
  if (!/^[a-zA-Z0-9\s\.,\-'&\(\)\!\?\:\;\"\+\=\/\%\#\@\*]+$/.test(val))
    return 'Deskripsi mengandung karakter yang tidak diperbolehkan.';
  const kata = hitungKata(val);
  if (kata < 10) return 'Deskripsi minimal 10 kata (saat ini ' + kata + ' kata).';
  if (kata > 100) return 'Deskripsi maksimal 100 kata (saat ini ' + kata + ' kata).';
  return null;
}


function validasiJamJS(val) {
  val = val.trim();
  if (val === '') return null; // opsional
  const pola = /^\d{2}[\.:]?\d{2}\s*-\s*\d{2}[\.:]?\d{2}(\s+(WITA|WIB|WIT))?$/i;
  if (!pola.test(val))
    return 'Format jam buka tidak valid. Contoh: 09.00 - 17.00 atau 09:00 - 17:00';
  return null;
}


document.getElementById('inputFotoWisata').addEventListener('change', function () {
  const file    = this.files[0];
  const allowed = ['image/jpeg', 'image/png', 'image/webp'];
  const errEl   = document.getElementById('errorFotoWisata');
  if (file && !allowed.includes(file.type)) {
    errEl.style.display = 'block';
    this.value = '';
  } else {
    errEl.style.display = 'none';
  }
});


document.getElementById('formWisata').addEventListener('submit', function (e) {
  let valid = true;

  
  const errNama = validasiNamaJS(document.getElementById('inputNama').value);
  const elNama  = document.getElementById('errorNama');
  if (errNama) { elNama.textContent = '⚠️ ' + errNama; elNama.style.display = 'block'; valid = false; }
  else         { elNama.style.display = 'none'; }

  
  const errDesc = validasiDeskripsiJS(inputDeskripsi.value);
  const elDesc  = document.getElementById('errorDeskripsi');
  if (errDesc) { elDesc.textContent = '⚠️ ' + errDesc; elDesc.style.display = 'block'; valid = false; }
  else         { elDesc.style.display = 'none'; }

  
  const errJam = validasiJamJS(document.getElementById('inputJam').value);
  const elJam  = document.getElementById('errorJam');
  if (errJam) { elJam.textContent = '⚠️ ' + errJam; elJam.style.display = 'block'; valid = false; }
  else        { elJam.style.display = 'none'; }

  
  const fotoFile    = document.getElementById('inputFotoWisata').files[0];
  const allowedFoto = ['image/jpeg', 'image/png', 'image/webp'];
  const elFoto      = document.getElementById('errorFotoWisata');
  if (fotoFile && !allowedFoto.includes(fotoFile.type)) {
    elFoto.style.display = 'block'; valid = false;
  }

  if (!valid) e.preventDefault();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>