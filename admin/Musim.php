<?php
ini_set('display_errors', 0);
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_logged_in'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'session expired']);
        exit;
    }
    header("Location: login.php"); exit;
}

require_once __DIR__ . '/../config.php';
$conn = getDB();


function uploadFoto($fileKey, $oldFoto = '') {
    if (empty($_FILES[$fileKey]['name'])) return $oldFoto;

    $file     = $_FILES[$fileKey];
    $allowed  = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
    $maxSize  = 2 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Gagal mengupload file.');
    }
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran file maksimal 2MB.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed)) {
        throw new Exception('File harus berformat JPG atau PNG. PDF, Word, dan format lain tidak diizinkan.');
    }

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeReal = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeReal, $allowed)) {
        throw new Exception('Tipe file tidak valid. Hanya gambar JPG/PNG yang diizinkan.');
    }

    $namaFile = 'musim_' . uniqid() . '.' . $ext;
    $dest     = __DIR__ . '/../assets/musim/' . $namaFile;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Gagal menyimpan file.');
    }

    if ($oldFoto && file_exists(__DIR__ . '/../assets/musim/' . $oldFoto)) {
        unlink(__DIR__ . '/../assets/musim/' . $oldFoto);
    }

    return $namaFile;
}


function validasiNama($nama) {
    if (!preg_match('/^[\p{L}\p{N}\s.,\'"()\-\/&+@#%!?:;]+$/u', $nama)) {
        throw new Exception('Nama buah hanya boleh berisi huruf, angka, dan simbol umum (. , \' " - ( ) / & + @ # % ! ? : ;).');
    }
}


function validasiDeskripsi($desk) {
    if (empty($desk)) return;
    $words = preg_split('/\s+/', trim($desk), -1, PREG_SPLIT_NO_EMPTY);
    $n = count($words);
    if ($n < 10)  throw new Exception("Deskripsi minimal 10 kata (sekarang: $n kata).");
    if ($n > 100) throw new Exception("Deskripsi maksimal 100 kata (sekarang: $n kata).");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $id   = (int)($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'toggle_status' && $id > 0) {
        $cur = $conn->query("SELECT status FROM musim_buah WHERE id=$id")->fetch_assoc();
        $new = ($cur['status'] === 'panen') ? 'tidak_panen' : 'panen';
        $conn->query("UPDATE musim_buah SET status='$new', updated_at=NOW() WHERE id=$id");
    }
    header("Location: musim.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $id   = (int)($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'toggle_status') {
        $cur = $conn->query("SELECT status FROM musim_buah WHERE id=$id")->fetch_assoc();
        $new = ($cur['status'] === 'panen') ? 'tidak_panen' : 'panen';
        $conn->query("UPDATE musim_buah SET status='$new', updated_at=NOW() WHERE id=$id");
        echo json_encode(['ok' => true, 'status' => $new]);
        exit;
    }

    if ($aksi === 'tambah') {
        $namaRaw     = trim($_POST['nama_buah'] ?? '');
        $deskRaw     = trim($_POST['deskripsi'] ?? '');
        $bulan_mulai = (int)($_POST['bulan_mulai'] ?? 1);
        $bulan_akhir = (int)($_POST['bulan_akhir'] ?? 1);
        $status      = in_array($_POST['status'] ?? '', ['panen','tidak_panen']) ? $_POST['status'] : 'tidak_panen';

        if (empty($namaRaw)) { echo json_encode(['ok'=>false,'msg'=>'Nama buah wajib diisi.']); exit; }

        try {
            validasiNama($namaRaw);
            validasiDeskripsi($deskRaw);
            $foto = uploadFoto('foto');
        } catch (Exception $e) {
            echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); exit;
        }

        $nama      = $conn->real_escape_string($namaRaw);
        $deskripsi = $conn->real_escape_string($deskRaw);

        $conn->query("INSERT INTO musim_buah (nama_buah, foto, deskripsi, bulan_mulai, bulan_akhir, status, created_at, updated_at)
            VALUES ('$nama', " . ($foto ? "'$foto'" : "NULL") . ", '$deskripsi', $bulan_mulai, $bulan_akhir, '$status', NOW(), NOW())");
        $newId = $conn->insert_id;
        echo json_encode(['ok'=>true, 'id'=>$newId, 'foto'=>$foto]);
        exit;
    }

    if ($aksi === 'update') {
        $namaRaw     = trim($_POST['nama_buah'] ?? '');
        $deskRaw     = trim($_POST['deskripsi'] ?? '');
        $bulan_mulai = (int)($_POST['bulan_mulai'] ?? 1);
        $bulan_akhir = (int)($_POST['bulan_akhir'] ?? 1);
        $status      = in_array($_POST['status'] ?? '', ['panen','tidak_panen']) ? $_POST['status'] : 'tidak_panen';

        if (empty($namaRaw)) { echo json_encode(['ok'=>false,'msg'=>'Nama buah wajib diisi.']); exit; }

        $oldRow  = $conn->query("SELECT foto FROM musim_buah WHERE id=$id")->fetch_assoc();
        $oldFoto = $oldRow['foto'] ?? '';

        try {
            validasiNama($namaRaw);
            validasiDeskripsi($deskRaw);
            $foto = uploadFoto('foto', $oldFoto);
        } catch (Exception $e) {
            echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); exit;
        }

        $nama      = $conn->real_escape_string($namaRaw);
        $deskripsi = $conn->real_escape_string($deskRaw);
        $fotoSql   = $foto ? "'$foto'" : ($oldFoto ? "'$oldFoto'" : "NULL");

        $conn->query("UPDATE musim_buah SET
            nama_buah='$nama', foto=$fotoSql, deskripsi='$deskripsi',
            bulan_mulai=$bulan_mulai, bulan_akhir=$bulan_akhir,
            status='$status', updated_at=NOW()
            WHERE id=$id");
        echo json_encode(['ok'=>true, 'foto'=>$foto ?: $oldFoto]);
        exit;
    }

    if ($aksi === 'delete') {
        $oldRow = $conn->query("SELECT foto FROM musim_buah WHERE id=$id")->fetch_assoc();
        if (!empty($oldRow['foto'])) {
            $path = __DIR__ . '/../assets/musim/' . $oldRow['foto'];
            if (file_exists($path)) unlink($path);
        }
        $conn->query("DELETE FROM musim_buah WHERE id=$id");
        echo json_encode(['ok'=>true]);
        exit;
    }

    echo json_encode(['ok'=>false,'msg'=>'Aksi tidak dikenali.']);
    exit;
}


$buah_list = $conn->query(
    "SELECT * FROM musim_buah ORDER BY (status='panen') DESC, nama_buah ASC"
)->fetch_all(MYSQLI_ASSOC);

$bulan_nama  = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$bulan_penuh = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$total_panen     = count(array_filter($buah_list, fn($b) => $b['status'] === 'panen'));
$total_tdk_panen = count(array_filter($buah_list, fn($b) => $b['status'] === 'tidak_panen'));
$bulan_ini       = (int)date('n');

$pageTitle   = 'Musim Buah – Admin Kebun Ndesa';
$currentPage = 'musim';
include __DIR__ . '/includes/header.php';
?>

<style>
:root { --forest:#1e3a2f; --leaf:#3d7a5a; --gold:#c9a84c; --cream:#f5f0e8; --white:#fff; }
* { box-sizing:border-box; }

.mb-wrap { padding:30px 28px; max-width:1100px; margin:0 auto; }
.mb-title { font-family:'Cormorant Garamond',serif; font-size:1.7rem; color:var(--forest); margin:0 0 24px; }

.mb-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px; }
.mb-stat  { background:var(--white); border-radius:14px; padding:20px 22px; box-shadow:0 2px 12px rgba(0,0,0,.06); }
.mb-stat .num { font-size:2rem; font-weight:700; color:var(--forest); }
.mb-stat .lbl { font-size:.78rem; text-transform:uppercase; letter-spacing:.07em; color:#888; margin-top:4px; }

.buah-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-bottom:28px; }
.buah-card { background:var(--white); border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; transition:box-shadow .2s; }
.buah-card:hover { box-shadow:0 4px 24px rgba(0,0,0,.1); }
.buah-card-img {
  width:100%; height:160px; object-fit:cover; display:block;
  background:linear-gradient(145deg,#3d7832,#1a3215);
}
.buah-card-img-placeholder {
  width:100%; height:160px;
  background:linear-gradient(145deg,var(--forest),#1a3a08);
  display:flex; align-items:center; justify-content:center;
  font-size:40px; color:rgba(255,255,255,.15);
}
.buah-card-head { padding:14px 18px 10px; border-bottom:1px solid #f0ebe0; display:flex; align-items:center; justify-content:space-between; gap:10px; }
.buah-nama { font-weight:600; font-size:1rem; color:var(--forest); }
.buah-card-body { padding:12px 18px 16px; }
.buah-desc { font-size:.82rem; color:#666; line-height:1.55; margin-bottom:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.buah-meta { font-size:.78rem; color:#888; margin-bottom:12px; }
.buah-meta span { color:var(--forest); font-weight:500; }
.buah-aksi { display:flex; gap:6px; }
.btn-edit-buah { padding:5px 14px; background:var(--cream); color:var(--forest); border:1px solid #ddd; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:500; transition:.15s; }
.btn-edit-buah:hover { background:var(--gold); border-color:var(--gold); }
.btn-del-buah { padding:5px 12px; background:#fff0f0; color:#b83232; border:1px solid #f0c0c0; border-radius:8px; cursor:pointer; font-size:.8rem; transition:.15s; }
.btn-del-buah:hover { background:#ffe0e0; }

.toggle-wrap { display:flex; align-items:center; gap:8px; }
.toggle { position:relative; width:44px; height:24px; cursor:pointer; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:#ddd; border-radius:24px; transition:.25s; }
.toggle-slider::before { content:''; position:absolute; width:18px; height:18px; background:#fff; border-radius:50%; left:3px; top:3px; transition:.25s; box-shadow:0 1px 4px rgba(0,0,0,.2); }
.toggle input:checked + .toggle-slider { background:var(--leaf); }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
.toggle-label { font-size:.8rem; font-weight:500; }
.toggle-label.panen { color:var(--leaf); }
.toggle-label.tidak { color:#bbb; }

.tambah-card { background:var(--white); border-radius:14px; padding:24px 26px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:28px; }
.tambah-title { font-family:'Cormorant Garamond',serif; font-size:1.1rem; color:var(--forest); margin:0 0 18px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:.78rem; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--forest); margin-bottom:6px; }
.form-control { width:100%; padding:10px 14px; border:1.5px solid #ddd; border-radius:10px; font-size:.92rem; transition:.2s; box-sizing:border-box; font-family:inherit; }
.form-control:focus { outline:none; border-color:var(--leaf); box-shadow:0 0 0 3px rgba(61,122,90,.1); }
.btn-tambah { padding:11px 26px; background:var(--forest); color:#fff; border:none; border-radius:10px; cursor:pointer; font-size:.9rem; font-weight:500; transition:.2s; }
.btn-tambah:hover { background:var(--leaf); }


.foto-upload-wrap { border:2px dashed #ddd; border-radius:10px; padding:16px; text-align:center; cursor:pointer; transition:border-color .2s; position:relative; }
.foto-upload-wrap:hover { border-color:var(--leaf); }
.foto-upload-wrap input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.foto-upload-label { font-size:.85rem; color:#888; }
.foto-upload-label strong { color:var(--forest); }
.foto-upload-hint { font-size:.72rem; color:#bbb; margin-top:4px; }
.foto-preview { width:100%; max-height:140px; object-fit:cover; border-radius:8px; margin-top:10px; display:none; }
.foto-err { color:#b83232; font-size:.78rem; margin-top:6px; display:none; }

.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:none; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:var(--white); border-radius:18px; padding:32px; width:100%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,.2); max-height:90vh; overflow-y:auto; }
.modal-title { font-family:'Cormorant Garamond',serif; font-size:1.2rem; color:var(--forest); margin:0 0 20px; }
.modal-aksi { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
.btn-simpan { padding:10px 24px; background:var(--forest); color:#fff; border:none; border-radius:10px; cursor:pointer; font-size:.9rem; font-weight:500; }
.btn-simpan:hover { background:var(--leaf); }
.btn-batal-modal { padding:10px 20px; background:#f5f0e8; color:#555; border:1px solid #ddd; border-radius:10px; cursor:pointer; font-size:.9rem; }
.alert-err { background:#fff0f0; border-left:3px solid #e05555; border-radius:6px; padding:10px 14px; color:#b83232; font-size:.85rem; margin-bottom:16px; display:none; }


.desk-hint { font-size:.72rem; color:#aaa; margin-top:4px; }
.desk-hint.ok   { color:var(--leaf); }
.desk-hint.warn { color:#b83232; }

@media(max-width:600px){ .mb-stats{grid-template-columns:1fr 1fr;} .form-row{grid-template-columns:1fr;} .mb-wrap{padding:18px 14px;} }
</style>

<div class="mb-wrap">
  <h1 class="mb-title">Musim Buah</h1>

  <div class="mb-stats">
    <div class="mb-stat"><div class="num"><?= count($buah_list) ?></div><div class="lbl">Total Buah</div></div>
    <div class="mb-stat"><div class="num" style="color:var(--leaf)"><?= $total_panen ?></div><div class="lbl">Sedang Panen</div></div>
    <div class="mb-stat"><div class="num" style="color:#bbb"><?= $total_tdk_panen ?></div><div class="lbl">Tidak Panen</div></div>
  </div>

  
  <div class="tambah-card">
    <div class="tambah-title">Tambah Buah Baru</div>
    <div id="err-tambah" class="alert-err"></div>
    <div class="form-row">
      <div class="form-group">
        <label>Nama Buah *</label>
        <input type="text" id="t-nama" class="form-control" placeholder="cth: Mangga">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select id="t-status" class="form-control">
          <option value="panen">Panen</option>
          <option value="tidak_panen">Tidak Panen</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Bulan Mulai</label>
        <select id="t-mulai" class="form-control">
          <?php for($i=1;$i<=12;$i++): ?><option value="<?=$i?>"><?=$bulan_penuh[$i]?></option><?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Bulan Akhir</label>
        <select id="t-akhir" class="form-control">
          <?php for($i=1;$i<=12;$i++): ?><option value="<?=$i?>"><?=$bulan_penuh[$i]?></option><?php endfor; ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Foto Buah <span style="color:#888;font-weight:400;text-transform:none;">(JPG/PNG, maks 2MB)</span></label>
      <div class="foto-upload-wrap" onclick="document.getElementById('t-foto').click()">
        <input type="file" id="t-foto" accept=".jpg,.jpeg,.png" style="display:none" onchange="previewFoto(this,'t-preview','t-foto-err')">
        <div class="foto-upload-label">📷 <strong>Klik untuk upload foto</strong></div>
        <div class="foto-upload-hint">Hanya JPG dan PNG — PDF, Word, dan file lain tidak diterima</div>
        <img id="t-preview" class="foto-preview" alt="Preview">
        <div class="foto-err" id="t-foto-err"></div>
      </div>
    </div>
    <div class="form-group">
      <label>Deskripsi <span style="color:#888;font-weight:400;text-transform:none;">(min 10 kata, maks 100 kata)</span></label>
      <textarea id="t-desk" class="form-control" rows="2" placeholder="Deskripsi singkat buah..." oninput="updateWordCount(this,'t-desk-hint')"></textarea>
      <div class="desk-hint" id="t-desk-hint">0 kata</div>
    </div>
    <button class="btn-tambah" onclick="tambahBuah()">Tambah Buah</button>
  </div>

  
  <div class="buah-grid" id="buah-grid">
    <?php foreach($buah_list as $b):
      $isPanen     = $b['status'] === 'panen';
      $sedangMusim = ($bulan_ini >= $b['bulan_mulai'] && $bulan_ini <= $b['bulan_akhir']);
    ?>
    <div class="buah-card" id="card-<?= $b['id'] ?>">
      <?php if (!empty($b['foto'])): ?>
        <img src="/assets/musim/<?= htmlspecialchars($b['foto']) ?>"
             class="buah-card-img"
             alt="<?= htmlspecialchars($b['nama_buah']) ?>"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="buah-card-img-placeholder" style="display:none;">🌿</div>
      <?php else: ?>
        <div class="buah-card-img-placeholder">🌿</div>
      <?php endif; ?>

      <div class="buah-card-head">
        <div>
          <div class="buah-nama"><?= htmlspecialchars($b['nama_buah']) ?></div>
          <?php if($sedangMusim): ?><span style="font-size:.72rem;color:var(--gold);">📅 Bulan ini musimnya</span><?php endif; ?>
        </div>
        <div class="toggle-wrap">
          <label class="toggle">
            <input type="checkbox" <?= $isPanen ? 'checked' : '' ?> onchange="toggleStatus(<?= $b['id'] ?>, this)">
            <span class="toggle-slider"></span>
          </label>
          <span class="toggle-label <?= $isPanen ? 'panen' : 'tidak' ?>" id="lbl-<?= $b['id'] ?>">
            <?= $isPanen ? 'Panen' : 'Tdk Panen' ?>
          </span>
        </div>
      </div>

      <div class="buah-card-body">
        <div class="buah-desc"><?= htmlspecialchars($b['deskripsi'] ?: '—') ?></div>
        <div class="buah-meta">Musim: <span><?= $bulan_nama[$b['bulan_mulai']] ?> – <?= $bulan_nama[$b['bulan_akhir']] ?></span></div>
        <div class="buah-aksi">
          <button class="btn-edit-buah" onclick="bukaEdit(
            <?= $b['id'] ?>,
            '<?= addslashes(htmlspecialchars($b['nama_buah'])) ?>',
            '<?= addslashes(htmlspecialchars($b['deskripsi'] ?? '')) ?>',
            <?= $b['bulan_mulai'] ?>, <?= $b['bulan_akhir'] ?>,
            '<?= $b['status'] ?>',
            '<?= htmlspecialchars($b['foto'] ?? '') ?>'
          )">Edit</button>
          <button class="btn-del-buah" onclick="hapusBuah(<?= $b['id'] ?>)">Hapus</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>


<div class="modal-overlay" id="modal-edit">
  <div class="modal-box">
    <div class="modal-title">Edit Buah</div>
    <div id="err-edit" class="alert-err"></div>
    <input type="hidden" id="e-id">
    <div class="form-group">
      <label>Nama Buah *</label>
      <input type="text" id="e-nama" class="form-control">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Bulan Mulai</label>
        <select id="e-mulai" class="form-control">
          <?php for($i=1;$i<=12;$i++): ?><option value="<?=$i?>"><?=$bulan_penuh[$i]?></option><?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Bulan Akhir</label>
        <select id="e-akhir" class="form-control">
          <?php for($i=1;$i<=12;$i++): ?><option value="<?=$i?>"><?=$bulan_penuh[$i]?></option><?php endfor; ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Status</label>
      <select id="e-status" class="form-control">
        <option value="panen">Panen</option>
        <option value="tidak_panen">Tidak Panen</option>
      </select>
    </div>
    <div class="form-group">
      <label>Ganti Foto <span style="color:#888;font-weight:400;text-transform:none;">(kosongkan jika tidak ingin ganti)</span></label>
      <div class="foto-upload-wrap" onclick="document.getElementById('e-foto').click()">
        <input type="file" id="e-foto" accept=".jpg,.jpeg,.png" style="display:none" onchange="previewFoto(this,'e-preview','e-foto-err')">
        <div class="foto-upload-label">📷 <strong>Klik untuk ganti foto</strong></div>
        <div class="foto-upload-hint">Hanya JPG dan PNG — PDF, Word, dan file lain tidak diterima</div>
        <img id="e-preview" class="foto-preview" alt="Preview">
        <div class="foto-err" id="e-foto-err"></div>
      </div>
      <div id="e-foto-current" style="margin-top:10px;display:none;">
        <div style="font-size:.75rem;color:#888;margin-bottom:4px;">Foto saat ini:</div>
        <img id="e-foto-current-img" style="width:100%;max-height:120px;object-fit:cover;border-radius:8px;" alt="">
      </div>
    </div>
    <div class="form-group">
      <label>Deskripsi <span style="color:#888;font-weight:400;text-transform:none;">(min 10 kata, maks 100 kata)</span></label>
      <textarea id="e-desk" class="form-control" rows="3" oninput="updateWordCount(this,'e-desk-hint')"></textarea>
      <div class="desk-hint" id="e-desk-hint">0 kata</div>
    </div>
    <div class="modal-aksi">
      <button class="btn-batal-modal" onclick="tutupModal()">Batal</button>
      <button class="btn-simpan" onclick="simpanEdit()">Simpan</button>
    </div>
  </div>
</div>

<script>

function cekNama(nama) {
  var re = /^[\p{L}\p{N}\s.,'"()\-\/&+@#%!?:;]+$/u;
  if (!re.test(nama)) return 'Nama buah hanya boleh berisi huruf, angka, dan simbol umum (. , \' " - ( ) / & + @ # % ! ? : ;).';
  return '';
}


function cekDesk(desk) {
  if (!desk) return ''; // deskripsi opsional
  var words = desk.trim().split(/\s+/).filter(Boolean);
  if (words.length < 10)  return 'Deskripsi minimal 10 kata (sekarang: ' + words.length + ' kata).';
  if (words.length > 100) return 'Deskripsi maksimal 100 kata (sekarang: ' + words.length + ' kata).';
  return '';
}


function updateWordCount(textarea, hintId) {
  var hint  = document.getElementById(hintId);
  var val   = textarea.value.trim();
  var words = val ? val.split(/\s+/).filter(Boolean) : [];
  var n     = words.length;

  if (n === 0) {
    hint.textContent = '0 kata';
    hint.className   = 'desk-hint';
  } else if (n < 10) {
    hint.textContent = n + ' kata (minimal 10 kata)';
    hint.className   = 'desk-hint warn';
  } else if (n > 100) {
    hint.textContent = n + ' kata (maksimal 100 kata)';
    hint.className   = 'desk-hint warn';
  } else {
    hint.textContent = n + ' kata ✓';
    hint.className   = 'desk-hint ok';
  }
}


function previewFoto(input, previewId, errId) {
  var preview = document.getElementById(previewId);
  var errEl   = document.getElementById(errId);
  var allowed = ['jpg','jpeg','png'];

  if (!input.files || !input.files[0]) {
    preview.style.display = 'none';
    errEl.style.display   = 'none';
    return;
  }

  var file = input.files[0];
  var ext  = file.name.split('.').pop().toLowerCase();
  var maxSize = 2 * 1024 * 1024;

  if (!allowed.includes(ext)) {
    errEl.textContent   = '❌ File harus berformat JPG atau PNG. PDF, Word, dan format lain tidak diterima.';
    errEl.style.display = 'block';
    preview.style.display = 'none';
    input.value = '';
    return;
  }

  if (file.size > maxSize) {
    errEl.textContent   = '❌ Ukuran file maksimal 2MB.';
    errEl.style.display = 'block';
    preview.style.display = 'none';
    input.value = '';
    return;
  }

  errEl.style.display = 'none';
  var reader = new FileReader();
  reader.onload = function(e) {
    preview.src   = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(file);
}


function toggleStatus(id, checkbox) {
  fetch('musim.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:'id='+id+'&aksi=toggle_status'
  })
  .then(r=>r.json())
  .then(data=>{
    if (!data.ok) { checkbox.checked = !checkbox.checked; return; }
    var lbl = document.getElementById('lbl-'+id);
    lbl.textContent = data.status==='panen' ? 'Panen' : 'Tdk Panen';
    lbl.className   = 'toggle-label ' + (data.status==='panen' ? 'panen' : 'tidak');
  })
  .catch(()=>{ checkbox.checked = !checkbox.checked; });
}


function bukaEdit(id, nama, desk, mulai, akhir, status, foto) {
  document.getElementById('e-id').value     = id;
  document.getElementById('e-nama').value   = nama;
  document.getElementById('e-desk').value   = desk;
  document.getElementById('e-mulai').value  = mulai;
  document.getElementById('e-akhir').value  = akhir;
  document.getElementById('e-status').value = status;
  document.getElementById('e-foto').value   = '';
  document.getElementById('e-preview').style.display    = 'none';
  document.getElementById('e-foto-err').style.display   = 'none';
  document.getElementById('err-edit').style.display     = 'none';

  
  updateWordCount(document.getElementById('e-desk'), 'e-desk-hint');

  var currentWrap = document.getElementById('e-foto-current');
  var currentImg  = document.getElementById('e-foto-current-img');
  if (foto) {
    currentImg.src = '/assets/musim/' + foto;
    currentWrap.style.display = 'block';
  } else {
    currentWrap.style.display = 'none';
  }

  document.getElementById('modal-edit').classList.add('open');
}

function tutupModal() {
  document.getElementById('modal-edit').classList.remove('open');
}
document.getElementById('modal-edit').addEventListener('click', function(e) {
  if (e.target === this) tutupModal();
});


function simpanEdit() {
  var errEl = document.getElementById('err-edit');
  errEl.style.display = 'none';

  var fotoErr = document.getElementById('e-foto-err');
  if (fotoErr.style.display === 'block') {
    errEl.textContent   = 'Perbaiki error foto terlebih dahulu.';
    errEl.style.display = 'block'; return;
  }

  var nama = document.getElementById('e-nama').value.trim();
  if (!nama) {
    errEl.textContent   = 'Nama buah wajib diisi.';
    errEl.style.display = 'block'; return;
  }

  var errNama = cekNama(nama);
  if (errNama) {
    errEl.textContent   = errNama;
    errEl.style.display = 'block'; return;
  }

  var desk    = document.getElementById('e-desk').value.trim();
  var errDesk = cekDesk(desk);
  if (errDesk) {
    errEl.textContent   = errDesk;
    errEl.style.display = 'block'; return;
  }

  var fd = new FormData();
  fd.append('id',          document.getElementById('e-id').value);
  fd.append('aksi',        'update');
  fd.append('nama_buah',   nama);
  fd.append('deskripsi',   desk);
  fd.append('bulan_mulai', document.getElementById('e-mulai').value);
  fd.append('bulan_akhir', document.getElementById('e-akhir').value);
  fd.append('status',      document.getElementById('e-status').value);
  var fotoFile = document.getElementById('e-foto').files[0];
  if (fotoFile) fd.append('foto', fotoFile);

  fetch('musim.php', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
  .then(r=>r.json())
  .then(data=>{
    if (data.ok) { tutupModal(); location.reload(); }
    else { errEl.textContent = data.msg || 'Gagal menyimpan.'; errEl.style.display = 'block'; }
  });
}


function hapusBuah(id) {
  if (!confirm('Yakin hapus data buah ini?')) return;
  fetch('musim.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:'id='+id+'&aksi=delete'
  })
  .then(r=>r.json())
  .then(data=>{
    if (data.ok) {
      var card = document.getElementById('card-'+id);
      card.style.transition = 'opacity .3s';
      card.style.opacity = '0';
      setTimeout(function(){ card.remove(); }, 300);
    }
  });
}


function tambahBuah() {
  var errEl   = document.getElementById('err-tambah');
  var fotoErr = document.getElementById('t-foto-err');
  errEl.style.display = 'none';

  if (fotoErr.style.display === 'block') {
    errEl.textContent   = 'Perbaiki error foto terlebih dahulu.';
    errEl.style.display = 'block'; return;
  }

  var nama = document.getElementById('t-nama').value.trim();
  if (!nama) {
    errEl.textContent   = 'Nama buah wajib diisi.';
    errEl.style.display = 'block'; return;
  }

  var errNama = cekNama(nama);
  if (errNama) {
    errEl.textContent   = errNama;
    errEl.style.display = 'block'; return;
  }

  var desk    = document.getElementById('t-desk').value.trim();
  var errDesk = cekDesk(desk);
  if (errDesk) {
    errEl.textContent   = errDesk;
    errEl.style.display = 'block'; return;
  }

  var fd = new FormData();
  fd.append('aksi',        'tambah');
  fd.append('nama_buah',   nama);
  fd.append('deskripsi',   desk);
  fd.append('bulan_mulai', document.getElementById('t-mulai').value);
  fd.append('bulan_akhir', document.getElementById('t-akhir').value);
  fd.append('status',      document.getElementById('t-status').value);
  var fotoFile = document.getElementById('t-foto').files[0];
  if (fotoFile) fd.append('foto', fotoFile);

  fetch('musim.php', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
  .then(r=>r.json())
  .then(data=>{
    if (data.ok) {
      document.getElementById('t-nama').value = '';
      document.getElementById('t-desk').value = '';
      document.getElementById('t-foto').value = '';
      document.getElementById('t-preview').style.display = 'none';
      document.getElementById('t-desk-hint').textContent = '0 kata';
      document.getElementById('t-desk-hint').className   = 'desk-hint';
      location.reload();
    } else {
      errEl.textContent   = data.msg || 'Gagal menambah buah.';
      errEl.style.display = 'block';
    }
  });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>