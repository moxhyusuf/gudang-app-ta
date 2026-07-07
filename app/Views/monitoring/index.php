<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
function badgeKondisi($k) {
    $map = [
        'habis'         => '<span class="badge-gt badge-habis">Habis</span>',
        'kritis'        => '<span class="badge-gt badge-kritis">Kritis</span>',
        'hampir_kritis' => '<span class="badge-gt badge-hampir">Hampir Kritis</span>',
        'normal'        => '<span class="badge-gt badge-normal">Normal</span>',
    ];
    return isset($map[$k]) ? $map[$k] : '<span class="badge-gt badge-normal">Normal</span>';
}
function badgeBatch($b) {
    if (!$b) return '<span class="badge-gt badge-umum">UMUM</span>';
    $map = [
        'LOCAL'     => 'badge-lokal',
        'IMPORT'    => 'badge-import',
        'EXPLANT'   => 'badge-explant',
        'EXPROJECT' => 'badge-lokal',
        'REKONDISI' => 'badge-rekondisi',
        'DEFECT'    => 'badge-defect',
        'DAMAGE'    => 'badge-damage',
        'UMUM'      => 'badge-umum',
    ];
    $cls = isset($map[$b]) ? $map[$b] : 'badge-umum';
    return '<span class="badge-gt '.$cls.'">'.esc($b).'</span>';
}
function renderRow($m, $role) {
    $isPlant = $role === 'plant';
    $html  = '<tr>';
    $html .= '<td><code class="mono" style="font-size:.75rem;color:var(--navy)">'.(!empty($m['kode_sap']) ? esc($m['kode_sap']) : '-').'</code></td>';
    $html .= '<td><div style="font-weight:600;color:#1f2937">'.esc($m['nama_material']).'</div>';
    if (isset($m['is_tabung']) && (int)$m['is_tabung'] === 1) $html .= '<span class="badge-gt badge-tabung" style="margin-top:2px">TABUNG</span>';
    $html .= '</td>';
    if (!$isPlant) {
        $html .= '<td>'.badgeBatch(isset($m['batch']) ? $m['batch'] : null).'</td>';
        $html .= '<td>';
        if (!empty($m['kode_rak'])) $html .= '<small style="color:#6b7280"><strong>'.esc($m['kode_rak']).'</strong> · '.esc($m['zona']).'</small>';
        else $html .= '<span style="color:#9ca3af">—</span>';
        $html .= '</td>';
        $html .= '<td><strong>'.number_format($m['stok']).'</strong> <span style="color:#9ca3af;font-size:.75rem">'.esc($m['satuan']).'</span></td>';
        $html .= '<td>'.number_format($m['stok_booking']).'</td>';
        $color = $m['stok_tersedia'] <= 0 ? 'color:var(--red)' : 'color:var(--green)';
        $html .= '<td><strong style="'.$color.'">'.number_format($m['stok_tersedia']).'</strong></td>';
        $html .= '<td>'.($m['safety_stock'] !== null ? $m['safety_stock'] : '—').'</td>';
    } else {
        $color = $m['stok_tersedia'] <= 0 ? 'color:var(--red)' : '';
        $html .= '<td><strong style="'.$color.'">'.number_format($m['stok_tersedia']).'</strong></td>';
        $html .= '<td>'.number_format($m['stok_booking']).'</td>';
    }
    $html .= '<td>'.badgeKondisi($m['kondisi_stok']).'</td>';
    if (!$isPlant) {
        $html .= '<td style="white-space:nowrap;text-align:center;padding:0.65rem 0.2rem">';
        $html .= '<button class="btn-histori" onclick="showHistori('.$m['id'].', \''.addslashes($m['nama_material']).'\')" title=\"Histori\"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg></button>';
        $html .= '<button class="btn-kepemilikan" onclick="showKepemilikan('.$m['id'].', \''.addslashes($m['nama_material']).'\')" title=\"Kepemilikan\"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg></button>';
        $html .= '</td>';
    } else {
        $html .= '<td style="text-align:center;padding:0.65rem 0.2rem"><button class="btn-kepemilikan" onclick="showKepemilikan('.$m['id'].', \''.addslashes($m['nama_material']).'\')" title=\"Kepemilikan\"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg></button></td>';
    }
    $html .= '</tr>';
    return $html;
}
$isPlant = $role === 'plant';
$colspan = $isPlant ? 7 : 11;
?>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Monitoring Persediaan</h1>
    <p id="info-count">
      <?= number_format($total) ?> material ditemukan &mdash;
      Halaman <?= $current_page ?> dari <?= $total_page ?>,
      menampilkan <?= count($materials) ?> data
    </p>
  </div>
</div>

<!-- FILTER BAR -->
<div class="filter-bar mb-3">
  <div class="filter-field">
    <label class="filter-label">Cari</label>
    <input type="text" id="inp-search" class="form-control-gt"
           placeholder="<?= $isPlant ? 'Kode SAP / Nama / Group...' : 'Kode SAP / Nama / Rak / Group...' ?>"
           value="<?= esc($filter_search) ?>" style="min-width:220px">
  </div>
  <div class="filter-field">
    <label class="filter-label">Batch</label>
    <select id="inp-batch" class="form-select-gt">
      <option value="">Semua Batch</option>
      <option value="__NO_SAP__" <?= $filter_batch === '__NO_SAP__' ? 'selected' : '' ?>>
        ⚠ Tanpa Kode SAP
      </option>
      <?php foreach ($batch_options as $b): ?>
      <option value="<?= esc($b) ?>" <?= $filter_batch === $b ? 'selected' : '' ?>>
        <?= esc($b) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-field">
    <label class="filter-label">Kondisi Stok</label>
    <select id="inp-kondisi" class="form-select-gt">
      <option value="">Semua Kondisi</option>
      <option value="habis"         <?= $filter_kondisi === 'habis'         ? 'selected' : '' ?>>Habis</option>
      <option value="kritis"        <?= $filter_kondisi === 'kritis'        ? 'selected' : '' ?>>Kritis</option>
      <option value="hampir_kritis" <?= $filter_kondisi === 'hampir_kritis' ? 'selected' : '' ?>>Hampir Kritis</option>
      <option value="normal"        <?= $filter_kondisi === 'normal'        ? 'selected' : '' ?>>Normal</option>
    </select>
  </div>
  <div class="filter-field" style="align-self:flex-end">
    <button id="btn-reset" class="btn-outline-g" style="display:<?= ($filter_search || $filter_batch || $filter_kondisi) ? 'inline-flex' : 'none' ?>">
      ✕ Reset
    </button>
  </div>
  <div class="filter-field" style="align-self:flex-end">
    <div id="loading-spinner" style="display:none;color:var(--navy);font-size:.82rem;font-weight:600">
      ⏳ Memuat...
    </div>
  </div>
</div>

<!-- TABEL -->
<div class="card-g">
  <div class="card-header-g">📊 Data Persediaan Material</div>
  <div class="tbl-wrap">
    <table class="tbl-g">
      <thead>
        <tr>
          <th style="width:110px">Kode SAP</th>
          <th>Nama Material</th>
          <?php if (!$isPlant): ?>
          <th style="width:80px;text-align:center">Batch</th>
          <th style="width:120px">Lokasi Rak</th>
          <th style="width:90px;text-align:right">Stok Fisik</th>
          <th style="width:80px;text-align:right">Booking</th>
          <th style="width:80px;text-align:right">Tersedia</th>
          <th style="width:70px;text-align:right">Safety</th>
          <?php else: ?>
          <th style="width:100px;text-align:right">Stk. Tersedia</th>
          <th style="width:100px;text-align:right">Stk. Booking</th>
          <?php endif; ?>
          <th style="width:90px;text-align:center">Kondisi</th>
          <th style="width:80px;text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody id="tbl-body">
        <?php if (empty($materials)): ?>
        <tr><td colspan="<?= $colspan ?>" class="tbl-empty">Tidak ada material yang sesuai filter</td></tr>
        <?php else: ?>
        <?php foreach ($materials as $m): ?>
          <?= renderRow($m, $role) ?>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- PAGINATION -->
<div id="pagination-bar" class="pagination-bar mt-3">
  <?php if ($total_page > 1): ?>
  <?php
    $prev = $current_page > 1 ? $current_page - 1 : null;
    $next = $current_page < $total_page ? $current_page + 1 : null;
  ?>
  <button class="pg-btn" <?= !$prev ? 'disabled' : '' ?> onclick="goPage(<?= $prev ?? 1 ?>)">← Prev</button>
  <span class="pg-info">Hal <strong id="pg-current"><?= $current_page ?></strong> / <?= $total_page ?></span>
  <button class="pg-btn" <?= !$next ? 'disabled' : '' ?> onclick="goPage(<?= $next ?? $total_page ?>)">Next →</button>
  <?php endif; ?>
</div>

<!-- MODAL KEPEMILIKAN (semua role) -->
<div id="modal-kepemilikan" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:540px">
    <div class="modal-head">
      <h6 id="modal-kep-title">Detail Kepemilikan Stok</h6>
      <button class="modal-close" onclick="closeKepemilikan()">&#x2715;</button>
    </div>
    <div class="modal-body-gt" id="modal-kep-body">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
  </div>
</div>

<!-- MODAL EDIT/TAMBAH KEPEMILIKAN (admin & petugas gudang) -->
<?php if (!$isPlant): ?>
<div id="modal-edit-kepemilikan" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-head">
      <h6 id="edit-kep-title" style="color:#ffffff">Tambah Kepemilikan</h6>
      <button class="modal-close" onclick="closeEditKepemilikan()">&#x2715;</button>
    </div>
    <div class="modal-body-gt">
      <p id="edit-kep-info" style="font-size:.82rem;color:#6b7280;margin:0 0 1rem"></p>

      <input type="hidden" id="edit-kep-material-id">

      <div style="margin-bottom:.9rem">
        <label class="filter-label" style="display:block;margin-bottom:4px">Requester / Pemilik</label>
        <input type="text" id="edit-kep-requester" class="form-control-gt" style="width:100%"
               list="edit-kep-requester-list" placeholder="Nama requester / pemilik">
        <datalist id="edit-kep-requester-list"></datalist>
      </div>

      <div style="margin-bottom:.4rem">
        <label class="filter-label" style="display:block;margin-bottom:4px">Qty</label>
        <input type="number" id="edit-kep-qty" class="form-control-gt" style="width:100%" min="0" step="1">
        <div id="edit-kep-max-hint" style="font-size:.75rem;color:#9ca3af;margin-top:4px"></div>
      </div>

      <div id="edit-kep-error" style="display:none;background:#fef2f2;border:1.5px solid #fecaca;color:#b91c1c;border-radius:8px;padding:.6rem .8rem;font-size:.8rem;margin-top:.6rem"></div>

      <div class="modal-actions" style="justify-content:flex-end;margin-top:1.2rem">
        <button class="btn-g btn-out-g" onclick="closeEditKepemilikan()">Batal</button>
        <button class="btn-g btn-navy-g" onclick="simpanEditKepemilikan()">Simpan</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- MODAL HISTORI -->
<?php if (!$isPlant): ?>
<div id="modal-histori" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:680px">
    <div class="modal-head">
      <h6 id="modal-histori-title">Histori Mutasi</h6>
      <button class="modal-close" onclick="closeHistori()">✕</button>
    </div>
    <div class="modal-body-gt" id="modal-histori-body">
      <div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
.filter-bar{background:var(--gray100,#f0f2f8);border-radius:10px;padding:.8rem 1rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
.filter-field{display:flex;flex-direction:column;gap:4px}
.filter-label{font-size:.78rem;font-weight:700;color:var(--navy);letter-spacing:.02em}
.form-control-gt,.form-select-gt{border:1.5px solid var(--border);border-radius:8px;padding:.5rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;outline:none;transition:.2s}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(26,39,68,.08)}
.btn-outline-g{background:transparent;color:var(--navy);border:1.5px solid var(--border);border-radius:8px;padding:.4rem .9rem;font-weight:600;font-size:.8rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.btn-outline-g:hover{border-color:var(--navy);background:#f0f2f8}
.btn-histori{margin-right:3px;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#e8edf7;color:#1a3a7c;border:none;cursor:pointer;transition:background .15s}
.btn-histori:hover{background:#d0d9f0}
.btn-kepemilikan{margin-left:3px;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#e6f4ed;color:#0f6e40;border:none;cursor:pointer;transition:background .15s}
.btn-kepemilikan:hover{background:#c8e8d5}
.kep-header-info{background:#f0f4ff;border-radius:10px;padding:.8rem 1rem;margin-bottom:1rem;display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center}
.kep-stok-val{font-size:1.3rem;font-weight:800;color:var(--navy)}
.kep-stok-lbl{font-size:.72rem;color:#6b7280;margin-bottom:2px}
.kep-table th{background:#f8f9fc;font-size:.75rem;color:#6b7280;font-weight:700;padding:.5rem .8rem}
.kep-table td{padding:.55rem .8rem;font-size:.83rem;border-bottom:1px solid #f0f2f8}
.kep-total-row td{font-weight:700;background:#f0f4ff;color:var(--navy)}
.kep-warning{background:#fffbeb;border:1.5px solid #fcd34d;border-radius:8px;padding:.6rem 1rem;font-size:.8rem;color:#92400e;margin-top:.8rem}
.btn-edit-kep{width:26px;height:26px;border-radius:6px;border:1.5px solid var(--border);background:#fff;color:var(--navy);cursor:pointer;font-size:.78rem;display:inline-flex;align-items:center;justify-content:center;transition:.15s}
.btn-edit-kep:hover{background:var(--navy);color:#fff;border-color:var(--navy)}
.card-header-g{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;border-radius:12px 12px 0 0}
.tbl-empty{text-align:center;padding:2rem;color:#9ca3af}
.badge-gt{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700;letter-spacing:.02em}
.badge-habis{background:#fee2e2;color:#991b1b}
.badge-kritis{background:#fef2f2;color:#c0282d}
.badge-hampir{background:#fef3c7;color:#b45309}
.badge-normal{background:#d1fae5;color:#1a7f4b}
.badge-lokal{background:#ede9fe;color:#6d28d9}
.badge-import{background:#fce7f3;color:#9d174d}
.badge-explant{background:#e0f2fe;color:#0369a1}
.badge-rekondisi{background:#fdf4ff;color:#7e22ce}
.badge-defect{background:#fff7ed;color:#c2410c}
.badge-damage{background:#fef2f2;color:#b91c1c}
.badge-umum{background:#f3f4f6;color:#4b5563}
.badge-tabung{background:#fdf4ff;color:#7e22ce}
/* Pagination */
.pagination-bar{display:flex;align-items:center;gap:.6rem;justify-content:center}
.pg-btn{background:#fff;border:1.5px solid var(--border);border-radius:8px;padding:.4rem 1rem;font-weight:600;font-size:.82rem;color:var(--navy);cursor:pointer;transition:.2s}
.pg-btn:hover:not(:disabled){background:var(--navy);color:#fff;border-color:var(--navy)}
.pg-btn:disabled{opacity:.4;cursor:not-allowed}
.pg-info{font-size:.82rem;color:#6b7280;min-width:90px;text-align:center}
/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);z-index:5000;display:flex;align-items:center;justify-content:center}
.modal-box{background:#fff;border-radius:16px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal-head{background:linear-gradient(135deg, #1a3a7c 0%, #2d5a9f 100%);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem;color:#ffffff}
.modal-close{background:none;border:none;color:#ffffff;font-size:1.2rem;cursor:pointer;line-height:1}
.modal-close:hover{color:#fff}
.modal-body-gt{padding:1.3rem}
.stok-summary{background:#f8f9fc;border-radius:8px;padding:.7rem 1rem;margin-bottom:.8rem;display:flex;gap:1.5rem;flex-wrap:wrap}
.stok-summary-item .sv{font-size:1.2rem;font-weight:800}
.stok-summary-item .sl{font-size:.7rem;color:#6b7280}
.mt-3{margin-top:1rem}
.mb-3{margin-bottom:1rem}
.tbl-g{width:100%;border-collapse:collapse}
.tbl-g thead{background:#f8f9fc;border-bottom:1px solid #e5e7eb}
.tbl-g th{padding:.6rem .8rem;text-align:left;font-size:.75rem;font-weight:700;color:#4b5563;letter-spacing:.02em}
.tbl-g td{padding:.65rem .8rem;border-bottom:1px solid #f0f2f8;font-size:.83rem}
.tbl-g tbody tr:hover{background:#f9fafb}
.tbl-wrap{overflow-x:auto}

/* ============================================================
   RESPONSIVE — HP (desktop/laptop tidak berubah)
   ============================================================ */
@media (max-width:640px) {
  .filter-bar { flex-direction:column; align-items:stretch !important; gap:.7rem; }
  .filter-bar .filter-field { width:100%; align-self:stretch !important; }
  .filter-field input, .filter-field select, .filter-field .form-control-gt, .filter-field .form-select-gt { width:100%; min-width:0 !important; }
  .kep-header-info { gap:.8rem; }
  .btn-histori, .btn-kepemilikan { width:30px; height:30px; }
  .modal-box { max-width:100% !important; width:100% !important; margin:0 8px; }
  .modal-body-gt, .modal-body { padding:.9rem !important; }
  .kep-table th, .kep-table td { padding:.4rem .5rem; font-size:.76rem; }
}
</style>

<script>
// ── State ─────────────────────────────────────────────────────────────────────
var currentPage  = <?= $current_page ?>;
var totalPage    = <?= $total_page ?>;
var debounceTimer;
var role         = '<?= $role ?>';
var isPlant      = role === 'plant';

// ── Helper: badge HTML (mirror PHP helpers di JS) ─────────────────────────────
function badgeKondisiJS(k) {
    var map = {
        habis:         '<span class="badge-gt badge-habis">Habis</span>',
        kritis:        '<span class="badge-gt badge-kritis">Kritis</span>',
        hampir_kritis: '<span class="badge-gt badge-hampir">Hampir Kritis</span>',
        normal:        '<span class="badge-gt badge-normal">Normal</span>',
    };
    return map[k] || '<span class="badge-gt badge-normal">Normal</span>';
}
function badgeBatchJS(b) {
    if (!b) return '<span class="badge-gt badge-umum">UMUM</span>';
    var map = {
        LOCAL:'badge-lokal', IMPORT:'badge-import', EXPLANT:'badge-explant',
        EXPROJECT:'badge-lokal', REKONDISI:'badge-rekondisi',
        DEFECT:'badge-defect', DAMAGE:'badge-damage', UMUM:'badge-umum'
    };
    var cls = map[b] || 'badge-umum';
    return '<span class="badge-gt ' + cls + '">' + escHtml(b) + '</span>';
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtNum(n) {
    return parseInt(n||0).toLocaleString('id-ID');
}

// ── Render satu baris tabel ───────────────────────────────────────────────────
function renderRow(m) {
    var html = '<tr>';
    html += '<td><code class="mono" style="font-size:.75rem;color:var(--navy)">' + (m.kode_sap ? escHtml(m.kode_sap) : '-') + '</code></td>';
    html += '<td><div style="font-weight:600;color:#1f2937">' + escHtml(m.nama_material) + '</div>';
    if (parseInt(m.is_tabung) === 1) html += '<span class="badge-gt badge-tabung" style="margin-top:2px">TABUNG</span>';
    html += '</td>';
    if (!isPlant) {
        html += '<td>' + badgeBatchJS(m.batch || null) + '</td>';
        html += '<td style="white-space:nowrap">';
        if (m.kode_rak) html += '<small style="color:#6b7280"><strong>' + escHtml(m.kode_rak) + '</strong> · ' + escHtml(m.zona) + '</small>';
        else html += '<span style="color:#9ca3af">—</span>';
        html += '</td>';
        html += '<td><strong>' + fmtNum(m.stok) + '</strong> <span style="color:#9ca3af;font-size:.75rem">' + escHtml(m.satuan) + '</span></td>';
        html += '<td>' + fmtNum(m.stok_booking) + '</td>';
        var colorT = parseInt(m.stok_tersedia) <= 0 ? 'color:var(--red)' : 'color:var(--green)';
        html += '<td><strong style="' + colorT + '">' + fmtNum(m.stok_tersedia) + '</strong></td>';
        html += '<td>' + (m.safety_stock !== null ? m.safety_stock : '—') + '</td>';
    } else {
        var colorT2 = parseInt(m.stok_tersedia) <= 0 ? 'color:var(--red)' : '';
        html += '<td><strong style="' + colorT2 + '">' + fmtNum(m.stok_tersedia) + '</strong></td>';
        html += '<td>' + fmtNum(m.stok_booking) + '</td>';
    }
    html += '<td>' + badgeKondisiJS(m.kondisi_stok) + '</td>';
    if (!isPlant) {
        html += '<td style="white-space:nowrap;text-align:center;padding:.65rem .2rem">';
        html += '<button class="btn-histori" onclick="showHistori(' + m.id + ', \'' + escHtml(m.nama_material).replace(/'/g,"\\'") + '\')" title=\"Histori\"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg></button>';
        html += '<button class="btn-kepemilikan" onclick="showKepemilikan(' + m.id + ', \'' + escHtml(m.nama_material).replace(/'/g,"\\'") + '\')" title=\"Kepemilikan\"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg></button>';
        html += '</td>';
    } else {
        html += '<td style="text-align:center;padding:.65rem .2rem"><button class="btn-kepemilikan" onclick="showKepemilikan(' + m.id + ', \'' + escHtml(m.nama_material).replace(/'/g,"\\'") + '\')" title=\"Kepemilikan\"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg></button></td>';
    }
    html += '</tr>';
    return html;
}

// ── Fetch data dari server ────────────────────────────────────────────────────
function fetchData(page) {
    page = page || 1;
    var search  = document.getElementById('inp-search').value;
    var batch   = document.getElementById('inp-batch').value;
    var kondisi = document.getElementById('inp-kondisi').value;

    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('btn-reset').style.display =
        (search || batch || kondisi) ? 'inline-flex' : 'none';

    var params = new URLSearchParams({
        search: search, batch: batch, kondisi: kondisi, page: page
    });

    fetch('/monitoring/data?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        currentPage = data.current_page;
        totalPage   = data.total_page;

        // Update info
        var showing = data.materials.length;
        document.getElementById('info-count').innerHTML =
            data.total.toLocaleString('id-ID') + ' material ditemukan &mdash; ' +
            'Halaman <strong>' + currentPage + '</strong> dari ' + totalPage + ', ' +
            'menampilkan ' + showing + ' data';

        // Update tbody
        var tbody = document.getElementById('tbl-body');
        if (data.materials.length === 0) {
            tbody.innerHTML = '<tr><td colspan="<?= $colspan ?>" class="tbl-empty">Tidak ada material yang sesuai filter</td></tr>';
        } else {
            var rows = '';
            data.materials.forEach(function(m) { rows += renderRow(m); });
            tbody.innerHTML = rows;
        }

        // Update pagination
        updatePagination();

        document.getElementById('loading-spinner').style.display = 'none';
    })
    .catch(function() {
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('tbl-body').innerHTML =
            '<tr><td colspan="<?= $colspan ?>" class="tbl-empty" style="color:var(--red)">Gagal memuat data. Coba lagi.</td></tr>';
    });
}

// ── Update tombol pagination ──────────────────────────────────────────────────
function updatePagination() {
    var bar = document.getElementById('pagination-bar');
    if (totalPage <= 1) { bar.innerHTML = ''; return; }

    var prev = currentPage > 1 ? currentPage - 1 : null;
    var next = currentPage < totalPage ? currentPage + 1 : null;

    bar.innerHTML =
        '<button class="pg-btn" ' + (!prev ? 'disabled' : 'onclick="goPage(' + prev + ')"') + '>← Prev</button>' +
        '<span class="pg-info">Hal <strong id="pg-current">' + currentPage + '</strong> / ' + totalPage + '</span>' +
        '<button class="pg-btn" ' + (!next ? 'disabled' : 'onclick="goPage(' + next + ')"') + '>Next →</button>';
}

function goPage(p) {
    fetchData(p);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Event listeners ───────────────────────────────────────────────────────────
document.getElementById('inp-search').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function() { fetchData(1); }, 500);
});
document.getElementById('inp-batch').addEventListener('change', function() { fetchData(1); });
document.getElementById('inp-kondisi').addEventListener('change', function() { fetchData(1); });
document.getElementById('btn-reset').addEventListener('click', function() {
    document.getElementById('inp-search').value  = '';
    document.getElementById('inp-batch').value   = '';
    document.getElementById('inp-kondisi').value = '';
    fetchData(1);
});

// ── Kepemilikan modal ──────────────────────────────────────────────────────
var KEP = { materialId: null, satuan: '', stok: 0, requesters: [] }; // konteks material yg sedang dibuka

function showKepemilikan(matId, matNama) {
    document.getElementById('modal-kep-title').textContent = 'Kepemilikan Stok — ' + matNama;
    document.getElementById('modal-kep-body').innerHTML =
        '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    document.getElementById('modal-kepemilikan').style.display = 'flex';

    fetch('/monitoring/kepemilikan/' + matId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var mat = data.material;
        var list = data.kepemilikan || [];
        var totalQty = list.reduce(function(s,i){ return s + parseInt(i.qty||0); }, 0);
        var stok = parseInt(mat.stok || 0);
        var sisaTanpaOwner = stok - totalQty;

        KEP.materialId = matId;
        KEP.satuan     = mat.satuan;
        KEP.stok       = stok;
        KEP.requesters = list.map(function(k){ return k.requester; });

        var canEdit = !isPlant;

        var rows = '';
        list.forEach(function(k) {
            rows += '<tr>' +
                '<td style="font-weight:600">' + escHtml(k.requester) + '</td>' +
                '<td style="text-align:right;font-weight:700;color:var(--green)">' + fmtNum(k.qty) + ' <span style="color:#9ca3af;font-size:.75rem">' + escHtml(mat.satuan) + '</span></td>' +
                (canEdit ? '<td style="text-align:center;width:38px"><button class="btn-edit-kep" title="Edit" onclick="openEditKepemilikan(' + matId + ', \'' + escHtml(k.requester).replace(/'/g,"\\'") + '\', ' + parseInt(k.qty) + ')">&#9998;</button></td>' : '') +
            '</tr>';
        });

        if (list.length === 0) {
            rows = '<tr><td colspan="' + (canEdit ? 3 : 2) + '" style="text-align:center;color:#9ca3af;padding:1rem">Belum ada data kepemilikan tercatat</td></tr>';
        }

        var html = '<div class="kep-header-info">' +
            '<div><div class="kep-stok-lbl">Total Stok</div><div class="kep-stok-val">' + fmtNum(stok) + ' ' + escHtml(mat.satuan) + '</div></div>' +
            '<div><div class="kep-stok-lbl">Total Tercatat</div><div class="kep-stok-val" style="color:var(--green)">' + fmtNum(totalQty) + '</div></div>';
        if (mat.kode_rak) html += '<div><div class="kep-stok-lbl">Lokasi Rak</div><div style="font-weight:700;color:#6b7280">' + escHtml(mat.kode_rak) + '</div></div>';
        html += '</div>';

        html += '<table class="tbl-g kep-table" style="width:100%"><thead><tr><th>Requester / Pemilik</th><th style="text-align:right">Qty Tersedia</th>' + (canEdit ? '<th></th>' : '') + '</tr></thead><tbody>' +
            rows;

        if (list.length > 0) {
            html += '<tr class="kep-total-row"><td>Total Tercatat</td><td style="text-align:right">' + fmtNum(totalQty) + ' ' + escHtml(mat.satuan) + '</td>' + (canEdit ? '<td></td>' : '') + '</tr>';
        }

        html += '</tbody></table>';

        if (sisaTanpaOwner > 0) {
            html += '<div class="kep-warning">&#9888; Ada <strong>' + fmtNum(sisaTanpaOwner) + ' ' + escHtml(mat.satuan) + '</strong> stok tanpa requester tercatat' +
                (canEdit ? ' &nbsp;<button class="btn-g btn-out-g" style="padding:.3rem .7rem;font-size:.75rem;margin-left:.4rem" onclick="openEditKepemilikan(' + matId + ', \'\', 0)">+ Catat Pemilik</button>' : '') +
                '</div>';
        } else if (canEdit) {
            html += '<div style="margin-top:.8rem;text-align:right">' +
                '<button class="btn-g btn-out-g" style="padding:.3rem .8rem;font-size:.78rem" onclick="openEditKepemilikan(' + matId + ', \'\', 0)">+ Tambah Requester Baru</button>' +
                '</div>';
        }

        document.getElementById('modal-kep-body').innerHTML = html;
    })
    .catch(function() {
        document.getElementById('modal-kep-body').innerHTML =
            '<div style="color:var(--red);padding:1rem">Gagal memuat data kepemilikan.</div>';
    });
}
function closeKepemilikan() {
    document.getElementById('modal-kepemilikan').style.display = 'none';
}
document.getElementById('modal-kepemilikan').addEventListener('click', function(e) {
    if (e.target === this) closeKepemilikan();
});

<?php if (!$isPlant): ?>
// ── Edit / Tambah Kepemilikan ────────────────────────────────────────────────
function openEditKepemilikan(matId, requesterName, currentQty) {
    document.getElementById('edit-kep-material-id').value = matId;
    document.getElementById('edit-kep-requester').value   = requesterName;
    document.getElementById('edit-kep-qty').value          = currentQty;
    document.getElementById('edit-kep-error').style.display = 'none';

    document.getElementById('edit-kep-title').textContent = requesterName
        ? 'Edit Kepemilikan — ' + requesterName
        : 'Catat Kepemilikan Baru';

    // Total qty milik requester lain (di luar yang sedang diedit) menentukan batas maksimal

    fetch('/monitoring/kepemilikan/' + matId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r){ return r.json(); })
        .then(function(data){
            var list = data.kepemilikan || [];
            var totalLain = list.reduce(function(s,i){
                return s + (i.requester === requesterName ? 0 : parseInt(i.qty||0));
            }, 0);
            var maxQty = Math.max(0, KEP.stok - totalLain);
            document.getElementById('edit-kep-qty').setAttribute('max', maxQty);
            document.getElementById('edit-kep-max-hint').textContent =
                'Maksimal ' + fmtNum(maxQty) + ' ' + KEP.satuan + ' (sisa stok yang bisa dialokasikan ke requester ini)';
        });

    document.getElementById('edit-kep-info').textContent =
        'Total stok material ini: ' + fmtNum(KEP.stok) + ' ' + KEP.satuan + '. Isi qty untuk mengalokasikan kepemilikan ke requester.';

    // Isi datalist dari daftar requester yang sudah ada (untuk autocomplete)
    var dl = document.getElementById('edit-kep-requester-list');
    dl.innerHTML = KEP.requesters.map(function(r){ return '<option value="' + escHtml(r) + '">'; }).join('');

    document.getElementById('modal-edit-kepemilikan').style.display = 'flex';
}
function closeEditKepemilikan() {
    document.getElementById('modal-edit-kepemilikan').style.display = 'none';
}
document.getElementById('modal-edit-kepemilikan').addEventListener('click', function(e) {
    if (e.target === this) closeEditKepemilikan();
});

function simpanEditKepemilikan() {
    var matId      = document.getElementById('edit-kep-material-id').value;
    var requester  = document.getElementById('edit-kep-requester').value.trim();
    var qty        = parseInt(document.getElementById('edit-kep-qty').value || 0);
    var errBox     = document.getElementById('edit-kep-error');
    errBox.style.display = 'none';

    if (!requester) {
        errBox.textContent = 'Nama requester/pemilik wajib diisi.';
        errBox.style.display = 'block';
        return;
    }
    if (isNaN(qty) || qty < 0) {
        errBox.textContent = 'Qty tidak valid.';
        errBox.style.display = 'block';
        return;
    }

    fetch('/monitoring/kepemilikan/simpan/' + matId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'requester=' + encodeURIComponent(requester) + '&qty=' + encodeURIComponent(qty)
    })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (!res.success) {
            errBox.textContent = res.message || 'Gagal menyimpan.';
            errBox.style.display = 'block';
            return;
        }
        closeEditKepemilikan();
        showToastMonitoring(res.message);
        // Refresh tampilan modal kepemilikan dengan data terbaru
        var matNama = document.getElementById('modal-kep-title').textContent.replace('Kepemilikan Stok — ', '');
        showKepemilikan(matId, matNama);
        fetchData(currentPage); // sinkronkan kolom "Tersedia" di tabel utama jika ada perubahan
    })
    .catch(function(){
        errBox.textContent = 'Gagal menghubungi server.';
        errBox.style.display = 'block';
    });
}

function showToastMonitoring(msg) {
    if (typeof showAlert === 'function') { showAlert(msg); return; }
    alert(msg);
}
<?php endif; ?>

<?php if (!$isPlant): ?>
function showHistori(matId, matNama) {
    document.getElementById('modal-histori-title').textContent = 'Histori Mutasi — ' + matNama;
    document.getElementById('modal-histori-body').innerHTML =
        '<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
    document.getElementById('modal-histori').style.display = 'flex';

    fetch('/monitoring/histori/' + matId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var mat = data.material;
        var jColors = {
            masuk:'color:var(--green)', keluar:'color:var(--red)',
            booking:'color:var(--amber)', selesai_booking:'color:#6b7280',
            penyesuaian:'color:var(--navy)'
        };
        var rows = '';
        if (!data.mutasi || data.mutasi.length === 0) {
            rows = '<tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:1rem">Belum ada mutasi</td></tr>';
        } else {
            data.mutasi.forEach(function(m) {
                var jStyle = jColors[m.jenis] || '';
                var qStyle = parseFloat(m.jumlah) > 0 ? 'color:var(--green)' : 'color:var(--red)';
                var qSign  = parseFloat(m.jumlah) > 0 ? '+' : '';
                rows += '<tr>' +
                    '<td style="color:#9ca3af;font-size:.72rem;white-space:nowrap">' + (m.created_at||'—') + '</td>' +
                    '<td style="' + jStyle + ';font-weight:700;font-size:.78rem;text-transform:uppercase">' + m.jenis + '</td>' +
                    '<td style="' + qStyle + ';font-weight:700">' + qSign + m.jumlah + '</td>' +
                    '<td><strong>' + m.stok_sesudah + '</strong> ' + escHtml(mat.satuan) + '</td>' +
                    '<td style="font-size:.75rem">' + (m.nama_user||'—') + '</td>' +
                    '<td style="font-size:.72rem;color:#6b7280">' + (m.keterangan||'—') + '</td>' +
                '</tr>';
            });
        }
        var tersedia = parseInt(mat.stok||0) - parseInt(mat.stok_booking||0);
        document.getElementById('modal-histori-body').innerHTML =
            '<div class="stok-summary">' +
            '<div class="stok-summary-item"><div class="sl">Stok Fisik</div><div class="sv" style="color:var(--navy)">' + mat.stok + ' ' + escHtml(mat.satuan) + '</div></div>' +
            '<div class="stok-summary-item"><div class="sl">Tersedia</div><div class="sv" style="color:var(--green)">' + tersedia + '</div></div>' +
            '<div class="stok-summary-item"><div class="sl">Booking</div><div class="sv" style="color:var(--amber)">' + (mat.stok_booking||0) + '</div></div>' +
            '</div>' +
            '<div class="tbl-wrap"><table class="tbl-g"><thead><tr>' +
            '<th>Waktu</th><th>Jenis</th><th>Jumlah</th><th>Saldo</th><th>Oleh</th><th>Keterangan</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table></div>';
    })
    .catch(function() {
        document.getElementById('modal-histori-body').innerHTML =
            '<div style="color:var(--red);padding:1rem">Gagal memuat data.</div>';
    });
}
function closeHistori() {
    document.getElementById('modal-histori').style.display = 'none';
}
document.getElementById('modal-histori').addEventListener('click', function(e) {
    if (e.target === this) closeHistori();
});
<?php endif; ?>
</script>

<?= $this->endSection() ?>