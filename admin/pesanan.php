<?php
error_reporting(0);  
ini_set('display_errors', 0);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
$conn = getDB();


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    if (empty($_SESSION['admin_logged_in'])) {
        echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $id_pesanan = (int)$_POST['id'];
    $aksi       = $_POST['aksi'];

    if (in_array($aksi, ['verified', 'cancelled'])) {

        $stmt = $conn->prepare("UPDATE pesanan SET status=? WHERE id=?");
        $stmt->bind_param('si', $aksi, $id_pesanan);

        $ok = $stmt->execute();

        if ($ok) {
            echo json_encode([
                'ok' => true,
                'status' => $aksi
            ]);
        } else {
            echo json_encode([
                'ok' => false,
                'msg' => $stmt->error
            ]);
        }

        $stmt->close();

    } else {
        echo json_encode([
            'ok' => false,
            'msg' => 'Aksi tidak valid'
        ]);
    }

    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = (int)$_POST['id'];
    $aksi       = $_POST['aksi'];
    if (in_array($aksi, ['verified', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE pesanan SET status=? WHERE id=?");
        $stmt->bind_param('si', $aksi, $id_pesanan);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: pesanan.php");
    exit;
}


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_GET['cari'])) {
    $kode = mysqli_real_escape_string($conn, trim($_GET['cari']));
    $res  = mysqli_query($conn, "
    SELECT p.*, COALESCE(w.nama, 'Bundling') AS nama_wisata
    FROM pesanan p
    LEFT JOIN wisata w ON p.id_wisata = w.id
    WHERE p.kode_pesan = '$kode'
");
    $p = mysqli_fetch_assoc($res);
    if (!$p) {
        echo '<div class="sr-notfound">❌ Kode <strong>' . htmlspecialchars($kode) . '</strong> tidak ditemukan.</div>';
    } else {
        $statusClass = $p['status'];
        $statusLabel = ucfirst($p['status']);
        $aksiHtml = '';
        if ($p['status'] === 'pending') {
            $aksiHtml = '
              <div style="display:flex;gap:8px;margin-top:12px;">
                <button class="btn-verify" onclick="ubahStatus(' . $p['id'] . ',\'verified\')">✔ Verifikasi</button>
                <button class="btn-cancel" onclick="ubahStatus(' . $p['id'] . ',\'cancelled\')">✖ Batalkan</button>
              </div>';
        } elseif ($p['status'] === 'verified') {
            $aksiHtml = '<div style="margin-top:10px;color:#2d6b45;font-weight:500;">✔ Pesanan ini sudah diverifikasi.</div>';
        } else {
            $aksiHtml = '<div style="margin-top:10px;color:#b83232;">✖ Pesanan ini sudah dibatalkan.</div>';
        }
        echo '
        <div class="sr-found">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
            <div>
              <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#888;margin-bottom:4px;">Kode Pesanan</div>
              <div style="font-family:monospace;font-size:1.1rem;font-weight:700;color:var(--forest);">' . htmlspecialchars($p['kode_pesan']) . '</div>
            </div>
            <span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin-top:14px;font-size:.88rem;">
            <div><span style="color:#888;">Nama</span><br><strong>' . htmlspecialchars($p['nama']) . '</strong></div>
            <div><span style="color:#888;">No. HP</span><br><strong>' . htmlspecialchars($p['no_hp']) . '</strong></div>
            <div><span style="color:#888;">Wisata</span><br><strong>' . htmlspecialchars($p['nama_wisata']) . '</strong></div>
            <div><span style="color:#888;">Tanggal</span><br><strong>' . date('d M Y', strtotime($p['tanggal_kunjungan'])) . '</strong></div>
            <div><span style="color:#888;">Tiket</span><br><strong>' . $p['jumlah_tiket'] . ' orang</strong></div>
            <div><span style="color:#888;">Total</span><br><strong>Rp ' . number_format($p['total_harga'],0,',','.') . '</strong></div>
          </div>
          ' . $aksiHtml . '
        </div>';
    }
    exit;
}


$pageTitle = 'Kelola Pesanan – Admin Kebun Ndesa';
require_once __DIR__ . '/includes/header.php';

$filter = $_GET['filter'] ?? 'all';
$where  = $filter !== 'all'
    ? "WHERE p.status='" . mysqli_real_escape_string($conn, $filter) . "'"
    : "";

$pesanan_res = mysqli_query($conn, "
    SELECT p.*, COALESCE(w.nama, 'Bundling') AS nama_wisata
    FROM pesanan p
    LEFT JOIN wisata w ON p.id_wisata = w.id
    $where
    ORDER BY p.created_at DESC
");

$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT
        COUNT(*) AS total,
        SUM(status='pending')   AS pending,
        SUM(status='verified')  AS verified,
        SUM(status='cancelled') AS cancelled,
        SUM(CASE WHEN status='verified' THEN total_harga ELSE 0 END) AS pendapatan
     FROM pesanan"
));
?>

<link rel="stylesheet" href="assets/css/pesanan_admin.css">

<div class="adm-wrap">
  <h1 class="adm-title">Kelola Pesanan</h1>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="num"><?= $stats['total'] ?></div>
      <div class="lbl">Total Pesanan</div>
    </div>
    <div class="stat-card">
      <div class="num" style="color:#c9a84c"><?= $stats['pending'] ?></div>
      <div class="lbl">Pending</div>
    </div>
    <div class="stat-card">
      <div class="num" style="color:#3d7a5a"><?= $stats['verified'] ?></div>
      <div class="lbl">Terverifikasi</div>
    </div>
    <div class="stat-card">
      <div class="num" style="color:#c9a84c">Rp <?= number_format($stats['pendapatan'],0,',','.') ?></div>
      <div class="lbl">Total Pendapatan</div>
    </div>
  </div>

  <div class="scan-area">
    <div class="scan-area-top">
      <input type="text" id="kode-input" placeholder="🔍 Ketik atau scan kode pesanan (cth: KN-ABC123)" autocomplete="off">
      <button class="btn-cari" onclick="cariKode()">Cari</button>
      <button class="btn-scan" onclick="toggleKamera()" id="btn-kamera">📷 Scan QR</button>
    </div>
    <div class="qr-scanner-wrap" id="scanner-wrap">
      <video id="qr-video" playsinline></video>
      <div class="scan-hint">Arahkan kamera ke QR code tiket pengunjung</div>
      <button class="btn-stop-scan" onclick="stopKamera()">✖ Tutup Kamera</button>
    </div>
    <div class="scan-result" id="scan-result"></div>
  </div>

  <div class="filter-tabs">
    <a href="?filter=all"       class="<?= $filter==='all'       ?'active':'' ?>">Semua</a>
    <a href="?filter=pending"   class="<?= $filter==='pending'   ?'active':'' ?>">Pending</a>
    <a href="?filter=verified"  class="<?= $filter==='verified'  ?'active':'' ?>">Verified</a>
    <a href="?filter=cancelled" class="<?= $filter==='cancelled' ?'active':'' ?>">Dibatalkan</a>
  </div>

  <div class="table-card">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Kode</th><th>Nama</th><th>Wisata</th>
            <th>Tgl Kunjungan</th><th>Tiket</th><th>Total</th><th>Hari</th><th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1; $ada = false;
          while ($p = mysqli_fetch_assoc($pesanan_res)):
            $ada = true;
          ?>
          <tr id="row-<?= $p['id'] ?>">
            <td><?= $no++ ?></td>
            <td style="font-family:monospace;font-weight:700;font-size:.85rem"><?= htmlspecialchars($p['kode_pesan']) ?></td>
            <td>
              <div style="font-weight:500"><?= htmlspecialchars($p['nama']) ?></div>
              <div style="font-size:.78rem;color:#888"><?= htmlspecialchars($p['no_hp']) ?></div>
            </td>
            <td><?= htmlspecialchars($p['nama_wisata']) ?></td>
            <td><?= date('d M Y', strtotime($p['tanggal_kunjungan'])) ?></td>
            <td><?= $p['jumlah_tiket'] ?> org</td>
            <td>Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
            <td>
              <?php if (($p['tipe_hari'] ?? '') === 'weekend'): ?>
                <span style="background:rgba(201,168,76,.15);color:#9a7a20;font-size:.75rem;padding:3px 10px;border-radius:20px;font-weight:500;">Weekend</span>
              <?php else: ?>
                <span style="color:#aaa;font-size:.82rem;">Weekday</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge badge-<?= $p['status'] ?>" id="badge-<?= $p['id'] ?>">
                <?= ucfirst($p['status']) ?>
              </span>
            </td>
            <td>
              <?php if ($p['status'] === 'pending'): ?>
              <div class="btn-group">
                <button class="btn-verify" onclick="ubahStatus(<?= $p['id'] ?>, 'verified')">✔ Verifikasi</button>
                <button class="btn-cancel" onclick="ubahStatus(<?= $p['id'] ?>, 'cancelled')">✖ Batal</button>
              </div>
              <?php elseif ($p['status'] === 'verified'): ?>
                <span style="color:#3d7a5a;font-size:.85rem">✔ Verified</span>
              <?php else: ?>
                <span style="color:#ccc;font-size:.85rem">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if (!$ada): ?>
          <tr><td colspan="10"><div class="empty-state">Tidak ada pesanan ditemukan</div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
function ubahStatus(id, aksi) {
  if (!confirm('Yakin ' + (aksi === 'verified' ? 'memverifikasi' : 'membatalkan') + ' pesanan ini?')) return;
  fetch('pesanan.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: 'id=' + id + '&aksi=' + aksi
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.ok) { alert('Gagal: ' + (data.msg || 'status tidak berubah')); return; }
    var badge = document.getElementById('badge-' + id);
    if (badge) {
      badge.className = 'badge badge-' + data.status;
      badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
    }
    var td = document.querySelector('#row-' + id + ' td:last-child');
    if (td) {
      td.innerHTML = data.status === 'verified'
        ? '<span style="color:#3d7a5a;font-size:.85rem">✔ Verified</span>'
        : '<span style="color:#ccc;font-size:.85rem">—</span>';
    }
    var kode = document.getElementById('kode-input').value.trim();
    if (kode) cariKode();
  })
  .catch(function(err) {
    console.error('Fetch error:', err);
    alert('Terjadi kesalahan koneksi.');
  });
}

function cariKode(kodeOverride) {
  var kode = kodeOverride || document.getElementById('kode-input').value.trim();
  if (!kode) return;
  document.getElementById('kode-input').value = kode;
  var box = document.getElementById('scan-result');
  box.innerHTML = '<div style="color:#888;font-size:.88rem;padding:10px 0;">⏳ Mencari...</div>';
  fetch('pesanan.php?cari=' + encodeURIComponent(kode), {
    headers: {'X-Requested-With': 'XMLHttpRequest'}
  })
  .then(function(r) { return r.text(); })
  .then(function(html) { box.innerHTML = html; });
}

document.getElementById('kode-input').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') cariKode();
});

var stream = null, scanning = false, animFrame = null;

function toggleKamera() {
  if (scanning) { stopKamera(); return; }
  document.getElementById('scanner-wrap').style.display = 'block';
  document.getElementById('btn-kamera').textContent = '⏹ Stop Scan';
  startKamera();
}

function startKamera() {
  var video = document.getElementById('qr-video');
  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(function(s) {
      stream = s; scanning = true;
      video.srcObject = s;
      video.play();
      video.addEventListener('loadeddata', scanFrame);
    })
    .catch(function() {
      alert('Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan di browser.');
      stopKamera();
    });
}

function scanFrame() {
  if (!scanning) return;
  var video  = document.getElementById('qr-video');
  var canvas = document.createElement('canvas');
  canvas.width  = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
  var imageData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
  var code = jsQR(imageData.data, imageData.width, imageData.height);
  if (code && code.data) { stopKamera(); cariKode(code.data.trim()); return; }
  animFrame = requestAnimationFrame(scanFrame);
}

function stopKamera() {
  scanning = false;
  if (animFrame) cancelAnimationFrame(animFrame);
  if (stream) stream.getTracks().forEach(function(t) { t.stop(); });
  stream = null;
  document.getElementById('scanner-wrap').style.display = 'none';
  document.getElementById('qr-video').srcObject = null;
  document.getElementById('btn-kamera').textContent = '📷 Scan QR';
}
</script>

<?php require_once __DIR__ . '/../admin/includes/footer.php'; ?>