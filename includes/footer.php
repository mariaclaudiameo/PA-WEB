<?php
$conn = getDB();
$pengaturan = [];
try {
    $conn = getDB();
    $r = $conn->query("SELECT kunci, nilai FROM pengaturan");
    if ($r) while ($row = $r->fetch_assoc()) $pengaturan[$row['kunci']] = $row['nilai'];
    $conn->close();
} catch (Exception $e) {  }
?>
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-brand-name">Kebun Ndesa<br>Tanah Merah</div>
      <p class="footer-brand-desc"><?= htmlspecialchars($pengaturan['about'] ?? 'Destinasi wisata alam  di Samarinda, Kalimantan Timur.') ?></p>
    </div>
    <div>
      <div class="footer-heading">Navigasi</div>
      <ul class="footer-links">
        <li><a href="<?= SITE_URL ?>/index.php">Beranda</a></li>
        <li><a href="<?= SITE_URL ?>/pages/wisata.php">Wisata</a></li>
        <li><a href="<?= SITE_URL ?>/pages/musim.php">Musim Buah</a></li>
        <li><a href="<?= SITE_URL ?>/pages/event.php">Event</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-heading">Layanan</div>
      <ul class="footer-links">
        <li><a href="<?= SITE_URL ?>/pages/wisata.php">Paket Wisata</a></li>
        <li><a href="<?= SITE_URL ?>/pages/galeri.php">Galeri Foto</a></li>
        <li><a href="<?= SITE_URL ?>/pages/kontak.php">Kontak Kami</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-heading">Kontak</div>
      <ul class="footer-links">
        <li><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $pengaturan['telp'] ?? '') ?>"><?= htmlspecialchars($pengaturan['telp'] ?? '085190043300') ?></a></li>
        <li><a href="mailto:<?= htmlspecialchars($pengaturan['email'] ?? 'kebunndesatanahmerah@gmail.com') ?>"><?= htmlspecialchars($pengaturan['email'] ?? 'kebunndesatanahmerah@gmail.com') ?></a></li>
        <li><a href="https://www.instagram.com/kebundesatanahmerah?igsh=cmJkcWlmcXFzZjJm">@kebundesatanahmerah</a></li>
      </ul>
    </div>
  </div>
  <div style="width:40px;height:1px;background:rgba(184,149,74,0.4);margin:0 auto 36px;"></div>
  <div class="footer-bottom">
    <div class="footer-copy">© <?= date('Y') ?> Kebun Ndesa Tanah Merah. Hak cipta dilindungi.</div>
    <div class="footer-copy">Samarinda, Kalimantan Timur</div>
  </div>
</footer>



<script>

var dot  = document.getElementById('cursor-dot');
var ring = document.getElementById('cursor-ring');
if (dot && ring) {
  window.addEventListener('mousemove', function(e) {
    dot.style.top   = e.clientY + 'px';
    dot.style.left  = e.clientX + 'px';
    ring.style.top  = e.clientY + 'px';
    ring.style.left = e.clientX + 'px';
  }, { passive: true });

  document.querySelectorAll('a, button, [onclick]').forEach(function(el) {
    el.addEventListener('mouseenter', function() {
      dot.style.width = '12px'; dot.style.height = '12px';
      ring.style.width = '52px'; ring.style.height = '52px';
    });
    el.addEventListener('mouseleave', function() {
      dot.style.width = ''; dot.style.height = '';
      ring.style.width = ''; ring.style.height = '';
    });
  });
}


(function() {
  
  document.querySelectorAll(
    '.wisata-card, .musim-card, .event-item, .testi-card, .stat-card, .band-item, .buah-card, .mb-stat'
  ).forEach(function(el, i) {
    if (!el.classList.contains('reveal')) {
      el.classList.add('reveal');
      var delay = i % 4;
      if (delay > 0) el.classList.add('reveal-delay-' + delay);
    }
  });

  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.reveal, .reveal-left, .reveal-scale').forEach(function(el) {
    observer.observe(el);
  });
})();


if (window.innerWidth > 768) {
  var parallaxEls = document.querySelectorAll('.parallax-img');
  if (parallaxEls.length) {
    window.addEventListener('scroll', function() {
      var scrollY = window.pageYOffset;
      parallaxEls.forEach(function(el) {
        var wrap = el.closest('.parallax-wrap');
        if (!wrap) return;
        var center = wrap.getBoundingClientRect().top + wrap.offsetHeight / 2 - window.innerHeight / 2;
        el.style.transform = 'translateY(' + (center * 0.12) + 'px)';
      });
    }, { passive: true });
  }
}



function animateCounter(el) {
  var target = parseFloat(el.dataset.target);
  var suffix = el.dataset.suffix || '';
  var prefix = el.dataset.prefix || '';
  var duration = 1800;
  var start = null;

  function step(timestamp) {
    if (!start) start = timestamp;
    var progress = Math.min((timestamp - start) / duration, 1);
    var ease = 1 - Math.pow(1 - progress, 3);
    var current = Math.floor(ease * target);
    el.textContent = prefix + (Number.isInteger(target) ? current : (ease * target).toFixed(1)) + suffix;
    if (progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

var counterObserver = new IntersectionObserver(function(entries) {
  entries.forEach(function(entry) {
    if (entry.isIntersecting && !entry.target.dataset.counted) {
      entry.target.dataset.counted = 'true';
      animateCounter(entry.target);
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('.counter').forEach(function(el) {
  counterObserver.observe(el);
});




(function() {
  var slider = document.getElementById('testi-slider');
  if (!slider) return;
  var slides = slider.querySelectorAll('.testi-slide');
  var total = slides.length;
  var current = 0;
  var dotsWrap = document.getElementById('testi-dots');

  
  slides.forEach(function(_, i) {
    var dot = document.createElement('button');
    dot.className = 'testi-dot' + (i === 0 ? ' active' : '');
    dot.onclick = function() { goTo(i); };
    dotsWrap.appendChild(dot);
  });

  function goTo(n) {
    current = (n + total) % total;
    slider.style.transform = 'translateX(-' + (current * 100) + '%)';
    dotsWrap.querySelectorAll('.testi-dot').forEach(function(d, i) {
      d.classList.toggle('active', i === current);
    });
  }

  document.getElementById('testi-prev').onclick = function() { goTo(current - 1); };
  document.getElementById('testi-next').onclick = function() { goTo(current + 1); };

  // Auto slide setiap 5 detik
  setInterval(function() { goTo(current + 1); }, 5000);
})();



</script>

</body>
</html>