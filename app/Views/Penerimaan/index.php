<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/choices.js/10.2.0/choices.min.css"/>
<script src="/js/rak-picker.js?v=2"></script>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Input Penerimaan Material</h1>
    <p>Catat penerimaan barang dari supplier ke gudang teknik</p>
  </div>
</div>

<div class="tabs-bar mb-3">
  <button class="tab-btn active" onclick="switchTab('form',this)">Form Penerimaan</button>
  <button class="tab-btn" onclick="switchTab('riwayat',this)">Riwayat Penerimaan</button>
</div>

<!-- TAB FORM -->
<div id="tab-form" class="tab-content">
  <div class="form-grid-2">

    <!-- Header -->
    <div class="card-g">
      <div class="card-header-g">Header Penerimaan</div>
      <div class="card-body-g">
        <div class="no-surat-chip">No. Surat: <strong><?= esc($no_surat) ?></strong> (auto)</div>

        <div class="form-row">
          <label class="form-label-gt">Tanggal Terima *</label>
          <input type="date" id="pen-tgl" class="form-control-gt" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-row">
          <label class="form-label-gt">Supplier *</label>
          <select id="pen-supplier" class="form-select-gt">
            <option value="">Pilih atau ketik supplier...</option>
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>"><?= esc($s['nama_supplier']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn-link-g mt-1" onclick="toggleSupplierBaru()">
            + Supplier tidak ada di daftar?
          </button>
        </div>

        <div id="form-supplier-baru" class="supplier-baru-box" style="display:none">
          <div class="supplier-baru-title">Tambah Supplier Baru</div>
          <div class="form-row">
            <label class="form-label-gt">Nama Supplier *</label>
            <input type="text" id="sup-nama" class="form-control-gt" placeholder="Nama perusahaan...">
          </div>
          <div class="form-grid-2-sm">
            <div>
              <label class="form-label-gt">Telepon</label>
              <input type="text" id="sup-telp" class="form-control-gt" placeholder="031-...">
            </div>
            <div>
              <label class="form-label-gt">Alamat</label>
              <input type="text" id="sup-alamat" class="form-control-gt" placeholder="Kota...">
            </div>
          </div>
          <div style="display:flex;gap:.5rem;margin-top:.5rem">
            <button class="btn-navy-g" onclick="simpanSupplierBaru()">Simpan & Pilih</button>
            <button class="btn-outline-g" onclick="toggleSupplierBaru()">Batal</button>
          </div>
        </div>

        <div class="form-row">
          <label class="form-label-gt">Catatan</label>
          <textarea id="pen-catatan" class="form-control-gt" rows="2" placeholder="Opsional..."></textarea>
        </div>

        <div class="petugas-chip">Petugas: <strong><?= esc($nama) ?></strong> (auto)</div>
      </div>
    </div>

    <!-- Tambah Item -->
    <div class="card-g card-tambah-item">
      <div class="card-header-g">Tambah Item</div>
      <div class="card-body-g">
        <div class="mode-bar mb-2">
          <button class="mode-btn active" id="btn-mode-sap" onclick="setMode('sap', this)">
            Cari Kode SAP
          </button>
          <button class="mode-btn" id="btn-mode-manual" onclick="setMode('manual', this)">
            Tanpa Kode SAP
          </button>
        </div>

        <div id="area-mode-sap">
          <div class="form-row">
            <label class="form-label-gt">Kode SAP</label>
            <div class="input-group-gt">
              <input type="text" id="pen-kode" class="form-control-gt"
                     placeholder="Contoh: 700200311"
                     onkeydown="if(event.key==='Enter')cariSAP()">
              <button class="btn-navy-g" onclick="cariSAP()">Cari</button>
            </div>
          </div>
          <div id="pen-item-area"></div>
        </div>

        <div id="area-mode-manual" style="display:none"></div>
      </div>
    </div>

  </div>

  <!-- Keranjang -->
  <div class="card-g mt-3">
    <div class="sh-g" style="background:var(--navy);color:#fff;border-radius:12px 12px 0 0;padding:.8rem 1.2rem">
      <span style="font-weight:700;font-size:.85rem">Keranjang - <span id="keranjang-count">0 item</span></span>
    </div>
    <div id="keranjang-body">
      <div class="empty-state">Belum ada item</div>
    </div>
  </div>
</div>

<!-- TAB RIWAYAT -->
<div id="tab-riwayat" class="tab-content" style="display:none">
  <div class="card-g">
    <div class="card-header-g">Riwayat Penerimaan</div>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead>
          <tr>
            <th>No. Surat</th><th>Tanggal</th><th>Supplier</th>
            <th>Item</th><th>Petugas</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($riwayat)): ?>
<tr><td colspan="6" class="tbl-empty">Tidak ada item</td></tr>
          <?php else: ?>
          <?php foreach ($riwayat as $r): ?>
          <tr>
            <td><code class="mono" style="font-size:.75rem;color:var(--navy)"><?= esc($r['no_surat_penerimaan']) ?></code></td>
            <td><?= esc($r['tanggal_terima']) ?></td>
            <td><?= esc($r['nama_supplier'] ?? '-') ?></td>
            <td><?= $r['jml_item'] ?> item</td>
            <td style="font-size:.78rem;color:#6b7280"><?= esc($r['nama_petugas'] ?? '-') ?></td>
            <td style="display:flex;gap:.3rem;flex-wrap:wrap">
              <button class="btn-detail-g" onclick="showDetail(<?= $r['id'] ?>)">
                <span class="btn-detail-icon">🔍</span> Lihat Detail
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($total_page > 1): ?>
    <div class="pagination-bar mt-3" style="padding-bottom:1rem">
      <?php if ($current_page > 1): ?>
      <a href="/penerimaan?page=<?= $current_page - 1 ?>" class="pg-btn">Prev</a>
      <?php else: ?><button class="pg-btn" disabled>Prev</button><?php endif; ?>
      <span class="pg-info">Hal <strong><?= $current_page ?></strong> / <?= $total_page ?></span>
      <?php if ($current_page < $total_page): ?>
      <a href="/penerimaan?page=<?= $current_page + 1 ?>" class="pg-btn">Next</a>
      <?php else: ?><button class="pg-btn" disabled>Next</button><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Detail -->
<!-- Modal Kepemilikan -->
<div id="modal-kepemilikan" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:520px">
    <div class="modal-head">
      <h6 id="mk-title">Kepemilikan Material</h6>
      <button class="modal-close" onclick="document.getElementById('modal-kepemilikan').style.display='none'">✕</button>
    </div>
    <div class="modal-body-gt" id="mk-body">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
  </div>
</div>

<div id="modal-detail" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:1000px;width:95vw">
    <div class="modal-head">
      <h6 id="modal-detail-title">Detail Penerimaan</h6>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <!-- Tab nav di dalam modal -->
    <div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;background:#f8f9fc;padding:0 1.3rem">
      <button class="md-tab-btn active" id="mdtab-lihat" onclick="switchMdTab('lihat',this)">📋 Detail</button>
      <button class="md-tab-btn" id="mdtab-edit" onclick="switchMdTab('edit',this)">✏️ Edit</button>
      <button class="md-tab-btn" id="mdtab-log" onclick="switchMdTab('log',this)">🕑 Riwayat Edit</button>
    </div>
    <div id="mdtab-content-lihat" class="modal-body-gt">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
    <div id="mdtab-content-edit" class="modal-body-gt" style="display:none">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
    <div id="mdtab-content-log" class="modal-body-gt" style="display:none">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
  </div>
</div>


<script>
var keranjang = [];
UnsavedGuard.watch('#tab-form', 'Data penerimaan (header, item, atau keranjang) yang sedang diisi belum disimpan. Yakin ingin pindah halaman?');
UnsavedGuard.watch('#modal-detail', 'Ada perubahan pada detail penerimaan yang belum disimpan. Yakin ingin pindah halaman?');
var choicesSupplier;

// Batch options generated from DB
var batchOptionsHTML = '<option value="">-- Pilih Batch --</option>' +
    <?php
    $batchEnum = ['LOCAL','IMPORT','EXPLANT','REKONDISI','DEFECT','EXPROJECT','DAMAGE','UMUM'];
    // Build label map from kategoris
    $labelMap = ['UMUM' => 'UMUM'];
    foreach ($kategoris as $k) {
        $upper = strtoupper($k['nama_kategori']);
        if (in_array($upper, $batchEnum)) $labelMap[$upper] = $k['nama_kategori'];
        // handle alias
        if (strtolower($k['nama_kategori']) === 'lokal') $labelMap['LOCAL'] = 'Lokal';
        if (strtolower($k['nama_kategori']) === 'import') $labelMap['IMPORT'] = 'Import';
        if (strtolower($k['nama_kategori']) === 'explant') $labelMap['EXPLANT'] = 'Explant';
        if (strtolower($k['nama_kategori']) === 'exproject') $labelMap['EXPROJECT'] = 'Exproject';
        if (strtolower($k['nama_kategori']) === 'rekondisi') $labelMap['REKONDISI'] = 'Rekondisi';
        if (strtolower($k['nama_kategori']) === 'defect') $labelMap['DEFECT'] = 'Defect';
        if (strtolower($k['nama_kategori']) === 'damage') $labelMap['DAMAGE'] = 'Damage';
    }
    $opts = [];
    foreach ($batchEnum as $b) {
        $label = $labelMap[$b] ?? $b;
        $opts[] = "'<option value=\"{$b}\">{$label}</option>'";
    }
    echo implode(" +\n    ", $opts);
    ?>;

function batchField(required) {
    var req = required ? ' *' : ' (opsional)';
    return '<div class="form-row"><label class="form-label-gt">Batch / Kategori' + req + '</label>' +
        '<select class="form-select-gt" id="pen-batch">' + batchOptionsHTML + '</select></div>';
}

function loadChoicesJS(callback) {
    if (window.Choices) { callback(); return; }
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/choices.js/10.2.0/choices.min.js';
    s.onload = callback;
    document.head.appendChild(s);
}

document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'riwayat') {
        switchTab('riwayat', document.querySelectorAll('.tab-btn')[1]);
    }

    loadChoicesJS(function() {
        choicesSupplier = new Choices('#pen-supplier', {
            searchEnabled: true,
            searchPlaceholderValue: 'Ketik nama supplier...',
            noResultsText: 'Tidak ditemukan',
            itemSelectText: '',
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Pilih atau ketik supplier...',
        });
    });

    var manualArea = document.getElementById('area-mode-manual');
    if (manualArea) {
        manualArea.innerHTML =
            '<div class="notif-info">Material aset / non-rutinan tanpa kode SAP</div>' +
            '<div class="form-row"><label class="form-label-gt">Deskripsi / Nama Material *</label><input class="form-control-gt" id="pen-nama-manual" placeholder="Nama barang yang diterima..."></div>' +
            batchField(false) +
            '<div class="form-grid-2-sm">' +
                '<div><label class="form-label-gt">Qty *</label><input type="number" class="form-control-gt" id="pen-jml" min="1" value="1"></div>' +
                '<div><label class="form-label-gt">UoM / Satuan *</label><input class="form-control-gt" id="pen-uom" placeholder="PC / KG / M / SET"></div>' +
            '</div>' +
            '<div class="form-row"><label class="form-label-gt">Kondisi</label><select class="form-select-gt" id="pen-kondisi"><option value="baik">Baik</option><option value="cacat_ringan">Cacat Ringan</option><option value="rusak">Rusak</option></select></div>' +
            '<div class="form-row"><label class="form-label-gt">Requisitioner</label><input class="form-control-gt" id="pen-req" placeholder="Nama requisitioner..."></div>' +
            '<div id="pen-manual-rakpicker-slot"></div>' +
            '<button class="btn-navy-g w-100" onclick="addKeranjangManual()">+ Tambah ke Keranjang</button>';
        RakPicker.init('pen-manual');
    }
});

function setMode(mode, btn) {
    document.getElementById('area-mode-sap').style.display    = mode === 'sap'    ? 'block' : 'none';
    document.getElementById('area-mode-manual').style.display = mode === 'manual' ? 'block' : 'none';
    document.querySelectorAll('.mode-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    if (mode === 'sap') {
        document.getElementById('pen-kode').value = '';
        document.getElementById('pen-item-area').innerHTML = '';
    }
}

function toggleSupplierBaru() {
    var box = document.getElementById('form-supplier-baru');
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function simpanSupplierBaru() {
    var nama   = document.getElementById('sup-nama').value.trim();
    var telp   = document.getElementById('sup-telp').value.trim();
    var alamat = document.getElementById('sup-alamat').value.trim();
    if (!nama) { alert('Nama supplier wajib diisi!'); return; }
    fetch('/penerimaan/simpan-supplier', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ nama_supplier: nama, telepon: telp, alamat: alamat }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            choicesSupplier.setChoices([{
                value: String(res.supplier.id),
                label: res.supplier.nama_supplier,
                selected: true,
            }], 'value', 'label', false);
            document.getElementById('sup-nama').value   = '';
            document.getElementById('sup-telp').value   = '';
            document.getElementById('sup-alamat').value = '';
            document.getElementById('form-supplier-baru').style.display = 'none';
        } else {
            alert('Gagal: ' + (res.message || ''));
        }
    });
}

function cariSAP() {
    var kode = document.getElementById('pen-kode').value.trim();
    if (!kode) { alert('Masukkan Kode SAP!'); return; }
    var area = document.getElementById('pen-item-area');
    area.innerHTML = '<div style="color:#6b7280;font-size:.82rem;padding:.5rem 0">Mencari...</div>';

    fetch('/penerimaan/cari-material?kode=' + encodeURIComponent(kode), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.found) {
            var m = res.material;
            var tabungField = parseInt(m.is_tabung) === 1
                ? '<div class="form-row"><label class="form-label-gt">Jumlah Tabung Diterima *</label>' +
                  '<div style="display:flex;gap:.5rem;align-items:center">' +
                  '<input type="number" class="form-control-gt" id="pen-jml-tabung" min="1" value="1" style="max-width:100px" oninput="generateTabungInputs()">' +
                  '<span style="font-size:.78rem;color:#6b7280">tabung - input no. tabung masing-masing</span>' +
                  '</div></div>' +
                  '<div id="pen-tabung-inputs"></div>'
                : '';
            area.innerHTML =
                '<div class="mat-info-box">' +
                    '<div class="mat-info-name">' + escHtml(m.nama_material) +
                        (parseInt(m.is_tabung) === 1 ? ' <span class="badge-gt badge-tabung">TABUNG</span>' : '') +
                        ' <span class="badge-gt" style="background:#e0e7ff;color:#3730a3;font-size:.72rem">' + escHtml(m.batch || 'UMUM') + '</span>' +
                    '</div>' +
                    '<div class="mat-info-meta"><code>' + escHtml(m.kode_sap) + '</code> Rak: ' + escHtml(m.kode_rak) + ' Stok: ' + m.stok + ' ' + escHtml(m.satuan) + '</div>' +
                '</div>' +
                formItemHTML(parseInt(m.is_tabung) === 1, m.satuan) +
                tabungField +
                '<button class="btn-navy-g w-100" onclick=\'addKeranjangSAP(' + JSON.stringify(m) + ')\'>+ Tambah ke Keranjang</button>';
        } else {
            area.innerHTML =
                '<div class="notif-warning">Kode ' + escHtml(kode) + ' belum terdaftar - isi sebagai material baru</div>' +
                '<div class="form-row"><label class="form-label-gt">Nama Material *</label><input class="form-control-gt" id="pen-nama-baru" placeholder="Deskripsi material..."></div>' +
                batchField(true) +
                '<div class="form-row"><label class="form-label-gt" style="display:flex;align-items:center;gap:.5rem;cursor:pointer"><input type="checkbox" id="pen-is-tabung-baru" onchange="toggleTabungBaru()" style="width:16px;height:16px;accent-color:var(--navy)"> Material ini adalah <strong style=\"margin:0 2px\">Tabung</strong> (input no. tabung satu per satu)</label></div>' +
                '<div id="pen-tabung-baru-area"></div>' +
                formItemHTML(false) +
                '<div id="pen-baru-rakpicker-slot"></div>' +
                '<button class="btn-navy-g w-100" onclick="addKeranjangSAPBaru(\'' + escHtml(kode) + '\')">+ Tambah ke Keranjang</button>';
            RakPicker.init('pen-baru');
        }
    })
    .catch(function() {
        area.innerHTML = '<div style="color:var(--red);padding:.5rem">Gagal menghubungi server.</div>';
    });
}

function formItemHTML(isTabung, satuan) {
    var satuanVal  = satuan || '';
    var satuanHint = satuanVal
        ? '<span style="font-size:.72rem;color:var(--navy);margin-left:4px">&#10003; dari data material</span>'
        : '';
    var satuanStyle = satuanVal ? 'border-color:var(--navy);background:#f0f4ff;' : '';
    var qtyField = isTabung ? '' :
        '<div><label class="form-label-gt">Qty *</label><input type="number" class="form-control-gt" id="pen-jml" min="1" value="1"></div>';
    return '<div class="form-grid-2-sm">' +
        qtyField +
        '<div><label class="form-label-gt">UoM / Satuan *' + satuanHint + '</label>' +
            '<input class="form-control-gt" id="pen-uom" placeholder="PC / KG / M" value="' + escHtml(satuanVal) + '" style="' + satuanStyle + '"></div>' +
    '</div>' +
    '<div class="form-row"><label class="form-label-gt">Kondisi</label><select class="form-select-gt" id="pen-kondisi"><option value="baik">Baik</option><option value="cacat_ringan">Cacat Ringan</option><option value="rusak">Rusak</option></select></div>' +
    '<div class="form-row"><label class="form-label-gt">Requisitioner</label><input class="form-control-gt" id="pen-req" placeholder="Nama requisitioner..."></div>';
}

function generateTabungInputs() {
    var jml = parseInt(document.getElementById('pen-jml-tabung') ? document.getElementById('pen-jml-tabung').value : 0) || 0;
    var container = document.getElementById('pen-tabung-inputs');
    if (!container) return;
    if (jml < 1) { container.innerHTML = ''; return; }
    var html = '<div class="tabung-bulk-box">';
    for (var i = 1; i <= jml; i++) {
        html += '<div class="tabung-bulk-row">' +
            '<span class="tabung-bulk-num">' + i + '</span>' +
            '<input type="text" class="form-control-gt" id="pen-tabung-' + i + '" placeholder="No. tabung ke-' + i + '">' +
        '</div>';
    }
    html += '</div>';
    container.innerHTML = html;
}

function addKeranjangSAP(mat) {
    var jml     = parseInt(document.getElementById('pen-jml') ? document.getElementById('pen-jml').value : 0) || 0;
    var uom     = document.getElementById('pen-uom') ? document.getElementById('pen-uom').value.trim() : '';
    var kondisi = document.getElementById('pen-kondisi') ? document.getElementById('pen-kondisi').value : 'baik';
    var req     = document.getElementById('pen-req') ? document.getElementById('pen-req').value.trim() : '';

    if (!uom) { alert('UoM wajib diisi!'); return; }

    var noTabungList = [];
    if (parseInt(mat.is_tabung) === 1) {
        var jmlTabung = parseInt(document.getElementById('pen-jml-tabung') ? document.getElementById('pen-jml-tabung').value : 0) || 0;
        if (jmlTabung < 1) { alert('Input jumlah tabung terlebih dahulu!'); return; }
        for (var t = 1; t <= jmlTabung; t++) {
            var inp = document.getElementById('pen-tabung-' + t);
            if (!inp || !inp.value.trim()) {
                alert('No. Tabung ke-' + t + ' wajib diisi!');
                return;
            }
            noTabungList.push(inp.value.trim());
        }
    } else {
        if (jml < 1) { alert('Qty minimal 1!'); return; }
    }

    keranjang.push({
        is_material_baru: 0,
        tanpa_kode:       0,
        material_id:      mat.id,
        kode_sap:         mat.kode_sap,
        nama_material:    mat.nama_material,
        satuan:           uom || mat.satuan,
        is_tabung:        parseInt(mat.is_tabung),
        rak_id:           mat.rak_id,
        kode_rak:         mat.kode_rak,
        jumlah_terima:    parseInt(mat.is_tabung) === 1 ? noTabungList.length : jml,
        kondisi:          kondisi,
        no_tabung_list:   noTabungList,
        requisitioner:    req,
    });
    resetItemArea();
    renderKeranjang();
}

function toggleTabungBaru() {
    var cb = document.getElementById('pen-is-tabung-baru');
    var area = document.getElementById('pen-tabung-baru-area');
    var jmlEl = document.getElementById('pen-jml');
    if (!area) return;
    if (cb && cb.checked) {
        area.innerHTML =
            '<div class="form-row"><label class="form-label-gt">Jumlah Tabung Diterima *</label>' +
            '<div style="display:flex;gap:.5rem;align-items:center">' +
            '<input type="number" class="form-control-gt" id="pen-jml-tabung-baru" min="1" value="1" style="max-width:100px" oninput="generateTabungInputsBaru()">' +
            '<span style="font-size:.78rem;color:#6b7280">tabung - input no. tabung masing-masing</span>' +
            '</div></div>' +
            '<div id="pen-tabung-inputs-baru"></div>';
        // hide qty field
        if (jmlEl && jmlEl.parentElement) jmlEl.parentElement.style.display = 'none';
        generateTabungInputsBaru();
    } else {
        area.innerHTML = '';
        if (jmlEl && jmlEl.parentElement) jmlEl.parentElement.style.display = '';
    }
}

function generateTabungInputsBaru() {
    var jml = parseInt(document.getElementById('pen-jml-tabung-baru') ? document.getElementById('pen-jml-tabung-baru').value : 0) || 0;
    var container = document.getElementById('pen-tabung-inputs-baru');
    if (!container) return;
    if (jml < 1) { container.innerHTML = ''; return; }
    var html = '<div class="tabung-bulk-box">';
    for (var i = 1; i <= jml; i++) {
        html += '<div class="tabung-bulk-row">' +
            '<span class="tabung-bulk-num">' + i + '</span>' +
            '<input type="text" class="form-control-gt" id="pen-tabung-baru-' + i + '" placeholder="No. tabung ke-' + i + '">' +
            '</div>';
    }
    html += '</div>';
    container.innerHTML = html;
}

function addKeranjangSAPBaru(kode) {
    var nama     = document.getElementById('pen-nama-baru') ? document.getElementById('pen-nama-baru').value.trim() : '';
    var batch    = document.getElementById('pen-batch') ? document.getElementById('pen-batch').value : '';
    var uom      = document.getElementById('pen-uom') ? document.getElementById('pen-uom').value.trim() : '';
    var kondisi  = document.getElementById('pen-kondisi') ? document.getElementById('pen-kondisi').value : 'baik';
    var req      = document.getElementById('pen-req') ? document.getElementById('pen-req').value.trim() : '';
    var isTabung = document.getElementById('pen-is-tabung-baru') && document.getElementById('pen-is-tabung-baru').checked ? 1 : 0;

    if (!nama)  { alert('Nama material wajib diisi!'); return; }
    if (!batch) { alert('Batch / Kategori wajib dipilih!'); return; }
    if (!uom)   { alert('UoM wajib diisi!'); return; }

    var rakVal = RakPicker.getValue('pen-baru', { required: false });
    if (rakVal === null) return; // lokasi rak diisi tapi tidak valid, batal simpan

    var noTabungList = [];
    var jml;
    if (isTabung) {
        var jmlTabung = parseInt(document.getElementById('pen-jml-tabung-baru') ? document.getElementById('pen-jml-tabung-baru').value : 0) || 0;
        if (jmlTabung < 1) { alert('Input jumlah tabung terlebih dahulu!'); return; }
        for (var t = 1; t <= jmlTabung; t++) {
            var inp = document.getElementById('pen-tabung-baru-' + t);
            if (!inp || !inp.value.trim()) { alert('No. Tabung ke-' + t + ' wajib diisi!'); return; }
            noTabungList.push(inp.value.trim());
        }
        jml = noTabungList.length;
    } else {
        jml = parseInt(document.getElementById('pen-jml') ? document.getElementById('pen-jml').value : 0) || 0;
        if (jml < 1) { alert('Qty minimal 1!'); return; }
    }

    keranjang.push({
        is_material_baru: 1, tanpa_kode: 0, kode_sap: kode,
        nama_material: nama, satuan: uom, is_tabung: isTabung, batch: batch,
        kode_rak: rakVal.kode_rak, rak_kategori_id: rakVal.kategori_id,
        rak_baris: rakVal.baris, rak_kolom: rakVal.kolom, rak_detail: rakVal.detail,
        jumlah_terima: jml, kondisi: kondisi,
        no_tabung_list: noTabungList, requisitioner: req,
    });
    resetItemArea();
    renderKeranjang();
}

function addKeranjangManual() {
    var nama    = document.getElementById('pen-nama-manual') ? document.getElementById('pen-nama-manual').value.trim() : '';
    var batch   = document.getElementById('pen-batch') ? document.getElementById('pen-batch').value : '';
    var jml     = parseInt(document.getElementById('pen-jml') ? document.getElementById('pen-jml').value : 0) || 0;
    var uom     = document.getElementById('pen-uom') ? document.getElementById('pen-uom').value.trim() : '';
    var kondisi = document.getElementById('pen-kondisi') ? document.getElementById('pen-kondisi').value : 'baik';
    var req     = document.getElementById('pen-req') ? document.getElementById('pen-req').value.trim() : '';
    if (!nama) { alert('Deskripsi material wajib diisi!'); return; }
    if (jml < 1) { alert('Qty minimal 1!'); return; }
    if (!uom)  { alert('UoM wajib diisi!'); return; }

    var rakVal = RakPicker.getValue('pen-manual', { required: false });
    if (rakVal === null) return;

    keranjang.push({
        is_material_baru: 1, tanpa_kode: 1, kode_sap: null,
        nama_material: nama, satuan: uom, is_tabung: 0, batch: batch || 'UMUM',
        kode_rak: rakVal.kode_rak, rak_kategori_id: rakVal.kategori_id,
        rak_baris: rakVal.baris, rak_kolom: rakVal.kolom, rak_detail: rakVal.detail,
        jumlah_terima: jml, kondisi: kondisi,
        no_tabung_list: [], requisitioner: req,
    });
    if (document.getElementById('pen-nama-manual')) document.getElementById('pen-nama-manual').value = '';
    if (document.getElementById('pen-jml'))         document.getElementById('pen-jml').value = '1';
    if (document.getElementById('pen-uom'))         document.getElementById('pen-uom').value = '';
    if (document.getElementById('pen-req'))         document.getElementById('pen-req').value = '';
    RakPicker.reset('pen-manual');
    renderKeranjang();
}

function resetItemArea() {
    document.getElementById('pen-kode').value = '';
    document.getElementById('pen-item-area').innerHTML = '';
}

function renderKeranjang() {
    document.getElementById('keranjang-count').textContent = keranjang.length + ' item';
    var body = document.getElementById('keranjang-body');
    if (keranjang.length === 0) {
        body.innerHTML = '<div class="empty-state">Belum ada item</div>';
        return;
    }
    var rows = keranjang.map(function(item, i) {
        var badge = item.tanpa_kode
            ? '<span class="badge-gt badge-warning">Tanpa Kode</span>'
            : (item.is_material_baru ? '<span class="badge-gt badge-aktif">Baru</span>' : '');
        var tabungChips = '';
        if (item.is_tabung && item.no_tabung_list && item.no_tabung_list.length > 0) {
            tabungChips = '<div class="cart-tabung-chips">' +
                item.no_tabung_list.map(function(n, idx) {
                    return '<span class="cart-tabung-chip">' + (idx+1) + '. ' + escHtml(n) + '</span>';
                }).join('') +
            '</div>';
        }
        return '<div class="cart-item" id="cart-item-' + i + '">' +
            '<div class="cart-item-info">' +
                '<div class="cart-item-name">' + escHtml(item.nama_material) + ' ' + badge + '</div>' +
                '<div class="cart-item-meta">' +
                    (item.kode_sap ? escHtml(item.kode_sap) + ' - ' : '') +
                    item.jumlah_terima + ' ' + escHtml(item.satuan) +
                    ' - ' + item.kondisi +
                    (item.batch ? ' - <strong>' + escHtml(item.batch) + '</strong>' : '') +
                    (item.kode_rak ? ' - Rak: ' + escHtml(item.kode_rak) : '') +
                '</div>' +
                (item.requisitioner ? '<div class="cart-item-meta" style="color:var(--navy)">Req: ' + escHtml(item.requisitioner) + '</div>' : '') +
                tabungChips +
            '</div>' +
            '<div style="display:flex;gap:.3rem;flex-shrink:0">' +
                '<button class="cart-edit" onclick="editItem(' + i + ')" title="Edit item">✏️</button>' +
                '<button class="cart-remove" onclick="hapusItem(' + i + ')" title="Hapus item">✕</button>' +
            '</div>' +
        '</div>';
    }).join('');
    rows += '<div class="cart-footer">' +
        '<button class="btn-outline-g" onclick="resetKeranjang()">Reset</button>' +
        '<button class="btn-green-g" onclick="simpanPenerimaan()">Simpan Penerimaan</button>' +
    '</div>';
    body.innerHTML = rows;
}

function hapusItem(i) { keranjang.splice(i,1); renderKeranjang(); }

// ── Edit item keranjang (inline form) ────────────────────────────────────────
function editItem(i) {
    var item = keranjang[i];
    var batchOpts = ['LOCAL','IMPORT','EXPLANT','REKONDISI','DEFECT','EXPROJECT','DAMAGE','UMUM'].map(function(b) {
        return '<option value="' + b + '"' + (item.batch === b ? ' selected' : '') + '>' + b + '</option>';
    }).join('');
    var kondisiOpts = ['baik','rusak','rekondisi'].map(function(k) {
        return '<option value="' + k + '"' + (item.kondisi === k ? ' selected' : '') + '>' + k + '</option>';
    }).join('');

    var tabungSection = '';
    if (item.is_tabung && item.no_tabung_list && item.no_tabung_list.length > 0) {
        tabungSection = '<div class="cart-edit-row"><label class="cart-edit-label">No. Tabung <span style="font-size:.7rem;color:#9ca3af">(1 per baris)</span></label>' +
            '<textarea class="form-control-gt" id="cedit-tabung-' + i + '" rows="' + Math.min(item.no_tabung_list.length + 1, 5) + '" style="font-size:.78rem;resize:vertical">' +
            item.no_tabung_list.join('\n') +
            '</textarea></div>';
    }

    var html = '<div class="cart-edit-form" id="cart-editform-' + i + '">' +
        '<div style="font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:.5rem">Edit: ' + escHtml(item.nama_material) + '</div>' +
        '<div class="cart-edit-grid">' +
            '<div class="cart-edit-row"><label class="cart-edit-label">Qty' + (item.is_tabung ? ' (dari no. tabung)' : ' *') + '</label>' +
                (item.is_tabung
                    ? '<input type="number" class="form-control-gt" id="cedit-qty-' + i + '" value="' + item.jumlah_terima + '" min="1" disabled style="background:#f3f4f6">'
                    : '<input type="number" class="form-control-gt" id="cedit-qty-' + i + '" value="' + item.jumlah_terima + '" min="1">') +
            '</div>' +
            '<div class="cart-edit-row"><label class="cart-edit-label">Kondisi</label>' +
                '<select class="form-select-gt" id="cedit-kondisi-' + i + '">' + kondisiOpts + '</select>' +
            '</div>' +
            '<div class="cart-edit-row"><label class="cart-edit-label">Rak</label>' +
                '<input type="text" class="form-control-gt" id="cedit-rak-' + i + '" value="' + escHtml(item.kode_rak || '') + '" placeholder="Kode rak...">' +
            '</div>' +
            '<div class="cart-edit-row"><label class="cart-edit-label">Requisitioner</label>' +
                '<input type="text" class="form-control-gt" id="cedit-req-' + i + '" value="' + escHtml(item.requisitioner || '') + '" placeholder="Kosongkan jika tidak ada">' +
            '</div>' +
            (item.batch ? '<div class="cart-edit-row"><label class="cart-edit-label">Batch</label><select class="form-select-gt" id="cedit-batch-' + i + '">' + batchOpts + '</select></div>' : '') +
        '</div>' +
        tabungSection +
        '<div style="display:flex;gap:.4rem;margin-top:.6rem;justify-content:flex-end">' +
            '<button class="btn-outline-g" onclick="cancelEditItem(' + i + ')" style="font-size:.78rem;padding:.35rem .8rem">Batal</button>' +
            '<button class="btn-navy-g" onclick="saveEditItem(' + i + ')" style="font-size:.78rem;padding:.35rem .8rem">✔ Simpan Perubahan</button>' +
        '</div>' +
    '</div>';

    // Replace cart item row with edit form
    var el = document.getElementById('cart-item-' + i);
    if (el) el.outerHTML = html;
}

function cancelEditItem(i) { renderKeranjang(); }

function saveEditItem(i) {
    var item = keranjang[i];

    if (!item.is_tabung) {
        var qty = parseInt(document.getElementById('cedit-qty-' + i) ? document.getElementById('cedit-qty-' + i).value : 0) || 0;
        if (qty < 1) { alert('Qty minimal 1!'); return; }
        item.jumlah_terima = qty;
    }

    var kondisiEl = document.getElementById('cedit-kondisi-' + i);
    if (kondisiEl) item.kondisi = kondisiEl.value;

    var rakEl = document.getElementById('cedit-rak-' + i);
    if (rakEl) item.kode_rak = rakEl.value.trim();

    var reqEl = document.getElementById('cedit-req-' + i);
    if (reqEl) item.requisitioner = reqEl.value.trim();

    var batchEl = document.getElementById('cedit-batch-' + i);
    if (batchEl) item.batch = batchEl.value;

    if (item.is_tabung) {
        var tabungEl = document.getElementById('cedit-tabung-' + i);
        if (tabungEl) {
            var lines = tabungEl.value.split('\n').map(function(l){ return l.trim(); }).filter(Boolean);
            if (lines.length < 1) { alert('No. tabung tidak boleh kosong!'); return; }
            item.no_tabung_list = lines;
            item.jumlah_terima  = lines.length;
        }
    }

    keranjang[i] = item;
    renderKeranjang();
}
function resetKeranjang() {
    if (!confirm('Reset semua item?')) return;
    keranjang = []; renderKeranjang();
}

function simpanPenerimaan() {
    var tgl     = document.getElementById('pen-tgl').value;
    var supId   = choicesSupplier ? choicesSupplier.getValue(true) : '';
    var catatan = document.getElementById('pen-catatan').value.trim();
    if (!tgl || !supId) { alert('Lengkapi tanggal dan supplier!'); return; }
    if (keranjang.length === 0) { alert('Keranjang masih kosong!'); return; }
    fetch('/penerimaan/simpan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
            header: { tanggal_terima: tgl, supplier_id: supId, catatan: catatan },
            items:  keranjang,
        }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            UnsavedGuard.markClean();
            showAlert(res.no_surat + ' berhasil disimpan!').then(function(){
                window.location.href = window.location.pathname + '?tab=riwayat';
            });
        } else {
            alert('Gagal: ' + (res.message || 'Terjadi kesalahan.'));
        }
    })
    .catch(function() { alert('Gagal menghubungi server.'); });
}

function showKepemilikan(materialId, namaMaterial) {
    document.getElementById('mk-title').textContent = 'Kepemilikan: ' + namaMaterial;
    document.getElementById('mk-body').innerHTML = '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    document.getElementById('modal-kepemilikan').style.display = 'flex';

    fetch('/penerimaan/kepemilikan/' + materialId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.error) {
            document.getElementById('mk-body').innerHTML = '<div style="color:red;padding:1rem">' + res.error + '</div>';
            return;
        }
        var m = res.material;
        var list = res.kepemilikan;

        var totalMilik = list.reduce(function(sum, r){ return sum + parseInt(r.qty); }, 0);

        var rows = list.length === 0
            ? '<tr><td colspan="2" style="text-align:center;padding:2rem;color:#9ca3af">Belum ada data kepemilikan tercatat</td></tr>'
            : list.map(function(r) {
                return '<tr>' +
                    '<td style="padding:.65rem 1rem;font-weight:600;color:#1a2744">' + escHtml(r.requester) + '</td>' +
                    '<td style="padding:.65rem 1rem;text-align:right">' +
                        '<strong style="color:' + (parseInt(r.qty) <= 0 ? '#CE2626' : '#1a7f4b') + '">' +
                        r.qty + ' ' + escHtml(m.satuan) + '</strong>' +
                        (parseInt(r.qty) <= 0 ? ' <span style="font-size:.7rem;color:#CE2626">(habis)</span>' : '') +
                    '</td>' +
                '</tr>';
            }).join('');

        document.getElementById('mk-body').innerHTML =
            '<div style="background:#f0f4ff;border-radius:8px;padding:.7rem 1rem;margin-bottom:1rem;font-size:.83rem">' +
                '<strong>' + escHtml(m.nama_material) + '</strong>' +
                (m.kode_sap ? ' &nbsp;·&nbsp; <code style="font-size:.75rem">' + escHtml(m.kode_sap) + '</code>' : '') +
                ' &nbsp;·&nbsp; Rak: <strong>' + escHtml(m.kode_rak || '—') + '</strong>' +
                ' &nbsp;·&nbsp; Total Stok: <strong>' + m.stok + ' ' + escHtml(m.satuan) + '</strong>' +
            '</div>' +
            '<table style="width:100%;border-collapse:collapse;font-size:.84rem">' +
                '<thead><tr style="background:#f7f5ee;border-bottom:1px solid #e2d9c0">' +
                    '<th style="padding:.6rem 1rem;text-align:left;font-size:.67rem;text-transform:uppercase;letter-spacing:.07em;color:#8d9ab5">Requester / Pemilik</th>' +
                    '<th style="padding:.6rem 1rem;text-align:right;font-size:.67rem;text-transform:uppercase;letter-spacing:.07em;color:#8d9ab5">Qty Tersedia</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '<tfoot><tr style="border-top:2px solid #e2d9c0;background:#f7f5ee">' +
                    '<td style="padding:.65rem 1rem;font-weight:700">Total Tercatat</td>' +
                    '<td style="padding:.65rem 1rem;text-align:right;font-weight:700">' + totalMilik + ' ' + escHtml(m.satuan) + '</td>' +
                '</tr></tfoot>' +
            '</table>' +
            (m.stok > totalMilik ? '<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:.6rem 1rem;margin-top:.8rem;font-size:.78rem;color:#92400e">⚠️ Ada <strong>' + (m.stok - totalMilik) + ' ' + escHtml(m.satuan) + '</strong> stok tanpa requester tercatat</div>' : '');
    })
    .catch(function() {
        document.getElementById('mk-body').innerHTML = '<div style="color:red;padding:1rem">Gagal memuat data.</div>';
    });
}

// ── Variabel state modal detail ───────────────────────────────────────────────
var _currentDetailId = null;
var _currentDetailData = null;
var _supplierMap = <?php
    $supMap = [];
    foreach ($suppliers as $s) $supMap[$s['id']] = $s['nama_supplier'];
    echo json_encode($supMap);
?>;

function switchMdTab(tab, btn) {
    ['lihat','edit','log'].forEach(function(t) {
        document.getElementById('mdtab-content-' + t).style.display = t === tab ? 'block' : 'none';
    });
    document.querySelectorAll('.md-tab-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    if (tab === 'log' && _currentDetailId) loadEditLog(_currentDetailId);
    if (tab === 'edit' && _currentDetailId && _currentDetailData) renderEditTab(_currentDetailId, _currentDetailData);
}

function showDetail(id) {
    _currentDetailId = id;
    document.getElementById('modal-detail-title').textContent = 'Detail Penerimaan';
    document.getElementById('mdtab-content-lihat').innerHTML = '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    document.getElementById('mdtab-content-edit').innerHTML  = '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    document.getElementById('mdtab-content-log').innerHTML   = '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    // reset ke tab pertama
    switchMdTab('lihat', document.getElementById('mdtab-lihat'));
    document.getElementById('modal-detail').style.display = 'flex';
    fetch('/penerimaan/detail/' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        _currentDetailData = res;
        document.getElementById('modal-detail-title').textContent = 'Detail ' + res.header.no_surat_penerimaan;
        renderLihatTab(res);
    });
}

function renderLihatTab(res) {
    var h = res.header;
    var rows = res.detail.length === 0
        ? '<tr><td colspan="9" class="tbl-empty">Tidak ada item</td></tr>'
        : res.detail.map(function(d) {
            return '<tr>' +
                '<td><code>' + escHtml(d.kode_sap || '—') + '</code></td>' +
                '<td>' + escHtml(d.nama_material || 'Tanpa Nama') +
                    (parseInt(d.is_material_baru) ? ' <span class="badge-gt badge-aktif">Baru</span>' : '') +
                    (parseInt(d.is_tabung) ? ' <span class="badge-gt badge-tabung">TABUNG</span>' : '') +
                '</td>' +
                '<td style="text-align:right">' + d.jumlah_terima + ' ' + escHtml(d.satuan || '') + '</td>' +
                '<td>' + (d.batch ? '<span class="badge-gt" style="background:#e0e7ff;color:#3730a3;font-size:.72rem">' + escHtml(d.batch) + '</span>' : '—') + '</td>' +
                '<td>' + escHtml(d.kondisi) + '</td>' +
                '<td>' + escHtml(d.kode_rak || '—') + '</td>' +
                '<td><strong style="color:#1a2744">' + escHtml(d.requester || '—') + '</strong></td>' +
                '<td>' +
                    (d.requester && d.material_id
                        ? '<button onclick="showKepemilikan(' + d.material_id + ',\'' + escHtml(d.nama_material) + '\')" style="background:#1a2744;color:#fff;border:none;border-radius:6px;padding:2px 8px;font-size:.7rem;cursor:pointer">👥 Lihat</button>'
                        : '—') +
                '</td>' +
            '</tr>';
        }).join('');
    document.getElementById('mdtab-content-lihat').innerHTML =
        '<div class="detail-header-info">' +
            '<div><span class="dhi-label">Tanggal</span><span class="dhi-val">' + h.tanggal_terima + '</span></div>' +
            '<div><span class="dhi-label">Supplier</span><span class="dhi-val">' + escHtml(h.nama_supplier||'-') + '</span></div>' +
            '<div><span class="dhi-label">Petugas</span><span class="dhi-val">' + escHtml(h.nama_petugas||'-') + '</span></div>' +
            (h.catatan ? '<div style="grid-column:1/-1"><span class="dhi-label">Catatan</span><span class="dhi-val">' + escHtml(h.catatan) + '</span></div>' : '') +
        '</div>' +
        '<div class="tbl-wrap"><table class="tbl-g"><thead><tr>' +
        '<th>Kode SAP</th><th>Deskripsi</th><th>Qty</th><th>Batch</th><th>Kondisi</th><th>Rak</th><th>Requester</th><th>Kepemilikan</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';
}

function renderEditTab(id, res) {
    var h = res.header;
    // ── Edit header ──────────────────────────────────────────────────────────
    var supOpts = Object.keys(_supplierMap).map(function(k) {
        return '<option value="' + k + '"' + ((String(k) === String(h.supplier_id)) ? ' selected' : '') + '>' + escHtml(_supplierMap[k]) + '</option>';
    }).join('');

    var headerHtml =
        '<div style="background:#f0f4ff;border-radius:10px;padding:1rem;margin-bottom:1rem">' +
            '<div style="font-weight:700;font-size:.82rem;color:var(--navy);margin-bottom:.7rem">📋 Edit Header</div>' +
            '<div class="edit-form-grid">' +
                '<div><label class="form-label-gt">Tanggal Terima</label>' +
                    '<input type="date" class="form-control-gt" id="edt-tgl" value="' + h.tanggal_terima + '"></div>' +
                '<div><label class="form-label-gt">Supplier</label>' +
                    '<select class="form-select-gt" id="edt-supplier">' + supOpts + '</select></div>' +
                '<div style="grid-column:1/-1"><label class="form-label-gt">Catatan</label>' +
                    '<input type="text" class="form-control-gt" id="edt-catatan" value="' + escHtml(h.catatan||'') + '"></div>' +
            '</div>' +
            '<div style="display:flex;justify-content:flex-end;margin-top:.6rem">' +
                '<button class="btn-navy-g" onclick="simpanEditHeader(' + id + ')" style="font-size:.78rem">💾 Simpan Header</button>' +
            '</div>' +
        '</div>';

    // ── Edit item per baris ──────────────────────────────────────────────────
    var itemsHtml = '<div style="font-weight:700;font-size:.82rem;color:var(--navy);margin-bottom:.6rem">📦 Item Material</div>';
    if (res.detail.length === 0) {
        itemsHtml += '<div style="color:#9ca3af;font-size:.82rem;padding:.8rem;text-align:center">Tidak ada item</div>';
    } else {
        itemsHtml += res.detail.map(function(d, idx) {
            var kondisiOpts = ['baik','rusak','rekondisi'].map(function(k) {
                return '<option value="' + k + '"' + (d.kondisi === k ? ' selected' : '') + '>' + k + '</option>';
            }).join('');
            return '<div class="edit-item-card" id="edit-item-card-' + d.id + '">' +
                '<div class="edit-item-title">' +
                    '<span>' + (d.kode_sap ? '<code style="font-size:.72rem">' + escHtml(d.kode_sap) + '</code> ' : '') + escHtml(d.nama_material||'—') + '</span>' +
                    '<button class="btn-del-item" onclick="hapusItemDetail(' + id + ',' + d.id + ',\'' + escHtml(d.nama_material) + '\')" title="Hapus item ini">🗑</button>' +
                '</div>' +
                '<div class="edit-form-grid">' +
                    '<div><label class="cart-edit-label">Qty</label>' +
                        '<input type="number" class="form-control-gt" id="edit-qty-' + d.id + '" value="' + d.jumlah_terima + '" min="1" style="font-size:.82rem"></div>' +
                    '<div><label class="cart-edit-label">Kondisi</label>' +
                        '<select class="form-select-gt" id="edit-kondisi-' + d.id + '" style="font-size:.82rem">' + kondisiOpts + '</select></div>' +
                    '<div><label class="cart-edit-label">Rak</label>' +
                        '<input type="text" class="form-control-gt" id="edit-rak-' + d.id + '" value="' + escHtml(d.kode_rak||'') + '" placeholder="Kode rak" style="font-size:.82rem"></div>' +
                    '<div><label class="cart-edit-label">Requester</label>' +
                        '<input type="text" class="form-control-gt" id="edit-req-' + d.id + '" value="' + escHtml(d.requester||'') + '" placeholder="—" style="font-size:.82rem"></div>' +
                '</div>' +
                '<div style="display:flex;justify-content:flex-end;margin-top:.4rem">' +
                    '<button class="btn-save-item" onclick="simpanEditItem(' + id + ',' + d.id + ')">✔ Simpan Baris Ini</button>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    // ── Tambah item baru ─────────────────────────────────────────────────────
    var tambahHtml =
        '<div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:1rem;margin-top:1rem">' +
            '<div style="font-weight:700;font-size:.82rem;color:#15803d;margin-bottom:.7rem">➕ Tambah Material ke Surat Ini</div>' +
            '<div class="form-row"><label class="form-label-gt">Kode SAP</label>' +
                '<div class="input-group-gt">' +
                    '<input type="text" class="form-control-gt" id="edit-tambah-kode" placeholder="Kode SAP..." onkeydown="if(event.key===\'Enter\')cariMaterialTambah()">' +
                    '<button class="btn-navy-g" onclick="cariMaterialTambah()">Cari</button>' +
                '</div>' +
            '</div>' +
            '<div id="edit-tambah-hasil"></div>' +
        '</div>';

    document.getElementById('mdtab-content-edit').innerHTML = headerHtml + itemsHtml + tambahHtml;
}

function simpanEditHeader(id) {
    var tgl      = document.getElementById('edt-tgl').value;
    var supId    = document.getElementById('edt-supplier').value;
    var catatan  = document.getElementById('edt-catatan').value.trim();
    if (!tgl || !supId) { alert('Tanggal dan supplier wajib diisi!'); return; }

    fetch('/penerimaan/edit-header/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        body: JSON.stringify({ tanggal_terima: tgl, supplier_id: supId, catatan: catatan }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            UnsavedGuard.markClean();
            alert('✅ Header berhasil diperbarui!');
            reloadDetail(id);
        } else {
            alert('❌ ' + (res.message || 'Gagal menyimpan'));
        }
    });
}

function simpanEditItem(headerId, detailId) {
    var qty     = parseInt(document.getElementById('edit-qty-' + detailId) ? document.getElementById('edit-qty-' + detailId).value : 0) || 0;
    var kondisi = document.getElementById('edit-kondisi-' + detailId) ? document.getElementById('edit-kondisi-' + detailId).value : 'baik';
    var rak     = document.getElementById('edit-rak-' + detailId) ? document.getElementById('edit-rak-' + detailId).value.trim() : '';
    var req     = document.getElementById('edit-req-' + detailId) ? document.getElementById('edit-req-' + detailId).value.trim() : '';
    if (qty < 1) { alert('Qty minimal 1!'); return; }

    fetch('/penerimaan/edit-item/' + headerId + '/' + detailId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        body: JSON.stringify({ jumlah_terima: qty, kondisi: kondisi, kode_rak: rak, requester: req }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            UnsavedGuard.markClean();
            alert('✅ Item berhasil diperbarui!');
            reloadDetail(headerId);
        } else {
            alert('❌ ' + (res.message || 'Gagal menyimpan'));
        }
    });
}

function hapusItemDetail(headerId, detailId, namaMaterial) {
    if (!confirm('Yakin hapus item "' + namaMaterial + '"?\n\nPerhatian: Stok monitoring akan berkurang sesuai qty item ini.')) return;

    fetch('/penerimaan/hapus-item/' + headerId + '/' + detailId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            alert('✅ Item berhasil dihapus. Stok monitoring sudah diperbarui.');
            reloadDetail(headerId);
        } else {
            alert('❌ ' + (res.message || 'Gagal menghapus'));
        }
    });
}

function cariMaterialTambah() {
    var kode = document.getElementById('edit-tambah-kode').value.trim();
    var hasil = document.getElementById('edit-tambah-hasil');
    if (!kode) { alert('Masukkan kode SAP terlebih dahulu!'); return; }
    hasil.innerHTML = '<div style="color:#9ca3af;font-size:.8rem;padding:.5rem">Mencari...</div>';
    fetch('/penerimaan/cari-material?kode=' + encodeURIComponent(kode), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.found) { hasil.innerHTML = '<div style="color:var(--red);font-size:.8rem;padding:.5rem">Material tidak ditemukan.</div>'; return; }
        var m = res.material;
        var kondisiOpts = ['baik','rusak','rekondisi'].map(function(k){ return '<option value="'+k+'">'+k+'</option>'; }).join('');
        hasil.innerHTML =
            '<div class="edit-item-card" style="margin-top:.5rem">' +
                '<div class="edit-item-title"><span><code style="font-size:.72rem">' + escHtml(m.kode_sap||'—') + '</code> ' + escHtml(m.nama_material) + '</span></div>' +
                '<div class="edit-form-grid">' +
                    '<div><label class="cart-edit-label">Qty *</label><input type="number" class="form-control-gt" id="tambah-qty" min="1" value="1" style="font-size:.82rem"></div>' +
                    '<div><label class="cart-edit-label">Kondisi</label><select class="form-select-gt" id="tambah-kondisi" style="font-size:.82rem">' + kondisiOpts + '</select></div>' +
                    '<div><label class="cart-edit-label">Rak</label><input type="text" class="form-control-gt" id="tambah-rak" value="' + escHtml(m.kode_rak||'') + '" style="font-size:.82rem"></div>' +
                    '<div><label class="cart-edit-label">Requester</label><input type="text" class="form-control-gt" id="tambah-req" placeholder="Opsional" style="font-size:.82rem"></div>' +
                '</div>' +
                '<div style="display:flex;justify-content:flex-end;margin-top:.4rem">' +
                    '<button class="btn-save-item" style="background:var(--green,#1a7f4b)" onclick="simpanTambahItem(' + _currentDetailId + ',' + m.id + ')">➕ Tambahkan ke Surat</button>' +
                '</div>' +
            '</div>';
    })
    .catch(function(){ hasil.innerHTML = '<div style="color:var(--red);font-size:.8rem">Gagal mencari material.</div>'; });
}

function simpanTambahItem(headerId, materialId) {
    var qty     = parseInt(document.getElementById('tambah-qty') ? document.getElementById('tambah-qty').value : 0) || 0;
    var kondisi = document.getElementById('tambah-kondisi') ? document.getElementById('tambah-kondisi').value : 'baik';
    var rak     = document.getElementById('tambah-rak') ? document.getElementById('tambah-rak').value.trim() : '';
    var req     = document.getElementById('tambah-req') ? document.getElementById('tambah-req').value.trim() : '';
    if (qty < 1) { alert('Qty minimal 1!'); return; }

    fetch('/penerimaan/tambah-item/' + headerId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        body: JSON.stringify({ material_id: materialId, jumlah_terima: qty, kondisi: kondisi, kode_rak: rak, requester: req }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            UnsavedGuard.markClean();
            alert('✅ Material berhasil ditambahkan ke surat!');
            reloadDetail(headerId);
        } else {
            alert('❌ ' + (res.message || 'Gagal menambahkan'));
        }
    });
}

function loadEditLog(id) {
    document.getElementById('mdtab-content-log').innerHTML = '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat riwayat...</div>';
    fetch('/penerimaan/edit-log/' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.log || res.log.length === 0) {
            document.getElementById('mdtab-content-log').innerHTML = '<div style="text-align:center;padding:2rem;color:#9ca3af">Belum ada riwayat perubahan</div>';
            return;
        }
        var aksiLabel = { edit_header: '📋 Edit Header', edit_item: '✏️ Edit Item', hapus_item: '🗑 Hapus Item', tambah_item: '➕ Tambah Item' };
        var rows = res.log.map(function(l) {
            var detail = '';
            if (l.field_diubah) {
                try {
                    var fd = JSON.parse(l.field_diubah);
                    detail = Object.keys(fd).map(function(k) {
                        return '<div style="font-size:.72rem;color:#6b7280">' +
                            '<strong>' + escHtml(k) + ':</strong> ' +
                            '<span style="color:var(--red);text-decoration:line-through">' + escHtml(String(fd[k].lama||'—')) + '</span>' +
                            ' → <span style="color:var(--green,#1a7f4b)">' + escHtml(String(fd[k].baru||'—')) + '</span>' +
                        '</div>';
                    }).join('');
                } catch(e) {}
            }
            return '<div class="log-entry">' +
                '<div class="log-meta">' +
                    '<span class="log-aksi">' + (aksiLabel[l.aksi] || l.aksi) + '</span>' +
                    '<span class="log-user">👤 ' + escHtml(l.nama_user||'—') + '</span>' +
                    '<span class="log-time">🕑 ' + escHtml(l.created_at||'—') + '</span>' +
                '</div>' +
                (l.keterangan ? '<div style="font-size:.78rem;color:#374151;margin:.3rem 0">' + escHtml(l.keterangan) + '</div>' : '') +
                detail +
            '</div>';
        }).join('');
        document.getElementById('mdtab-content-log').innerHTML = '<div>' + rows + '</div>';
    })
    .catch(function(){ document.getElementById('mdtab-content-log').innerHTML = '<div style="color:var(--red);padding:1rem">Gagal memuat riwayat.</div>'; });
}

function reloadDetail(id) {
    fetch('/penerimaan/detail/' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        _currentDetailData = res;
        document.getElementById('modal-detail-title').textContent = 'Detail ' + res.header.no_surat_penerimaan;
        var activeTab = document.querySelector('.md-tab-btn.active');
        if (activeTab && activeTab.id === 'mdtab-edit') {
            renderEditTab(id, res);
        } else {
            renderLihatTab(res);
        }
    });
}

function closeModal() { document.getElementById('modal-detail').style.display = 'none'; _currentDetailId = null; _currentDetailData = null; }
document.getElementById('modal-detail').addEventListener('click', function(e) { if(e.target===this) closeModal(); });

function switchTab(tab, btn) {
    document.getElementById('tab-form').style.display    = tab==='form'    ? 'block' : 'none';
    document.getElementById('tab-riwayat').style.display = tab==='riwayat' ? 'block' : 'none';
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
}

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

</script>

<style>
.tabs-bar{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:1rem}
.tab-btn{background:none;border:none;padding:.6rem 1.2rem;font-weight:600;font-size:.85rem;color:var(--ink3);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.2s}
.tab-btn.active{color:var(--navy);border-bottom-color:var(--navy)}
.mode-bar{display:flex;gap:.4rem}
.mode-btn{flex:1;background:#f0f2f8;border:1.5px solid var(--border);border-radius:8px;padding:.45rem .6rem;font-weight:600;font-size:.78rem;color:var(--navy);cursor:pointer;transition:.2s;text-align:center}
.mode-btn.active{background:var(--navy);color:#fff;border-color:var(--navy)}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-grid-2-sm{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.6rem}
.card-body-g{padding:1.2rem}
.card-header-g{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;border-radius:12px 12px 0 0}
.form-row{margin-bottom:.75rem}
.form-label-gt{display:block;font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:4px}
.form-control-gt,.form-select-gt{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:.48rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;outline:none;box-sizing:border-box;transition:.2s}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(26,39,68,.08)}
.input-group-gt{display:flex;gap:.4rem}
.input-group-gt .form-control-gt{flex:1}
.btn-navy-g{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.48rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;white-space:nowrap}
.btn-navy-g.w-100{width:100%;margin-top:.6rem}
.btn-outline-g{background:transparent;color:var(--navy);border:1.5px solid var(--border);border-radius:8px;padding:.4rem .9rem;font-weight:600;font-size:.8rem;cursor:pointer}
.btn-green-g{background:var(--green,#1a7f4b);color:#fff;border:none;border-radius:8px;padding:.48rem 1.2rem;font-weight:700;font-size:.85rem;cursor:pointer}
.btn-link-g{background:none;border:none;color:var(--navy);font-size:.75rem;cursor:pointer;text-decoration:underline;padding:0;display:block}
.btn-sm-g{background:#fff;border:1.5px solid var(--border);border-radius:6px;padding:.3rem .7rem;font-weight:600;font-size:.75rem;color:var(--navy);cursor:pointer}
.btn-detail-g{display:inline-flex;align-items:center;gap:.35rem;background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.4rem .85rem;font-weight:600;font-size:.78rem;cursor:pointer;transition:.2s}
.btn-detail-g:hover{opacity:.88;transform:translateY(-1px)}
.btn-detail-icon{font-size:.85rem}
.mt-1{margin-top:.3rem}
.no-surat-chip{background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;padding:.5rem .9rem;font-size:.8rem;color:#374151;margin-bottom:.8rem}
.petugas-chip{background:#f8f9fc;border-radius:8px;padding:.5rem .9rem;font-size:.78rem;color:#6b7280;margin-top:.5rem}
.supplier-baru-box{background:#fffbeb;border:1.5px solid #fcd34d;border-radius:10px;padding:1rem;margin:.5rem 0}
.supplier-baru-title{font-weight:700;font-size:.82rem;color:#92400e;margin-bottom:.6rem}
.mat-info-box{background:#f0f4ff;border-radius:8px;padding:.7rem .9rem;margin-bottom:.7rem}
.mat-info-name{font-weight:700;color:var(--navy);font-size:.88rem}
.mat-info-meta{font-size:.75rem;color:#6b7280;margin-top:2px}
.notif-warning{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;color:#92400e;margin-bottom:.7rem}
.notif-info{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;color:#1e40af;margin-bottom:.7rem}
.tabung-bulk-box{background:#f8faff;border:1.5px solid #c7d2fe;border-radius:8px;padding:.6rem .8rem;margin-bottom:.6rem;display:flex;flex-direction:column;gap:.4rem}
.tabung-bulk-row{display:flex;align-items:center;gap:.5rem}
.tabung-bulk-num{background:var(--navy);color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0}
.cart-item{display:flex;align-items:flex-start;justify-content:space-between;padding:.7rem 1rem;border-bottom:1px solid var(--border)}
.cart-item-name{font-weight:600;color:#1f2937;font-size:.85rem}
.cart-item-meta{font-size:.75rem;color:#6b7280;margin-top:2px}
.cart-remove{background:none;border:none;color:#9ca3af;font-size:1.1rem;cursor:pointer;padding:.2rem .5rem;border-radius:6px;flex-shrink:0}
.cart-remove:hover{background:#fee2e2;color:var(--red)}
.cart-edit{background:none;border:none;color:#9ca3af;font-size:.95rem;cursor:pointer;padding:.2rem .4rem;border-radius:6px;flex-shrink:0}
.cart-edit:hover{background:#e0e7ff;color:var(--navy)}
.cart-edit-form{background:#f8faff;border:1.5px solid #c7d2fe;border-radius:10px;padding:.9rem 1rem;margin:.2rem .4rem .4rem}
.cart-edit-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem}
.cart-edit-row{display:flex;flex-direction:column;gap:3px}
.cart-edit-label{font-size:.72rem;font-weight:700;color:var(--navy)}
.cart-footer{display:flex;justify-content:flex-end;gap:.5rem;padding:.8rem 1rem;border-top:1px solid var(--border)}
.cart-tabung-chips{display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.3rem}
.cart-tabung-chip{background:#ede9fe;color:#5b21b6;border-radius:6px;padding:.15rem .5rem;font-size:.7rem;font-weight:600}
.empty-state{text-align:center;padding:2rem;color:#9ca3af;font-size:.85rem}
.tbl-empty{text-align:center;padding:2rem;color:#9ca3af}
.detail-header-info{display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem;background:#f8f9fc;border-radius:8px;padding:.8rem 1rem;margin-bottom:.8rem}
.dhi-label{font-size:.72rem;color:#6b7280;display:block}
.dhi-val{font-size:.85rem;font-weight:600;color:#1f2937}
.badge-gt{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700}
.badge-tabung{background:#fdf4ff;color:#7e22ce}
.badge-aktif{background:#dbeafe;color:#1d4ed8}
.badge-warning{background:#fef3c7;color:#92400e}
.modal-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);z-index:5000;display:flex;align-items:center;justify-content:center}
#modal-kepemilikan{z-index:5100}
.modal-box{background:#fff;border-radius:16px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);display:flex;flex-direction:column}
.modal-head{background:linear-gradient(135deg, #1a3a7c 0%, #2d5a9f 100%);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem;color:#ffffff}
.modal-close{background:none;border:none;color:#ffffff;font-size:1.2rem;cursor:pointer}
.modal-close:hover{color:#fff}
.modal-body-gt{padding:1.3rem;overflow-y:auto}
.md-tab-btn{background:none;border:none;padding:.55rem 1.1rem;font-weight:600;font-size:.78rem;color:#6b7280;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;transition:.2s}
.md-tab-btn.active{color:var(--navy);border-bottom-color:var(--navy);background:#fff}
.md-tab-btn:hover:not(.active){color:var(--navy);background:#f0f4ff}
.edit-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem}
.edit-item-card{background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;padding:.8rem 1rem;margin-bottom:.6rem}
.edit-item-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;font-size:.82rem;font-weight:600;color:#1f2937}
.btn-del-item{background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1rem;padding:.2rem .4rem;border-radius:6px}
.btn-del-item:hover{background:#fee2e2;color:var(--red,#dc2626)}
.btn-save-item{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.35rem .9rem;font-size:.75rem;font-weight:600;cursor:pointer}
.btn-save-item:hover{opacity:.88}
.log-entry{border-left:3px solid #c7d2fe;padding:.5rem .9rem;margin-bottom:.5rem;background:#f8f9fc;border-radius:0 8px 8px 0}
.log-meta{display:flex;gap:.8rem;flex-wrap:wrap;align-items:center;margin-bottom:.2rem}
.log-aksi{font-weight:700;font-size:.78rem;color:var(--navy)}
.log-user{font-size:.72rem;color:#6b7280}
.log-time{font-size:.72rem;color:#9ca3af}
.pagination-bar{display:flex;align-items:center;gap:.6rem;justify-content:center}
.pg-btn{background:#fff;border:1.5px solid var(--border);border-radius:8px;padding:.4rem 1rem;font-weight:600;font-size:.82rem;color:var(--navy);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.pg-btn:hover:not([disabled]){background:var(--navy);color:#fff;border-color:var(--navy)}
.pg-btn[disabled]{opacity:.4;cursor:not-allowed}
.pg-info{font-size:.82rem;color:#6b7280;min-width:90px;text-align:center}
.mt-3{margin-top:1rem}
.mb-2{margin-bottom:.5rem}
.sh-g{font-size:.85rem;font-weight:700}
.choices{margin:0}
.choices__inner{border:1.5px solid var(--border)!important;border-radius:8px!important;background:#fff!important;padding:.3rem .6rem!important;min-height:38px!important;font-size:.83rem!important}
.choices__list--dropdown{border:1.5px solid var(--border)!important;border-radius:8px!important;box-shadow:0 4px 20px rgba(0,0,0,.1)!important;z-index:9999!important}
.choices__input{font-size:.83rem!important}
.choices__list--dropdown .choices__item--selectable.is-highlighted{background:var(--navy)!important;color:#fff!important}
.tbl-wrap{overflow-x:auto}
.tbl-g{width:100%;border-collapse:collapse}
.tbl-g thead{background:#f8f9fc;border-bottom:1px solid #e5e7eb}
.tbl-g th{padding:.45rem .6rem;text-align:left;font-size:.75rem;font-weight:700;color:#4b5563;letter-spacing:.02em}
.tbl-g td{padding:.45rem .6rem;border-bottom:1px solid #f0f2f8;font-size:.83rem}
.tbl-g tbody tr:hover{background:#f9fafb}
@media(max-width:768px){.form-grid-2{grid-template-columns:1fr}.form-grid-2-sm{grid-template-columns:1fr}}

/* .card-g pakai backdrop-filter, yang otomatis membentuk stacking context
   sendiri per kartu. Tanpa ini, dropdown Lokasi Rak (z-index:60) di kartu
   "Tambah Item" ketimbun kartu "Keranjang" di bawahnya, karena keduanya
   stacking context terpisah dan Keranjang render belakangan di DOM. */
.card-tambah-item{position:relative;z-index:5}

/* ============================================================
   RESPONSIVE — HP (desktop/laptop tidak berubah)
   ============================================================ */
@media (max-width:640px) {
  .tabs-bar { overflow-x:auto; flex-wrap:nowrap; -webkit-overflow-scrolling:touch; }
  .tab-btn { white-space:nowrap; flex-shrink:0; }
  .modal-box { max-width:100% !important; width:100% !important; margin:0 8px; }
  .modal-body-gt, .modal-body { padding:.9rem !important; }
  .md-tab-btn { font-size:.76rem; padding:.5rem .6rem; }
  .detail-header-info { grid-template-columns:1fr !important; gap:.5rem; }
  .tbl-g th, .tbl-g td { padding:.4rem .55rem; font-size:.76rem; }
  #tab-riwayat .tbl-g td[style*="display:flex"] { flex-direction:column; align-items:stretch; }
}
</style>

<?= $this->endSection() ?>