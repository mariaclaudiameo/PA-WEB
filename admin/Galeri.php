<?php
$pageTitle = 'Galeri Foto — Admin Kebun Ndesa';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

$conn = getDB();


if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $q = mysqli_query($conn, "SELECT foto FROM galeri WHERE id=$id");
    $row = mysqli_fetch_assoc($q);

    if ($row && !empty($row['foto'])) {
        $path = __DIR__ . '/../assets/galeri/' . $row['foto'];

        if (file_exists($path)) {
            unlink($path);
        }
    }

    mysqli_query($conn, "DELETE FROM galeri WHERE id=$id");

    header('Location: Galeri.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== 0) {
        header('Location: Galeri.php?error=File+tidak+valid');
        exit;
    }

    $file = $_FILES['foto'];

    
    if ($file['size'] > 2 * 1024 * 1024) {
        header('Location: Galeri.php?error=Ukuran+maksimal+2MB');
        exit;
    }

    
    if (!is_uploaded_file($file['tmp_name'])) {
        header('Location: Galeri.php?error=Upload+gagal');
        exit;
    }

    
    $imgType = @exif_imagetype($file['tmp_name']);

    $allowedTypes = [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_WEBP
    ];

    if (!in_array($imgType, $allowedTypes)) {
        header('Location: Galeri.php?error=Hanya+boleh+JPG+PNG+WEBP');
        exit;
    }

    
    if (@getimagesize($file['tmp_name']) === false) {
        header('Location: Galeri.php?error=File+bukan+gambar');
        exit;
    }

    
    $extMap = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp'
    ];

    $ext = $extMap[$imgType];

    
    $nama_file = 'galeri_' . time() . '_' . rand(1000,9999) . '.' . $ext;

    
    $folder = __DIR__ . '/../assets/galeri/';

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $dest = $folder . $nama_file;

    
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        header('Location: Galeri.php?error=Gagal+upload+gambar');
        exit;
    }

    
    mysqli_query($conn, "INSERT INTO galeri (foto) VALUES ('$nama_file')");

    header('Location: Galeri.php');
    exit;
}


$aksi = $_GET['aksi'] ?? 'list';

$galeris = mysqli_fetch_all(
    mysqli_query($conn, "SELECT * FROM galeri ORDER BY urutan ASC, id DESC"),
    MYSQLI_ASSOC
);
?>

<?php if (!empty($_GET['error'])): ?>
<div style="background:rgba(200,70,70,.08);border:1px solid rgba(200,70,70,.25);color:#c84646;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;">
⚠️ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<div class="admin-topbar">
    <div class="admin-page-title">Galeri Foto</div>

    <?php if ($aksi === 'list'): ?>
        <a href="?aksi=tambah"
           style="background:var(--forest);color:white;padding:10px 20px;border-radius:100px;text-decoration:none;font-size:13px;">
           + Tambah Foto
        </a>
    <?php endif; ?>
</div>

<?php if ($aksi === 'tambah'): ?>


<div style="margin-bottom:20px;">
    <a href="Galeri.php"
       style="font-size:13px;color:var(--text-muted);text-decoration:none;">
       ← Kembali
    </a>
</div>

<div style="background:var(--white);border-radius:16px;padding:32px;border:1px solid rgba(201,169,110,.15);max-width:560px;">

<form method="POST" enctype="multipart/form-data">

<div style="margin-bottom:20px;">
<label style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);display:block;margin-bottom:8px;">
Foto *
</label>

<input
  type="file"
  name="foto"
  id="inputFoto"
  accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
  required
  style="width:100%;padding:12px 16px;border:1px solid rgba(0,0,0,.12);border-radius:10px;font-size:14px;box-sizing:border-box;"
>
<div id="errorFoto" style="display:none;color:#c84646;font-size:12px;margin-top:6px;">⚠️ Hanya file JPG, PNG, atau WEBP yang diperbolehkan.</div>

<small style="color:var(--text-muted);font-size:11px;">
Hanya JPG, PNG, WEBP (maks 2MB)
</small>
</div>

<button type="submit"
style="background:var(--forest);color:white;border:none;padding:12px 32px;border-radius:100px;font-size:14px;cursor:pointer;">
Simpan
</button>

<script>
document.getElementById('inputFoto').addEventListener('change', function () {
  const file = this.files[0];
  const allowed = ['image/jpeg', 'image/png', 'image/webp'];
  const errEl = document.getElementById('errorFoto');

  if (file && !allowed.includes(file.type)) {
    errEl.style.display = 'block';
    this.value = ''; // reset input
  } else {
    errEl.style.display = 'none';
  }
});

document.querySelector('form').addEventListener('submit', function (e) {
  const file = document.getElementById('inputFoto').files[0];
  const allowed = ['image/jpeg', 'image/png', 'image/webp'];

  if (file && !allowed.includes(file.type)) {
    e.preventDefault();
    document.getElementById('errorFoto').style.display = 'block';
  }
});
</script>

</form>
</div>

<?php else: ?>


<?php if (empty($galeris)): ?>

<div style="background:var(--white);border-radius:16px;padding:60px;text-align:center;border:1px solid rgba(201,169,110,.15);">
<div style="font-size:48px;opacity:.3;">🖼️</div>
<p style="color:var(--text-muted);margin-top:16px;">
Belum ada foto.
</p>
</div>

<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">

<?php foreach ($galeris as $g): ?>

<div style="background:var(--white);border-radius:12px;overflow:hidden;border:1px solid rgba(201,169,110,.15);">

<?php
$fotoPath = __DIR__ . '/../assets/galeri/' . $g['foto'];

if (!empty($g['foto']) && file_exists($fotoPath)):
?>

<img src="/assets/galeri/<?= htmlspecialchars($g['foto']) ?>"
style="height:160px;width:100%;object-fit:cover;">

<?php else: ?>

<div style="height:160px;background:linear-gradient(135deg,var(--forest),#1a3a08);display:flex;align-items:center;justify-content:center;font-size:40px;color:rgba(255,255,255,.3);">
🖼️
</div>

<?php endif; ?>

<div style="padding:8px 14px 12px;border-top:1px solid rgba(0,0,0,.05);display:flex;justify-content:flex-end;">

<a href="?hapus=<?= $g['id'] ?>"
onclick="return confirm('Hapus foto ini?')"
style="font-size:12px;color:#c0392b;border:1px solid #c0392b;padding:4px 12px;border-radius:100px;text-decoration:none;">
Hapus
</a>

</div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>