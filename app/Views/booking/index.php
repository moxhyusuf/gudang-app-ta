<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="breadcrumb-gt">GT-SIS · <span>Booking Material</span></div>
<div class="page-title">Booking Material</div>
<div class="page-sub mb-3">Booking material umum (kode 7xxxxxx) untuk kebutuhan bersama plant</div>

<!-- ── Tab bar (persis mockup .lap-tab) ──────────────────────────────────── -->
<div class="lap-tab-bar">
  <div class="lap-tab active" id="ltab-form"    onclick="switchLTab('form')">📝 Form Booking</div>
  <div class="lap-tab"        id="ltab-riwayat" onclick="switchLTab('riwayat')">📋 Riwayat Booking</div>
</div>

<!-- ══════════════════ FORM BOOKING ══════════════════ -->
<div id="tab-form">

  <!-- Step indicator (.steps persis mockup) -->
  <div class="steps" id="bk-steps">
    <div class="step">
      <div class="step-num active" id="sn-1">1</div>
      <div class="step-label active" id="sl-1">Pilih Material</div>
    </div>
    <div class="step-line" id="line-1"></div>
    <div class="step">
      <div class="step-num" id="sn-2">2</div>
      <div class="step-label" id="sl-2">Review Keranjang</div>
    </div>
    <div class="step-line" id="line-2"></div>
    <div class="step">
      <div class="step-num" id="sn-3">3</div>
      <div class="step-label" id="sl-3">Konfirmasi</div>
    </div>
    <div class="step-line" id="line-3"></div>
    <div class="step">
      <div class="step-num" id="sn-4">4</div>
      <div class="step-label" id="sl-4">Selesai</div>
    </div>
  </div>

  <!-- Konten step -->
  <div id="bk-content"></div>

</div>

<!-- ══════════════════ RIWAYAT BOOKING ══════════════════ -->
<div id="tab-riwayat" style="display:none">
  <div class="card-gt">
    <div class="card-header-gt">
      <span>📋 Riwayat Booking Saya</span>
      <div style="display:flex;gap:.4rem;align-items:center">
        <input type="text" id="rw-search" class="form-control-gt"
               style="max-width:170px;font-size:.78rem" placeholder="Cari no. booking..."
               oninput="filterRiwayat()">
        <select id="rw-status" class="form-select-gt"
                style="max-width:130px;font-size:.78rem" onchange="filterRiwayat()">
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="selesai">Selesai</option>
          <option value="batal">Dibatalkan</option>
          <option value="kadaluarsa">Kadaluarsa</option>
        </select>
      </div>
    </div>
    <div class="table-wrap">
      <table class="table-gt" id="tbl-riwayat">
        <thead>
          <tr>
            <th>No. Booking</th>
            <th>Tgl. Booking</th>
            <th>Item</th>
            <th>Status</th>
            <th>Tgl. Butuh</th>
            <th>Sisa Hari</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="rw-tbody">
          <?php if (empty($riwayat)): ?>
          <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:2rem">Belum ada riwayat booking</td></tr>
          <?php else: ?>
          <?php foreach ($riwayat as $r):
            $sisa = (int)$r['sisa_hari'];
            $sisaStyle = $r['status']==='pending'
              ? ($sisa<=1?'color:#c0282d;font-weight:700':'color:#b45309') : 'color:#9ca3af';
          ?>
          <tr data-no="<?= esc($r['no_booking']) ?>" data-status="<?= esc($r['status']) ?>">
            <td><span class="mono" style="font-size:.75rem;color:var(--navy3)"><?= esc($r['no_booking']) ?></span></td>
            <td><?= esc($r['tanggal_booking']) ?></td>
            <td><?= (int)$r['jml_item'] ?> item</td>
            <td><?= bkBadge($r['status']) ?></td>
            <td><?= esc($r['tanggal_butuh'] ?? '—') ?></td>
            <td style="<?= $sisaStyle ?>">
              <?= $r['status']==='pending' ? ($sisa>0?$sisa.' hari':'Hari ini!') : '—' ?>
            </td>
            <td><button class="btn-sm-g" onclick="showRwDetail(<?= (int)$r['id'] ?>)">Detail</button></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($total_page > 1): ?>
    <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.8rem 1rem;border-top:1px solid var(--gray200)">
      <?php if ($current_page>1): ?>
        <a href="/booking?page=<?= $current_page-1 ?>" class="btn-outline">← Prev</a>
      <?php else: ?>
        <button class="btn-outline" disabled style="opacity:.4">← Prev</button>
      <?php endif; ?>
      <span style="font-size:.82rem;color:#6b7280">Hal <strong><?= $current_page ?></strong> / <?= $total_page ?></span>
      <?php if ($current_page<$total_page): ?>
        <a href="/booking?page=<?= $current_page+1 ?>" class="btn-outline">Next →</a>
      <?php else: ?>
        <button class="btn-outline" disabled style="opacity:.4">Next →</button>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══════════════════ MODAL DETAIL RIWAYAT ══════════════════ -->
<div id="modal-rw" style="display:none;position:fixed;inset:0;background:rgba(15,32,68,.55);z-index:5000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:620px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)">
    <div class="modal-head">
      <h6 id="modal-rw-title">Detail Booking</h6>
      <button onclick="document.getElementById('modal-rw').style.display='none'"
              style="background:none;border:none;color:rgba(255,255,255,.6);font-size:1.2rem;cursor:pointer">✕</button>
    </div>
    <div style="padding:1.3rem" id="modal-rw-body">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
  </div>
</div>

<!-- ══════════════════ PASS DATA KE JS ══════════════════ -->
<script>
UnsavedGuard.watch('#tab-form', 'Data booking (tanggal, catatan, atau keranjang) yang sedang diisi belum disimpan. Yakin ingin pindah halaman?');
var BK = {
  step      : 1,
  noBooking : '<?= esc($no_booking) ?>',
  role      : '<?= esc($role) ?>',
  nama      : '<?= esc($nama) ?>',
  plants    : <?= json_encode(array_values($plants)) ?>,
  // Plant otomatis dari session user yang login
  userPlantId   : '<?= esc($user_plant_id ?? '') ?>',
  userPlantNama : '<?= esc($user_plant_nama ?? '') ?>',
  header    : {
    plant_id      : '<?= esc($user_plant_id ?? '') ?>',  // langsung terisi
    tanggal_butuh : '',
    catatan       : '',
  },
  cart      : [],
  searchRes : [],
  debounce  : null,
  searchQ   : '',
};

/* ── Helper ─────────────────────────────────────────────────────────────── */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function badgeSt(s){
  var m={
    pending    :'<span class="badge-gt badge-pending">Pending</span>',
    selesai    :'<span class="badge-gt badge-selesai">Selesai</span>',
    batal      :'<span class="badge-gt badge-ditolak">Dibatalkan</span>',
    kadaluarsa :'<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>',
  };
  return m[s]||'<span class="badge-gt badge-umum">'+esc(s)+'</span>';
}

/* ── Tab switch ─────────────────────────────────────────────────────────── */
function switchLTab(t){
  document.getElementById('tab-form').style.display    = t==='form'    ? 'block':'none';
  document.getElementById('tab-riwayat').style.display = t==='riwayat' ? 'block':'none';
  document.getElementById('ltab-form').classList.toggle('active',    t==='form');
  document.getElementById('ltab-riwayat').classList.toggle('active', t==='riwayat');
}

/* ── Step indicator ─────────────────────────────────────────────────────── */
function updateSteps(n){
  [1,2,3,4].forEach(function(i){
    var sn=document.getElementById('sn-'+i);
    var sl=document.getElementById('sl-'+i);
    if(!sn) return;
    sn.className = 'step-num'+(n>i?' done':n===i?' active':'');
    sn.textContent = n>i?'✓':String(i);
    sl.className = 'step-label'+(n>=i?' active':'');
  });
  [1,2,3].forEach(function(i){
    var ln=document.getElementById('line-'+i);
    if(ln) ln.className='step-line'+(n>i?' done':'');
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   STEP 1 — Pilih Material + Keranjang inline (persis mockup)
   Re-render seluruh konten step 1 setiap ada perubahan keranjang,
   sama persis dengan cara mockup: renderPage('booking') tiap bkAddToCart.
   ══════════════════════════════════════════════════════════════════════════ */
function renderStep1(){
  updateSteps(1);

  /* ── Header form ── */
  var hForm =
    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-bottom:1rem">'+
      '<div>'+
        '<label class="form-label-gt">Tgl. Dibutuhkan *</label>'+
        '<input type="date" class="form-control-gt" id="bk-tgl"'+
        ' value="'+(BK.header.tanggal_butuh||'')+'"'+
        ' min="'+minDate()+'"'+
        ' onchange="BK.header.tanggal_butuh=this.value">'+
      '</div>'+
      '<div>'+
        '<label class="form-label-gt">Catatan</label>'+
        '<input class="form-control-gt" id="bk-catatan"'+
        ' placeholder="Keperluan / keterangan..."'+
        ' value="'+esc(BK.header.catatan)+'"'+
        ' onchange="BK.header.catatan=this.value">'+
      '</div>'+
    '</div>';

  /* ── Filter / search bar ── */
  var filterBar =
    '<div class="filter-bar">'+
      '<div>'+
        '<label class="form-label-gt">Cari Material</label>'+
        '<div class="input-group-gt">'+
          '<input type="text" class="form-control-gt" id="bk-search"'+
          ' placeholder="Kode SAP / nama material..."'+
          ' value="'+esc(BK.searchQ)+'"'+
          ' oninput="onSearchInput()" onkeydown="if(event.key===\'Enter\')doSearch()">'+
          '<button class="btn-navy" onclick="doSearch()">Cari</button>'+
        '</div>'+
      '</div>'+
      '<div style="align-self:flex-end">'+
        '<span id="bk-spin" style="display:none;font-size:.78rem;color:#6b7280">Mencari...</span>'+
      '</div>'+
    '</div>'+
    '<p style="font-size:.72rem;color:#9ca3af;margin:.2rem 0 .8rem">'+
      '💡 Ketik kode SAP atau nama. Hanya material kode <strong>7xxxxxx</strong> yang dapat di-booking.'+
    '</p>';

  /* ── Tabel material ── */
  var tabelMat = '';
  if (BK.searchRes.length) {
    var rows = BK.searchRes.map(function(m){
      var bookable  = /^7/.test(m.kode_sap);
      var inCart    = BK.cart.some(function(c){ return parseInt(c.material_id)===parseInt(m.id); });
      var habis     = parseInt(m.stok_tersedia) <= 0;
      var stokColor = habis ? 'color:#c0282d' : 'color:var(--green)';

      var aksiCol = '';
      if (!bookable) {
        aksiCol = '<button class="btn-sm-g" style="font-size:.7rem" onclick="showPesanSAP(\''+esc(m.kode_sap)+'\')">ⓘ Lihat Info</button>';
      } else if (inCart) {
        // Sudah di keranjang — tampil di bawah, disable tombol
        aksiCol = '<span style="font-size:.75rem;color:var(--green);font-weight:600">✓ Di Keranjang</span>';
      } else if (habis) {
        aksiCol = '<span class="badge-gt badge-habis">Stok Habis</span>';
      } else {
        aksiCol =
          '<div style="display:flex;align-items:center;gap:4px">'+
            '<input type="number" id="bk-qty-'+parseInt(m.id)+'" min="1" max="'+parseInt(m.stok_tersedia)+'" value="1"'+
            ' style="width:56px;border:1px solid var(--gray200);border-radius:6px;padding:3px 6px;font-size:.8rem">'+
            '<button class="btn-sm-g" onclick="bkAddToCart('+m.id+')">+ Keranjang</button>'+
          '</div>';
      }

      var rowBg = inCart ? 'background:#f0fdf4' : '';

      return '<tr style="'+rowBg+'">'+
        '<td><span class="mono" style="font-size:.75rem;color:var(--navy3)">'+esc(m.kode_sap)+'</span>'+
            (bookable ? '<br><span class="badge-gt badge-aktif" style="font-size:.6rem">UMUM</span>' : '')+
        '</td>'+
        '<td style="font-weight:600">'+esc(m.nama_material)+'</td>'+
        '<td><strong style="'+stokColor+'">'+parseInt(m.stok_tersedia)+'</strong> '+esc(m.satuan)+'</td>'+
        '<td style="color:var(--amber)">'+parseInt(m.stok_booking)+'</td>'+
        '<td>'+aksiCol+'</td>'+
      '</tr>';
    }).join('');

    tabelMat =
      '<div class="card-gt">'+
        '<div class="card-header-gt">'+
          '<span>📊 Pilih Material</span>'+
          '<span style="color:rgba(255,255,255,.6);font-size:.73rem">'+BK.searchRes.length+' material ditemukan</span>'+
        '</div>'+
        '<div class="table-wrap"><table class="table-gt">'+
          '<thead><tr>'+
            '<th>Kode SAP</th><th>Nama Material</th>'+
            '<th>Tersedia</th><th>Booking</th><th>Tambah</th>'+
          '</tr></thead>'+
          '<tbody>'+rows+'</tbody>'+
        '</table></div>'+
      '</div>';
  }

  /* ── Keranjang inline (selalu tampil jika ada item) ── */
  var tabelKeranjang = '';
  if (BK.cart.length) {
    var kRows = BK.cart.map(function(item, i){
      return '<tr>'+
        '<td><span class="mono" style="font-size:.75rem;color:var(--navy3)">'+esc(item.kode_sap)+'</span></td>'+
        '<td style="font-weight:600">'+esc(item.nama)+'</td>'+
        '<td><strong>'+item.jumlah+'</strong> '+esc(item.satuan)+
          ' <span style="color:#9ca3af;font-size:.72rem">(tersedia: '+item.stok_tersedia+')</span></td>'+
        '<td>'+
          '<button class="cart-remove" onclick="bkRemoveCart('+i+')" title="Hapus dari keranjang">🗑</button>'+
        '</td>'+
      '</tr>';
    }).join('');

    tabelKeranjang =
      '<div class="card-gt" style="margin-top:.8rem">'+
        '<div class="card-header-gt">'+
          '<span>🛒 Keranjang — <strong style="color:#fde68a">'+BK.cart.length+' material</strong></span>'+
          '<button class="btn-sm-g" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;font-size:.72rem" onclick="bkResetCart()">Kosongkan</button>'+
        '</div>'+
        '<div class="table-wrap"><table class="table-gt">'+
          '<thead><tr><th>Kode SAP</th><th>Nama</th><th>Jumlah</th><th>Hapus</th></tr></thead>'+
          '<tbody>'+kRows+'</tbody>'+
        '</table></div>'+
        '<div style="padding:.8rem 1rem;border-top:1px solid var(--gray200);text-align:right">'+
          '<button class="btn-navy" onclick="goStep(2)">Lanjut → Konfirmasi</button>'+
        '</div>'+
      '</div>';
  }

  /* ── Notif SAP placeholder ── */
  var notifArea = '<div id="bk-notif-sap-area"></div>';

  document.getElementById('bk-content').innerHTML =
    hForm + filterBar + notifArea + tabelMat + tabelKeranjang;
}

/* ── Search ─────────────────────────────────────────────────────────────── */
function onSearchInput(){
  BK.searchQ = document.getElementById('bk-search').value;
  clearTimeout(BK.debounce);
  if (BK.searchQ.length < 2) return;
  BK.debounce = setTimeout(doSearch, 400);
}

function doSearch(){
  var q = document.getElementById('bk-search') ? document.getElementById('bk-search').value : BK.searchQ;
  if (!q.trim()) return;
  BK.searchQ = q;
  var spin = document.getElementById('bk-spin');
  if (spin) spin.style.display = 'inline';

  fetch('/booking/search-material?q='+encodeURIComponent(q),{
    headers:{'X-Requested-With':'XMLHttpRequest'}
  })
  .then(function(r){ return r.json(); })
  .then(function(res){
    BK.searchRes = res.materials || [];
    renderStep1();  // re-render seluruh step 1 seperti mockup
  })
  .catch(function(){
    var spin = document.getElementById('bk-spin');
    if (spin) spin.style.display = 'none';
  });
}

/* ── Tambah ke keranjang — persis mockup bkAddToCart ────────────────────── */
function bkAddToCart(matId){
  matId = parseInt(matId);  // pastikan selalu number, bukan string
  // Simpan nilai input form sebelum re-render
  var tglEl = document.getElementById('bk-tgl');
  var catEl = document.getElementById('bk-catatan');
  if (tglEl) BK.header.tanggal_butuh = tglEl.value;
  if (catEl) BK.header.catatan       = catEl.value;

  var mat = BK.searchRes.find(function(m){ return parseInt(m.id)===parseInt(matId); });
  if (!mat) return;

  var qtyEl = document.getElementById('bk-qty-'+matId);
  var qty   = parseInt(qtyEl ? qtyEl.value : 1) || 1;
  var av    = parseInt(mat.stok_tersedia);

  if (qty < 1 || qty > av) { alert('Jumlah tidak valid! Tersedia: '+av); return; }

  var existing = BK.cart.find(function(c){ return c.material_id===matId; });
  if (existing) {
    existing.jumlah = qty;
  } else {
    BK.cart.push({
      material_id   : matId,
      kode_sap      : mat.kode_sap,
      nama          : mat.nama_material,
      satuan        : mat.satuan,
      jumlah        : qty,
      stok_tersedia : av,
    });
  }

  // Re-render step 1 (persis cara mockup) — keranjang otomatis muncul di bawah
  UnsavedGuard.markDirty();
  renderStep1();
}

function bkRemoveCart(i){
  // Simpan nilai form
  var tglEl = document.getElementById('bk-tgl');
  var catEl = document.getElementById('bk-catatan');
  if (tglEl) BK.header.tanggal_butuh = tglEl.value;
  if (catEl) BK.header.catatan       = catEl.value;

  BK.cart.splice(i, 1);
  renderStep1();
}

function bkResetCart(){
  if (!confirm('Kosongkan semua item dari keranjang?')) return;
  var tglEl = document.getElementById('bk-tgl');
  var catEl = document.getElementById('bk-catatan');
  if (tglEl) BK.header.tanggal_butuh = tglEl.value;
  if (catEl) BK.header.catatan       = catEl.value;
  BK.cart = [];
  renderStep1();
}

/* ── Pesan SAP ──────────────────────────────────────────────────────────── */
function showPesanSAP(kode){
  var area = document.getElementById('bk-notif-sap-area');
  if (!area) return;
  area.innerHTML =
    '<div class="bk-notif-sap">'+
      '<strong>⚠️ '+esc(kode)+' tidak dapat di-booking</strong><br>'+
      'Booking hanya untuk material umum (kode 7xxxxxx). '+
      'Selain barang umum, silahkan lakukan proses tersebut pada <strong>SAP</strong>.'+
      '<button onclick="document.getElementById(\'bk-notif-sap-area\').innerHTML=\'\'"'+
      ' style="background:none;border:none;float:right;cursor:pointer;font-size:1rem;color:#991b1b;margin-top:-2px">✕</button>'+
    '</div>';
}

/* ══════════════════════════════════════════════════════════════════════════
   STEP 2 — Konfirmasi (step 2 di sini = step 3 lama, karena review sudah inline)
   ══════════════════════════════════════════════════════════════════════════ */
function renderStep2(){
  updateSteps(3);  // tampilkan step indicator di posisi 3 "Konfirmasi"

  var rows = BK.cart.map(function(item){
    return '<tr>'+
      '<td><span class="mono" style="font-size:.75rem;color:var(--navy3)">'+esc(item.kode_sap)+'</span></td>'+
      '<td style="font-weight:600">'+esc(item.nama)+'</td>'+
      '<td><strong>'+item.jumlah+'</strong> '+esc(item.satuan)+'</td>'+
    '</tr>';
  }).join('');

  document.getElementById('bk-content').innerHTML =
    '<div class="card-gt">'+
      '<div class="card-header-gt">Konfirmasi Booking</div>'+
      '<div style="padding:1.2rem">'+
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-bottom:1rem">'+
          '<div style="background:var(--gray50);border-radius:8px;padding:.8rem">'+
            '<div class="form-label-gt">Plant Pemohon</div>'+
            '<div style="font-weight:700;color:var(--navy);margin-top:4px;font-size:.95rem">'+
              esc(BK.userPlantNama||'—')+
            '</div>'+
            '<input type="hidden" id="bk-plant-sel" value="'+esc(BK.userPlantId)+'">'+
          '</div>'+
          '<div style="background:var(--gray50);border-radius:8px;padding:.8rem">'+
            '<div class="form-label-gt">Tanggal Dibutuhkan</div>'+
            '<div style="font-weight:700;color:var(--navy);margin-top:4px">'+(BK.header.tanggal_butuh||'—')+'</div>'+
          '</div>'+
          '<div style="background:var(--gray50);border-radius:8px;padding:.8rem">'+
            '<div class="form-label-gt">No. Booking (otomatis)</div>'+
            '<div style="font-weight:700;color:var(--green);font-family:monospace;margin-top:4px">'+esc(BK.noBooking)+'</div>'+
          '</div>'+
          '<div style="background:var(--gray50);border-radius:8px;padding:.8rem">'+
            '<div class="form-label-gt">Catatan</div>'+
            '<div style="font-size:.85rem;margin-top:4px">'+esc(BK.header.catatan||'—')+'</div>'+
          '</div>'+
        '</div>'+
        '<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:.6rem .9rem;font-size:.78rem;color:#92400e;margin-bottom:.8rem">'+
          '⏱️ Booking berlaku <strong>3 hari</strong> sejak submit. Jika tidak diambil, stok otomatis dikembalikan.'+
        '</div>'+
        '<table class="table-gt">'+
          '<thead><tr><th>Kode SAP</th><th>Material</th><th>Jumlah</th></tr></thead>'+
          '<tbody>'+rows+'</tbody>'+
        '</table>'+
      '</div>'+
    '</div>'+
    '<div style="margin-top:.8rem;display:flex;justify-content:space-between">'+
      '<button class="btn-outline" onclick="goStep(1)">← Kembali</button>'+
      '<button class="btn-green"   onclick="bkSubmit()">✓ Submit Booking</button>'+
    '</div>';
}

/* ══════════════════════════════════════════════════════════════════════════
   STEP 4 — Selesai
   ══════════════════════════════════════════════════════════════════════════ */
function renderStep4(noBooking){
  updateSteps(4);
  document.getElementById('bk-content').innerHTML =
    '<div class="card-gt" style="text-align:center;padding:2.5rem">'+
      '<div style="font-size:3rem;margin-bottom:.5rem">✅</div>'+
      '<div style="font-size:1.3rem;font-weight:800;color:var(--navy);margin-bottom:.3rem">Booking Berhasil!</div>'+
      '<div style="color:#6b7280;margin-bottom:1rem">Nomor booking Anda sebagai referensi:</div>'+
      '<div style="font-size:1.5rem;font-weight:700;font-family:monospace;color:var(--green);'+
           'border:2px solid var(--green);border-radius:8px;display:inline-block;'+
           'padding:.4rem 1.2rem;margin-bottom:1rem">'+esc(noBooking)+'</div>'+
      '<div style="color:#9ca3af;font-size:.8rem;margin-bottom:1.5rem">'+
        'Status booking dapat dilihat di tab <strong>Riwayat Booking</strong>.'+
      '</div>'+
      '<button class="btn-navy" onclick="resetBooking()">+ Booking Baru</button>'+
    '</div>';

  // PERBAIKAN: Refresh riwayat tanpa reload halaman penuh
  loadRiwayat();
}

/* ── Load riwayat via AJAX (ditambahkan untuk fix refresh) ─────────────── */
function loadRiwayat(){
  fetch('/booking/riwayat-ajax', {headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(function(r){ return r.json(); })
  .then(function(res){
    if (!res.html) return;
    var tbody = document.querySelector('#tbl-riwayat tbody');
    if (tbody) tbody.innerHTML = res.html;
  })
  .catch(function(){}); // silent fail, riwayat tetap tampil saat reload manual
}

/* ── Navigasi step ──────────────────────────────────────────────────────── */
function goStep(n){
  if (n===1){
    renderStep1();
  } else if (n===2){
    // Simpan nilai form dulu
    var tglEl=document.getElementById('bk-tgl');
    var catEl=document.getElementById('bk-catatan');
    if (tglEl) BK.header.tanggal_butuh=tglEl.value;
    if (catEl) BK.header.catatan=catEl.value;
    if (!BK.cart.length){ alert('Keranjang masih kosong!'); return; }
    if (!BK.header.tanggal_butuh){ alert('Tanggal dibutuhkan wajib diisi!'); return; }
    renderStep2();
  }
}

function resetBooking(){
  BK.step=1; BK.cart=[]; BK.searchRes=[];
  BK.header={plant_id:BK.userPlantId,tanggal_butuh:'',catatan:''};
  BK.searchQ='';
  renderStep1();
}

/* ── Submit ─────────────────────────────────────────────────────────────── */
function bkSubmit(){
  // Plant otomatis dari session — tidak perlu dipilih manual
  BK.header.plant_id = BK.userPlantId;
  if (!BK.header.plant_id) { alert('Data plant tidak ditemukan. Silahkan logout dan login kembali.'); return; }
  if (!BK.header.tanggal_butuh) { alert('Tanggal dibutuhkan wajib diisi!'); return; }
  if (!BK.cart.length)          { alert('Keranjang kosong!'); return; }

  var btn=event.target;
  btn.disabled=true; btn.textContent='⏳ Menyimpan...';

  var items=BK.cart.map(function(c){
    return { material_id:c.material_id, jumlah_booking:c.jumlah, nama_material:c.nama };
  });

  fetch('/booking/simpan',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body:JSON.stringify({header:BK.header, items:items}),
  })
  .then(function(r){ return r.json(); })
  .then(function(res){
    if (res.success){
      UnsavedGuard.markClean();
      renderStep4(res.no_booking);
    } else {
      btn.disabled=false; btn.textContent='✓ Submit Booking';
      alert('Gagal: '+(res.message||'Terjadi kesalahan.'));
    }
  })
  .catch(function(){
    btn.disabled=false; btn.textContent='✓ Submit Booking';
    alert('Gagal menghubungi server.');
  });
}

/* ── Detail riwayat ─────────────────────────────────────────────────────── */
function showRwDetail(id){
  document.getElementById('modal-rw-title').textContent='Detail Booking';
  document.getElementById('modal-rw-body').innerHTML=
    '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
  document.getElementById('modal-rw').style.display='flex';

  fetch('/booking/detail/'+id,{headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(function(r){ return r.json(); })
  .then(function(res){
    if (res.error){
      document.getElementById('modal-rw-body').innerHTML=
        '<div style="color:#c0282d;padding:1rem">'+esc(res.error)+'</div>';
      return;
    }
    var h=res.header;
    document.getElementById('modal-rw-title').textContent='Detail '+h.no_booking;

    var rows=res.detail.map(function(d){
      return '<tr>'+
        '<td><span class="mono" style="font-size:.73rem;color:var(--navy3)">'+esc(d.kode_sap||'—')+'</span></td>'+
        '<td style="font-weight:600">'+esc(d.nama_material||'—')+'</td>'+
        '<td>'+d.jumlah_booking+' '+esc(d.satuan||'')+'</td>'+
        '<td><strong style="color:var(--green)">'+d.stok_tersedia+'</strong></td>'+
      '</tr>';
    }).join('');

    var aksi='';
    if (h.status==='pending' && '<?= esc($role) ?>'!=='plant'){
      aksi=
        '<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;'+
        'padding:.7rem .9rem;margin-top:.8rem;display:flex;align-items:center;'+
        'justify-content:space-between;flex-wrap:wrap;gap:.5rem">'+
          '<small style="color:#92400e">⚠️ Pembatalan hanya pada kondisi urgent. '+
          'Pastikan sudah konfirmasi plant via telepon terlebih dahulu.</small>'+
          '<div style="display:flex;gap:.4rem">'+
            '<button class="btn-green" onclick="aksiSelesai('+h.id+')">✅ Selesai</button>'+
            '<button class="btn-red"   onclick="aksiTolak('+h.id+')">✕ Batalkan</button>'+
          '</div>'+
        '</div>';
    }

    document.getElementById('modal-rw-body').innerHTML=
      '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem;'+
      'background:var(--gray50);border-radius:8px;padding:.8rem 1rem;margin-bottom:.8rem;font-size:.82rem">'+
        '<div><span style="font-size:.7rem;color:#6b7280;display:block">No. Booking</span>'+
             '<span class="mono" style="font-weight:700">'+esc(h.no_booking)+'</span></div>'+
        '<div><span style="font-size:.7rem;color:#6b7280;display:block">Status</span>'+badgeSt(h.status)+'</div>'+
        '<div><span style="font-size:.7rem;color:#6b7280;display:block">Plant</span>'+
             '<strong>'+esc(h.nama_plant||'—')+'</strong></div>'+
        '<div><span style="font-size:.7rem;color:#6b7280;display:block">Diajukan</span>'+
             esc(h.nama_user||'—')+'</div>'+
        '<div><span style="font-size:.7rem;color:#6b7280;display:block">Tgl. Booking</span>'+
             esc(h.tanggal_booking)+'</div>'+
        '<div><span style="font-size:.7rem;color:#6b7280;display:block">Tgl. Butuh</span>'+
             esc(h.tanggal_butuh||'—')+'</div>'+
        (h.catatan?'<div style="grid-column:1/-1"><span style="font-size:.7rem;color:#6b7280;display:block">Catatan</span>'+
             '<em>'+esc(h.catatan)+'</em></div>':'')+
      '</div>'+
      '<div class="table-wrap"><table class="table-gt">'+
        '<thead><tr><th>Kode SAP</th><th>Material</th><th>Jumlah</th><th>Stok Tersedia</th></tr></thead>'+
        '<tbody>'+(rows||'<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:1rem">—</td></tr>')+'</tbody>'+
      '</table></div>'+
      aksi;
  });
}

document.getElementById('modal-rw').addEventListener('click',function(e){
  if(e.target===this) this.style.display='none';
});

/* ── Aksi selesai / batal ───────────────────────────────────────────────── */
function aksiSelesai(id){
  if(!confirm('Tandai booking ini SELESAI?\nStok booking akan dikurangi (dikembalikan ke stok tersedia).')) return;
  fetch('/booking/selesai/'+id,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(function(r){ return r.json(); })
  .then(function(res){
    if(res.success){showAlert('Booking ditandai selesai!').then(function(){location.reload();});}
    else alert('Gagal: '+(res.message||''));
  });
}
function aksiTolak(id){
  var alasan=prompt('Alasan pembatalan (wajib):\n\n⚠️ Hanya untuk kondisi urgent.\nPastikan sudah konfirmasi via telepon.');
  if(alasan===null) return;
  if(!alasan.trim()){alert('Alasan wajib diisi!'); return;}
  if(!confirm('BATALKAN booking ini? Stok akan dikembalikan.')) return;
  fetch('/booking/batal/'+id,{
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body:JSON.stringify({alasan:alasan}),
  })
  .then(function(r){ return r.json(); })
  .then(function(res){
    if(res.success){showAlert('Booking dibatalkan.').then(function(){location.reload();});}
    else alert('Gagal: '+(res.message||''));
  });
}

/* ── Filter riwayat ─────────────────────────────────────────────────────── */
function filterRiwayat(){
  var q =(document.getElementById('rw-search').value||'').toLowerCase();
  var st=document.getElementById('rw-status').value;
  document.querySelectorAll('#rw-tbody tr[data-no]').forEach(function(tr){
    var ok=(!q||tr.dataset.no.toLowerCase().includes(q))&&(!st||tr.dataset.status===st);
    tr.style.display=ok?'':'none';
  });
}

/* ── Helper ─────────────────────────────────────────────────────────────── */
function minDate(){ var d=new Date(); d.setDate(d.getDate()+1); return d.toISOString().slice(0,10); }

/* ── Init ───────────────────────────────────────────────────────────────── */
renderStep1();
</script>


<!-- ── Style: class dari mockup yang belum ada di layout project ini ── -->
<style>
/* ── CSS variables yang tidak ada di layout/main.php project ini ── */
:root {
  --green   : #1a7f4b;
  --amber   : #b45309;
  --gray50  : #f8f9fc;
  --gray200 : #e2e8f0;
  --navy3   : #243460;
}

/* Dari mockup persis */
.breadcrumb-gt{font-size:.75rem;color:#9ca3af;margin-bottom:.3rem}
.breadcrumb-gt span{color:var(--navy);font-weight:600}
.page-title{font-size:1.4rem;font-weight:800;color:var(--navy);margin:0}
.page-sub{font-size:.8rem;color:#6b7280;margin-top:3px}
.lap-tab-bar{display:flex;gap:0;border-bottom:2px solid var(--border,#dde2ef);margin-bottom:1rem;overflow-x:auto}
.lap-tab{padding:.5rem 1.1rem;font-size:.82rem;font-weight:600;cursor:pointer;color:#6b7280;border-bottom:3px solid transparent;margin-bottom:-2px;white-space:nowrap;transition:.2s}
.lap-tab.active{color:var(--navy);border-bottom-color:var(--clay,#a05a42)}
.lap-tab:hover{color:var(--navy)}
/* Steps */
.steps{display:flex;align-items:center;margin-bottom:1.5rem}
.step{display:flex;align-items:center;gap:6px}
.step-num{width:28px;height:28px;border-radius:50%;border:2px solid #dde2ef;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#9ca3af;flex-shrink:0;transition:.3s}
.step-num.active{background:var(--navy);border-color:var(--navy);color:#fff}
.step-num.done{background:#1a7f4b;border-color:#1a7f4b;color:#fff}
.step-label{font-size:.72rem;color:#9ca3af;font-weight:600;white-space:nowrap}
.step-label.active{color:var(--navy)}
.step-line{flex:1;height:2px;background:#dde2ef;margin:0 8px;transition:.3s}
.step-line.done{background:#1a7f4b}
/* Card & Table */
.card-gt{background:#fff;border-radius:12px;border:1px solid var(--border,#dde2ef);overflow:hidden;margin-bottom:.8rem}
.card-header-gt{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:space-between;gap:8px}
.table-wrap{overflow-x:auto;border-radius:0 0 10px 10px}
.table-gt{width:100%;border-collapse:separate;border-spacing:0;font-size:.82rem}
.table-gt thead th{background:#f0f2f8;color:var(--navy);font-weight:700;padding:.6rem .8rem;border-bottom:2px solid #dde2ef;white-space:nowrap;font-size:.76rem;letter-spacing:.03em;text-transform:uppercase}
.table-gt tbody td{padding:.55rem .8rem;border-bottom:1px solid #f0f2f8;vertical-align:middle;color:#374151}
.table-gt tbody tr:hover{background:#f8f9fc}
.table-gt tbody tr:last-child td{border-bottom:none}
/* Filter bar */
.filter-bar{background:#f0f2f8;border-radius:10px;padding:.8rem 1rem;margin-bottom:.8rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
/* Form */
.form-label-gt{font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:4px;letter-spacing:.02em;display:block}
.form-control-gt,.form-select-gt{width:100%;border:1.5px solid #dde2ef;border-radius:8px;padding:.5rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;transition:.2s;outline:none;box-sizing:border-box}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(15,32,68,.08)}
.input-group-gt{display:flex;gap:0}
.input-group-gt .form-control-gt{border-radius:8px 0 0 8px;flex:1}
.input-group-gt .btn-navy{border-radius:0 8px 8px 0}
/* Buttons */
.btn-navy{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.45rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.btn-navy:hover{background:#1a3260}
.btn-green{background:#1a7f4b;color:#fff;border:none;border-radius:8px;padding:.45rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.btn-green:hover{background:#15693d}
.btn-red{background:#c0282d;color:#fff;border:none;border-radius:8px;padding:.45rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.btn-red:hover{background:#a82024}
.btn-outline{background:transparent;color:var(--navy);border:1.5px solid #dde2ef;border-radius:8px;padding:.4rem .9rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;text-decoration:none;transition:.2s}
.btn-outline:hover{border-color:var(--navy);background:#f8f9fc}
.btn-outline[disabled]{opacity:.4;cursor:not-allowed}
.btn-sm-g{background:transparent;border:1.5px solid #dde2ef;border-radius:6px;padding:.25rem .6rem;font-size:.75rem;font-weight:600;cursor:pointer;color:#374151;transition:.2s}
.btn-sm-g:hover{background:#f0f2f8}
/* Badge */
.badge-gt{display:inline-flex;align-items:center;gap:4px;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700;letter-spacing:.02em}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-selesai{background:#e0f2fe;color:#0c4a6e}
.badge-ditolak{background:#fee2e2;color:#991b1b}
.badge-kadaluarsa{background:#f3f4f6;color:#6b7280}
.badge-aktif{background:#dbeafe;color:#1d4ed8}
.badge-normal{background:#d1fae5;color:#065f46}
.badge-habis{background:#fee2e2;color:#991b1b}
.badge-umum{background:#f3f4f6;color:#4b5563}
/* Cart remove */
.cart-remove{background:none;border:none;color:#9ca3af;cursor:pointer;font-size:1.1rem;padding:0 4px;transition:.2s}
.cart-remove:hover{color:#c0282d}
/* Modal */
.modal-head{background:var(--navy);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem}
/* Utilities */
.mb-3{margin-bottom:1rem}
.bk-notif-sap{background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.7rem .9rem;font-size:.82rem;color:#991b1b;line-height:1.6;margin-bottom:.6rem;overflow:hidden}
.mono{font-family:monospace}
@media(max-width:640px){
  .steps{flex-wrap:wrap;gap:.4rem}
  .step-line{display:none}
  .filter-bar { flex-direction:column; align-items:stretch !important; }
  .filter-bar .filter-field { width:100%; align-self:stretch !important; }
  .filter-field input, .filter-field select { width:100%; min-width:0 !important; }
}
</style>

<?php
function bkBadge($s){
  $m=['pending'=>'<span class="badge-gt badge-pending">Pending</span>',
      'selesai'=>'<span class="badge-gt badge-selesai">Selesai</span>',
      'batal'=>'<span class="badge-gt badge-ditolak">Dibatalkan</span>',
      'kadaluarsa'=>'<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>'];
  return $m[$s] ?? '<span class="badge-gt badge-umum">'.htmlspecialchars($s).'</span>';
}
?>

<?= $this->endSection() ?>