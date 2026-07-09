<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Kelola Kategori Rak</h1>
    <p>Master nama rak beserta batas maksimal baris &amp; kolom. Dipakai sebagai batasan saat input lokasi rak di Mapping &amp; Penerimaan.</p>
  </div>
  <div class="page-hd-right">
    <a href="/mapping" class="btn-g btn-out-g">&larr; Kembali ke Mapping</a>
  </div>
</div>

<div class="card-g mb-3" id="card-tambah-kategori-rak">
  <div class="card-header-g">➕ Tambah Kategori Rak</div>
  <div class="card-body-g">
    <div class="form-grid-2-sm">
      <div>
        <label class="form-label-gt">Nama/Kode Rak *</label>
        <input type="text" id="new-kode" class="form-control-gt" placeholder="Contoh: K.39">
      </div>
      <div style="display:flex;gap:8px">
        <div style="flex:1">
          <label class="form-label-gt">Maks Baris *</label>
          <input type="number" id="new-baris" class="form-control-gt" min="1" placeholder="6">
        </div>
        <div style="flex:1">
          <label class="form-label-gt">Maks Kolom *</label>
          <input type="number" id="new-kolom" class="form-control-gt" min="1" placeholder="3">
        </div>
      </div>
    </div>
    <div class="form-row mt-2">
      <label class="form-label-gt">Keterangan (opsional)</label>
      <input type="text" id="new-ket" class="form-control-gt" placeholder="Catatan tambahan...">
    </div>
    <button class="btn-g btn-navy-g mt-2" onclick="tambahKategori()">💾 Simpan Kategori</button>
  </div>
</div>

<div class="rak-side-by-side">
  <div class="card-g">
    <div class="card-header-g" style="cursor:pointer" onclick="toggleImportBox()">📋 Import Massal (tempel dari catatan) ▾</div>
    <div class="card-body-g" id="import-box" style="display:none">
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

  <div class="card-g">
    <div class="card-header-g">🗂️ Daftar Kategori Rak (<?= count($kategoris) ?>)</div>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead>
          <tr>
            <th>Kode / Nama Rak</th>
            <th>Zona</th>
            <th>Maks Baris</th>
            <th>Maks Kolom</th>
            <th>Keterangan</th>
            <th style="text-align:center">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbl-kategori">
          <?php if (empty($kategoris)): ?>
          <tr><td colspan="6" class="tbl-empty">Belum ada kategori rak. Tambahkan di atas atau import massal.</td></tr>
          <?php else: ?>
          <?php foreach ($kategoris as $k): ?>
          <tr id="kat-row-<?= $k['id'] ?>">
            <td><span class="rak-chip"><?= esc($k['kode_kategori']) ?></span></td>
            <td><?= esc($k['zona'] ?: '—') ?></td>
            <td>
              <span id="kat-baris-<?= $k['id'] ?>"><?= (int)$k['max_baris'] ?></span>
              <button class="btn-link-g" style="font-size:.72rem" onclick="perluasKategori(<?= $k['id'] ?>,'baris')">+ tambah</button>
            </td>
            <td>
              <span id="kat-kolom-<?= $k['id'] ?>"><?= (int)$k['max_kolom'] ?></span>
              <button class="btn-link-g" style="font-size:.72rem" onclick="perluasKategori(<?= $k['id'] ?>,'kolom')">+ tambah</button>
            </td>
            <td><?= esc($k['keterangan'] ?: '—') ?></td>
            <td style="text-align:center">
              <button class="btn-g btn-navy-g btn-sm-g" onclick="editKategori(<?= $k['id'] ?>, '<?= esc($k['kode_kategori'], 'js') ?>', <?= (int)$k['max_baris'] ?>, <?= (int)$k['max_kolom'] ?>, '<?= esc($k['keterangan'] ?? '', 'js') ?>')">✏️ Edit</button>
              <button class="btn-g btn-out-g btn-sm-g" onclick="hapusKategori(<?= $k['id'] ?>)">🗑️</button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL EDIT KATEGORI -->
<div id="modal-edit-kat" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head">
      <h6>Edit Kategori Rak</h6>
      <button class="modal-close" onclick="closeEditKategori()">✕</button>
    </div>
    <div class="modal-body-gt">
      <input type="hidden" id="ek-id">
      <div class="form-row mb-3">
        <label class="form-label-gt">Nama/Kode Rak *</label>
        <input type="text" id="ek-kode" class="form-control-gt">
      </div>
      <div class="form-grid-2-sm mb-3">
        <div>
          <label class="form-label-gt">Maks Baris *</label>
          <input type="number" id="ek-baris" class="form-control-gt" min="1">
        </div>
        <div>
          <label class="form-label-gt">Maks Kolom *</label>
          <input type="number" id="ek-kolom" class="form-control-gt" min="1">
        </div>
      </div>
      <div class="form-row mb-3">
        <label class="form-label-gt">Keterangan</label>
        <input type="text" id="ek-ket" class="form-control-gt">
      </div>
      <div id="ek-error" style="display:none;color:var(--clay);font-size:.82rem;margin-bottom:.8rem;background:#fff5f5;padding:.5rem .8rem;border-radius:8px;border:1px solid #fecaca"></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button class="btn-g btn-out-g" onclick="closeEditKategori()">Batal</button>
        <button class="btn-g btn-navy-g" onclick="saveEditKategori()">💾 Simpan</button>
      </div>
    </div>
  </div>
</div>

<style>
.card-header-g{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;border-radius:12px 12px 0 0}
.card-body-g{padding:1.2rem}
.tbl-wrap{overflow-x:auto}
.tbl-g{width:100%;border-collapse:collapse}
.tbl-g thead{background:#f8f9fc;border-bottom:1px solid #e5e7eb}
.tbl-g th{padding:.45rem .6rem;text-align:left;font-size:.75rem;font-weight:700;color:#4b5563;letter-spacing:.02em}
.tbl-g td{padding:.45rem .6rem;border-bottom:1px solid #f0f2f8;font-size:.83rem}
.tbl-g tbody tr:hover{background:#f9fafb}
.tbl-empty{text-align:center;padding:2rem;color:#9ca3af}
.rak-chip{background:#ede9fe;color:#5b21b6;padding:.2rem .6rem;border-radius:6px;font-size:.75rem;font-weight:700}
.form-control-gt,.form-select-gt{border:1.5px solid var(--border);border-radius:8px;padding:.5rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;outline:none;transition:.2s;width:100%;box-sizing:border-box}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(26,39,68,.08)}
.form-row{display:flex;flex-direction:column;gap:5px}
.form-grid-2-sm{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
.form-label-gt{font-size:.78rem;font-weight:700;color:var(--navy)}
.btn-link-g{background:none;border:none;color:var(--navy);font-size:.75rem;cursor:pointer;text-decoration:underline;padding:0}
.mb-3{margin-bottom:1rem}
.mt-2{margin-top:.6rem}
.mt-3{margin-top:1rem}
.modal-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);z-index:5000;display:flex;align-items:center;justify-content:center;padding:1rem}
.modal-box{background:#fff;border-radius:16px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal-head{background:var(--navy);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem;color:#ffffff}
.modal-close{background:none;border:none;color:#ffffff;font-size:1.2rem;cursor:pointer;line-height:1;opacity:.85}
.modal-close:hover{opacity:1}
.modal-body-gt{padding:1.3rem}
.rak-side-by-side{display:grid;grid-template-columns:380px 1fr;gap:1rem;align-items:start;margin-bottom:1rem}
@media(max-width:768px){.form-grid-2-sm{grid-template-columns:1fr}}
@media(max-width:900px){.rak-side-by-side{grid-template-columns:1fr}}
@media(max-width:640px){.modal-box{max-width:100% !important;width:100% !important}}
</style>

<script>
UnsavedGuard.watch('#card-tambah-kategori-rak', 'Data kategori rak baru yang sedang diisi belum disimpan. Yakin ingin pindah halaman?');
UnsavedGuard.watch('#import-box', 'Data import massal yang sedang diisi belum diproses. Yakin ingin pindah halaman?');
UnsavedGuard.watch('#modal-edit-kat', 'Ada perubahan kategori rak yang belum disimpan. Yakin ingin pindah halaman?');

function tambahKategori() {
    var kode  = document.getElementById('new-kode').value.trim();
    var baris = parseInt(document.getElementById('new-baris').value, 10);
    var kolom = parseInt(document.getElementById('new-kolom').value, 10);
    var ket   = document.getElementById('new-ket').value.trim();

    if (!kode)  { alert('Nama/kode rak wajib diisi'); return; }
    if (!baris || baris < 1) { alert('Maks baris wajib diisi (angka > 0)'); return; }
    if (!kolom || kolom < 1) { alert('Maks kolom wajib diisi (angka > 0)'); return; }

    fetch('/rak-kategori/simpan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ kode_kategori: kode, max_baris: baris, max_kolom: kolom, keterangan: ket }),
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { alert(res.message || 'Gagal menyimpan'); return; }
        UnsavedGuard.markClean();
        location.reload();
    })
    .catch(() => alert('Gagal menghubungi server.'));
}

function perluasKategori(id, tipe) {
    var label = tipe === 'kolom' ? 'kolom' : 'baris';
    var tambah = prompt('Tambah berapa ' + label + ' lagi?', '1');
    if (tambah === null) return;
    tambah = parseInt(tambah, 10);
    if (!tambah || tambah < 1) { alert('Jumlah tidak valid'); return; }

    fetch('/rak-kategori/perluas/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ tipe: tipe, tambah: tambah }),
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { alert(res.message || 'Gagal memperluas'); return; }
        document.getElementById('kat-' + tipe + '-' + id).textContent = tipe === 'kolom' ? res.kategori.max_kolom : res.kategori.max_baris;
    })
    .catch(() => alert('Gagal menghubungi server.'));
}

function editKategori(id, kode, baris, kolom, ket) {
    document.getElementById('ek-error').style.display = 'none';
    document.getElementById('ek-id').value    = id;
    document.getElementById('ek-kode').value  = kode;
    document.getElementById('ek-baris').value = baris;
    document.getElementById('ek-kolom').value = kolom;
    document.getElementById('ek-ket').value   = ket;
    document.getElementById('modal-edit-kat').style.display = 'flex';
}

function closeEditKategori() {
    document.getElementById('modal-edit-kat').style.display = 'none';
    UnsavedGuard.markClean();
}

function saveEditKategori() {
    var id    = document.getElementById('ek-id').value;
    var kode  = document.getElementById('ek-kode').value.trim();
    var baris = parseInt(document.getElementById('ek-baris').value, 10);
    var kolom = parseInt(document.getElementById('ek-kolom').value, 10);
    var ket   = document.getElementById('ek-ket').value.trim();

    if (!kode)  { showEkError('Nama/kode rak wajib diisi'); return; }
    if (!baris || baris < 1) { showEkError('Maks baris wajib diisi'); return; }
    if (!kolom || kolom < 1) { showEkError('Maks kolom wajib diisi'); return; }

    fetch('/rak-kategori/update/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ kode_kategori: kode, max_baris: baris, max_kolom: kolom, keterangan: ket }),
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { showEkError(res.message || 'Gagal menyimpan'); return; }
        UnsavedGuard.markClean();
        location.reload();
    })
    .catch(() => showEkError('Gagal menghubungi server.'));
}

function showEkError(msg) {
    var el = document.getElementById('ek-error');
    el.textContent = msg;
    el.style.display = 'block';
}

function hapusKategori(id) {
    if (!confirm('Hapus kategori rak ini? Hanya bisa dihapus jika belum dipakai lokasi rak manapun.')) return;
    fetch('/rak-kategori/hapus/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { alert(res.message || 'Gagal menghapus'); return; }
        document.getElementById('kat-row-' + id).remove();
    })
    .catch(() => alert('Gagal menghubungi server.'));
}

function toggleImportBox() {
    var box = document.getElementById('import-box');
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function importKategori() {
    var teks = document.getElementById('import-teks').value;
    if (!teks.trim()) { alert('Tempel data terlebih dahulu'); return; }

    fetch('/rak-kategori/import', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ teks: teks }),
    })
    .then(r => r.json())
    .then(res => {
        var box = document.getElementById('import-result');
        if (!res.success) { box.innerHTML = '<span style="color:var(--clay)">Gagal import.</span>'; return; }
        var html = '<span style="color:#059669;font-weight:600">✔ ' + res.jumlah_sukses + ' baris berhasil diproses.</span>';
        if (res.gagal && res.gagal.length) {
            html += '<div style="margin-top:.4rem;color:var(--clay)">Dilewati:<ul>' +
                res.gagal.map(function(g){ return '<li>' + g + '</li>'; }).join('') + '</ul></div>';
        }
        box.innerHTML = html;
        UnsavedGuard.markClean();
        setTimeout(function(){ location.reload(); }, 1200);
    })
    .catch(() => { document.getElementById('import-result').innerHTML = '<span style="color:var(--clay)">Gagal menghubungi server.</span>'; });
}

document.getElementById('modal-edit-kat').addEventListener('click', function(e) {
    if (e.target === this) closeEditKategori();
});
</script>

<?= $this->endSection() ?>