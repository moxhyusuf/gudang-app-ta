<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Pengeluaran Material (Bon GI)</h1>
    <p>Catat pengeluaran material berdasarkan bon administrasi</p>
  </div>
</div>

<div class="tabs-bar mb-3">
  <button class="tab-btn active" onclick="switchTab('form',this)">📝 Form Bon</button>
  <button class="tab-btn" onclick="switchTab('riwayat',this)">📋 Riwayat Bon</button>
</div>

<!-- ══ TAB FORM ══════════════════════════════════════════════════════════════ -->
<div id="tab-form" class="tab-content">
  <div class="form-grid-2">

    <!-- Header Bon -->
    <div class="card-g">
      <div class="card-header-g">📋 Header Bon GI</div>
      <div class="card-body-g">
        <div class="no-surat-chip">No. Bon: <strong><?= esc($no_bon) ?></strong> (auto)</div>

        <div class="form-row">
          <label class="form-label-gt">Tanggal Bon *</label>
          <input type="date" id="bon-tgl" class="form-control-gt" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-row">
          <label class="form-label-gt">Plant *</label>
          <select id="bon-plant" class="form-select-gt">
            <option value="">Pilih plant...</option>
            <?php foreach ($plants as $p): ?>
            <option value="<?= $p['id'] ?>"><?= esc($p['nama_plant']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <label class="form-label-gt">Nama Pengambil *</label>
          <input type="text" id="bon-pengambil" class="form-control-gt" placeholder="Nama yang mengambil barang...">
        </div>

        <div class="form-row">
          <label class="form-label-gt">Keperluan *</label>
          <input type="text" id="bon-keperluan" class="form-control-gt" placeholder="Contoh: Maintenance pompa #3, Produksi line A...">
        </div>

        <div class="form-row">
          <label class="form-label-gt">Catatan</label>
          <textarea id="bon-catatan" class="form-control-gt" rows="2" placeholder="Opsional..."></textarea>
        </div>

        <div class="petugas-chip">Petugas: <strong><?= esc($nama) ?></strong> (auto)</div>
      </div>
    </div>

    <!-- Tambah Item -->
    <div class="card-g">
      <div class="card-header-g">➕ Tambah Item Material</div>
      <div class="card-body-g">
        <div class="form-row">
          <label class="form-label-gt">Mode Input</label>
          <div class="toggle-mode-bar">
            <button id="btn-mode-kode" class="toggle-mode-btn active" onclick="setMode('kode')">🔢 Dengan Kode SAP</button>
            <button id="btn-mode-nama" class="toggle-mode-btn" onclick="setMode('nama')">🔤 Tanpa Kode SAP</button>
          </div>
        </div>

        <!-- Mode: Dengan Kode SAP -->
        <div id="area-mode-kode">
          <div class="form-row">
            <label class="form-label-gt">Kode SAP</label>
            <div class="input-group-gt">
              <input type="text" id="bon-kode" class="form-control-gt"
                     placeholder="Contoh: 600001234"
                     onkeydown="if(event.key==='Enter')cariSAP()">
              <button class="btn-navy-g" onclick="cariSAP()">Cari</button>
            </div>
          </div>
          <div id="bon-item-area"></div>
        </div>

        <!-- Mode: Tanpa Kode SAP -->
        <div id="area-mode-nama" style="display:none">
          <div class="form-row">
            <label class="form-label-gt">Nama Material</label>
            <div class="input-group-gt">
              <input type="text" id="bon-nama-cari" class="form-control-gt"
                     placeholder="Ketik nama material..."
                     oninput="cariNama(this.value)">
            </div>
            <div id="bon-nama-dropdown" class="nama-dropdown" style="display:none"></div>
          </div>
          <div id="bon-item-area-nama"></div>
        </div>
      </div>
    </div>

  </div>

  <!-- Keranjang -->
  <div class="card-g mt-3">
    <div class="sh-g" style="background:var(--navy);color:#fff;border-radius:12px 12px 0 0;padding:.8rem 1.2rem">
      <span style="font-weight:700;font-size:.85rem">🛒 Keranjang Bon — <span id="keranjang-count">0 item</span></span>
    </div>
    <div id="keranjang-body">
      <div class="empty-state">Belum ada item</div>
    </div>
  </div>
</div>

<!-- ══ TAB RIWAYAT ═══════════════════════════════════════════════════════════ -->
<div id="tab-riwayat" class="tab-content" style="display:none">
  <div class="card-g">
    <div class="card-header-g">📋 Riwayat Bon GI</div>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead>
          <tr>
            <th>No. Bon</th><th>Tanggal</th><th>Plant</th>
            <th>Pengambil</th><th>Keperluan</th><th>Item</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($riwayat)): ?>
          <tr><td colspan="7" class="tbl-empty">Belum ada data</td></tr>
          <?php else: ?>
          <?php foreach ($riwayat as $r): ?>
          <tr>
            <td><code class="mono" style="font-size:.75rem;color:var(--navy)"><?= esc($r['no_bon']) ?></code></td>
            <td><?= esc($r['tanggal_bon']) ?></td>
            <td><?= esc($r['nama_plant'] ?? '—') ?></td>
            <td><?= esc($r['nama_pengambil']) ?></td>
            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($r['keperluan']) ?></td>
            <td><?= $r['jml_item'] ?> item</td>
            <td>
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
      <a href="/pengeluaran?page=<?= $current_page - 1 ?>" class="pg-btn">← Prev</a>
      <?php else: ?><button class="pg-btn" disabled>← Prev</button><?php endif; ?>
      <span class="pg-info">Hal <strong><?= $current_page ?></strong> / <?= $total_page ?></span>
      <?php if ($current_page < $total_page): ?>
      <a href="/pengeluaran?page=<?= $current_page + 1 ?>" class="pg-btn">Next →</a>
      <?php else: ?><button class="pg-btn" disabled>Next →</button><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Detail -->
<div id="modal-detail" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:1200px">
    <div class="modal-head">
      <h6 id="modal-detail-title">Detail Bon</h6>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body-gt" id="modal-detail-body">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
  </div>
</div>

<script>
var keranjang = [];
UnsavedGuard.watch('#tab-form', 'Data pengeluaran (header, item, atau keranjang bon) yang sedang diisi belum disimpan. Yakin ingin pindah halaman?');
var _lastRequesterList = []; // requester_list dari material yang sedang ditampilkan di form tambah item

document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'riwayat') {
        switchTab('riwayat', document.querySelectorAll('.tab-btn')[1]);
    }
});

// ── Cari material ─────────────────────────────────────────────────────────────
function cariSAP() {
    var kode = document.getElementById('bon-kode').value.trim();
    if (!kode) { alert('Masukkan Kode SAP!'); return; }
    var area = document.getElementById('bon-item-area');
    area.innerHTML = '<div style="color:#6b7280;font-size:.82rem;padding:.5rem 0">🔍 Mencari...</div>';

    fetch('/pengeluaran/cari-material?kode=' + encodeURIComponent(kode), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.found) {
            area.innerHTML = '<div class="notif-warning">⚠️ Kode SAP <strong>' + escHtml(kode) + '</strong> tidak ditemukan atau tidak aktif.</div>';
            return;
        }

        // Jika ada multi-batch, tampilkan pemilihan batch terlebih dahulu
        if (res.multi_batch && res.batch_list && res.batch_list.length > 1) {
            var batchRows = res.batch_list.map(function(b, idx) {
                var stokColor = b.stok_tersedia <= 0 ? 'color:var(--red)' : (b.stok_tersedia <= 5 ? 'color:var(--amber)' : 'color:var(--green)');
                return '<label class="batch-select-row' + (b.stok_tersedia <= 0 ? ' batch-kosong' : '') + '" onclick="pilihBatch(' + idx + ', ' + JSON.stringify(res) + ')">' +
                    '<div class="batch-select-info">' +
                        '<strong>' + escHtml(b.batch || 'UMUM') + '</strong>' +
                        '<span class="batch-select-meta">Stok tersedia: <strong style="' + stokColor + '">' + b.stok_tersedia + ' ' + escHtml(b.satuan) + '</strong></span>' +
                    '</div>' +
                    '<span class="batch-select-arrow">›</span>' +
                '</label>';
            }).join('');
            area.innerHTML =
                '<div class="mat-info-box">' +
                    '<div class="mat-info-name">' + escHtml(res.material.nama_material) + '</div>' +
                    '<div class="mat-info-meta"><code>' + escHtml(kode) + '</code> &nbsp;·&nbsp; Material ini memiliki <strong>' + res.batch_list.length + ' batch berbeda</strong></div>' +
                '</div>' +
                '<div class="batch-select-label">Pilih Batch yang akan dikeluarkan:</div>' +
                '<div class="batch-select-list">' + batchRows + '</div>';
            return;
        }

        // Single batch — langsung tampilkan seperti biasa
        var m = res.material;
        renderMatArea(area, m, res);
    })
    .catch(function() {
        area.innerHTML = '<div style="color:var(--red);padding:.5rem">Gagal menghubungi server.</div>';
    });
}

function pilihBatch(idx, res) {
    var area = document.getElementById('bon-item-area');
    var batchEntry = res.batch_list[idx];
    // Bentuk objek material dari batch yang dipilih, sertakan requester_list dari batch tersebut
    var m = {
        id:             batchEntry.id,
        kode_sap:       batchEntry.kode_sap,
        nama_material:  batchEntry.nama_material,
        satuan:         batchEntry.satuan,
        batch:          batchEntry.batch,
        stok:           batchEntry.stok,
        stok_booking:   batchEntry.stok_booking,
        stok_tersedia:  batchEntry.stok_tersedia,
        is_tabung:      batchEntry.is_tabung,
        kode_rak:       batchEntry.kode_rak,
    };
    var syntheticRes = {
        requester_list: batchEntry.requester_list || [],
        tabung_list:    batchEntry.tabung_list    || [],
    };
    renderMatArea(area, m, syntheticRes);
}

function renderMatArea(area, m, res) {
        // Simpan requester_list material ini agar bisa dibawa ke item keranjang saat ditambahkan
        _lastRequesterList = res.requester_list || [];

        // Warna stok
        var stokColor = m.stok_tersedia <= 0 ? 'color:var(--red)' : (m.stok_tersedia <= 5 ? 'color:var(--amber)' : 'color:var(--green)');

        var fifoInfo = '';
        if (parseInt(m.is_tabung) === 1) {
            if (!res.tabung_list || res.tabung_list.length === 0) {
                fifoInfo = '<div class="notif-warning">⚠️ Tidak ada tabung tersedia untuk material ini.</div>';
            } else {
                var checkRows = res.tabung_list.map(function(t) {
                    var isRek = parseInt(t.is_rekomendasi) === 1;
                    var rekBadge = isRek
                        ? '<span class="badge-gt badge-fifo">FIFO #' + t.urutan_fifo + '</span>'
                        : '';
                    return '<label class="tabung-check-row' + (isRek ? ' is-rek' : '') + '">' +
                        '<input type="checkbox" class="tabung-cb" value="' + t.id + '"' +
                        ' data-notabung="' + escHtml(t.no_tabung) + '"' +
                        ' data-tgl="' + t.tanggal_masuk + '"' +
                        (isRek ? ' checked' : '') + '>' +
                        '<div class="tabung-check-info">' +
                            '<strong>' + escHtml(t.no_tabung) + '</strong> ' + rekBadge +
                            '<span class="tabung-check-meta">Masuk: ' + t.tanggal_masuk + ' &nbsp;·&nbsp; ' + t.kondisi + '</span>' +
                        '</div>' +
                    '</label>';
                }).join('');

                fifoInfo = '<div class="tabung-list-box">' +
                    '<div class="tabung-list-title">' +
                        '📦 Pilih Tabung yang Keluar &nbsp;' +
                        '<span style="font-size:.72rem;color:#6b7280;font-weight:400">' +
                            '(✅ = rekomendasi FIFO, boleh diubah)' +
                        '</span>' +
                        '<span class="tabung-sel-count" id="tabung-sel-count">0 dipilih</span>' +
                    '</div>' +
                    '<div class="tabung-list-scroll">' + checkRows + '</div>' +
                '</div>';
            }
        }

        var stokInfo = parseInt(m.is_tabung) === 1
            ? '<div class="stok-detail"><span>Tersedia: <strong style="' + stokColor + '">' + m.stok_tersedia + ' TBG</strong></span></div>'
            : '<div class="stok-detail"><span>Stok fisik: <strong>' + m.stok + '</strong></span> <span>Booking: <strong>' + m.stok_booking + '</strong></span> <span>Tersedia: <strong style="' + stokColor + '">' + m.stok_tersedia + ' ' + escHtml(m.satuan) + '</strong></span></div>';

        area.innerHTML =
            '<div class="mat-info-box">' +
                '<div class="mat-info-name">' + escHtml(m.nama_material) +
                    (parseInt(m.is_tabung) === 1 ? ' <span class="badge-gt badge-tabung">TABUNG GAS</span>' : '') +
                    (m.batch ? ' <span class="badge-gt" style="background:#e0e7ff;color:#3730a3;font-size:.72rem">' + escHtml(m.batch) + '</span>' : '') +
                '</div>' +
                '<div class="mat-info-meta"><code>' + escHtml(m.kode_sap) + '</code> &nbsp;·&nbsp; Rak: <strong>' + escHtml(m.kode_rak) + '</strong></div>' +
                stokInfo +
            '</div>' +
            fifoInfo +
            (m.stok_tersedia <= 0 && parseInt(m.is_tabung) === 0
                ? '<div class="notif-warning">⚠️ Stok tidak tersedia!</div>'
                : parseInt(m.is_tabung) === 1
                    ? (res.tabung_list && res.tabung_list.length > 0
                        ? '<button class="btn-navy-g w-100" onclick=\'addKeranjangTabung(' + JSON.stringify(m) + ')\'>+ Tambah Tabung Terpilih ke Keranjang</button>'
                        : '')
                    : '<div class="form-row" style="margin-top:.7rem"><label class="form-label-gt">Jumlah Keluar *</label>' +
                        '<input type="number" class="form-control-gt" id="bon-jml" min="1" max="' + m.stok_tersedia + '" value="1"></div>' +
                        buildRequesterDropdown(res.requester_list) +
                        '<button class="btn-navy-g w-100" onclick=\'addKeranjang(' + JSON.stringify(m) + ')\'>+ Tambah ke Keranjang</button>'
            );

        // Update counter saat checkbox berubah
        updateTabungCount();
}

// ── Update counter tabung terpilih ───────────────────────────────────────────
function updateTabungCount() {
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('tabung-cb')) {
            var cbs  = document.querySelectorAll('.tabung-cb:checked');
            var el   = document.getElementById('tabung-sel-count');
            if (el) el.textContent = cbs.length + ' dipilih';
        }
    });
}

// ── Tambah tabung terpilih ke keranjang ───────────────────────────────────────
function addKeranjangTabung(mat) {
    var cbs = document.querySelectorAll('.tabung-cb:checked');
    if (cbs.length === 0) { alert('Pilih minimal 1 tabung!'); return; }

    // Cek duplikat material di keranjang
    for (var i = 0; i < keranjang.length; i++) {
        if (keranjang[i].material_id === mat.id) {
            alert('Material ini sudah ada di keranjang. Hapus dulu jika ingin mengubah pilihan.');
            return;
        }
    }

    var tabungDipilih = [];
    cbs.forEach(function(cb) {
        tabungDipilih.push({
            id:        cb.value,
            no_tabung: cb.dataset.notabung,
            tgl_masuk: cb.dataset.tgl,
        });
    });

    keranjang.push({
        material_id:    mat.id,
        kode_sap:       mat.kode_sap,
        nama_material:  mat.nama_material,
        satuan:         'TBG',
        is_tabung:      1,
        kode_rak:       mat.kode_rak,
        jumlah_keluar:  tabungDipilih.length,
        stok_tersedia:  mat.stok_tersedia,
        tabung_dipilih: tabungDipilih,
    });

    document.getElementById('bon-kode').value = '';
    document.getElementById('bon-item-area').innerHTML = '';
    renderKeranjang();
}

// ── Helper: buat dropdown requester dari list kepemilikan ────────────────────
function buildRequesterDropdown(list) {
    if (!list || list.length === 0) {
        return '<div class="form-row"><label class="form-label-gt">Requester <span style="font-size:.7rem;color:#9ca3af">(belum ada data kepemilikan)</span></label>' +
               '<input type="text" class="form-control-gt" id="bon-requester" placeholder="Kosongkan jika tidak ada data requester"></div>';
    }
    var opts = '<option value="">-- Pilih Requester --</option>';
    list.forEach(function(r) {
        opts += '<option value="' + escHtml(r.requester) + '" data-qty="' + r.qty + '">' +
                escHtml(r.requester) + ' (' + r.qty + ' tersedia)</option>';
    });
    return '<div class="form-row">' +
        '<label class="form-label-gt">Requester / Pemilik *</label>' +
        '<select class="form-select-gt" id="bon-requester" onchange="updateMaxByRequester()">' + opts + '</select>' +
        '<div id="requester-info" style="font-size:.75rem;color:#6b7280;margin-top:3px"></div>' +
    '</div>';
}

// ── Update max qty berdasar requester yang dipilih ───────────────────────────
function updateMaxByRequester() {
    var sel = document.getElementById('bon-requester');
    var jmlInput = document.getElementById('bon-jml');
    if (!sel || !jmlInput) return;
    var opt = sel.options[sel.selectedIndex];
    var qty = parseInt(opt ? opt.getAttribute('data-qty') : 0) || 0;
    var info = document.getElementById('requester-info');
    if (qty > 0) {
        jmlInput.max = qty;
        if (parseInt(jmlInput.value) > qty) jmlInput.value = qty;
        if (info) info.textContent = 'Stok milik requester ini: ' + qty + ' unit';
    } else {
        if (info) info.textContent = '';
    }
}

function addKeranjang(mat) {
    var jml = parseInt(document.getElementById('bon-jml').value) || 0;
    if (jml < 1) { alert('Jumlah minimal 1!'); return; }
    if (jml > mat.stok_tersedia) { alert('Jumlah melebihi stok tersedia (' + mat.stok_tersedia + ')!'); return; }

    var requesterEl = document.getElementById('bon-requester');
    var requester = requesterEl ? requesterEl.value.trim() : '';

    // Validasi: jika ada list kepemilikan, requester wajib dipilih
    if (requesterEl && requesterEl.tagName === 'SELECT' && !requester) {
        alert('Pilih requester/pemilik material terlebih dahulu!');
        return;
    }

    // Validasi qty vs stok requester (jika dropdown)
    if (requesterEl && requesterEl.tagName === 'SELECT' && requester) {
        var opt = requesterEl.options[requesterEl.selectedIndex];
        var maxQty = parseInt(opt ? opt.getAttribute('data-qty') : 0) || 0;
        if (maxQty > 0 && jml > maxQty) {
            alert('Jumlah melebihi stok milik "' + requester + '" (' + maxQty + ')!');
            return;
        }
    }

    // Cek duplikat
    for (var i = 0; i < keranjang.length; i++) {
        if (keranjang[i].material_id === mat.id) {
            alert('Material ini sudah ada di keranjang. Hapus dulu jika ingin mengubah jumlah.');
            return;
        }
    }

    keranjang.push({
        material_id:   mat.id,
        kode_sap:      mat.kode_sap,
        nama_material: mat.nama_material,
        satuan:        mat.satuan,
        is_tabung:     parseInt(mat.is_tabung),
        kode_rak:      mat.kode_rak,
        jumlah_keluar: jml,
        stok_tersedia: mat.stok_tersedia,
        requester:     requester,
        requester_list: (typeof _lastRequesterList !== 'undefined' ? _lastRequesterList : []),
    });

    document.getElementById('bon-kode').value = '';
    document.getElementById('bon-item-area').innerHTML = '';
    renderKeranjang();
}

// ── Render keranjang ──────────────────────────────────────────────────────────
function renderKeranjang() {
    document.getElementById('keranjang-count').textContent = keranjang.length + ' item';
    var body = document.getElementById('keranjang-body');
    if (keranjang.length === 0) {
        body.innerHTML = '<div class="empty-state">Belum ada item</div>';
        return;
    }
    var rows = keranjang.map(function(item, i) {
        var tabungInfo = '';
        if (item.is_tabung && item.tabung_dipilih && item.tabung_dipilih.length > 0) {
            tabungInfo = '<div class="cart-tabung-list">' +
                item.tabung_dipilih.map(function(t, idx) {
                    return '<span class="cart-tabung-chip">' + (idx+1) + '. ' + escHtml(t.no_tabung) + '</span>';
                }).join('') +
            '</div>';
        }
        return '<div class="cart-item" id="cart-item-' + i + '">' +
            '<div class="cart-item-info">' +
                '<div class="cart-item-name">' + escHtml(item.nama_material) +
                    (item.is_tabung ? ' <span class="badge-gt badge-tabung">TABUNG</span>' : '') +
                '</div>' +
                '<div class="cart-item-meta">' +
                    (item.kode_sap ? '<code>' + escHtml(item.kode_sap) + '</code> &nbsp;·&nbsp; ' : '') +
                    '<strong>' + item.jumlah_keluar + '</strong> ' + escHtml(item.satuan) +
                    ' &nbsp;·&nbsp; Rak: ' + escHtml(item.kode_rak || '—') +
                '</div>' +
                (item.requester ? '<div class="cart-item-meta" style="color:#1a2744;font-weight:600">👤 Req: ' + escHtml(item.requester) + '</div>' : '') +
                tabungInfo +
            '</div>' +
            '<div style="display:flex;gap:.3rem;flex-shrink:0">' +
                '<button class="cart-edit" onclick="editItem(' + i + ')" title="Edit item">✏️</button>' +
                '<button class="cart-remove" onclick="hapusItem(' + i + ')">✕</button>' +
            '</div>' +
        '</div>';
    }).join('');
    rows += '<div class="cart-footer">' +
        '<button class="btn-outline-g" onclick="resetKeranjang()">Reset</button>' +
        '<button class="btn-green-g" onclick="simpanBon()">✓ Simpan Bon GI</button>' +
    '</div>';
    body.innerHTML = rows;
}

function hapusItem(i) { keranjang.splice(i,1); renderKeranjang(); }

// ── Edit item keranjang pengeluaran (inline form) ─────────────────────────────
function editItem(i) {
    var item = keranjang[i];

    var tabungSection = '';
    if (item.is_tabung && item.tabung_dipilih && item.tabung_dipilih.length > 0) {
        tabungSection = '<div class="cart-edit-row" style="grid-column:1/-1">' +
            '<label class="cart-edit-label">Tabung Terpilih <span style="font-size:.7rem;color:#9ca3af">(qty otomatis dari jumlah tabung)</span></label>' +
            '<div style="background:#f3f4f6;border-radius:8px;padding:.5rem .8rem;font-size:.78rem;color:#6b7280">' +
            item.tabung_dipilih.map(function(t, idx){ return (idx+1)+'. '+escHtml(t.no_tabung); }).join(' | ') +
            '</div><small style="font-size:.7rem;color:#9ca3af">Tabung tidak dapat diubah di keranjang. Hapus lalu pilih ulang jika perlu ganti tabung.</small>' +
        '</div>';
    }

    var html = '<div class="cart-edit-form" id="cart-editform-' + i + '">' +
        '<div style="font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:.5rem">Edit: ' + escHtml(item.nama_material) + '</div>' +
        '<div class="cart-edit-grid">' +
            (item.is_tabung
                ? ''
                : '<div class="cart-edit-row"><label class="cart-edit-label">Qty *</label>' +
                  '<input type="number" class="form-control-gt" id="cedit-qty-' + i + '" value="' + item.jumlah_keluar + '" min="1" max="' + item.stok_tersedia + '"></div>') +
            '<div class="cart-edit-row">' +
                '<label class="cart-edit-label">Requester / Pemilik</label>' +
                buildRequesterEditField(i, item) +
            '</div>' +
            tabungSection +
        '</div>' +
        '<div style="display:flex;gap:.4rem;margin-top:.6rem;justify-content:flex-end">' +
            '<button class="btn-outline-g" onclick="cancelEditItem(' + i + ')" style="font-size:.78rem;padding:.35rem .8rem">Batal</button>' +
            '<button class="btn-navy-g" onclick="saveEditItem(' + i + ')" style="font-size:.78rem;padding:.35rem .8rem">✔ Simpan Perubahan</button>' +
        '</div>' +
    '</div>';

    var el = document.getElementById('cart-item-' + i);
    if (el) el.outerHTML = html;
}

// ── Helper: buat field requester untuk form edit keranjang ───────────────────
// Tetap menampilkan SEMUA pemilik material ini (requester_list), bukan cuma
// requester yang kebetulan sudah dipilih sebelumnya, supaya bisa dipindah ke
// pemilik lain saat edit. Jika tidak ada data kepemilikan, fallback ke input teks.
function buildRequesterEditField(i, item) {
    var list = item.requester_list || [];
    if (list.length === 0) {
        return '<input type="text" class="form-control-gt" id="cedit-req-' + i + '" value="' + escHtml(item.requester || '') + '" placeholder="Kosongkan jika tidak ada">';
    }
    var opts = '<option value="">-- Pilih Requester --</option>';
    var currentMatched = false;
    list.forEach(function(r) {
        var isSel = (item.requester && r.requester === item.requester);
        if (isSel) currentMatched = true;
        opts += '<option value="' + escHtml(r.requester) + '" data-qty="' + r.qty + '"' + (isSel ? ' selected' : '') + '>' +
                escHtml(r.requester) + ' (' + r.qty + ' tersedia)</option>';
    });
    // Jika requester yang tersimpan di item tidak ada lagi di requester_list (mis. sudah berubah),
    // tetap tampilkan sebagai opsi terpilih supaya datanya tidak hilang/tertimpa diam-diam.
    if (item.requester && !currentMatched) {
        opts += '<option value="' + escHtml(item.requester) + '" selected>' + escHtml(item.requester) + ' (tidak terdaftar lagi)</option>';
    }
    return '<select class="form-select-gt" id="cedit-req-' + i + '">' + opts + '</select>';
}

function cancelEditItem(i) { renderKeranjang(); }

function saveEditItem(i) {
    var item = keranjang[i];

    var reqEl = document.getElementById('cedit-req-' + i);
    var requester = reqEl ? reqEl.value.trim() : (item.requester || '');

    // Jika field-nya dropdown (ada data kepemilikan), requester wajib dipilih
    if (reqEl && reqEl.tagName === 'SELECT' && !requester) {
        alert('Pilih requester/pemilik material terlebih dahulu!');
        return;
    }

    if (!item.is_tabung) {
        var qty = parseInt(document.getElementById('cedit-qty-' + i) ? document.getElementById('cedit-qty-' + i).value : 0) || 0;
        if (qty < 1) { alert('Qty minimal 1!'); return; }
        if (qty > item.stok_tersedia) { alert('Qty melebihi stok tersedia (' + item.stok_tersedia + ')!'); return; }

        // Validasi qty vs stok milik requester yang dipilih (jika dropdown)
        if (reqEl && reqEl.tagName === 'SELECT' && requester) {
            var opt = reqEl.options[reqEl.selectedIndex];
            var maxQty = parseInt(opt ? opt.getAttribute('data-qty') : 0) || 0;
            if (maxQty > 0 && qty > maxQty) {
                alert('Jumlah melebihi stok milik "' + requester + '" (' + maxQty + ')!');
                return;
            }
        }
        item.jumlah_keluar = qty;
    }

    item.requester = requester;

    keranjang[i] = item;
    renderKeranjang();
}
function resetKeranjang() {
    if (!confirm('Reset semua item?')) return;
    keranjang = []; renderKeranjang();
}

// ── Simpan bon ────────────────────────────────────────────────────────────────
function simpanBon() {
    var tgl       = document.getElementById('bon-tgl').value;
    var plantId   = document.getElementById('bon-plant').value;
    var pengambil = document.getElementById('bon-pengambil').value.trim();
    var keperluan = document.getElementById('bon-keperluan').value.trim();
    var catatan   = document.getElementById('bon-catatan').value.trim();

    if (!tgl || !plantId || !pengambil || !keperluan) {
        alert('Lengkapi header bon! (Tanggal, Plant, Pengambil, Keperluan)');
        return;
    }
    if (keranjang.length === 0) { alert('Keranjang masih kosong!'); return; }

    // Validasi: tabung harus ada tabung_dipilih
    for (var i = 0; i < keranjang.length; i++) {
        if (keranjang[i].is_tabung && (!keranjang[i].tabung_dipilih || keranjang[i].tabung_dipilih.length === 0)) {
            alert('Item "' + keranjang[i].nama_material + '" belum ada tabung yang dipilih!');
            return;
        }
    }

    fetch('/pengeluaran/simpan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
            header: {
                tanggal_bon:    tgl,
                plant_id:       plantId,
                nama_pengambil: pengambil,
                keperluan:      keperluan,
                catatan:        catatan,
            },
            items: keranjang,
        }),
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            UnsavedGuard.markClean();
            showAlert('✅ ' + res.no_bon + ' berhasil disimpan!').then(function(){
                window.location.href = window.location.pathname + '?tab=riwayat';
            });
        } else {
            alert('❌ ' + (res.message || 'Gagal menyimpan.'));
        }
    })
    .catch(function() { alert('Gagal menghubungi server.'); });
}

// ── Modal detail ──────────────────────────────────────────────────────────────
function showDetail(id) {
    document.getElementById('modal-detail-title').textContent = 'Detail Bon';
    document.getElementById('modal-detail-body').innerHTML = '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    document.getElementById('modal-detail').style.display = 'flex';

    fetch('/pengeluaran/detail/' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        var h = res.header;
        var rows = res.detail.length === 0
            ? '<tr><td colspan="6" class="tbl-empty">Tidak ada item</td></tr>'
            : res.detail.map(function(d) {
                return '<tr>' +
                    '<td><code>' + escHtml(d.kode_sap || '—') + '</code></td>' +
                    '<td>' + escHtml(d.nama_material || '—') +
                        (parseInt(d.is_tabung) === 1 ? ' <span class="badge-gt badge-tabung">TABUNG</span>' : '') +
                    '</td>' +
                    '<td>' + d.jumlah_keluar + ' ' + escHtml(d.satuan || '') + '</td>' +
                    '<td>' + (d.batch ? '<span class="badge-gt" style="background:#e0e7ff;color:#3730a3;font-size:.72rem">' + escHtml(d.batch) + '</span>' : '—') + '</td>' +
                    '<td><strong>' + escHtml(d.requester || '—') + '</strong></td>' +
                    '<td>' + d.stok_sebelum + ' → ' + d.stok_sesudah + '</td>' +
                    '<td>' + escHtml(d.no_tabung || '—') + '</td>' +
                    '<td>' + (parseInt(d.is_fifo) ? '<span class="badge-gt badge-normal">FIFO #' + d.urutan_fifo + '</span>' : '—') + '</td>' +
                '</tr>';
            }).join('');

        document.getElementById('modal-detail-title').textContent = 'Detail ' + h.no_bon;
        document.getElementById('modal-detail-body').innerHTML =
            '<div class="detail-header-info">' +
                '<div><span class="dhi-label">Tanggal</span><span class="dhi-val">' + h.tanggal_bon + '</span></div>' +
                '<div><span class="dhi-label">Plant</span><span class="dhi-val">' + escHtml(h.nama_plant||'—') + '</span></div>' +
                '<div><span class="dhi-label">Pengambil</span><span class="dhi-val">' + escHtml(h.nama_pengambil) + '</span></div>' +
                '<div><span class="dhi-label">Petugas GT</span><span class="dhi-val">' + escHtml(h.nama_petugas||'—') + '</span></div>' +
                '<div style="grid-column:1/-1"><span class="dhi-label">Keperluan</span><span class="dhi-val">' + escHtml(h.keperluan) + '</span></div>' +
                (h.catatan ? '<div style="grid-column:1/-1"><span class="dhi-label">Catatan</span><span class="dhi-val">' + escHtml(h.catatan) + '</span></div>' : '') +
            '</div>' +
            '<div class="tbl-wrap"><table class="tbl-g"><thead><tr>' +
            '<th>Kode SAP</th><th>Material</th><th>Jumlah</th><th>Batch</th><th>Requester</th><th>Stok (Sebelum→Sesudah)</th><th>No. Tabung</th><th>FIFO</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table></div>';
    });
}
function closeModal() { document.getElementById('modal-detail').style.display = 'none'; }
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

// ── Toggle mode input kode/nama ──────────────────────────────────────────
var _modeInput = 'kode';
function setMode(mode) {
    _modeInput = mode;
    document.getElementById('area-mode-kode').style.display  = mode === 'kode' ? 'block' : 'none';
    document.getElementById('area-mode-nama').style.display  = mode === 'nama' ? 'block' : 'none';
    document.getElementById('btn-mode-kode').classList.toggle('active', mode === 'kode');
    document.getElementById('btn-mode-nama').classList.toggle('active', mode === 'nama');
    // reset kedua area
    document.getElementById('bon-item-area').innerHTML      = '';
    document.getElementById('bon-item-area-nama').innerHTML = '';
    document.getElementById('bon-nama-dropdown').style.display = 'none';
    if (mode === 'kode') document.getElementById('bon-kode').value = '';
    else { document.getElementById('bon-nama-cari').value = ''; }
}

// ── Cari material tanpa kode SAP (search by nama) ─────────────────────────
var _namaTimer = null;
function cariNama(val) {
    var dd = document.getElementById('bon-nama-dropdown');
    clearTimeout(_namaTimer);
    if (val.length < 2) { dd.style.display = 'none'; return; }
    _namaTimer = setTimeout(function() {
        fetch('/pengeluaran/cari-material-nama?q=' + encodeURIComponent(val), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.list || res.list.length === 0) {
                dd.innerHTML = '<div class="nama-dd-empty">Tidak ada material tanpa kode SAP yang cocok</div>';
                dd.style.display = 'block';
                return;
            }
            dd.innerHTML = res.list.map(function(m) {
                var stokColor = m.stok_tersedia <= 0 ? 'color:var(--red)' : (m.stok_tersedia <= 5 ? 'color:var(--amber)' : 'color:var(--green)');
                return '<div class="nama-dd-item" onclick=\'pilihMaterialNama(' + JSON.stringify(m) + ')\'>' +
                    '<div class="nama-dd-name">' + escHtml(m.nama_material) +
                        (m.batch ? ' <span class="badge-gt" style="background:#e0e7ff;color:#3730a3;font-size:.7rem">' + escHtml(m.batch) + '</span>' : '') +
                    '</div>' +
                    '<div class="nama-dd-meta">Rak: ' + escHtml(m.kode_rak || '—') + ' &nbsp;·&nbsp; Stok: <strong style="' + stokColor + '">' + m.stok_tersedia + ' ' + escHtml(m.satuan) + '</strong></div>' +
                '</div>';
            }).join('');
            dd.style.display = 'block';
        })
        .catch(function(){});
    }, 300);
}

// Tutup dropdown jika klik di luar
document.addEventListener('click', function(e) {
    var dd = document.getElementById('bon-nama-dropdown');
    if (dd && !dd.contains(e.target) && e.target.id !== 'bon-nama-cari') {
        dd.style.display = 'none';
    }
});

// ── Pilih material dari dropdown nama ─────────────────────────────────────
function pilihMaterialNama(m) {
    document.getElementById('bon-nama-cari').value = m.nama_material + (m.batch ? ' [' + m.batch + ']' : '');
    document.getElementById('bon-nama-dropdown').style.display = 'none';
    var area = document.getElementById('bon-item-area-nama');
    // Gunakan renderMatArea yang sama dengan mode kode SAP
    var syntheticRes = {
        requester_list: m.requester_list || [],
        tabung_list:    m.tabung_list    || [],
    };
    renderMatArea(area, m, syntheticRes);
}

// ── Override addKeranjang agar sumber area disesuaikan mode ───────────────
// (renderMatArea sudah pakai area yang dikirim, addKeranjang baca dari DOM)
// Patch: setelah addKeranjang sukses, clear area yang benar
var _origAddKeranjang = addKeranjang;
addKeranjang = function(mat) {
    _origAddKeranjang(mat);
    // Jika mode nama, clear area nama juga
    if (_modeInput === 'nama') {
        document.getElementById('bon-nama-cari').value = '';
        document.getElementById('bon-item-area-nama').innerHTML = '';
        document.getElementById('bon-nama-dropdown').style.display = 'none';
    }
};
var _origAddKeranjangTabung = addKeranjangTabung;
addKeranjangTabung = function(mat) {
    _origAddKeranjangTabung(mat);
    if (_modeInput === 'nama') {
        document.getElementById('bon-nama-cari').value = '';
        document.getElementById('bon-item-area-nama').innerHTML = '';
        document.getElementById('bon-nama-dropdown').style.display = 'none';
    }
};
</script>

<style>
.tabs-bar{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:1rem}
.tab-btn{background:none;border:none;padding:.6rem 1.2rem;font-weight:600;font-size:.85rem;color:var(--ink3);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.2s}
.tab-btn.active{color:var(--navy);border-bottom-color:var(--navy)}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.card-body-g{padding:1.2rem}
.card-header-g{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;border-radius:12px 12px 0 0}
.form-row{margin-bottom:.75rem}
.form-label-gt{display:block;font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:4px}
.form-control-gt,.form-select-gt{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:.48rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;outline:none;box-sizing:border-box;transition:.2s}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(26,39,68,.08)}
.input-group-gt{display:flex;gap:.4rem}
.input-group-gt .form-control-gt{flex:1}
.btn-navy-g{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.48rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer}
.btn-navy-g.w-100{width:100%;margin-top:.6rem}
.btn-outline-g{background:transparent;color:var(--navy);border:1.5px solid var(--border);border-radius:8px;padding:.4rem .9rem;font-weight:600;font-size:.8rem;cursor:pointer}
.btn-green-g{background:var(--green,#1a7f4b);color:#fff;border:none;border-radius:8px;padding:.48rem 1.2rem;font-weight:700;font-size:.85rem;cursor:pointer}
.btn-detail-g{display:inline-flex;align-items:center;gap:.35rem;background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.4rem .85rem;font-weight:600;font-size:.78rem;cursor:pointer;transition:.2s}
.btn-detail-g:hover{opacity:.88;transform:translateY(-1px)}
.btn-detail-icon{font-size:.85rem}
.no-surat-chip{background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;padding:.5rem .9rem;font-size:.8rem;color:#374151;margin-bottom:.8rem}
.petugas-chip{background:#f8f9fc;border-radius:8px;padding:.5rem .9rem;font-size:.78rem;color:#6b7280;margin-top:.5rem}
.mat-info-box{background:#f0f4ff;border-radius:8px;padding:.7rem .9rem;margin-bottom:.5rem}
.mat-info-name{font-weight:700;color:var(--navy);font-size:.88rem}
.batch-select-label{font-size:.8rem;font-weight:600;color:#374151;margin:.4rem 0 .3rem}
.batch-select-list{display:flex;flex-direction:column;gap:.4rem;margin-bottom:.5rem}
.batch-select-row{display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border:1.5px solid var(--border);border-radius:8px;padding:.55rem .8rem;cursor:pointer;transition:.15s}
.batch-select-row:hover{border-color:var(--navy);background:#eff2ff}
.batch-select-row.batch-kosong{opacity:.5;cursor:not-allowed;pointer-events:none}
.batch-select-info{display:flex;flex-direction:column;gap:2px}
.batch-select-meta{font-size:.75rem;color:#6b7280}
.batch-select-arrow{font-size:1.2rem;color:var(--navy);font-weight:700}
.mat-info-meta{font-size:.75rem;color:#6b7280;margin-top:2px}
.stok-detail{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.4rem;font-size:.78rem;color:#374151}
.tabung-list-box{background:#f8faff;border:1.5px solid #c7d2fe;border-radius:10px;margin-bottom:.6rem}
.tabung-list-title{padding:.6rem .9rem;font-size:.78rem;font-weight:700;color:var(--navy);border-bottom:1px solid #e0e7ff;display:flex;align-items:center;gap:.5rem}
.tabung-sel-count{margin-left:auto;background:var(--navy);color:#fff;border-radius:20px;padding:.1rem .6rem;font-size:.7rem;font-weight:700}
.tabung-list-scroll{max-height:200px;overflow-y:auto}
.tabung-check-row{display:flex;align-items:flex-start;gap:.6rem;padding:.5rem .9rem;cursor:pointer;border-bottom:1px solid #f0f4ff;transition:.15s}
.tabung-check-row:last-child{border-bottom:none}
.tabung-check-row:hover{background:#eef2ff}
.tabung-check-row.is-rek{background:#fffbeb}
.tabung-check-row.is-rek:hover{background:#fef3c7}
.tabung-check-row input[type=checkbox]{margin-top:2px;flex-shrink:0;width:15px;height:15px;cursor:pointer}
.tabung-check-info{display:flex;flex-direction:column;gap:2px}
.tabung-check-meta{font-size:.72rem;color:#6b7280}
.badge-fifo{background:#fef3c7;color:#92400e}
.cart-tabung-list{display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.3rem}
.cart-tabung-chip{background:#ede9fe;color:#5b21b6;border-radius:6px;padding:.15rem .5rem;font-size:.7rem;font-weight:600}
.notif-warning{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;color:#92400e;margin-bottom:.7rem}
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
.empty-state{text-align:center;padding:2rem;color:#9ca3af;font-size:.85rem}
.tbl-empty{text-align:center;padding:2rem;color:#9ca3af}
.detail-header-info{display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem;background:#f8f9fc;border-radius:8px;padding:.8rem 1rem;margin-bottom:.8rem}
.dhi-label{font-size:.72rem;color:#6b7280;display:block}
.dhi-val{font-size:.85rem;font-weight:600;color:#1f2937}
.badge-gt{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700}
.badge-tabung{background:#fdf4ff;color:#7e22ce}
.badge-normal{background:#d1fae5;color:#1a7f4b}
.modal-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);z-index:5000;display:flex;align-items:center;justify-content:center;padding:1rem;box-sizing:border-box}
.modal-box{background:#fff;border-radius:16px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal-head{background:var(--navy);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem;color:#ffffff}
.modal-close{background:none;border:none;color:#ffffff;font-size:1.2rem;cursor:pointer;opacity:.85}
.modal-close:hover{opacity:1}
.modal-body-gt{padding:1.3rem}
.pagination-bar{display:flex;align-items:center;gap:.6rem;justify-content:center}
.pg-btn{background:#fff;border:1.5px solid var(--border);border-radius:8px;padding:.4rem 1rem;font-weight:600;font-size:.82rem;color:var(--navy);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.pg-btn:hover:not([disabled]){background:var(--navy);color:#fff;border-color:var(--navy)}
.pg-btn[disabled]{opacity:.4;cursor:not-allowed}
.pg-info{font-size:.82rem;color:#6b7280;min-width:90px;text-align:center}
.mt-3{margin-top:1rem}
@media(max-width:768px){.form-grid-2{grid-template-columns:1fr}}
/* Toggle mode */
.toggle-mode-bar{display:flex;gap:.3rem;background:#f3f4f6;border-radius:8px;padding:3px}
.toggle-mode-btn{flex:1;background:transparent;border:none;border-radius:6px;padding:.4rem .6rem;font-size:.78rem;font-weight:600;color:#6b7280;cursor:pointer;transition:.15s}
.toggle-mode-btn.active{background:#fff;color:var(--navy);box-shadow:0 1px 4px rgba(0,0,0,.1)}
/* Nama dropdown */
.nama-dropdown{position:absolute;z-index:200;background:#fff;border:1.5px solid var(--border);border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);width:100%;max-height:220px;overflow-y:auto;margin-top:2px}
.nama-dd-item{padding:.55rem .9rem;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:.12s}
.nama-dd-item:last-child{border-bottom:none}
.nama-dd-item:hover{background:#eff2ff}
.nama-dd-name{font-weight:600;color:#1f2937;font-size:.83rem}
.nama-dd-meta{font-size:.73rem;color:#6b7280;margin-top:1px}
.nama-dd-empty{padding:.7rem .9rem;color:#9ca3af;font-size:.82rem;text-align:center}
#area-mode-nama{position:relative}

/* ── Tabel riwayat: rapatkan padding ─────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
.tbl-g{width:100%;border-collapse:collapse}
.tbl-g thead{background:#f8f9fc;border-bottom:1px solid #e5e7eb}
.tbl-g th{padding:.45rem .6rem;text-align:left;font-size:.75rem;font-weight:700;color:#4b5563;letter-spacing:.02em}
.tbl-g td{padding:.45rem .6rem;border-bottom:1px solid #f0f2f8;font-size:.83rem}
.tbl-g tbody tr:hover{background:#f9fafb}

/* ============================================================
   RESPONSIVE — HP (desktop/laptop tidak berubah)
   ============================================================ */
@media (max-width:640px) {
  .tabs-bar { overflow-x:auto; flex-wrap:nowrap; -webkit-overflow-scrolling:touch; }
  .tab-btn { white-space:nowrap; flex-shrink:0; }
  .modal-box { max-width:100% !important; width:100% !important; }
  .modal-body-gt, .modal-body { padding:.9rem !important; }
  .tbl-g th, .tbl-g td { padding:.4rem .55rem; font-size:.76rem; }
  .toggle-mode-btn { font-size:.72rem; padding:.4rem .4rem; }
}
</style>

<?= $this->endSection() ?>