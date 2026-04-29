<?php
$page      = 'beranda';
$pageTitle = 'Kebun Ndesa Tanah Merah — Wisata Alam Samarinda';
include 'includes/header.php';
$conn = getDB();

$wisatas = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM wisata WHERE status='aktif' ORDER BY id ASC LIMIT 4"), MYSQLI_ASSOC);
$galeris = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM galeri ORDER BY urutan ASC, id DESC LIMIT 5"), MYSQLI_ASSOC);
$ulasans = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM ulasan WHERE ditampilkan=1 ORDER BY id DESC LIMIT 9"), MYSQLI_ASSOC);
$musims  = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM musim_buah ORDER BY (status='panen') DESC, nama_buah ASC LIMIT 4"), MYSQLI_ASSOC);

$heroFoto   = !empty($galeris) ? '/assets/galeri/' . $galeris[0]['foto'] : '';
$bulan_ini  = (int)date('n');
$bulanNama  = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$bulanPenuh = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$testi_groups = array_chunk($ulasans, 3);
?>

<style>

.hero-full {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  overflow: hidden;
}
.hero-bg {
  position: absolute; inset: 0;
  background-image: url('<?= $heroFoto ?>');
  background-size: cover;
  background-position: center;
  transform: scale(1.05);
  transition: transform 8s ease;
}
.hero-bg::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(10,20,10,0.92) 0%, rgba(10,20,10,0.55) 45%, rgba(10,20,10,0.15) 100%);
}
.hero-full:hover .hero-bg { transform: scale(1); }

.hero-content {
  position: relative; z-index: 2;
  padding: 0 8vw 80px;
  max-width: 900px;
}
.hero-award {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(201,169,110,.15); border: 1px solid rgba(201,169,110,.35);
  border-radius: 100px; padding: 6px 16px;
  font-size: 11px; font-weight: 400; letter-spacing: .1em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 28px;
}
.hero-award::before { content:'★'; font-size: 12px; }

.hero-full h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(52px, 7vw, 96px);
  font-weight: 600; line-height: 1.0;
  color: #fff; margin-bottom: 20px;
}
.hero-full h1 em { font-style: italic; color: var(--gold); }
.hero-sub {
  font-size: 15px; font-weight: 300;
  color: rgba(255,255,255,.6); max-width: 440px;
  line-height: 1.8; margin-bottom: 40px;
}
.hero-btns { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.btn-hero-primary {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 14px 32px; background: var(--gold); color: var(--forest);
  font-size: 12px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
  border: none; border-radius: 100px; cursor: pointer; transition: all .3s; text-decoration: none;
}
.btn-hero-primary:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 12px 32px rgba(201,169,110,.3); }
.btn-hero-ghost {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 14px 28px; background: transparent; color: rgba(255,255,255,.8);
  font-size: 12px; font-weight: 400; letter-spacing: .08em; text-transform: uppercase;
  border: 1px solid rgba(255,255,255,.25); border-radius: 100px;
  cursor: pointer; transition: all .3s; text-decoration: none;
}
.btn-hero-ghost:hover { border-color: rgba(255,255,255,.6); color: #fff; background: rgba(255,255,255,.08); }

.hero-stats-bar {
  display: flex; gap: 36px; margin-top: 56px;
  padding-top: 32px; border-top: 1px solid rgba(255,255,255,.1); flex-wrap: wrap;
}
.hero-stat-num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 36px; font-weight: 600; color: var(--gold); line-height: 1;
}
.hero-stat-label {
  font-size: 10px; letter-spacing: .12em; text-transform: uppercase;
  color: rgba(255,255,255,.4); margin-top: 4px;
}

.avail-tag {
  position: absolute; top: 110px; right: 8vw; z-index: 3;
  background: rgba(61,107,56,.85); color: #8fd488;
  padding: 6px 14px; border-radius: 100px;
  font-size: 10px; letter-spacing: .08em; text-transform: uppercase;
  display: inline-flex; align-items: center; gap: 6px;
  width: fit-content;
}
.avail-dot { width: 5px; height: 5px; border-radius: 50%; background: #8fd488; animation: pulse 2s infinite; flex-shrink:0; }

.hero-scroll-hint {
  position: absolute; bottom: 32px; left: 50%; transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  color: rgba(255,255,255,.3); font-size: 9px; letter-spacing: .2em; text-transform: uppercase;
  z-index: 2; animation: scrollBounce 2s infinite;
}
.hero-scroll-hint::after { content: ''; width: 1px; height: 36px; background: linear-gradient(to bottom, rgba(201,169,110,.5), transparent); }
@keyframes scrollBounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(6px)} }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }


.stats-band {
  background: var(--forest); padding: 0;
  display: grid; grid-template-columns: repeat(4,1fr);
}
.band-item {
  padding: 40px 36px; border-right: 1px solid rgba(201,169,110,.1); position: relative; overflow: hidden;
}
.band-item:last-child { border-right: none; }
.band-item::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, var(--gold), transparent);
  transform: scaleX(0); transform-origin: left; transition: transform .6s ease;
}
.band-item:hover::before { transform: scaleX(1); }
.band-num { font-family: 'Cormorant Garamond', serif; font-size: 52px; color: var(--cream); line-height: 1; font-weight: 500; }
.band-num sup { font-size: 22px; font-family: 'Jost', sans-serif; font-weight: 300; }
.band-label { font-size: 11px; font-weight: 400; letter-spacing: .12em; text-transform: uppercase; color: rgba(247,243,237,.4); margin-top: 10px; }
.band-desc  { font-size: 13px; font-weight: 300; color: rgba(247,243,237,.5); margin-top: 6px; line-height: 1.5; }


.musim-foto-section { background: #EAF7EC; padding: 100px 8vw; }
.musim-foto-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px; }
.musim-foto-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 48px; font-weight: 300; }
.musim-foto-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; }
.musim-foto-card { background: var(--white); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(26,51,40,.08); transition: transform .3s, box-shadow .3s; }
.musim-foto-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(26,51,40,.14); }
.musim-foto-img { width: 100%; height: 200px; object-fit: cover; display: block; background: var(--forest-mid); }
.musim-foto-img-placeholder { width: 100%; height: 200px; background: linear-gradient(145deg, var(--forest-mid), var(--forest)); display: flex; align-items: center; justify-content: center; font-size: 56px; color: rgba(255,255,255,.15); }
.musim-foto-body { padding: 20px; }
.musim-foto-tag { font-size: 10px; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px; }
.musim-foto-nama { font-family: 'Cormorant Garamond', serif; font-size: 22px; color: var(--forest); font-weight: 500; margin-bottom: 4px; }
.musim-foto-period { font-size: 12px; color: var(--text-muted); font-weight: 300; margin-bottom: 14px; }
.musim-foto-status { display: inline-block; padding: 3px 12px; border-radius: 100px; font-size: 10px; font-weight: 500; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 16px; }
.status-panen     { background: rgba(61,107,56,.12); color: #2d6b45; border: 1px solid rgba(61,107,56,.25); }
.status-tdk-panen { background: rgba(0,0,0,.05); color: var(--text-muted); border: 1px solid rgba(0,0,0,.1); }
.btn-musim-pesan { display: block; width: 100%; padding: 11px; text-align: center; border: 1.5px solid var(--forest); border-radius: 100px; font-size: 11px; font-weight: 500; letter-spacing: .08em; text-transform: uppercase; color: var(--forest); text-decoration: none; background: transparent; transition: all .2s; cursor: pointer; font-family: 'Jost', sans-serif; }
.btn-musim-pesan:hover { background: var(--forest); color: var(--cream); }


.testi-section { background: var(--forest); padding: 80px 8vw; }
.testi-marquee-outer { overflow: hidden; border-radius: 16px; mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%); -webkit-mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%); }
.testi-marquee-track { display: flex; gap: 12px; width: max-content; animation: marquee-scroll 20s linear infinite; }
.testi-marquee-track:hover { animation-play-state: paused; }
@keyframes marquee-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
.testi-card { background: rgba(255,255,255,.04); border: 1px solid rgba(201,169,110,.12); border-radius: 20px; padding: 28px; width: 280px; flex-shrink: 0; transition: background .3s, border-color .3s, transform .3s; }
.testi-card:hover { background: rgba(255,255,255,.07); border-color: rgba(201,169,110,.25); transform: translateY(-4px); }
.testi-stars { color: #C9A96E; font-size: 13px; letter-spacing: 2px; margin-bottom: 16px; }
.testi-text { font-size: 14px; font-weight: 300; color: rgba(247,243,237,.7); line-height: 1.85; margin-bottom: 22px; font-style: italic; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
.testi-author { display: flex; align-items: center; gap: 12px; }
.testi-avatar { width: 40px; height: 40px; border-radius: 50%; background: rgba(201,169,110,.2); display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-size: 18px; color: #C9A96E; flex-shrink: 0; }
.testi-name { font-size: 14px; font-weight: 400; color: var(--cream); }
.testi-loc  { font-size: 11px; font-weight: 300; color: rgba(247,243,237,.4); margin-top: 2px; }
.testi-cta  { margin-top: 40px; text-align: center; }
.testi-cta a { color: #C9A96E; font-size: 13px; font-weight: 300; text-decoration: underline; text-underline-offset: 4px; }


.sejarah-section { background: #EAF7EC; padding: 100px 8vw 60px; }
.sejarah-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: start; }
.sejarah-left { position: sticky; top: 100px; }
.sejarah-desc { font-size: 17px; font-weight: 400; font-family: 'Cormorant Garamond', serif; color: var(--text-mid); line-height: 1.9; margin-top: 16px; }
.timeline { display: flex; flex-direction: column; gap: 0; }
.tl-item { display: flex; gap: 24px; padding-bottom: 36px; position: relative; }
.tl-item::before { content: ''; position: absolute; left: 52px; top: 36px; bottom: 0; width: 1px; background: linear-gradient(to bottom, rgba(64,145,108,0.3), transparent); }
.tl-item:last-child::before { display: none; }
.tl-year { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 600; color: #C9A96E; min-width: 68px; line-height: 1; padding-top: 4px; flex-shrink: 0; }
.tl-content { background: var(--cream); border-radius: 16px; padding: 20px 24px; border-left: 3px solid rgba(64,145,108,0.25); flex: 1; }
.tl-title { font-family: 'Cormorant Garamond', serif; font-size: 23px; color: #C9A96E; font-weight: 500; margin-bottom: 8px; }
.tl-desc { font-size: 17px; font-weight: 400; font-family: 'Cormorant Garamond', serif; color: var(--text-mid); line-height: 1.9; }


.gallery-section { background: #EAF7EC; padding: 80px 8vw 60px; }

@media(max-width:1024px) { .musim-foto-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width:768px) {
  .hero-full h1 { font-size: clamp(36px,10vw,56px); }
  .hero-content { padding: 0 5vw 60px; }
  .stats-band { grid-template-columns: 1fr; }
  .sejarah-inner { grid-template-columns: 1fr; gap: 40px; }
  .sejarah-left { position: static; }
  .sejarah-section { padding: 64px 5vw; }
  .musim-foto-grid { grid-template-columns: repeat(2,1fr); }
  .avail-tag { top: 84px; left: 5vw; }
}
@media(max-width:480px) {
  .musim-foto-grid { grid-template-columns: 1fr; }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<div class="hero-full">
  <div class="hero-bg" id="hero-bg"></div>

  <div class="avail-tag">
    <span class="avail-dot"></span>
    Buka Hari Ini
  </div>

  <div class="hero-content">
    <h1>Kebun Ndesa<br><em>Tanah Merah</em></h1>
    <p class="hero-sub">Destinasi wisata alam dan kebun buah yang edukatif, segar, dan menyenangkan untuk semua keluarga di jantung Kalimantan Timur.</p>
    <div class="hero-btns">
      <a href="pages/wisata.php" class="btn-hero-primary">Jelajahi Sekarang →</a>
      <a href="pages/kontak.php" class="btn-hero-ghost">Hubungi Kami</a>
    </div>
    <div class="hero-stats-bar">
      <div class="hero-stat">
        <div class="hero-stat-num"><span class="counter" data-target="7">0</span><sup style="font-size:18px;font-family:Jost,sans-serif;">+</sup></div>
        <div class="hero-stat-label">Jenis Buah</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num">4.9</div>
        <div class="hero-stat-label">Rating</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num">2019</div>
        <div class="hero-stat-label">Tahun Berdiri</div>
      </div>
    </div>
  </div>

  <div class="hero-scroll-hint">Scroll</div>
</div>


<div class="stats-band">
  <div class="band-item">
    <div class="band-num"><span class="counter" data-target="7">0</span><sup>+</sup></div>
    <div class="band-label">Jenis Buah</div>
    <div class="band-desc">Jambu, jeruk, pepaya & lebih banyak lagi</div>
  </div>
  <div class="band-item">
    <div class="band-num"><span class="counter" data-target="5">0</span><sup>ha</sup></div>
    <div class="band-label">Luas Kebun</div>
    <div class="band-desc">Area hijau asri di Kalimantan Timur</div>
  </div>
  <div class="band-item">
    <div class="band-num"><span class="counter" data-target="4.9" data-decimal="1">0</span></div>
    <div class="band-label">Rating</div>
    <div class="band-desc">Berdasarkan ulasan wisatawan</div>
  </div>
  <div class="band-item">
    <div class="band-num">2019</div>
    <div class="band-label">Tahun Berdiri</div>
    <div class="band-desc">Melayani wisatawan Kalimantan Timur</div>
  </div>
</div>


<section class="sejarah-section">
  <div class="sejarah-inner">
    <div class="sejarah-left">
      <div class="section-eyebrow">Perjalanan Kami</div>
      <h2 class="section-title">Sejarah<br><em>Kebun Ndesa</em></h2>
      <p class="sejarah-desc">Kebun Ndesa Tanah Merah berdiri sejak 2019 di Samarinda, Kalimantan Timur. Berawal dari kebun buah sederhana, kini berkembang menjadi destinasi wisata alam yang menyediakan berbagai fasilitas untuk keluarga.</p>
      <p class="sejarah-desc">Setiap fasilitas dibangun bertahap dengan memperhatikan kenyamanan pengunjung dan kelestarian lingkungan sekitar.</p>
    </div>
    <div class="sejarah-right">
      <div class="timeline">
        <div class="tl-item">
          <div class="tl-year">2019</div>
          <div class="tl-content">
            <div class="tl-title">Kebun Buah Pertama</div>
            <div class="tl-desc">Kebun Ndesa mulai beroperasi dengan menanam berbagai buah tropis seperti jambu, jeruk, pepaya, belimbing, dan anggur.</div>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-year">2020</div>
          <div class="tl-content">
            <div class="tl-title">Pemancingan Dibuka</div>
            <div class="tl-desc">Area pemancingan menawarkan suasana tenang di tepi kolam di tengah lingkungan kebun yang asri.</div>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-year">2021</div>
          <div class="tl-content">
            <div class="tl-title">Kolam Renang Hadir</div>
            <div class="tl-desc">Kolam renang keluarga hadir melengkapi fasilitas yang ada, cocok untuk liburan seharian bersama anak-anak.</div>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-year">2024</div>
          <div class="tl-content">
            <div class="tl-title">Taman Bunga Mekar</div>
            <div class="tl-desc">Taman bunga dibuka sebagai area terbaru, menambah daya tarik visual kebun sekaligus menjadi spot foto favorit pengunjung.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<section class="testi-section">
  <div class="section-eyebrow" style="color:rgba(201,169,110,0.85)">Kata Mereka</div>
  <h2 class="section-title" style="color:var(--cream)">Pengalaman <em>Wisatawan</em></h2>

  <?php if (!empty($ulasans)): ?>
  <div class="testi-marquee-outer" style="margin-top:56px;">
    <div class="testi-marquee-track">
      <?php for ($loop = 0; $loop < 2; $loop++): foreach ($ulasans as $u): ?>
        <div class="testi-card">
          <div class="testi-stars"><?= str_repeat('★', (int)$u['bintang']) . str_repeat('☆', 5 - (int)$u['bintang']) ?></div>
          <p class="testi-text">"<?= htmlspecialchars($u['komentar']) ?>"</p>
          <div class="testi-author">
            <div class="testi-avatar"><?= strtoupper(mb_substr($u['nama'], 0, 1)) ?></div>
            <div>
              <div class="testi-name"><?= htmlspecialchars($u['nama']) ?></div>
              <?php if (!empty($u['asal'])): ?>
                <div class="testi-loc"><?= htmlspecialchars($u['asal']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; endfor; ?>
    </div>
  </div>
  <?php else: ?>
    <p style="text-align:center;color:rgba(247,243,237,.4);margin-top:40px;font-size:14px;">Belum ada ulasan yang ditampilkan.</p>
  <?php endif; ?>

  <div class="testi-cta">
    <a href="pages/kontak.php">Ingin berbagi pengalamanmu? Tulis ulasan di sini →</a>
  </div>
</section>


<section class="gallery-section">
  <div class="section-eyebrow">Galeri</div>
  <h2 class="section-title">Suasana <em>Kebun</em></h2>
  <p class="section-sub">Sekilas keindahan alam dan aktivitas di Kebun Ndesa Tanah Merah</p>
  <div class="gallery-grid">
    <?php if(!empty($galeris)):
      foreach($galeris as $i => $g):
        $span = '';
        if($i === 0) $span = 'grid-column:span 2;grid-row:span 2;';
        elseif($i === 3) $span = 'grid-column:span 2;';
    ?>
    <div class="gallery-item" style="<?= $span ?>background-image:url('/assets/galeri/<?= htmlspecialchars($g['foto']) ?>');background-size:cover;background-position:center;min-height:200px;">
      <div class="gallery-overlay"></div>
    </div>
    <?php endforeach; else: ?>
    <div class="gallery-item g1"><div class="gallery-overlay"></div></div>
    <div class="gallery-item g2"><div class="gallery-overlay"></div></div>
    <div class="gallery-item g3"><div class="gallery-overlay"></div></div>
    <div class="gallery-item g4"><div class="gallery-overlay"></div></div>
    <div class="gallery-item g5"><div class="gallery-overlay"></div></div>
    <?php endif; ?>
  </div>
  <div style="text-align:center;margin-top:32px;">
    <a href="pages/galeri.php"><button class="link-subtle">Lihat Semua Foto →</button></a>
  </div>
</section>

<script>

(function() {
  var counters = document.querySelectorAll('.counter');
  var started  = {};
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      var key = el.dataset.target + Math.random();
      if (started[key]) return;
      started[key] = true;
      var target   = parseFloat(el.dataset.target);
      var decimal  = parseInt(el.dataset.decimal || 0);
      var duration = 1800;
      var startTime = null;
      function step(ts) {
        if (!startTime) startTime = ts;
        var progress = Math.min((ts - startTime) / duration, 1);
        var ease = 1 - Math.pow(1 - progress, 3);
        var val = target * ease;
        el.textContent = decimal > 0 ? val.toFixed(decimal) : Math.floor(val);
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = decimal > 0 ? target.toFixed(decimal) : target;
      }
      requestAnimationFrame(step);
      observer.unobserve(el);
    });
  }, { threshold: 0.3 });
  counters.forEach(function(c) { observer.observe(c); });
})();


window.addEventListener('scroll', function() {
  var bg = document.getElementById('hero-bg');
  if (bg) bg.style.transform = 'scale(1.05) translateY(' + (window.scrollY * 0.15) + 'px)';
}, { passive: true });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>