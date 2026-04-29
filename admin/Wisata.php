<?php
$pageTitle = 'Data Wisata — Admin Kebun Ndesa';
require_once __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';
$conn = getDB();


if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM wisata WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: Wisata.php');
    exit;
}

$wisata = mysqli_query($conn, "SELECT * FROM wisata ORDER BY id DESC");
?>

<div class="admin-topbar">
  <div class="admin-page-title">Data Wisata</div>
  <a href="edit_wisata.php" style="background:var(--forest);color:white;padding:10px 20px;border-radius:100px;text-decoration:none;font-size:13px;">+ Tambah Wisata</a>
</div>

<div style="background:var(--white);border-radius:16px;border:1px solid rgba(201,169,110,0.15);overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="border-bottom:2px solid rgba(201,169,110,0.2);">
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Foto</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Nama</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Harga Weekday</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Harga Weekend</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Jam Buka</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Status</th>
        <th style="padding:16px 20px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while($w = mysqli_fetch_assoc($wisata)): ?>
      <tr style="border-bottom:1px solid rgba(0,0,0,0.05);">
        <td style="padding:12px 20px;">
          <?php if(!empty($w['foto'])): ?>
          <img src="/assets/wisata/<?= htmlspecialchars($w['foto']) ?>"
               style="width:60px;height:45px;object-fit:cover;border-radius:6px;">
          <?php else: ?>
          <div style="width:60px;height:45px;border-radius:6px;background:rgba(0,0,0,0.06);display:flex;align-items:center;justify-content:center;font-size:18px;">🖼️</div>
          <?php endif; ?>
        </td>
        <td style="padding:16px 20px;">
          <div style="font-size:14px;font-weight:500;color:var(--text-dark);"><?= htmlspecialchars($w['nama']) ?></div>
          <?php if(!empty($w['slug'])): ?>
          <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($w['slug']) ?></div>
          <?php endif; ?>
        </td>
        <td style="padding:16px 20px;font-size:14px;">Rp <?= number_format($w['harga_tiket'],0,',','.') ?></td>
        <td style="padding:16px 20px;font-size:14px;">
          <?= !empty($w['harga_weekend']) ? 'Rp ' . number_format($w['harga_weekend'],0,',','.') : '<span style="color:var(--text-muted);font-size:12px;">—</span>' ?>
        </td>
        <td style="padding:16px 20px;font-size:14px;"><?= htmlspecialchars($w['jam_buka']) ?></td>
        <td style="padding:16px 20px;">
          <span style="font-size:11px;padding:4px 12px;border-radius:100px;
            background:<?= $w['status']=='aktif' ? 'rgba(61,107,56,0.12)' : 'rgba(0,0,0,0.06)' ?>;
            color:<?= $w['status']=='aktif' ? '#3D6B38' : '#888' ?>;">
            <?= ucfirst($w['status']) ?>
          </span>
        </td>
        <td style="padding:16px 20px;">
          <div style="display:flex;gap:8px;">
            <a href="edit_wisata.php?id=<?= $w['id'] ?>"
               style="font-size:12px;color:var(--gold);border:1px solid var(--gold);padding:4px 12px;border-radius:100px;text-decoration:none;">Edit</a>
            <a href="Wisata.php?hapus=<?= $w['id'] ?>"
               onclick="return confirm('Hapus wisata ini?')"
               style="font-size:12px;color:#c0392b;border:1px solid #c0392b;padding:4px 12px;border-radius:100px;text-decoration:none;">Hapus</a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>