<?php
$pageTitle = 'Kontak — Kebun Ndesa Tanah Merah';
$page      = 'kontak';
require_once __DIR__ . '/../config.php';
$conn = getDB();
include __DIR__ . '/../includes/header.php';

$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $asal    = trim($_POST['asal']    ?? '');
    $pesan   = trim($_POST['pesan']   ?? '');
    $bintang = (int)($_POST['bintang'] ?? 5);
    $ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ip      = mysqli_real_escape_string($conn, $ip);

    
    if (empty($nama)) {
        $error = 'Nama wajib diisi.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $nama)) {
        $error = 'Nama hanya boleh berisi huruf dan spasi, tanpa angka atau simbol.';
    } elseif (mb_strlen($nama) < 2 || mb_strlen($nama) > 60) {
        $error = 'Nama harus antara 2–60 karakter.';
    } elseif (empty($pesan)) {
        $error = 'Pesan/ulasan wajib diisi.';
    } elseif (mb_strlen($pesan) < 5) {
        $error = 'Pesan terlalu pendek.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Rate limiting: maks 3 ulasan per IP per hari
        $cek_ip = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) AS n FROM ulasan WHERE ip_address='$ip' AND DATE(tanggal) = CURDATE()"
        ));
        if ($cek_ip['n'] >= 3) {
            $error = 'Kamu sudah mengirim terlalu banyak ulasan hari ini. Coba lagi besok.';
        }
    }

    if (!$error) {
        $stmt = $conn->prepare(
            "INSERT INTO ulasan (nama, email, asal, bintang, komentar, ditampilkan, ip_address, tanggal)
             VALUES (?, ?, ?, ?, ?, 0, ?, NOW())"
        );
        if ($stmt) {
            $emailVal = $email ?: null;
            $asalVal  = $asal  ?: null;
            $stmt->bind_param("sssiss", $nama, $emailVal, $asalVal, $bintang, $pesan, $ip);
            if ($stmt->execute()) {
                $sent = true;
            } else {
                $error = 'Gagal mengirim pesan. Silakan coba lagi.';
            }
            $stmt->close();
        } else {
            $error = 'Terjadi kesalahan pada server.';
        }
    }
}

$kontakList = [];
$result = $conn->query("SELECT label, nilai, ikon FROM kontak ORDER BY urutan ASC");
if ($result) while ($row = $result->fetch_assoc()) $kontakList[] = $row;

$linkMap = [
    'whatsapp'  => fn($v) => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $v),
    'email'     => fn($v) => 'mailto:kebunndesatanahmerah@gmail.com',
    'instagram' => fn($v) => 'https://www.instagram.com/kebundesatanahmerah?igsh=cmJkcWlmcXFzZjJm',
    'facebook'  => fn($v) => 'https://www.facebook.com/profile.php?id=61577600369384',
];
$emojiMap = [
    'whatsapp'  => '💬',
    'email'     => '📧',
    'instagram' => '📸',
    'facebook'  => '👥',
    'alamat'    => '📍',
];

$inputStyle = "width:100%;padding:13px 16px;background:rgba(255,255,255,0.08);border:1px solid rgba(201,169,110,0.2);border-radius:8px;color:var(--cream);font-family:'Jost',sans-serif;font-size:14px;font-weight:300;outline:none;box-sizing:border-box;transition:border-color .2s;";
?>

<style>
.kontak-input:focus { border-color: rgba(201,169,110,0.6) !important; }
.kontak-input.invalid { border-color: #e57373 !important; }
.field-err { color:#e57373; font-size:.76rem; margin-top:4px; display:none; }
.char-count-kontak { font-size:.74rem; color:rgba(255,255,255,.3); text-align:right; margin-top:3px; }
</style>

<div style="padding-top: 30px; background: #EAF7EC;">
  <section class="kontak-section">
  <div class="section-eyebrow">Kunjungi Kami</div>
  <h1 class="section-title">Kontak & <em>Lokasi</em></h1>
  <div class="kontak-layout">

    
    <div>
      <div class="contact-list">
        <?php if ($kontakList): ?>
          <?php foreach ($kontakList as $k):
            $labelKey = strtolower($k['label']);
            $emoji    = $emojiMap[$labelKey] ?? '📌';
            $href     = '#';
            foreach ($linkMap as $key => $fn) {
              if (str_contains($labelKey, $key)) { $href = $fn($k['nilai']); break; }
            }
          ?>
          <div class="contact-item"
               onclick="window.open('<?= htmlspecialchars($href) ?>', '_blank')"
               style="cursor:pointer;">
            <div class="contact-icon" style="font-size:22px;">
              <?= $emoji ?>
            </div>
            <div>
              <div class="contact-label"><?= htmlspecialchars($k['label']) ?></div>
              <div class="contact-val"><?= htmlspecialchars($k['nilai']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:var(--cream);opacity:.6;">Data kontak belum tersedia.</p>
        <?php endif; ?>
      </div>

      <div class="jam-box">
        <div class="jam-title">Jam Operasional</div>
        <div class="jam-row"><span class="jam-day">Senin – Jumat</span><span class="jam-time">09:00 – 17:00</span></div>
        <div class="jam-row"><span class="jam-day">Sabtu – Minggu</span><span class="jam-time">09:00 – 18:00</span></div>
      </div>
    </div>

    
    <div>
      
      <div style="border-radius:16px;overflow:hidden;height:260px;border:1px solid rgba(201,169,110,0.2);">
        <iframe src="https://www.google.com/maps?q=Kebun+Ndesa+Tanah+Merah+Samarinda&output=embed"
                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
      </div>

      
      <div style="margin-top:24px;background:rgba(255,255,255,0.06);border:1px solid rgba(201,169,110,0.18);border-radius:24px;padding:32px;">
        <div style="font-family:'Playfair Display',serif;font-size:22px;color:var(--cream);margin-bottom:6px;">Tulis Ulasan</div>

        <?php if ($sent): ?>
          <div class="alert alert-success" style="border-radius:10px;">
            Ulasan berhasil dikirim! Akan ditampilkan setelah disetujui admin.
          </div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger" style="border-radius:10px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$sent): ?>
        <form method="POST" action="" id="formKontak" novalidate>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
            <div>
              <label class="contact-label" style="display:block;margin-bottom:8px;">Nama <span style="color:#e57373;">*</span></label>
              <input type="text" name="nama" id="k-nama" class="kontak-input"
                     style="<?= $inputStyle ?>"
                     placeholder="Nama lengkap"
                     value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
              <div class="field-err" id="k-err-nama"></div>
            </div>
            <div>
              <label class="contact-label" style="display:block;margin-bottom:8px;">Asal Kota</label>
              <input type="text" name="asal" class="kontak-input"
                     style="<?= $inputStyle ?>"
                     placeholder="cth: Samarinda"
                     value="<?= htmlspecialchars($_POST['asal'] ?? '') ?>">
            </div>
          </div>

          <div style="margin-bottom:16px;">
            <label class="contact-label" style="display:block;margin-bottom:8px;">Email <span style="color:rgba(255,255,255,.4);font-size:12px;">(opsional)</span></label>
            <input type="email" name="email" class="kontak-input"
                   style="<?= $inputStyle ?>"
                   placeholder="email@contoh.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>

          <div style="margin-bottom:16px;">
            <label class="contact-label" style="display:block;margin-bottom:8px;">Rating</label>
            <div style="display:flex;gap:8px;font-size:28px;cursor:pointer;" id="star-container">
              <?php $selectedBintang = (int)($_POST['bintang'] ?? 5); ?>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star" data-value="<?= $i ?>"
                      style="color:<?= $i <= $selectedBintang ? '#C9A96E' : 'rgba(255,255,255,0.2)' ?>;">★</span>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="bintang" id="bintang-input" value="<?= $selectedBintang ?>">
          </div>

          <div style="margin-bottom:24px;">
            <label class="contact-label" style="display:block;margin-bottom:8px;">Ulasan <span style="color:#e57373;">*</span></label>
            <textarea name="pesan" id="k-pesan" rows="4" maxlength="500"
                      class="kontak-input"
                      style="<?= $inputStyle ?>resize:vertical;"
                      placeholder="Ceritakan pengalamanmu di Kebun Ndesa..."><?= htmlspecialchars($_POST['pesan'] ?? '') ?></textarea>
            <div class="char-count-kontak"><span id="k-char">0</span>/500</div>
          </div>

          <button type="submit" class="btn-primary" style="width:100%;padding:16px;border-radius:8px;cursor:pointer;">
            Kirim Ulasan
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>
</div>

<script>

const stars = document.querySelectorAll('.star');
const input = document.getElementById('bintang-input');
if (stars.length) {
  const updateStars = (val) => {
    stars.forEach(s => {
      s.style.color = parseInt(s.dataset.value) <= val ? '#C9A96E' : 'rgba(255,255,255,0.2)';
    });
  };
  stars.forEach(star => {
    star.addEventListener('click',     () => { input.value = star.dataset.value; updateStars(+star.dataset.value); });
    star.addEventListener('mouseover', () => updateStars(+star.dataset.value));
    star.addEventListener('mouseout',  () => updateStars(+input.value));
  });
}


const pesanEl = document.getElementById('k-pesan');
const charEl  = document.getElementById('k-char');
if (pesanEl) {
  pesanEl.addEventListener('input', () => { charEl.textContent = pesanEl.value.length; });
  charEl.textContent = pesanEl.value.length;
}


const namaEl = document.getElementById('k-nama');
if (namaEl) {
  namaEl.addEventListener('input', function() {
    this.value = this.value.replace(/[^\p{L}\s'\-\.]/gu, '');
    const ok  = /^[\p{L}\s'\-\.]{2,}$/u.test(this.value.trim());
    const err = document.getElementById('k-err-nama');
    this.classList.toggle('invalid', !ok && this.value.length > 0);
    if (!ok && this.value.length > 0) {
      err.textContent = 'Nama hanya boleh huruf dan spasi.';
      err.style.display = 'block';
    } else {
      err.style.display = 'none';
    }
  });
}


const formK = document.getElementById('formKontak');
if (formK) {
  formK.addEventListener('submit', function(e) {
    const nama = namaEl.value.trim();
    if (!nama || !/^[\p{L}\s'\-\.]{2,}$/u.test(nama)) {
      const err = document.getElementById('k-err-nama');
      err.textContent = 'Nama hanya boleh huruf dan spasi, minimal 2 karakter.';
      err.style.display = 'block';
      namaEl.classList.add('invalid');
      e.preventDefault();
    }
  });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>