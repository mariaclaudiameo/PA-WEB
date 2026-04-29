<?php
$pageTitle = 'Dashboard — Admin Kebun Ndesa';
require_once __DIR__ . '/../config.php'; 
include __DIR__ . '/includes/header.php';
$conn = getDB();


$total_wisata  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM wisata"))['n'];
$total_event   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM event"))['n'];
$total_galeri  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM galeri"))['n'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM pesanan"))['n'];
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM pesanan WHERE status='pending'"))['n'];
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) AS n FROM pesanan WHERE status='verified'"))['n'] ?? 0;

$wisata_list = mysqli_fetch_all(mysqli_query($conn, "SELECT id, nama, harga_tiket, status FROM wisata ORDER BY id DESC LIMIT 3"), MYSQLI_ASSOC);
$event_list  = mysqli_fetch_all(mysqli_query($conn, "SELECT id, judul, tanggal, status FROM event ORDER BY tanggal DESC LIMIT 3"), MYSQLI_ASSOC);
$pesanan_pending = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, w.nama AS nama_wisata FROM pesanan p
    JOIN wisata w ON p.id_wisata = w.id
    WHERE p.status='pending' ORDER BY p.created_at DESC LIMIT 5
"), MYSQLI_ASSOC);
$statusLabel = ['akan_datang'=>'Akan Datang','berlangsung'=>'Berlangsung','selesai'=>'Selesai'];

$bulan_ini  = (int)date('n');
$musim_list = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM musim_buah ORDER BY status DESC, nama_buah ASC LIMIT 6"), MYSQLI_ASSOC);
$bulan_nama = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];


?>



<div class="admin-topbar">
  <div class="admin-page-title">Dashboard</div>
  <div style="font-size:13px;font-weight:300;color:var(--text-muted);">Selamat datang, <?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></div>
</div>


<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px;">
  <div style="background:var(--white);border-radius:16px;padding:24px 28px;border:1px solid rgba(201,169,110,0.15);">
    <div style="font-size:11px;font-weight:400;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;">Total Pesanan</div>
    <div style="font-family:'Playfair Display',serif;font-size:36px;color:var(--forest);line-height:1;"><?= $total_pesanan ?></div>
    <div style="margin-top:8px;">
      <span style="font-size:11px;padding:3px 10px;border-radius:100px;background:rgba(201,168,76,.15);color:#9a7a20;"><?= $total_pending ?> Pending</span>
    </div>
  </div>
  <div style="background:var(--white);border-radius:16px;padding:24px 28px;border:1px solid rgba(201,169,110,0.15);">
    <div style="font-size:11px;font-weight:400;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;">Total Pendapatan</div>
    <div style="font-family:'Playfair Display',serif;font-size:28px;color:var(--forest);line-height:1;">Rp <?= number_format($total_pendapatan,0,',','.') ?></div>
    <div style="margin-top:8px;font-size:11px;color:var(--text-muted);">dari pesanan terverifikasi</div>
  </div>
  <div style="background:var(--white);border-radius:16px;padding:24px 28px;border:1px solid rgba(201,169,110,0.15);">
    <div style="font-size:11px;font-weight:400;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;">Data Master</div>
    <div style="display:flex;gap:16px;margin-top:4px;">
      <div style="text-align:center;">
        <div style="font-family:'Playfair Display',serif;font-size:28px;color:var(--forest);"><?= $total_wisata ?></div>
        <div style="font-size:11px;color:var(--text-muted);">Wisata</div>
      </div>
      <div style="text-align:center;">
        <div style="font-family:'Playfair Display',serif;font-size:28px;color:var(--forest);"><?= $total_event ?></div>
        <div style="font-size:11px;color:var(--text-muted);">Event</div>
      </div>
      <div style="text-align:center;">
        <div style="font-family:'Playfair Display',serif;font-size:28px;color:var(--forest);"><?= $total_galeri ?></div>
        <div style="font-size:11px;color:var(--text-muted);">Galeri</div>
      </div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

  
  <div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid rgba(201,169,110,0.15);">
    <div style="font-family:'Playfair Display',serif;font-size:18px;color:var(--forest);margin-bottom:18px;">Wisata</div>
    <?php foreach($wisata_list as $w): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid rgba(0,0,0,0.05);">
      <div style="width:36px;height:36px;border-radius:8px;background:var(--forest);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;"></div>
      <div style="flex:1;font-size:13px;font-weight:400;color:var(--text-dark);"><?= htmlspecialchars($w['nama']) ?></div>
      <div style="font-size:12px;color:var(--text-muted);">Rp <?= number_format($w['harga_tiket'],0,',','.') ?></div>
      <a href="edit_wisata.php?id=<?= $w['id'] ?>" style="font-size:11px;color:var(--gold);text-decoration:none;border:1px solid var(--gold);padding:3px 10px;border-radius:20px;">Edit</a>
    </div>
    <?php endforeach; ?>
    <a href="wisata.php" style="display:block;text-align:center;margin-top:16px;font-size:12px;letter-spacing:0.08em;color:var(--forest-light);font-weight:400;">Kelola Data Wisata →</a>
  </div>

  <div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid rgba(201,169,110,0.15);margin-bottom:24px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
    <div style="font-family:'Playfair Display',serif;font-size:18px;color:var(--forest);">Musim Buah</div>
    <a href="musim.php" style="font-size:12px;color:var(--forest-light);letter-spacing:0.08em;text-decoration:none;">Kelola Musim →</a>
  </div>
 
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
    <?php foreach($musim_list as $b):
      $isPanen     = $b['status'] === 'panen';
      $sedangMusim = ($bulan_ini >= $b['bulan_mulai'] && $bulan_ini <= $b['bulan_akhir']);
    ?>
    <div style="border:1.5px solid <?= $isPanen ? 'rgba(61,122,90,.25)' : 'rgba(0,0,0,.07)' ?>;border-radius:12px;padding:14px 16px;position:relative;transition:.2s;">
 
      
      <form method="POST" action="musim.php" style="position:absolute;top:10px;right:12px;">
        <input type="hidden" name="id" value="<?= $b['id'] ?>">
        <input type="hidden" name="aksi" value="toggle_status">
        <button type="submit"
          style="width:32px;height:18px;border-radius:18px;border:none;cursor:pointer;padding:0;position:relative;background:<?= $isPanen ? 'var(--leaf)' : '#ddd' ?>;"
          title="<?= $isPanen ? 'Klik untuk set tidak panen' : 'Klik untuk set panen' ?>"
          onclick="return confirm('Ubah status <?= htmlspecialchars($b['nama_buah']) ?> menjadi <?= $isPanen ? 'tidak panen' : 'panen' ?>?')">
          <span style="position:absolute;top:2px;width:14px;height:14px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:.2s;<?= $isPanen ? 'right:2px;' : 'left:2px;' ?>"></span>
        </button>
      </form>
 
      <div style="font-size:1.5rem;margin-bottom:6px;"></div>
      <div style="font-weight:600;font-size:.9rem;color:var(--forest);margin-bottom:4px;"><?= htmlspecialchars($b['nama_buah']) ?></div>
      <div style="font-size:.75rem;color:#aaa;margin-bottom:8px;"><?= $bulan_nama[$b['bulan_mulai']] ?> – <?= $bulan_nama[$b['bulan_akhir']] ?></div>
 
      <?php if ($isPanen): ?>
        <span style="font-size:.7rem;padding:2px 10px;border-radius:20px;background:rgba(61,122,90,.12);color:#2d6b45;font-weight:600;">✓ Panen</span>
      <?php else: ?>
        <span style="font-size:.7rem;padding:2px 10px;border-radius:20px;background:#f0f0f0;color:#aaa;font-weight:600;">Tidak Panen</span>
      <?php endif; ?>
 
      <?php if($sedangMusim): ?>
        <div style="font-size:.68rem;color:var(--gold);margin-top:4px;">🗓️ Bulan ini</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
 

  
  <div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid rgba(201,169,110,0.15);">
    <div style="font-family:'Playfair Display',serif;font-size:18px;color:var(--forest);margin-bottom:18px;">Event</div>
    <?php foreach($event_list as $e): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid rgba(0,0,0,0.05);">
      <div style="font-family:'Playfair Display',serif;font-size:13px;color:var(--gold);min-width:60px;"><?= date('d M Y', strtotime($e['tanggal'])) ?></div>
      <div style="flex:1;font-size:13px;font-weight:400;color:var(--text-dark);"><?= htmlspecialchars($e['judul']) ?></div>
      <span style="font-size:10px;padding:3px 10px;border-radius:100px;background:rgba(0,0,0,0.06);color:#888;white-space:nowrap;">
        <?= $statusLabel[$e['status']] ?? $e['status'] ?>
      </span>
    </div>
    <?php endforeach; ?>
    <a href="event.php" style="display:block;text-align:center;margin-top:16px;font-size:12px;letter-spacing:0.08em;color:var(--forest-light);font-weight:400;">Kelola Data Event →</a>
  </div>

</div>


<?php if (!empty($pesanan_pending)): ?>
<div style="background:var(--white);border-radius:16px;padding:28px;border:1px solid rgba(201,169,110,0.15);">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
    <div style="font-family:'Playfair Display',serif;font-size:18px;color:var(--forest);">Pesanan Menunggu Verifikasi</div>
    <a href="pesanan_admin.php?filter=pending" style="font-size:12px;color:var(--forest-light);letter-spacing:0.08em;">Lihat Semua →</a>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="border-bottom:2px solid rgba(201,169,110,0.2);">
        <th style="padding:10px 16px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Kode</th>
        <th style="padding:10px 16px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Nama</th>
        <th style="padding:10px 16px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Wisata</th>
        <th style="padding:10px 16px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Tgl Kunjungan</th>
        <th style="padding:10px 16px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total</th>
        <th style="padding:10px 16px;text-align:left;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pesanan_pending as $p): ?>
      <tr style="border-bottom:1px solid rgba(0,0,0,0.05);">
        <td style="padding:12px 16px;font-family:monospace;font-size:12px;font-weight:600;color:var(--forest);"><?= htmlspecialchars($p['kode_pesan']) ?></td>
        <td style="padding:12px 16px;font-size:13px;color:var(--text-dark);"><?= htmlspecialchars($p['nama']) ?></td>
        <td style="padding:12px 16px;font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($p['nama_wisata']) ?></td>
        <td style="padding:12px 16px;font-size:13px;"><?= date('d M Y', strtotime($p['tanggal_kunjungan'])) ?></td>
        <td style="padding:12px 16px;font-size:13px;">Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
        <td style="padding:12px 16px;">
          <a href="pesanan_admin.php" style="font-size:11px;color:var(--forest);border:1px solid var(--forest);padding:4px 12px;border-radius:100px;text-decoration:none;">Verifikasi</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>