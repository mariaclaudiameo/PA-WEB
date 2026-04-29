<?php
$pageTitle = 'Data Event — Admin Kebun Ndesa';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';
$conn = getDB();

$aksi = $_GET['aksi'] ?? 'list';


if ($aksi === 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "DELETE FROM event WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: Event.php'); exit;
}


function uploadFotoEvent($file) {
    if (empty($file['name']) || $file['error'] !== 0) return null;
    $imgType = @exif_imagetype($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
    if (!in_array($imgType, $allowed)) return false;
    $extMap = [IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png', IMAGETYPE_WEBP=>'webp'];
    $ext    = $extMap[$imgType];
    $nama   = uniqid('ev_') . '.' . $ext;
    $folder = __DIR__ . '/../assets/event/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);
    if (move_uploaded_file($file['tmp_name'], $folder . $nama)) return $nama;
    return null;
}


function makeSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}


function validasiJudul($judul) {
    $judul = trim($judul);
    if ($judul === '') return 'Judul event tidak boleh kosong.';
    if (!preg_match('/^[a-zA-Z0-9\s\.,\-\'&\(\)]+$/u', $judul))
        return "Judul hanya boleh huruf, angka, dan simbol umum (. , - ' & ( )).";
    return null;
}


function validasiDeskripsiEvent($desc) {
    $desc = trim($desc);
    if ($desc === '') return 'Deskripsi tidak boleh kosong.';
    if (!preg_match('/^[a-zA-Z0-9\s\.,\-\'&\(\)\!\?\:\;\"\+\=\/\%\#\@\*]+$/u', $desc))
        return 'Deskripsi mengandung karakter yang tidak diperbolehkan.';
    $kata = str_word_count(strip_tags($desc));
    if ($kata < 10) return 'Deskripsi minimal 10 kata (saat ini ' . $kata . ' kata).';
    if ($kata > 100) return 'Deskripsi maksimal 100 kata (saat ini ' . $kata . ' kata).';
    return null;
}


function validasiJamEvent($jam) {
    $jam = trim($jam);
    if ($jam === '') return null;
    if (!preg_match('/^\d{2}[\.:]?\d{2}\s*-\s*\d{2}[\.:]?\d{2}(\s+(WITA|WIB|WIT))?$/i', $jam))
        return 'Format jam tidak valid. Contoh: 09.00 - 17.00 atau 09:00 - 17:00';
    return null;
}


function validasiTanggal($tgl) {
    if (empty($tgl)) return 'Tanggal tidak boleh kosong.';
    $today = date('Y-m-d');
    if ($tgl < $today) return 'Tanggal tidak boleh sebelum hari ini.';
    return null;
}


function validasiLokasi($lokasi) {
    $lokasi = trim($lokasi);
    if ($lokasi === '') return null;
    if (!preg_match('/^[a-zA-Z0-9\s\.,\-\'&\(\)\/\#]+$/u', $lokasi))
        return "Lokasi hanya boleh huruf, angka, dan simbol umum (. , - ' & ( ) / #).";
    return null;
}


if ($aksi === 'tambah-proses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = trim($_POST['judul'] ?? '');
    $desc   = trim($_POST['deskripsi'] ?? '');
    $tgl    = $_POST['tanggal'] ?? '';
    $jam    = trim($_POST['jam'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $status = $_POST['status'] ?? 'akan_datang';
    $slug   = makeSlug($judul) . '-' . time();

    $errors = [];
    if ($e = validasiJudul($judul))           $errors[] = $e;
    if ($e = validasiDeskripsiEvent($desc))   $errors[] = $e;
    if ($e = validasiTanggal($tgl))           $errors[] = $e;
    if ($e = validasiJamEvent($jam))          $errors[] = $e;
    if ($e = validasiLokasi($lokasi))         $errors[] = $e;

    if (!empty($errors)) {
        header('Location: Event.php?aksi=tambah&error=' . urlencode(implode(' | ', $errors)));
        exit;
    }

    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $result = uploadFotoEvent($_FILES['foto']);
        if ($result === false) {
            header('Location: Event.php?aksi=tambah&error=Hanya+boleh+JPG%2C+PNG%2C+WEBP');
            exit;
        }
        if ($result) $foto = $result;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO event (judul,deskripsi,foto,tanggal,jam,lokasi,status,slug) VALUES (?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "ssssssss", $judul, $desc, $foto, $tgl, $jam, $lokasi, $status, $slug);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: Event.php'); exit;
}


if ($aksi === 'edit-proses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['id'];
    $judul  = trim($_POST['judul'] ?? '');
    $desc   = trim($_POST['deskripsi'] ?? '');
    $tgl    = $_POST['tanggal'] ?? '';
    $jam    = trim($_POST['jam'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $status = $_POST['status'] ?? 'akan_datang';

    $errors = [];
    if ($e = validasiJudul($judul))           $errors[] = $e;
    if ($e = validasiDeskripsiEvent($desc))   $errors[] = $e;
    if ($e = validasiTanggal($tgl))           $errors[] = $e;
    if ($e = validasiJamEvent($jam))          $errors[] = $e;
    if ($e = validasiLokasi($lokasi))         $errors[] = $e;

    if (!empty($errors)) {
        header('Location: Event.php?aksi=edit&id=' . $id . '&error=' . urlencode(implode(' | ', $errors)));
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT foto FROM event WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $foto = $lama['foto'] ?? '';

    if (!empty($_FILES['foto']['name'])) {
        $result = uploadFotoEvent($_FILES['foto']);
        if ($result === false) {
            header('Location: Event.php?aksi=edit&id=' . $id . '&error=Hanya+boleh+JPG%2C+PNG%2C+WEBP');
            exit;
        }
        if ($result) $foto = $result;
    }

    $stmt = mysqli_prepare($conn, "UPDATE event SET judul=?,deskripsi=?,foto=?,tanggal=?,jam=?,lokasi=?,status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssssi", $judul, $desc, $foto, $tgl, $jam, $lokasi, $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: Event.php'); exit;
}

$events   = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM event ORDER BY tanggal DESC"), MYSQLI_ASSOC);
$editData = null;
if ($aksi === 'edit') {
    $id       = (int)($_GET['id'] ?? 0);
    $stmt     = mysqli_prepare($conn, "SELECT * FROM event WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $editData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

$statusLabel = ['akan_datang'=>'Akan Datang','berlangsung'=>'Berlangsung','selesai'=>'Selesai'];
$statusColor = ['akan_datang'=>'#8B6845','berlangsung'=>'#3D6B38','selesai'=>'#888'];
?>

<div class="admin-topbar">
  <div class="admin-page-title">Data Event</div>
  <?php if($aksi === 'list'): ?>
  <a href="?aksi=tambah" style="background:var(--forest);color:white;padding:10px 20px;border-radius:100px;text-decoration:none;font-size:13px;">+ Tambah Event</a>
  <?php endif; ?>
</div>

<?php if (!empty($_GET['error'])): ?>
<div style="background:rgba(200,70,70,0.08);border:1px solid rgba(200,70,70,0.25);color:#c84646;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;">
  ⚠️ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<?php if($aksi === 'list'): ?>
<div style="background:var(--white);border-radius:16px;border:1px solid rgba(201,169,110,0.15);overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="border-bottom:2px solid rgba(201,169,110,0.2);">
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Judul</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Tanggal</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Lokasi</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Status</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($events)): ?>
      <tr><td colspan="5" style="padding:40px;text-align:center;color:var(--text-muted);">Belum ada data event.</td></tr>
      <?php endif; ?>
      <?php foreach($events as $ev): ?>
      <tr style="border-bottom:1px solid rgba(0,0,0,0.05);">
        <td style="padding:16px 20px;">
          <div style="font-size:14px;font-weight:500;color:var(--text-dark);"><?= htmlspecialchars($ev['judul']) ?></div>
          <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars(substr($ev['deskripsi'],0,55)) ?>...</div>
        </td>
        <td style="padding:16px 20px;font-size:13px;">
          <?= date('d M Y', strtotime($ev['tanggal'])) ?><br>
          <span style="color:var(--text-muted);font-size:11px;"><?= htmlspecialchars($ev['jam']) ?></span>
        </td>
        <td style="padding:16px 20px;font-size:13px;"><?= htmlspecialchars($ev['lokasi']) ?></td>
        <td style="padding:16px 20px;">
          <span style="font-size:11px;padding:4px 12px;border-radius:100px;background:rgba(0,0,0,0.06);color:<?= $statusColor[$ev['status']] ?? '#888' ?>;">
            <?= $statusLabel[$ev['status']] ?? $ev['status'] ?>
          </span>
        </td>
        <td style="padding:16px 20px;">
          <div style="display:flex;gap:8px;">
            <a href="?aksi=edit&id=<?= $ev['id'] ?>"
               style="font-size:12px;color:var(--gold);border:1px solid var(--gold);padding:4px 12px;border-radius:100px;text-decoration:none;">Edit</a>
            <a href="?aksi=hapus&id=<?= $ev['id'] ?>" onclick="return confirm('Hapus event ini?')"
               style="font-size:12px;color:#c0392b;border:1px solid #c0392b;padding:4px 12px;border-radius:100px;text-decoration:none;">Hapus</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif($aksi === 'tambah' || $aksi === 'edit'): ?>
<div style="margin-bottom:20px;">
  <a href="Event.php" style="font-size:13px;color:var(--text-muted);text-decoration:none;">← Kembali</a>
</div>
<div style="background:var(--white);border-radius:16px;padding:32px;border:1px solid rgba(201,169,110,0.15);max-width:820px;">
  <form method="POST" action="?aksi=<?= $aksi==='edit' ? 'edit-proses' : 'tambah-proses' ?>" enctype="multipart/form-data" id="formEvent">
    <?php if($editData): ?>
    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

      
      <div style="grid-column:1/-1;margin-bottom:4px;">
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Judul Event *</label>
        <input type="text" name="judul" id="inputJudul" required value="<?= htmlspecialchars($editData['judul'] ?? '') ?>"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
        <div id="errorJudul" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
        <small style="color:var(--text-muted);font-size:11px;">Boleh huruf, angka, dan simbol umum (. , - ' &amp; ( ))</small>
      </div>

      
      <div style="grid-column:1/-1;">
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Deskripsi</label>
        <textarea name="deskripsi" id="inputDeskripsi" rows="4"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;resize:vertical;"><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
        <div id="errorDeskripsi" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
          <small style="color:var(--text-muted);font-size:11px;">Min 10 kata, maks 100 kata. Boleh huruf, angka, dan simbol umum.</small>
          <small id="wordCount" style="color:var(--text-muted);font-size:11px;">0 kata</small>
        </div>
      </div>

      
      <div>
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Tanggal *</label>
        <input type="date" name="tanggal" id="inputTanggal" required
          min="<?= date('Y-m-d') ?>"
          value="<?= htmlspecialchars($editData['tanggal'] ?? date('Y-m-d')) ?>"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
        <div id="errorTanggal" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
        <small style="color:var(--text-muted);font-size:11px;">Tidak boleh sebelum hari ini.</small>
      </div>

      
      <div>
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Jam</label>
        <input type="text" name="jam" id="inputJam" placeholder="09.00 - 17.00 WITA" value="<?= htmlspecialchars($editData['jam'] ?? '') ?>"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
        <div id="errorJam" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
        <small style="color:var(--text-muted);font-size:11px;">Format: 09.00 - 17.00 atau 09:00 - 17:00 (tambah WITA/WIB/WIT opsional)</small>
      </div>

      
      <div>
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Lokasi</label>
        <input type="text" name="lokasi" id="inputLokasi" value="<?= htmlspecialchars($editData['lokasi'] ?? '') ?>"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
        <div id="errorLokasi" style="display:none;color:#c84646;font-size:12px;margin-top:6px;"></div>
        <small style="color:var(--text-muted);font-size:11px;">Boleh huruf, angka, dan simbol umum (. , - ' &amp; ( ) / #)</small>
      </div>

      
      <div>
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Status</label>
        <select name="status" style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
          <option value="akan_datang" <?= ($editData['status']??'')==='akan_datang'?'selected':'' ?>>Akan Datang</option>
          <option value="berlangsung" <?= ($editData['status']??'')==='berlangsung'?'selected':'' ?>>Berlangsung</option>
          <option value="selesai"     <?= ($editData['status']??'')==='selesai'    ?'selected':'' ?>>Selesai</option>
        </select>
      </div>

      
      <div style="grid-column:1/-1;">
        <label style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">Foto Event</label>
        <input type="file" id="inputFotoEvent" name="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
          style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,0.12);border-radius:10px;font-size:14px;box-sizing:border-box;">
        <div id="errorFotoEvent" style="display:none;color:#c84646;font-size:12px;margin-top:6px;">⚠️ Hanya file JPG, PNG, atau WEBP yang diperbolehkan.</div>
        <?php if(!empty($editData['foto'])): ?>
        <img src="/assets/event/<?= htmlspecialchars($editData['foto']) ?>" height="72" style="border-radius:8px;margin-top:10px;">
        <small style="display:block;color:var(--text-muted);font-size:11px;margin-top:4px;">Upload baru untuk mengganti.</small>
        <?php endif; ?>
      </div>
    </div>

    <div style="margin-top:28px;display:flex;gap:12px;">
      <button type="submit" style="background:var(--forest);color:white;border:none;padding:12px 32px;border-radius:100px;font-size:14px;cursor:pointer;">
        <?= $aksi==='edit' ? 'Update' : 'Simpan' ?>
      </button>
      <a href="Event.php" style="padding:12px 24px;border:1px solid rgba(0,0,0,0.12);border-radius:100px;font-size:14px;text-decoration:none;color:var(--text-muted);">Batal</a>
    </div>
  </form>
</div>

<script>
const today = '<?= date('Y-m-d') ?>';


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
updateWordCount();


function validasiJudulJS(val) {
  val = val.trim();
  if (val === '') return 'Judul event tidak boleh kosong.';
  if (!/^[a-zA-Z0-9\s\.,\-'&\(\)]+$/.test(val))
    return "Judul hanya boleh huruf, angka, dan simbol umum (. , - ' & ( )).";
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


function validasiTanggalJS(val) {
  if (!val) return 'Tanggal tidak boleh kosong.';
  if (val < today) return 'Tanggal tidak boleh sebelum hari ini.';
  return null;
}


function validasiJamJS(val) {
  val = val.trim();
  if (val === '') return null;
  const pola = /^\d{2}[\.:]?\d{2}\s*-\s*\d{2}[\.:]?\d{2}(\s+(WITA|WIB|WIT))?$/i;
  if (!pola.test(val))
    return 'Format jam tidak valid. Contoh: 09.00 - 17.00 atau 09:00 - 17:00';
  return null;
}


function validasiLokasiJS(val) {
  val = val.trim();
  if (val === '') return null;
  if (!/^[a-zA-Z0-9\s\.,\-'&\(\)\/\#]+$/.test(val))
    return "Lokasi hanya boleh huruf, angka, dan simbol umum (. , - ' & ( ) / #).";
  return null;
}


document.getElementById('inputFotoEvent').addEventListener('change', function () {
  const file    = this.files[0];
  const allowed = ['image/jpeg', 'image/png', 'image/webp'];
  const errEl   = document.getElementById('errorFotoEvent');
  if (file && !allowed.includes(file.type)) {
    errEl.style.display = 'block';
    this.value = '';
  } else {
    errEl.style.display = 'none';
  }
});


document.getElementById('formEvent').addEventListener('submit', function (e) {
  let valid = true;

  function cek(errFnJS, inputId, errorId) {
    const err = errFnJS(document.getElementById(inputId).value);
    const el  = document.getElementById(errorId);
    if (err) { el.textContent = '⚠️ ' + err; el.style.display = 'block'; valid = false; }
    else     { el.style.display = 'none'; }
  }

  cek(validasiJudulJS,    'inputJudul',    'errorJudul');
  cek(validasiDeskripsiJS,'inputDeskripsi','errorDeskripsi');
  cek(validasiTanggalJS,  'inputTanggal',  'errorTanggal');
  cek(validasiJamJS,      'inputJam',      'errorJam');
  cek(validasiLokasiJS,   'inputLokasi',   'errorLokasi');

  
  const fotoFile    = document.getElementById('inputFotoEvent').files[0];
  const allowedFoto = ['image/jpeg', 'image/png', 'image/webp'];
  const elFoto      = document.getElementById('errorFotoEvent');
  if (fotoFile && !allowedFoto.includes(fotoFile.type)) {
    elFoto.style.display = 'block'; valid = false;
  }

  if (!valid) e.preventDefault();
});
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>