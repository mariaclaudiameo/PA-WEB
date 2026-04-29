/* ═══════════════════════════════════════════════════════════════
   KEBUN NDESA TANAH MERAH — Main JavaScript
   ═══════════════════════════════════════════════════════════════ */


let DB = {
  wisata: [
    {id:1, nama:'Kolam Renang', emoji:'🏊', kategori:'Air', harga:25000, hargaAnak:15000,
     deskripsi:'Nikmati kesegaran kolam renang alami di tengah kebun yang rindang. Aman untuk anak-anak dengan pengawas berpengalaman.',
     fitur:['Kolam Dewasa','Kolam Anak','Gazebo Tepi Kolam','Kamar Ganti'], status:'aktif'},
    {id:2, nama:'Taman', emoji:'🌺', kategori:'Rekreasi', harga:15000, hargaAnak:10000,
     deskripsi:'Jelajahi taman bunga cantik dengan berbagai spot foto instagramable. Sempurna untuk piknik keluarga di alam terbuka.',
     fitur:['Taman Bunga','Spot Foto','Area Piknik','Gazebo'], status:'aktif'},
    {id:3, nama:'Kebun Buah', emoji:'🍊', kategori:'Agrowisata', harga:35000, hargaAnak:20000,
     deskripsi:'Petik buah segar langsung dari pohon! Tersedia berbagai pilihan buah musiman dari kebun organik kami.',
     fitur:['Petik Langsung','Buah Organik','Edukasi Bertani','Oleh-oleh Buah'], status:'aktif'},
    {id:4, nama:'Pemancingan', emoji:'🎣', kategori:'Rekreasi', harga:20000, hargaAnak:20000,
     deskripsi:'Nikmati sensasi memancing di kolam alami yang tenang. Cocok untuk relaksasi dan menghabiskan waktu bersama keluarga.',
     fitur:['Kolam Pancing Alami','Sewa Alat Pancing','Bayar per Ikan','Gubuk Tepi Danau'], status:'aktif'},
  ],
  event: [
    {id:1, judul:'Festival Panen Durian 2025', tanggal:'15 Jan 2025', kategori:'Festival',
     deskripsi:'Rayakan musim panen durian bersama! Ada lomba makan durian, hiburan musik lokal, dan bazar kuliner desa.',
     lokasi:'Area Utama Kebun', waktu:'08.00–17.00 WITA', status:'upcoming'},
    {id:2, judul:'Workshop Pengolahan Hasil Kebun', tanggal:'5 Feb 2025', kategori:'Workshop',
     deskripsi:'Belajar mengolah hasil kebun menjadi produk bernilai: selai, keripik, dan minuman segar.',
     lokasi:'Gazebo Edukasi', waktu:'09.00–13.00 WITA', status:'upcoming'},
    {id:3, judul:'Outbound Seru Bersama Komunitas', tanggal:'20 Des 2024', kategori:'Outbound',
     deskripsi:'Kegiatan outbound eksklusif bersama 150 peserta. Seru, kompak, dan penuh kenangan.',
     lokasi:'Area Outbound', waktu:'07.30–16.00 WITA', status:'past'},
  ],
  testi: [
    {id:1, nama:'Rahma Dewi', asal:'Samarinda, Kaltim', rating:5,
     komentar:'Pengalaman terbaik bawa keluarga! Anak-anak sangat senang petik buah dan bermain di kolam. Pasti akan kembali lagi.'},
    {id:2, nama:'Dani Pratama', asal:'Balikpapan, Kaltim', rating:5,
     komentar:'Suasana kebunnya sangat asri dan sejuk. Spot fotonya keren banget! Kolam renangnya bersih dan nyaman.'},
    {id:3, nama:'Sari Wulandari', asal:'Bontang, Kaltim', rating:5,
     komentar:'Pemancingannya asyik banget! Anak-anak juga senang di taman bunga. Tempat yang sempurna untuk healing dari kota.'},
  ],
  musim: [
    {id:1, nama:'Durian', mulai:'Oktober', akhir:'Maret', status:'aktif'},
    {id:2, nama:'Rambutan', mulai:'November', akhir:'Maret', status:'aktif'},
    {id:3, nama:'Mangga', mulai:'April', akhir:'September', status:'aktif'},
    {id:4, nama:'Nanas', mulai:'Januari', akhir:'Juni', status:'aktif'},
    {id:5, nama:'Pepaya', mulai:'Januari', akhir:'Desember', status:'aktif'},
  ]
};

let nextId = {wisata:5, event:4, testi:4, musim:6};
let currentModal = '', editId = null;
const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const WA_NUM = '6281234567890';


function goPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
  const el = document.getElementById('nav-' + name);
  if (el) el.classList.add('active');
  window.scrollTo({top: 0, behavior: 'smooth'});
  if (name === 'wisata')  { renderWisataGrid(); renderBundling(); renderTestiGrid('wisata-testi-grid'); }
  if (name === 'event')   renderEventGrid();
  if (name === 'informasi') renderFAQ();
}


window.addEventListener('scroll', () => {
  const nav = document.querySelector('nav');
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 20);
});


const wColors = ['w1','w2','w3','w4'];
function renderWisataGrid() {
  const active = DB.wisata.filter(w => w.status === 'aktif');
  ['home-wisata-grid','wisata-grid'].forEach(gid => {
    const g = document.getElementById(gid);
    if (!g) return;
    g.innerHTML = active.map((w, i) => `
      <div class="wisata-card">
        <div class="wisata-card-img ${wColors[i % 4]}">
          <span>${w.emoji}</span>
          <span class="wisata-card-badge">${w.kategori}</span>
        </div>
        <div class="wisata-card-body">
          <h3>${w.nama}</h3>
          <p>${w.deskripsi}</p>
          <div class="wisata-card-features">${(w.fitur||[]).map(f => `<span class="wc-feat">✓ ${f}</span>`).join('')}</div>
        </div>
        <div class="wisata-card-footer">
          <div>
            <div class="wc-price-label">Mulai dari</div>
            <div class="wc-price">Rp ${w.harga.toLocaleString('id-ID')} <span>/ dewasa</span></div>
          </div>
          <a class="card-wa" href="https://wa.me/${WA_NUM}?text=Halo%20Kebun%20Ndesa%2C%20saya%20tertarik%20dengan%20wahana%20${encodeURIComponent(w.nama)}" target="_blank">💬 Info WA</a>
        </div>
      </div>`).join('');
  });
}


function renderBundling() {
  const active = DB.wisata.filter(w => w.status === 'aktif');
  document.getElementById('bundlingOptions').innerHTML = active.map(w => `
    <div class="bund-opt" id="bopt-${w.id}" onclick="toggleBundling(${w.id})">
      <div class="bund-opt-top">
        <span class="bund-opt-icon">${w.emoji}</span>
        <span class="bund-check" id="bcheck-${w.id}">✓</span>
      </div>
      <h4>${w.nama}</h4>
      <div class="bund-price">Rp ${w.harga.toLocaleString('id-ID')}/org</div>
    </div>`).join('');
  updateBundlingTotal();
}

let selectedBundling = new Set();

function toggleBundling(id) {
  if (selectedBundling.has(id)) selectedBundling.delete(id);
  else selectedBundling.add(id);
  document.getElementById('bopt-' + id).classList.toggle('selected', selectedBundling.has(id));
  updateBundlingTotal();
}

function updateBundlingTotal() {
  const n = selectedBundling.size;
  const selected = DB.wisata.filter(w => selectedBundling.has(w.id));
  const base = selected.reduce((s, w) => s + w.harga, 0);
  let disc = 0, label = '', note = '';
  if (n === 0)      { label = 'Rp 0'; note = 'Pilih minimal 2 wahana untuk harga bundling'; }
  else if (n === 1) { label = `Rp ${base.toLocaleString('id-ID')}`; note = 'Pilih 1 wahana lagi untuk bundling (hemat 10%)'; }
  else if (n === 2) { disc = .10; label = `Rp ${Math.round(base*(1-disc)).toLocaleString('id-ID')}`; note = `Diskon 10% — hemat Rp ${Math.round(base*disc).toLocaleString('id-ID')}/orang`; }
  else if (n === 3) { disc = .15; label = `Rp ${Math.round(base*(1-disc)).toLocaleString('id-ID')}`; note = `Diskon 15% — hemat Rp ${Math.round(base*disc).toLocaleString('id-ID')}/orang`; }
  else              { disc = .25; label = `Rp ${Math.round(base*(1-disc)).toLocaleString('id-ID')}`; note = `All-in Diskon 25% — hemat Rp ${Math.round(base*disc).toLocaleString('id-ID')}/orang`; }
  document.getElementById('bundlingTotal').textContent = label;
  document.getElementById('bundlingNote').textContent = note;
  const names = selected.map(w => w.nama).join(', ');
  const waMsg = `Halo Kebun Ndesa, saya ingin paket bundling: ${names||'(belum dipilih)'}. Total ${label}/orang. Mohon konfirmasi ketersediaan.`;
  document.getElementById('bundlingWA').href = `https://wa.me/${WA_NUM}?text=${encodeURIComponent(waMsg)}`;
}


function renderTestiGrid(gid) {
  const g = document.getElementById(gid);
  if (!g) return;
  const stars = n => '★'.repeat(n) + '☆'.repeat(5 - n);
  g.innerHTML = DB.testi.map(t => `
    <div class="testi-card">
      <div class="testi-stars">${stars(t.rating)}</div>
      <div class="testi-text">"${t.komentar}"</div>
      <div class="testi-author">${t.nama}</div>
      <div class="testi-loc">${t.asal}</div>
    </div>`).join('');
}


function renderEventGrid() {
  const g = document.getElementById('event-grid');
  if (!g) return;
  if (!DB.event.length) {
    g.innerHTML = `<div style="grid-column:span 2;text-align:center;padding:72px;color:var(--c-muted)">
      <div style="font-size:60px;opacity:.3;margin-bottom:18px">📭</div>
      <p style="font-size:15px;font-weight:300">Belum ada event saat ini.</p></div>`;
    return;
  }
  g.innerHTML = DB.event.map(e => {
    const p = e.tanggal.split(' ');
    const badgeCls = e.status === 'upcoming' ? 'ev-upcoming' : e.status === 'hot' ? 'ev-hot' : 'ev-past';
    const badgeTxt = e.status === 'upcoming' ? 'Akan Datang' : e.status === 'hot' ? '🔥 Hot' : 'Selesai';
    return `<div class="event-card">
      <div class="event-date-col">
        <div class="ev-day">${p[0]||''}</div>
        <div class="ev-mon">${(p[1]||'').substring(0,3)}</div>
      </div>
      <div class="event-info">
        <div class="ev-tag">${e.kategori} &nbsp;<span class="ev-badge ${badgeCls}">${badgeTxt}</span></div>
        <h3>${e.judul}</h3>
        <p>${e.deskripsi}</p>
        <div class="event-meta"><span>📍 ${e.lokasi}</span><span>🕐 ${e.waktu}</span></div>
      </div>
    </div>`;
  }).join('');
}


const faqs = [
  {q:'Apakah bisa pesan tiket secara online?', a:'Saat ini pemesanan hanya melalui WhatsApp. Kami tidak menyediakan pemesanan online. Hubungi kami via WA di +62 812-3456-7890 untuk informasi dan reservasi.'},
  {q:'Apa saja fasilitas yang tersedia di Kebun Ndesa?', a:'Kami memiliki 4 wahana utama: Kolam Renang, Taman, Kebun Buah, dan Pemancingan. Tersedia juga musholla, toilet bersih, area parkir luas, kantin/warung makan, dan gazebo untuk beristirahat.'},
  {q:'Apakah ada harga khusus untuk rombongan?', a:'Ya! Untuk rombongan minimal 20 orang kami menyediakan diskon khusus. Hubungi kami via WhatsApp untuk penawaran paket rombongan dan paket bundling.'},
  {q:'Jam buka Kebun Ndesa?', a:'Kami buka Selasa–Jumat pukul 08.00–17.00 WITA, dan Sabtu–Minggu pukul 07.00–18.00 WITA. Kami tutup setiap hari Senin untuk perawatan.'},
  {q:'Apa saja buah yang bisa dipetik?', a:'Tergantung musim. Buah yang selalu tersedia: Pepaya dan Pisang. Buah musiman seperti Durian, Rambutan, Mangga, Nanas, Langsat, Salak, dan Jambu Air tersedia sesuai kalender musim. Cek halaman Musim Buah untuk jadwal lengkap.'},
  {q:'Apakah aman membawa anak kecil?', a:'Sangat aman! Kebun Ndesa dirancang ramah keluarga. Kolam renang dilengkapi area anak terpisah dengan kedalaman yang sesuai dan petugas pengawas. Anak di bawah 3 tahun masuk gratis.'},
  {q:'Apakah tersedia makanan di lokasi?', a:'Ya, kami memiliki warung/kantin yang menyediakan makanan dan minuman khas desa dengan harga terjangkau. Tersedia juga area untuk makan bekal sendiri di gazebo dan area piknik taman.'},
  {q:'Bagaimana cara menuju Kebun Ndesa?', a:'Kebun Ndesa berlokasi di Tanah Merah, Kalimantan Timur, sekitar 45 menit dari pusat kota Samarinda. Cek halaman Lokasi untuk panduan rute lengkap dan akses Google Maps.'},
];

function renderFAQ() {
  document.getElementById('faqList').innerHTML = faqs.map((f, i) => `
    <div class="faq-item" id="faq-${i}">
      <div class="faq-q" onclick="toggleFAQ(${i})">
        <span>${f.q}</span><span class="faq-arrow">+</span>
      </div>
      <div class="faq-a">${f.a}</div>
    </div>`).join('');
}

function toggleFAQ(i) {
  const el = document.getElementById('faq-' + i);
  el.classList.toggle('open');
}


function openAdmin()  { document.getElementById('adminOverlay').classList.add('open'); }
function closeAdmin() { document.getElementById('adminOverlay').classList.remove('open'); }

function doLogin() {
  const u = document.getElementById('loginUser').value;
  const p = document.getElementById('loginPass').value;
  if (u === 'admin' && p === 'ndesa123') {
    document.getElementById('adminLoginSection').style.display = 'none';
    document.getElementById('adminLoggedSection').style.display = 'block';
    renderAllAdminTables();
  } else {
    document.getElementById('loginError').classList.add('show');
  }
}

function doLogout() {
  document.getElementById('adminLoginSection').style.display = 'block';
  document.getElementById('adminLoggedSection').style.display = 'none';
  document.getElementById('loginUser').value = '';
  document.getElementById('loginPass').value = '';
  document.getElementById('loginError').classList.remove('show');
}

function switchTab(name, el) {
  document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  if (el) el.classList.add('active');
}

function renderAllAdminTables() {
  renderWisataTable(); renderEventTable(); renderTestiTable(); renderMusimTable();
}

function renderWisataTable() {
  document.getElementById('wisataBody').innerHTML = DB.wisata.map((w, i) => `
    <tr>
      <td>${i+1}</td>
      <td>${w.emoji} <strong style="color:var(--c-forest)">${w.nama}</strong></td>
      <td>${w.kategori}</td>
      <td style="font-weight:600;color:var(--c-forest)">Rp ${w.harga.toLocaleString('id-ID')}</td>
      <td><span class="${w.status==='aktif'?'badge-aktif':'badge-nonaktif'}">${w.status}</span></td>
      <td><div class="tbl-actions">
        <button class="tbl-btn tbl-edit" onclick="editItem('wisata',${w.id})">Edit</button>
        <button class="tbl-btn tbl-del" onclick="deleteItem('wisata',${w.id})">Hapus</button>
      </div></td>
    </tr>`).join('');
}

function renderEventTable() {
  document.getElementById('eventBody').innerHTML = DB.event.map((e, i) => `
    <tr>
      <td>${i+1}</td>
      <td><strong style="color:var(--c-forest)">${e.judul}</strong></td>
      <td>${e.tanggal}</td>
      <td>${e.kategori}</td>
      <td><span class="${e.status!=='past'?'badge-aktif':'badge-nonaktif'}">${e.status}</span></td>
      <td><div class="tbl-actions">
        <button class="tbl-btn tbl-edit" onclick="editItem('event',${e.id})">Edit</button>
        <button class="tbl-btn tbl-del" onclick="deleteItem('event',${e.id})">Hapus</button>
      </div></td>
    </tr>`).join('');
}

function renderTestiTable() {
  document.getElementById('testiBody').innerHTML = DB.testi.map((t, i) => `
    <tr>
      <td>${i+1}</td>
      <td><strong style="color:var(--c-forest)">${t.nama}</strong></td>
      <td>${t.asal}</td>
      <td style="color:var(--c-gold)">${'★'.repeat(t.rating)}</td>
      <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.komentar}</td>
      <td><div class="tbl-actions">
        <button class="tbl-btn tbl-edit" onclick="editItem('testi',${t.id})">Edit</button>
        <button class="tbl-btn tbl-del" onclick="deleteItem('testi',${t.id})">Hapus</button>
      </div></td>
    </tr>`).join('');
}

function renderMusimTable() {
  document.getElementById('musimBody').innerHTML = DB.musim.map((m, i) => `
    <tr>
      <td>${i+1}</td>
      <td><strong style="color:var(--c-forest)">${m.nama}</strong></td>
      <td>${m.mulai}</td>
      <td>${m.akhir}</td>
      <td><span class="${m.status==='aktif'?'badge-aktif':'badge-nonaktif'}">${m.status}</span></td>
      <td><div class="tbl-actions">
        <button class="tbl-btn tbl-edit" onclick="editItem('musim',${m.id})">Edit</button>
        <button class="tbl-btn tbl-del" onclick="deleteItem('musim',${m.id})">Hapus</button>
      </div></td>
    </tr>`).join('');
}


function openModal(type, data = null) {
  currentModal = type; editId = data ? data.id : null;
  const titles = {wisata:'Paket Wisata', event:'Event', testi:'Testimoni', musim:'Musim Buah'};
  document.getElementById('modalTitle').textContent = (data ? 'Edit ' : 'Tambah ') + titles[type];
  let html = '';
  if (type === 'wisata') {
    html = `
      <div class="form-group"><label>Nama Wahana</label><input id="f-nama" value="${data?data.nama:''}"/></div>      <div class="form-group"><label>Kategori</label><select id="f-kat">
        <option ${data?.kategori==='Air'?'selected':''}>Air</option>
        <option ${data?.kategori==='Rekreasi'?'selected':''}>Rekreasi</option>
        <option ${data?.kategori==='Agrowisata'?'selected':''}>Agrowisata</option>
        <option ${data?.kategori==='Edukasi'?'selected':''}>Edukasi</option>
      </select></div>
      <div class="form-group"><label>Harga Dewasa (angka)</label><input id="f-harga" type="number" value="${data?data.harga:0}"/></div>
      <div class="form-group"><label>Harga Anak (angka)</label><input id="f-harga-anak" type="number" value="${data?data.hargaAnak:0}"/></div>
      <div class="form-group"><label>Deskripsi</label><textarea id="f-desk">${data?data.deskripsi:''}</textarea></div>
      <div class="form-group"><label>Fitur (pisahkan dengan koma)</label><input id="f-fitur" value="${data?(data.fitur||[]).join(', '):''}"/></div>
      <div class="form-group"><label>Status</label><select id="f-status">
        <option value="aktif" ${data?.status==='aktif'?'selected':''}>Aktif</option>
        <option value="nonaktif" ${data?.status==='nonaktif'?'selected':''}>Nonaktif</option>
      </select></div>`;
  } else if (type === 'event') {
    html = `
      <div class="form-group"><label>Judul Event</label><input id="f-judul" value="${data?data.judul:''}"/></div>
      <div class="form-group"><label>Tanggal (contoh: 15 Jan 2025)</label><input id="f-tgl" value="${data?data.tanggal:''}"/></div>
      <div class="form-group"><label>Kategori</label><select id="f-kat">
        <option ${data?.kategori==='Festival'?'selected':''}>Festival</option>
        <option ${data?.kategori==='Workshop'?'selected':''}>Workshop</option>
        <option ${data?.kategori==='Outbound'?'selected':''}>Outbound</option>
        <option ${data?.kategori==='Pengumuman'?'selected':''}>Pengumuman</option>
        <option ${data?.kategori==='Promo'?'selected':''}>Promo</option>
      </select></div>
      <div class="form-group"><label>Deskripsi</label><textarea id="f-desk">${data?data.deskripsi:''}</textarea></div>
      <div class="form-group"><label>Lokasi</label><input id="f-lok" value="${data?data.lokasi:''}"/></div>
      <div class="form-group"><label>Waktu</label><input id="f-wkt" value="${data?data.waktu:''}"/></div>
      <div class="form-group"><label>Status</label><select id="f-status">
        <option value="upcoming" ${data?.status==='upcoming'?'selected':''}>Akan Datang</option>
        <option value="hot" ${data?.status==='hot'?'selected':''}>Hot 🔥</option>
        <option value="past" ${data?.status==='past'?'selected':''}>Selesai</option>
      </select></div>`;
  } else if (type === 'testi') {
    html = `
      <div class="form-group"><label>Nama Pengunjung</label><input id="f-nama" value="${data?data.nama:''}"/></div>
      <div class="form-group"><label>Asal Kota</label><input id="f-asal" value="${data?data.asal:''}"/></div>
      <div class="form-group"><label>Rating (1-5)</label><select id="f-rating">
        ${[5,4,3,2,1].map(n => `<option value="${n}" ${data?.rating===n?'selected':''}>${'★'.repeat(n)} (${n})</option>`).join('')}
      </select></div>
      <div class="form-group"><label>Komentar</label><textarea id="f-komentar">${data?data.komentar:''}</textarea></div>`;
  } else if (type === 'musim') {
    html = `
      <div class="form-group"><label>Nama Buah</label><input id="f-nama" value="${data?data.nama:''}"/></div>
      <div class="form-group"><label>Mulai Panen</label><select id="f-mulai">${BULAN.map(b => `<option ${data?.mulai===b?'selected':''}>${b}</option>`).join('')}</select></div>
      <div class="form-group"><label>Akhir Panen</label><select id="f-akhir">${BULAN.map(b => `<option ${data?.akhir===b?'selected':''}>${b}</option>`).join('')}</select></div>
      <div class="form-group"><label>Status</label><select id="f-status">
        <option value="aktif" ${data?.status==='aktif'?'selected':''}>Aktif</option>
        <option value="nonaktif" ${data?.status==='nonaktif'?'selected':''}>Nonaktif</option>
      </select></div>`;
  }
  document.getElementById('modalForm').innerHTML = html;
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
  editId = null; currentModal = '';
}

function saveModal() {
  const t = currentModal;
  let obj;
  if (t === 'wisata') {
    obj = {
      id: editId||nextId.wisata++,
      nama: document.getElementById('f-nama').value,
      emoji: document.getElementById('f-emoji').value,
      kategori: document.getElementById('f-kat').value,
      harga: parseInt(document.getElementById('f-harga').value)||0,
      hargaAnak: parseInt(document.getElementById('f-harga-anak').value)||0,
      deskripsi: document.getElementById('f-desk').value,
      fitur: document.getElementById('f-fitur').value.split(',').map(s=>s.trim()).filter(Boolean),
      status: document.getElementById('f-status').value
    };
    if (editId) { const i = DB.wisata.findIndex(w=>w.id===editId); DB.wisata[i] = obj; }
    else DB.wisata.push(obj);
    renderWisataTable(); renderWisataGrid(); renderBundling();
    showAlert('wisata', editId ? 'Wahana berhasil diperbarui.' : 'Wahana baru berhasil ditambahkan.');
  } else if (t === 'event') {
    obj = {
      id: editId||nextId.event++,
      judul: document.getElementById('f-judul').value,
      tanggal: document.getElementById('f-tgl').value,
      kategori: document.getElementById('f-kat').value,
      deskripsi: document.getElementById('f-desk').value,
      lokasi: document.getElementById('f-lok').value,
      waktu: document.getElementById('f-wkt').value,
      status: document.getElementById('f-status').value
    };
    if (editId) { const i = DB.event.findIndex(e=>e.id===editId); DB.event[i] = obj; }
    else DB.event.push(obj);
    renderEventTable(); renderEventGrid();
    showAlert('event', editId ? 'Event berhasil diperbarui.' : 'Event baru berhasil ditambahkan.');
  } else if (t === 'testi') {
    obj = {
      id: editId||nextId.testi++,
      nama: document.getElementById('f-nama').value,
      asal: document.getElementById('f-asal').value,
      rating: parseInt(document.getElementById('f-rating').value),
      komentar: document.getElementById('f-komentar').value
    };
    if (editId) { const i = DB.testi.findIndex(t=>t.id===editId); DB.testi[i] = obj; }
    else DB.testi.push(obj);
    renderTestiTable(); renderTestiGrid('home-testi-grid'); renderTestiGrid('wisata-testi-grid');
    showAlert('testi', editId ? 'Testimoni diperbarui.' : 'Testimoni ditambahkan.');
  } else if (t === 'musim') {
    obj = {
      id: editId||nextId.musim++,
      nama: document.getElementById('f-nama').value,
      mulai: document.getElementById('f-mulai').value,
      akhir: document.getElementById('f-akhir').value,
      status: document.getElementById('f-status').value
    };
    if (editId) { const i = DB.musim.findIndex(m=>m.id===editId); DB.musim[i] = obj; }
    else DB.musim.push(obj);
    renderMusimTable();
    showAlert('musim', editId ? 'Data musim diperbarui.' : 'Data musim ditambahkan.');
  }
  closeModal();
}

function editItem(type, id) {
  const data = DB[type].find(d => d.id === id);
  openModal(type, data);
}

function deleteItem(type, id) {
  if (!confirm('Yakin hapus data ini?')) return;
  DB[type] = DB[type].filter(d => d.id !== id);
  const renders = {
    wisata: () => { renderWisataTable(); renderWisataGrid(); renderBundling(); },
    event:  () => { renderEventTable(); renderEventGrid(); },
    testi:  () => { renderTestiTable(); renderTestiGrid('home-testi-grid'); renderTestiGrid('wisata-testi-grid'); },
    musim:  () => renderMusimTable()
  };
  if (renders[type]) renders[type]();
  showAlert(type, 'Data berhasil dihapus.');
}

function showAlert(type, msg) {
  const el = document.getElementById('alert-' + type);
  el.textContent = msg; el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3500);
}


function openMobile()  { document.getElementById('mobileMenu').classList.add('open'); }
function closeMobile() { document.getElementById('mobileMenu').classList.remove('open'); }


document.addEventListener('DOMContentLoaded', () => {
  renderWisataGrid();
  renderTestiGrid('home-testi-grid');
  renderEventGrid();
  renderFAQ();
});

document.addEventListener('DOMContentLoaded', () => {
  const dot  = document.getElementById('cursor-dot');
  const ring = document.getElementById('cursor-ring');

  if (dot && ring) {
    document.addEventListener('mousemove', (e) => {
      dot.style.left = e.clientX + 'px';
      dot.style.top  = e.clientY + 'px';
      setTimeout(() => {
        ring.style.left = e.clientX + 'px';
        ring.style.top  = e.clientY + 'px';
      }, 80);
    });

   
    document.querySelectorAll('a, button, .wisata-card, .event-item, [onclick]').forEach(el => {
      el.addEventListener('mouseenter', () => {
        dot.style.width   = '14px';
        dot.style.height  = '14px';
        ring.style.width  = '52px';
        ring.style.height = '52px';
      });
      el.addEventListener('mouseleave', () => {
        dot.style.width   = '8px';
        dot.style.height  = '8px';
        ring.style.width  = '36px';
        ring.style.height = '36px';
      });
    });
  }
});


document.addEventListener('DOMContentLoaded', () => {
  renderWisataGrid();
  renderTestiGrid('home-testi-grid');
  renderEventGrid();
  renderFAQ();
});