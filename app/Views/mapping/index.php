<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<script src="/js/rak-picker.js"></script>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Mapping &amp; Kategori Rak</h1>
    <p>Kelola kategori rak, lihat peta gudang per zona, dan petakan material ke lokasi rak &mdash; semua dalam satu halaman.</p>
  </div>
</div>

<!-- ROW 1: Tambah Kategori Rak (kiri) + Import Massal permanen (kanan) -->
<div class="map-row-1">
  <div class="card-g">
    <div class="card-header-g">➕ Tambah Kategori Rak</div>
    <div class="card-body-g">
      <div class="form-row mb-3">
        <label class="form-label-gt">Nama/Kode Rak *</label>
        <input type="text" id="new-kode" class="form-control-gt" placeholder="Contoh: K.39">
      </div>
      <div class="form-grid-2-sm mb-3">
        <div>
          <label class="form-label-gt">Maks Baris *</label>
          <input type="number" id="new-baris" class="form-control-gt" min="1" placeholder="6">
        </div>
        <div>
          <label class="form-label-gt">Maks Kolom *</label>
          <input type="number" id="new-kolom" class="form-control-gt" min="1" placeholder="3">
        </div>
      </div>
      <div class="form-row mb-3">
        <label class="form-label-gt">Keterangan (opsional)</label>
        <input type="text" id="new-ket" class="form-control-gt" placeholder="Catatan tambahan...">
      </div>
      <button class="btn-g btn-navy-g" onclick="tambahKategori()">💾 Simpan Kategori</button>
    </div>
  </div>

  <div class="card-g">
    <div class="card-header-g">📋 Import Massal (tempel dari catatan)</div>
    <div class="card-body-g">
      <p style="font-size:.82rem;color:#6b7280;margin-bottom:.5rem">
        Tempel satu baris per rak dengan format: <code>KODE BARIS KOLOM</code>, contoh:<br>
        <code>A.1 6 3</code><br><code>K.39 7 5</code><br>
        Kategori yang sudah ada akan diperbarui batasnya, yang belum ada akan otomatis dibuat.
      </p>
      <textarea id="import-teks" class="form-control-gt" rows="6" placeholder="A.1 6 3&#10;A.2 6 3&#10;K.39 7 5"></textarea>
      <button class="btn-g btn-navy-g mt-2" onclick="importKategori()">⬆️ Proses Import</button>
      <div id="import-result" style="margin-top:.6rem;font-size:.82rem"></div>
    </div>
  </div>
</div>

<!-- ROW 2: [Peta Gudang + Detail&Edit Rak] | Material Belum Punya Rak -->
<div class="map-row-2">

  <div class="card-g peta-outer-card">
    <div class="peta-outer-grid">

      <!-- Peta Gudang -->
      <div class="peta-sub-card">
        <div class="card-header-g">🗺️ Peta Gudang</div>
        <div class="card-body-g">
          <div class="zona-tabs" id="zona-tabs"></div>
          <div class="zona-grid" id="zona-grid"></div>
          <div class="zona-legend">
            <span><i class="lg-dot lg-terisi"></i> Terisi</span>
            <span><i class="lg-dot lg-kritis"></i> Ada Kritis</span>
            <span><i class="lg-dot lg-kosong"></i> Kategori Baru (Kosong)</span>
          </div>
        </div>
      </div>

      <!-- Detail & Edit Rak -->
      <div class="peta-sub-card detail-sub-card">
        <div class="card-header-g">📦 Detail &amp; Edit Rak</div>
        <div class="card-body-g" id="detail-edit-body">
          <div class="detail-empty">Klik salah satu kotak rak di sebelah kiri untuk melihat detailnya.</div>
        </div>
      </div>

    </div>
  </div>

  <!-- Material Belum Punya Rak -->
  <div class="card-g unassigned-card">
    <div class="card-header-g">📭 Material Belum Punya Rak (<span id="unassigned-count"><?= count($unassigned) ?></span>)</div>
    <div class="card-body-g" style="padding-bottom:.6rem">
      <input type="text" id="unassigned-search" class="form-control-gt" placeholder="Cari kode SAP / nama material...">
    </div>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead>
          <tr>
            <th>Kode SAP</th>
            <th>Nama Material</th>
            <th>Kategori</th>
            <th style="text-align:center">Aksi</th>
          </tr>
        </thead>
        <tbody id="unassigned-tbody">
          <?php if (empty($unassigned)): ?>
          <tr><td colspan="4" class="tbl-empty">Semua material sudah punya lokasi rak 🎉</td></tr>
          <?php else: ?>
          <?php foreach ($unassigned as $m): ?>
          <tr id="unassigned-row-<?= $m['id'] ?>">
            <td><code class="mono" style="font-size:.75rem;color:var(--navy)"><?= esc($m['kode_sap']) ?></code></td>
            <td><span style="font-weight:600;color:#1f2937"><?= esc($m['nama_material']) ?></span></td>
            <td><?= esc($m['nama_kategori'] ?: '—') ?></td>
            <td style="text-align:center"><button class="btn-g btn-navy-g btn-sm-g" onclick="openEdit(<?= $m['id'] ?>)">✏️ Edit</button></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- MODAL EDIT MATERIAL -->
<div id="modal-edit" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:520px">
    <div class="modal-head">
      <h6 id="modal-edit-title">Edit Material</h6>
      <button class="modal-close" onclick="closeEdit()">✕</button>
    </div>
    <div class="modal-body-gt">
      <input type="hidden" id="edit-id">

      <div class="form-row mb-3">
        <label class="form-label-gt">Kode SAP <span style="color:var(--clay)">*</span></label>
        <input type="text" id="edit-kode-sap" class="form-control-gt" placeholder="Contoh: 600012345">
      </div>

      <div class="form-row mb-3">
        <label class="form-label-gt">Nama Material <span style="color:var(--clay)">*</span></label>
        <input type="text" id="edit-nama" class="form-control-gt" placeholder="Nama lengkap material">
      </div>

      <div class="form-row-2 mb-3">
        <div>
          <label class="form-label-gt">Satuan <span style="color:var(--clay)">*</span></label>
          <input type="text" id="edit-satuan" class="form-control-gt" placeholder="PC / KG / MTR ...">
        </div>
        <div>
          <label class="form-label-gt">Kategori</label>
          <select id="edit-kat" class="form-select-gt">
            <option value="">— Pilih Kategori —</option>
            <?php foreach ($kategoris as $k): ?>
            <option value="<?= $k['id'] ?>"><?= esc($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row mb-3">
        <label class="form-label-gt">Lokasi Rak</label>
        <div id="edit-rakpicker-slot"></div>
        <small style="color:#9ca3af;margin-top:4px;display:block">
          Pilih kategori rak, lalu isi baris &amp; kolom sesuai batas. Bisa tambah detail manual (mis. "kotak 1").
        </small>
      </div>

      <div class="form-row mb-3">
        <label class="form-label-gt">Safety Stock</label>
        <input type="number" id="edit-safety" class="form-control-gt" placeholder="Kosongkan jika tidak ada" min="0">
      </div>

      <div class="form-row mb-3">
        <label class="form-label-gt">Keterangan</label>
        <textarea id="edit-keterangan" class="form-control-gt" rows="2" placeholder="Opsional"></textarea>
      </div>

      <div id="edit-error" style="display:none;color:var(--clay);font-size:.82rem;margin-bottom:.8rem;background:#fff5f5;padding:.5rem .8rem;border-radius:8px;border:1px solid #fecaca"></div>

      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button class="btn-g btn-out-g" onclick="closeEdit()">Batal</button>
        <button class="btn-g btn-navy-g" id="btn-save" onclick="saveEdit()">💾 Simpan</button>
      </div>
    </div>
  </div>
</div>

<style>
.filter-bar{background:var(--gray100,#f0f2f8);border-radius:10px;padding:.8rem 1rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
.filter-field{display:flex;flex-direction:column;gap:4px}
.filter-label{font-size:.78rem;font-weight:700;color:var(--navy);letter-spacing:.02em}
.form-control-gt,.form-select-gt{border:1.5px solid var(--border);border-radius:8px;padding:.5rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;outline:none;transition:.2s;width:100%;box-sizing:border-box}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(26,39,68,.08)}
.card-header-g{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;border-radius:12px 12px 0 0}
.card-body-g{padding:1.2rem}
.tbl-empty{text-align:center;padding:2rem;color:#9ca3af}
.rak-chip{background:#ede9fe;color:#5b21b6;padding:.2rem .6rem;border-radius:6px;font-size:.75rem;font-weight:700}
.modal-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);z-index:5000;display:flex;align-items:center;justify-content:center;padding:1rem}
.modal-box{background:#fff;border-radius:16px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal-head{background:var(--navy);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem;color:#ffffff}
.modal-close{background:none;border:none;color:#ffffff;font-size:1.2rem;cursor:pointer;line-height:1;opacity:.85}
.modal-close:hover{opacity:1}
.modal-body-gt{padding:1.3rem}
.form-row{display:flex;flex-direction:column;gap:5px}
.form-row-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-grid-2-sm{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
.form-label-gt{font-size:.78rem;font-weight:700;color:var(--navy)}
.mb-3{margin-bottom:1rem}
.mt-2{margin-top:.6rem}
.mt-3{margin-top:1rem}
.tbl-wrap{overflow-x:auto}
.tbl-g{width:100%;border-collapse:collapse}
.tbl-g thead{background:#f8f9fc;border-bottom:1px solid #e5e7eb}
.tbl-g th{padding:.45rem .6rem;text-align:left;font-size:.75rem;font-weight:700;color:#4b5563;letter-spacing:.02em}
.tbl-g td{padding:.45rem .6rem;border-bottom:1px solid #f0f2f8;font-size:.83rem}
.tbl-g tbody tr:hover{background:#f9fafb}

/* ── Layout gabungan ─────────────────────────────────────────────────────── */
.map-row-1{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;align-items:stretch}
.map-row-1 > .card-g{display:flex;flex-direction:column}
.map-row-1 > .card-g > .card-body-g{flex:1;display:flex;flex-direction:column}
.map-row-1 #import-result{flex:0 0 auto}
.map-row-1 textarea#import-teks{flex:1;min-height:120px}
.map-row-1 > .card-g > .card-body-g > .btn-g{margin-top:auto}
.map-row-2{--map-card-h:640px;display:grid;grid-template-columns:2fr 1fr;gap:1rem;align-items:stretch}
@media(max-width:1100px){.map-row-1,.map-row-2{grid-template-columns:1fr}.map-row-2{--map-card-h:auto}}
@media(max-width:640px){
  .modal-box { max-width:100% !important; width:100% !important; }
  .zona-grid { grid-template-columns:repeat(auto-fill,minmax(84px,1fr)); gap:.4rem; }
  .zona-box { padding:.6rem .4rem; }
  .zona-tabs { gap:.3rem; }
  .zona-tab { padding:.35rem .7rem; font-size:.76rem; }
}

.peta-outer-card{padding:0;overflow:hidden;height:var(--map-card-h);display:flex;flex-direction:column}
.peta-outer-grid{display:grid;grid-template-columns:1fr 320px;gap:1px;background:#e5e7eb;flex:1;min-height:0}
@media(max-width:820px){.peta-outer-grid{grid-template-columns:1fr}.peta-outer-card{height:auto}}
.peta-sub-card{background:#fff;display:flex;flex-direction:column;min-height:0}
.peta-sub-card .card-body-g{flex:1;min-height:0;overflow-y:auto}

/* Card kanan "Material Belum Punya Rak" — tinggi disamakan persis dengan Peta Gudang,
   isi tabelnya di-scroll di dalam supaya kartu tidak memanjang tak terbatas. */
.map-row-2 > .card-g.unassigned-card{height:var(--map-card-h);display:flex;flex-direction:column}
@media(max-width:1100px){.map-row-2 > .card-g.unassigned-card{height:520px}}
.unassigned-card .tbl-wrap{flex:1;min-height:0;overflow-y:auto}

/* ── Peta Gudang ─────────────────────────────────────────────────────────── */
.zona-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem}
.zona-tab{background:#fff;border:1.5px solid var(--border);border-radius:8px;padding:.4rem 1rem;font-size:.82rem;font-weight:700;color:#4b5563;cursor:pointer;transition:.15s}
.zona-tab:hover{border-color:var(--navy)}
.zona-tab.active{background:var(--navy);border-color:var(--navy);color:#fff}
.zona-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:.6rem}
.zona-box{background:var(--navy);color:#fff;border-radius:10px;padding:.8rem .6rem;text-align:center;cursor:pointer;border:2px solid transparent;transition:.15s}
.zona-box:hover{filter:brightness(1.12)}
.zona-box.active{border-color:#93c5fd;box-shadow:0 0 0 2px rgba(147,197,253,.4)}
.zona-box.status-kritis{background:#ef4444}
.zona-box.status-kosong{background:#fff;color:#6b7280;border:2px dashed #d1d5db}
.zona-box.status-kosong:hover{border-color:var(--navy);color:var(--navy)}
.zona-box.status-kosong.active{border-style:solid}
.zona-box .zb-kode{font-weight:800;font-size:.85rem;display:block}
.zona-box .zb-count{font-size:.72rem;opacity:.85;display:block;margin-top:2px}
.zona-empty{color:#9ca3af;font-size:.85rem;padding:1rem 0}
.zona-legend{display:flex;gap:1rem;margin-top:1rem;font-size:.78rem;color:#6b7280}
.zona-legend .lg-dot{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:5px;vertical-align:middle}
.lg-terisi{background:var(--navy)}
.lg-kritis{background:#ef4444}
.lg-kosong{background:#fff;border:2px dashed #d1d5db}

/* ── Detail & Edit Rak ───────────────────────────────────────────────────── */
.detail-empty{color:#9ca3af;font-size:.85rem;text-align:center;padding:2rem 0}
.detail-head-box{background:var(--navy);color:#fff;border-radius:10px;padding:.9rem 1rem;margin-bottom:1rem}
.detail-head-box .dh-kode{font-size:1.05rem;font-weight:800}
.detail-head-box .dh-ket{font-size:.8rem;opacity:.85;margin-top:2px}
.detail-head-box .dh-meta{font-size:.75rem;opacity:.7;margin-top:2px}
.detail-mat-item{padding:.55rem 0;border-bottom:1px solid #f0f2f8;display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
.detail-mat-item:last-child{border-bottom:none}
.dm-name{font-weight:600;font-size:.83rem;color:#1f2937}
.dm-sub{font-size:.72rem;color:#9ca3af;margin-top:1px}
.dm-sub.kritis{color:#ef4444;font-weight:600}
.dm-right{text-align:right;flex-shrink:0}
.dm-stok{font-weight:700;font-size:.85rem}
.dm-edit-link{background:none;border:none;color:var(--navy);font-size:.72rem;cursor:pointer;text-decoration:underline;padding:0;margin-top:2px}
.detail-divider{border:none;border-top:1px solid #e5e7eb;margin:1rem 0}
.detail-edit-title{font-weight:700;font-size:.82rem;color:var(--navy);margin-bottom:.6rem}
</style>

<script>
// ══════════════════════════════════════════════════════════════════════════
// PETA GUDANG
// ══════════════════════════════════════════════════════════════════════════
var ZONA_DATA    = <?= json_encode($zonaGrid) ?>;
var ZONA_LIST    = <?= json_encode($zonaList) ?>;
var activeZona   = null;
var activeBoxKey = null; // format: "rak:12" atau "kategori:4"

function escHtml(s) {
    return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function renderZonaTabs() {
    var zonas = Object.keys(ZONA_DATA);
    var tabsEl = document.getElementById('zona-tabs');

    if (!zonas.length) {
        tabsEl.innerHTML = '';
        document.getElementById('zona-grid').innerHTML = '<div class="zona-empty">Belum ada rak yang terisi material.</div>';
        return;
    }
    if (!activeZona || zonas.indexOf(activeZona) === -1) activeZona = zonas[0];

    tabsEl.innerHTML = zonas.map(function(z) {
        return '<button type="button" class="zona-tab' + (z === activeZona ? ' active' : '') + '" onclick="selectZona(\'' + z + '\')">Zona ' + escHtml(z) + '</button>';
    }).join('');

    renderZonaGrid();
}

function selectZona(z) {
    activeZona = z;
    renderZonaTabs();
}

function renderZonaGrid() {
    var gridEl = document.getElementById('zona-grid');
    var list = ZONA_DATA[activeZona] || [];
    if (!list.length) {
        gridEl.innerHTML = '<div class="zona-empty">Tidak ada rak terisi di zona ini.</div>';
        return;
    }
    gridEl.innerHTML = list.map(function(r) {
        var type = r.type || 'rak';
        var statusClass = r.status === 'kritis' ? ' status-kritis' : (r.status === 'kosong' ? ' status-kosong' : '');
        var activeClass = (type + ':' + r.id === activeBoxKey) ? ' active' : '';
        var countLabel = r.status === 'kosong' ? 'Belum ada rak' : (r.item_count + ' item' + (r.status === 'kritis' ? ' ⚠️ Kritis' : ''));
        return '<div class="zona-box' + statusClass + activeClass + '" data-rak-id="' + r.id + '" onclick="pilihRak(' + r.id + ', \'' + type + '\')">' +
            '<span class="zb-kode">' + escHtml(r.kode_rak) + '</span>' +
            '<span class="zb-count">' + countLabel + '</span>' +
            '</div>';
    }).join('');
}

function refreshZonaGrid(callback) {
    fetch('/mapping/zona-grid', { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        ZONA_DATA = res.zona || {};
        renderZonaTabs();
        if (callback) callback();
    });
}

renderZonaTabs();

// ══════════════════════════════════════════════════════════════════════════
// DETAIL & EDIT RAK
// ══════════════════════════════════════════════════════════════════════════
function pilihRak(id, type) {
    type = type || 'rak';
    activeBoxKey = type + ':' + id;
    renderZonaGrid();

    var body = document.getElementById('detail-edit-body');
    body.innerHTML = '<div class="detail-empty">⏳ Memuat detail rak...</div>';

    if (type === 'kategori') {
        fetch('/mapping/kategori-detail/' + id, { headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.success) { body.innerHTML = '<div class="detail-empty">' + escHtml(res.message || 'Gagal memuat detail kategori') + '</div>'; return; }
            renderKategoriDetail(res.kategori);
        })
        .catch(function(){ body.innerHTML = '<div class="detail-empty">Gagal menghubungi server.</div>'; });
        return;
    }

    fetch('/mapping/rak-detail/' + id, { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.success) { body.innerHTML = '<div class="detail-empty">' + escHtml(res.message || 'Gagal memuat detail rak') + '</div>'; return; }
        renderDetailEdit(res.rak);
    })
    .catch(function(){ body.innerHTML = '<div class="detail-empty">Gagal menghubungi server.</div>'; });
}

function renderKategoriDetail(kat) {
    var body = document.getElementById('detail-edit-body');
    body.innerHTML =
        '<div class="detail-head-box">' +
            '<div class="dh-kode">' + escHtml(kat.kode_kategori) + '</div>' +
            (kat.keterangan ? '<div class="dh-ket">' + escHtml(kat.keterangan) + '</div>' : '') +
            '<div class="dh-meta">Zona ' + escHtml(kat.zona || '-') + ' &middot; Kategori rak</div>' +
        '</div>' +
        '<div class="detail-empty" style="padding:1rem 0">Kategori ini belum punya rak/material. Material hanya bisa ditempatkan lewat menu Penerimaan.</div>' +
        '<hr class="detail-divider">' +
        '<div class="detail-edit-title">📐 Batas Ukuran Kategori</div>' +
        '<div style="font-size:.83rem;color:#374151;line-height:2">' +
            'Maks Baris: <strong>' + escHtml(kat.max_baris) + '</strong><br>' +
            'Maks Kolom: <strong>' + escHtml(kat.max_kolom) + '</strong>' +
        '</div>';
}

function renderDetailEdit(rak) {
    var body = document.getElementById('detail-edit-body');

    var matHtml = '';
    if (!rak.materials.length) {
        matHtml = '<div class="detail-empty" style="padding:1rem 0">Belum ada material di rak ini.</div>';
    } else {
        matHtml = rak.materials.map(function(m) {
            var subClass = (m.kondisi_stok === 'kritis' || m.kondisi_stok === 'habis') ? ' kritis' : '';
            return '<div class="detail-mat-item">' +
                '<div>' +
                    '<div class="dm-name">' + escHtml(m.nama_material) + '</div>' +
                    '<div class="dm-sub' + subClass + '">' + escHtml(m.kode_sap) + ' &middot; ' + escHtml(m.kondisi_stok) + '</div>' +
                    '<button class="dm-edit-link" onclick="openEdit(' + m.id + ')">✏️ Edit</button>' +
                '</div>' +
                '<div class="dm-right">' +
                    '<div class="dm-stok">' + m.stok + '</div>' +
                    '<div class="dm-sub">' + escHtml(m.satuan) + '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    var zonaOptions = ZONA_LIST.map(function(z) {
        return '<option value="' + escHtml(z) + '"' + (z === rak.zona ? ' selected' : '') + '>' + escHtml(z) + '</option>';
    }).join('');

    body.innerHTML =
        '<div class="detail-head-box">' +
            '<div class="dh-kode">' + escHtml(rak.kode_rak) + '</div>' +
            (rak.keterangan ? '<div class="dh-ket">' + escHtml(rak.keterangan) + '</div>' : '') +
            '<div class="dh-meta">Zona ' + escHtml(rak.zona || '-') + ' &middot; ' + rak.jumlah_material + ' material</div>' +
        '</div>' +
        matHtml +
        '<hr class="detail-divider">' +
        '<div class="detail-edit-title">✏️ Edit Rak</div>' +
        '<input type="hidden" id="er-id" value="' + rak.id + '">' +
        '<div class="form-row mb-3">' +
            '<label class="form-label-gt">Kode Rak</label>' +
            '<input type="text" id="er-kode" class="form-control-gt" value="' + escHtml(rak.kode_rak) + '">' +
        '</div>' +
        '<div class="form-row mb-3">' +
            '<label class="form-label-gt">Zona</label>' +
            '<select id="er-zona" class="form-select-gt">' + zonaOptions + '</select>' +
        '</div>' +
        '<div class="form-row mb-3">' +
            '<label class="form-label-gt">Keterangan</label>' +
            '<input type="text" id="er-ket" class="form-control-gt" placeholder="Opsional" value="' + escHtml(rak.keterangan || '') + '">' +
        '</div>' +
        '<div id="er-error" style="display:none;color:var(--clay);font-size:.8rem;margin-bottom:.7rem;background:#fff5f5;padding:.5rem .7rem;border-radius:8px;border:1px solid #fecaca"></div>' +
        '<button class="btn-g btn-navy-g" style="width:100%" onclick="saveEditRak()">💾 Simpan Perubahan Rak</button>';
}

function saveEditRak() {
    var id    = document.getElementById('er-id').value;
    var kode  = document.getElementById('er-kode').value.trim();
    var zona  = document.getElementById('er-zona').value;
    var ket   = document.getElementById('er-ket').value.trim();
    var errEl = document.getElementById('er-error');
    errEl.style.display = 'none';

    if (!kode) { errEl.textContent = 'Kode rak wajib diisi.'; errEl.style.display = 'block'; return; }

    fetch('/mapping/rak-update/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ kode_rak: kode, zona: zona, keterangan: ket }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.success) { errEl.textContent = res.message || 'Gagal menyimpan perubahan.'; errEl.style.display = 'block'; return; }
        showToast('✅ Perubahan rak tersimpan');
        refreshZonaGrid(function() { pilihRak(parseInt(id, 10), 'rak'); });
    })
    .catch(function(){ errEl.textContent = 'Gagal menghubungi server.'; errEl.style.display = 'block'; });
}

// ══════════════════════════════════════════════════════════════════════════
// TAMBAH KATEGORI RAK & IMPORT MASSAL
// ══════════════════════════════════════════════════════════════════════════
function tambahKategori() {
    var kode  = document.getElementById('new-kode').value.trim();
    var baris = parseInt(document.getElementById('new-baris').value, 10);
    var kolom = parseInt(document.getElementById('new-kolom').value, 10);
    var ket   = document.getElementById('new-ket').value.trim();

    if (!kode)  { showAlert('Nama/kode rak wajib diisi', 'error'); return; }
    if (!baris || baris < 1) { showAlert('Maks baris wajib diisi (angka > 0)', 'error'); return; }
    if (!kolom || kolom < 1) { showAlert('Maks kolom wajib diisi (angka > 0)', 'error'); return; }

    fetch('/rak-kategori/simpan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ kode_kategori: kode, max_baris: baris, max_kolom: kolom, keterangan: ket }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.success) { showAlert(res.message || 'Gagal menyimpan kategori rak', 'error'); return; }
        document.getElementById('new-kode').value  = '';
        document.getElementById('new-baris').value = '';
        document.getElementById('new-kolom').value = '';
        document.getElementById('new-ket').value   = '';
        showToast('✅ Kategori rak "' + res.kategori.kode_kategori + '" tersimpan');
        refreshZonaGrid();
    })
    .catch(function(){ showAlert('Gagal menghubungi server.', 'error'); });
}

function importKategori() {
    var teks = document.getElementById('import-teks').value;
    if (!teks.trim()) { showAlert('Tempel data terlebih dahulu', 'error'); return; }

    fetch('/rak-kategori/import', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ teks: teks }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        var box = document.getElementById('import-result');
        if (!res.success) { box.innerHTML = '<span style="color:var(--clay)">Gagal import.</span>'; return; }
        var html = '<span style="color:#059669;font-weight:600">✔ ' + res.jumlah_sukses + ' baris berhasil diproses.</span>';
        if (res.gagal && res.gagal.length) {
            html += '<div style="margin-top:.4rem;color:var(--clay)">Dilewati:<ul>' +
                res.gagal.map(function(g){ return '<li>' + escHtml(g) + '</li>'; }).join('') + '</ul></div>';
        }
        box.innerHTML = html;
        document.getElementById('import-teks').value = '';
        refreshZonaGrid();
    })
    .catch(function(){ document.getElementById('import-result').innerHTML = '<span style="color:var(--clay)">Gagal menghubungi server.</span>'; });
}

// ══════════════════════════════════════════════════════════════════════════
// MATERIAL BELUM PUNYA RAK
// ══════════════════════════════════════════════════════════════════════════
var unassignedDebounce;

function renderUnassignedRow(m) {
    return '<tr id="unassigned-row-' + m.id + '">' +
        '<td><code class="mono" style="font-size:.75rem;color:var(--navy)">' + escHtml(m.kode_sap) + '</code></td>' +
        '<td><span style="font-weight:600;color:#1f2937">' + escHtml(m.nama_material) + '</span></td>' +
        '<td>' + escHtml(m.nama_kategori || '—') + '</td>' +
        '<td style="text-align:center"><button class="btn-g btn-navy-g btn-sm-g" onclick="openEdit(' + m.id + ')">✏️ Edit</button></td>' +
        '</tr>';
}

function refreshUnassigned(search) {
    fetch('/mapping/unassigned?' + new URLSearchParams({ search: search || '' }), { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        var tbody = document.getElementById('unassigned-tbody');
        var list  = res.materials || [];
        document.getElementById('unassigned-count').textContent = list.length;
        tbody.innerHTML = list.length
            ? list.map(renderUnassignedRow).join('')
            : '<tr><td colspan="4" class="tbl-empty">Semua material sudah punya lokasi rak 🎉</td></tr>';
    });
}

document.getElementById('unassigned-search').addEventListener('input', function() {
    var val = this.value;
    clearTimeout(unassignedDebounce);
    unassignedDebounce = setTimeout(function(){ refreshUnassigned(val); }, 350);
});

// ══════════════════════════════════════════════════════════════════════════
// MODAL EDIT MATERIAL (dipakai dari Detail Rak & tabel Belum Punya Rak)
// ══════════════════════════════════════════════════════════════════════════
function openEdit(matId) {
    document.getElementById('edit-error').style.display = 'none';
    document.getElementById('edit-error').textContent   = '';
    document.getElementById('btn-save').disabled        = false;
    document.getElementById('modal-edit-title').textContent = 'Edit Material';
    ['edit-kode-sap','edit-nama','edit-satuan','edit-keterangan'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
    document.getElementById('edit-safety').value  = '';
    document.getElementById('edit-kat').value     = '';
    document.getElementById('modal-edit').style.display = 'flex';
    RakPicker.init('edit');

    fetch('/mapping/get/' + matId, { headers:{'X-Requested-With':'XMLHttpRequest'} })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.error) { showEditError(data.error); return; }
        var m = data.material;

        document.getElementById('modal-edit-title').textContent = 'Edit — ' + m.nama_material;
        document.getElementById('edit-id').value        = m.id;
        document.getElementById('edit-kode-sap').value  = m.kode_sap   || '';
        document.getElementById('edit-nama').value      = m.nama_material || '';
        document.getElementById('edit-satuan').value    = m.satuan     || '';
        document.getElementById('edit-safety').value    = m.safety_stock !== null ? m.safety_stock : '';
        document.getElementById('edit-keterangan').value= m.keterangan || '';
        if (m.kategori_id) document.getElementById('edit-kat').value = m.kategori_id;
        if (m.kode_rak) RakPicker.setKode('edit', m.kode_rak);
    })
    .catch(function(){ showEditError('Gagal memuat data material'); });
}

function closeEdit() {
    document.getElementById('modal-edit').style.display = 'none';
}

function showEditError(msg) {
    var el = document.getElementById('edit-error');
    el.textContent    = msg;
    el.style.display  = 'block';
}

function saveEdit() {
    document.getElementById('edit-error').style.display = 'none';
    var id       = document.getElementById('edit-id').value;
    var kodeSap  = document.getElementById('edit-kode-sap').value.trim();
    var nama     = document.getElementById('edit-nama').value.trim();
    var satuan   = document.getElementById('edit-satuan').value.trim();
    var katId    = document.getElementById('edit-kat').value;
    var safety   = document.getElementById('edit-safety').value.trim();
    var ket      = document.getElementById('edit-keterangan').value.trim();

    if (!nama || !kodeSap || !satuan) {
        showEditError('Nama Material, Kode SAP, dan Satuan wajib diisi'); return;
    }

    var rakVal = RakPicker.getValue('edit', { required: false });
    if (rakVal === null) return;

    var btn = document.getElementById('btn-save');
    btn.disabled = true;
    btn.textContent = '⏳ Menyimpan...';

    fetch('/mapping/update/' + id, {
        method:  'POST',
        headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'},
        body: JSON.stringify({
            kode_sap:         kodeSap,
            nama_material:    nama,
            satuan:           satuan,
            kategori_id:      katId || null,
            kode_rak:         rakVal.kode_rak,
            rak_kategori_id:  rakVal.kategori_id,
            rak_baris:        rakVal.baris,
            rak_kolom:        rakVal.kolom,
            rak_detail:       rakVal.detail,
            safety_stock:     safety !== '' ? parseInt(safety) : null,
            keterangan:       ket,
        })
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        btn.disabled    = false;
        btn.textContent = '💾 Simpan';
        if (!data.success) { showEditError(data.message || 'Gagal menyimpan'); return; }

        closeEdit();
        showToast('✅ ' + data.message);

        // Segarkan semua widget terkait supaya data tetap sinkron
        refreshUnassigned(document.getElementById('unassigned-search').value);
        refreshZonaGrid(function() {
            if (activeBoxKey) {
                var parts = activeBoxKey.split(':');
                pilihRak(parseInt(parts[1], 10), parts[0]);
            }
        });
    })
    .catch(function() {
        btn.disabled    = false;
        btn.textContent = '💾 Simpan';
        showEditError('Gagal terhubung ke server');
    });
}

document.getElementById('modal-edit').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});

// ── Toast notifikasi ──────────────────────────────────────────────────────────
function showToast(msg) {
    var el = document.createElement('div');
    el.className = 'map-toast';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function(){ el.classList.add('map-toast-show'); }, 10);
    setTimeout(function() {
        el.classList.remove('map-toast-show');
        setTimeout(function(){ el.remove(); }, 300);
    }, 2800);
}
</script>

<style>
.map-toast{position:fixed;bottom:1.5rem;right:1.5rem;background:var(--navy);color:#fff;padding:.7rem 1.2rem;border-radius:10px;font-size:.83rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2);opacity:0;transform:translateY(8px);transition:all .25s;z-index:9999}
.map-toast-show{opacity:1;transform:translateY(0)}
</style>

<?= $this->endSection() ?>