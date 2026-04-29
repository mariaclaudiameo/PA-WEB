<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/../config.php';
$conn = getDB();


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $id   = (int)($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tampilkan') {
        mysqli_query($conn, "UPDATE ulasan SET ditampilkan=1 WHERE id=$id");
        echo json_encode(['ok' => true, 'status' => 1]);
    } elseif ($aksi === 'sembunyikan') {
        mysqli_query($conn, "UPDATE ulasan SET ditampilkan=0 WHERE id=$id");
        echo json_encode(['ok' => true, 'status' => 0]);
    } elseif ($aksi === 'hapus') {
        mysqli_query($conn, "DELETE FROM ulasan WHERE id=$id");
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

$filter  = $_GET['filter'] ?? 'semua';
$where   = match($filter) {
    'pending'     => "WHERE ditampilkan=0",
    'ditampilkan' => "WHERE ditampilkan=1",
    default       => ""
};

$ulasan_list = mysqli_fetch_all(mysqli_query($conn,
    "SELECT * FROM ulasan $where ORDER BY tanggal DESC"
), MYSQLI_ASSOC);

$total       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM ulasan"))['n'];
$pending     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM ulasan WHERE ditampilkan=0"))['n'];
$ditampilkan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM ulasan WHERE ditampilkan=1"))['n'];

$pageTitle = 'Kelola Ulasan – Admin Kebun Ndesa';
include __DIR__ . '/includes/header.php';
?>

<style>
:root { --forest:#1e3a2f; --leaf:#3d7a5a; --gold:#c9a84c; --cream:#f5f0e8; --white:#fff; }
* { box-sizing:border-box; }

.ul-wrap { padding:30px 28px; max-width:1100px; margin:0 auto; }
.ul-title { font-family:'Playfair Display',serif; font-size:1.7rem; color:var(--forest); margin:0 0 24px; }

.ul-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px; }
.ul-stat  { background:var(--white); border-radius:14px; padding:18px 22px; box-shadow:0 2px 12px rgba(0,0,0,.06); }
.ul-stat .num { font-size:1.8rem; font-weight:700; color:var(--forest); }
.ul-stat .lbl { font-size:.78rem; text-transform:uppercase; letter-spacing:.07em; color:#888; margin-top:4px; }

.filter-tabs { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.filter-tabs a { padding:8px 20px; border-radius:30px; font-size:.85rem; font-weight:500; text-decoration:none; background:var(--white); color:#555; border:1.5px solid #ddd; transition:.2s; }
.filter-tabs a.active, .filter-tabs a:hover { background:var(--forest); color:var(--white); border-color:var(--forest); }

.ulasan-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }

.ul-card { background:var(--white); border-radius:14px; padding:20px 22px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #eee; transition:.2s; }
.ul-card.ditampilkan { border-left-color:var(--leaf); }
.ul-card.pending     { border-left-color:var(--gold); }

.ul-card-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; }
.ul-nama { font-weight:600; font-size:.95rem; color:var(--forest); }
.ul-asal { font-size:.78rem; color:#888; margin-top:2px; }
.ul-stars { color:var(--gold); font-size:.95rem; letter-spacing:1px; }

.ul-komentar { font-size:.88rem; color:#444; line-height:1.6; margin:10px 0 14px; font-style:italic; }
.ul-meta { font-size:.75rem; color:#bbb; margin-bottom:14px; }

.ul-badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:.72rem; font-weight:600; text-transform:uppercase; }
.ul-badge.show   { background:rgba(61,122,90,.12); color:#2d6b45; }
.ul-badge.hidden { background:rgba(201,168,76,.12); color:#9a7a20; }

.ul-aksi { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }
.btn-tampil  { padding:6px 14px; background:var(--leaf); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:500; transition:.15s; }
.btn-tampil:hover { background:var(--forest); }
.btn-sembunyikan { padding:6px 14px; background:#fff8e6; color:#9a7a20; border:1px solid #e8d48a; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:500; transition:.15s; }
.btn-sembunyikan:hover { background:#f0e4a0; }
.btn-hapus   { padding:6px 14px; background:#fff0f0; color:#b83232; border:1px solid #f0c0c0; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:500; transition:.15s; }
.btn-hapus:hover { background:#ffe0e0; }

.empty-state { text-align:center; padding:60px 20px; color:#aaa; grid-column:1/-1; }

@media(max-width:600px){ .ul-stats{grid-template-columns:1fr 1fr;} .ul-wrap{padding:18px 14px;} }
</style>

<div class="ul-wrap">
  <h1 class="ul-title">Kelola Ulasan</h1>

  <div class="ul-stats">
    <div class="ul-stat"><div class="num"><?= $total ?></div><div class="lbl">Total Ulasan</div></div>
    <div class="ul-stat"><div class="num" style="color:var(--gold)"><?= $pending ?></div><div class="lbl">Menunggu Approve</div></div>
    <div class="ul-stat"><div class="num" style="color:var(--leaf)"><?= $ditampilkan ?></div><div class="lbl">Ditampilkan</div></div>
  </div>

  <div class="filter-tabs">
    <a href="?filter=semua"       class="<?= $filter==='semua'       ?'active':'' ?>">Semua</a>
    <a href="?filter=pending"     class="<?= $filter==='pending'     ?'active':'' ?>">Pending (<?= $pending ?>)</a>
    <a href="?filter=ditampilkan" class="<?= $filter==='ditampilkan' ?'active':'' ?>">Ditampilkan</a>
  </div>

  <div class="ulasan-grid">
    <?php if (empty($ulasan_list)): ?>
      <div class="empty-state">Tidak ada ulasan ditemukan.</div>
    <?php endif; ?>

    <?php foreach($ulasan_list as $u): ?>
    <div class="ul-card <?= $u['ditampilkan'] ? 'ditampilkan' : 'pending' ?>" id="card-<?= $u['id'] ?>">
      <div class="ul-card-head">
        <div>
          <div class="ul-nama"><?= htmlspecialchars($u['nama']) ?></div>
          <?php if($u['asal']): ?>
            <div class="ul-asal"><?= htmlspecialchars($u['asal']) ?></div>
          <?php endif; ?>
        </div>
        <span class="ul-badge <?= $u['ditampilkan'] ? 'show' : 'hidden' ?>" id="badge-<?= $u['id'] ?>">
          <?= $u['ditampilkan'] ? 'Tampil' : 'Pending' ?>
        </span>
      </div>

      <div class="ul-stars"><?= str_repeat('★', $u['bintang']) . str_repeat('☆', 5 - $u['bintang']) ?></div>
      <div class="ul-komentar">"<?= htmlspecialchars($u['komentar']) ?>"</div>
      <div class="ul-meta">
        <?= date('d M Y, H:i', strtotime($u['tanggal'])) ?>
        <?= $u['email'] ? ' · ' . htmlspecialchars($u['email']) : '' ?>
      </div>

      <div class="ul-aksi">
        <?php if (!$u['ditampilkan']): ?>
          <button class="btn-tampil" onclick="aksiUlasan(<?= $u['id'] ?>, 'tampilkan')">Tampilkan</button>
        <?php else: ?>
          <button class="btn-sembunyikan" onclick="aksiUlasan(<?= $u['id'] ?>, 'sembunyikan')">Sembunyikan</button>
        <?php endif; ?>
        <button class="btn-hapus" onclick="aksiUlasan(<?= $u['id'] ?>, 'hapus')">Hapus</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
function aksiUlasan(id, aksi) {
  const label = aksi === 'hapus' ? 'menghapus' : aksi === 'tampilkan' ? 'menampilkan' : 'menyembunyikan';
  if (!confirm('Yakin ' + label + ' ulasan ini?')) return;

  fetch('ulasan.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'id=' + id + '&aksi=' + aksi
  })
  .then(r => r.json())
  .then(data => {
    if (!data.ok) return;
    if (aksi === 'hapus') {
      const card = document.getElementById('card-' + id);
      card.style.transition = 'opacity .3s';
      card.style.opacity = '0';
      setTimeout(() => card.remove(), 300);
    } else {
      const card  = document.getElementById('card-' + id);
      const badge = document.getElementById('badge-' + id);
      const aksiDiv = card.querySelector('.ul-aksi');
      if (aksi === 'tampilkan') {
        card.classList.remove('pending');
        card.classList.add('ditampilkan');
        badge.className = 'ul-badge show';
        badge.textContent = 'Tampil';
        aksiDiv.innerHTML = '<button class="btn-sembunyikan" onclick="aksiUlasan(' + id + ', \'sembunyikan\')">Sembunyikan</button>'
                          + '<button class="btn-hapus" onclick="aksiUlasan(' + id + ', \'hapus\')">Hapus</button>';
      } else {
        card.classList.remove('ditampilkan');
        card.classList.add('pending');
        badge.className = 'ul-badge hidden';
        badge.textContent = 'Pending';
        aksiDiv.innerHTML = '<button class="btn-tampil" onclick="aksiUlasan(' + id + ', \'tampilkan\')">Tampilkan</button>'
                          + '<button class="btn-hapus" onclick="aksiUlasan(' + id + ', \'hapus\')">Hapus</button>';
      }
    }
  })
  .catch(() => alert('Terjadi kesalahan. Coba lagi.'));
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>