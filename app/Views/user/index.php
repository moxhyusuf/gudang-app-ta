<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Manajemen User</h1>
    <p>Total <?= number_format($total) ?> user terdaftar</p>
  </div>
  <div class="page-hd-right">
    <button class="btn-g btn-out-g" onclick="openRiwayatHapus()">🕓 Riwayat Hapus</button>
    <button class="btn-g btn-navy-g" onclick="openTambah()">＋ Tambah User Baru</button>
  </div>
</div>

<!-- FILTER BAR -->
<div class="filter-bar mb-3">
  <div class="filter-field">
    <label class="filter-label">Cari</label>
    <div class="input-icon-wrap">
      <span class="input-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </span>
      <input type="text" id="inp-search" class="form-control-gt form-control-icon"
             placeholder="Nama / Username..." value="<?= esc($search) ?>">
    </div>
  </div>
  <div class="filter-field">
    <label class="filter-label">Role</label>
    <div class="select-wrap">
      <select id="inp-role" class="form-select-gt">
        <option value="">Semua Role</option>
        <option value="admin_gt"   <?= $role==='admin_gt'   ? 'selected':'' ?>>Admin GT</option>
        <option value="petugas_gt" <?= $role==='petugas_gt' ? 'selected':'' ?>>Petugas GT</option>
        <option value="plant"      <?= $role==='plant'      ? 'selected':'' ?>>Plant</option>
      </select>
      <span class="select-arrow">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </div>
  </div>
  <div class="filter-field" style="align-self:flex-end">
    <button id="btn-reset" class="btn-reset-g"
            style="display:<?= ($search || $role) ? 'inline-flex' : 'none' ?>">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      Reset Filter
    </button>
  </div>
</div>

<!-- TABEL USER -->
<div class="card-g">
  <div class="card-header-g">👤 Daftar User</div>
  <div class="tbl-wrap">
    <table class="tbl-g">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Role</th>
          <th>Plant</th>
          <th>Status</th>
          <th>Tgl Dibuat</th>
          <th style="text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr><td colspan="8" class="tbl-empty">Tidak ada user ditemukan</td></tr>
        <?php else: ?>
        <?php foreach ($users as $i => $u): ?>
        <tr id="row-<?= $u['id'] ?>">
          <td style="color:#9ca3af;font-size:.8rem"><?= $i + 1 + (($page - 1) * 15) ?></td>
          <td><strong style="color:#1f2937"><?= esc($u['nama']) ?></strong></td>
          <td><code class="username-code"><?= esc($u['username']) ?></code></td>
          <td><?= roleBadge($u['role']) ?></td>
          <td><?= $u['nama_plant'] ? esc($u['nama_plant']) : '<span style="color:#9ca3af">—</span>' ?></td>
          <td>
            <span id="status-<?= $u['id'] ?>" class="status-chip <?= $u['is_active'] ? 'chip-aktif' : 'chip-nonaktif' ?>">
              <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
            </span>
          </td>
          <td style="font-size:.8rem;color:#6b7280"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td style="text-align:center">
            <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
              <button class="btn-g btn-navy-g btn-sm-g" onclick="openEdit(<?= $u['id'] ?>)">✏️ Edit</button>
              <button class="btn-g btn-sm-g <?= $u['is_active'] ? 'btn-warn-g' : 'btn-ok-g' ?>"
                      id="btn-toggle-<?= $u['id'] ?>"
                      onclick="toggleStatus(<?= $u['id'] ?>, <?= $u['is_active'] ?>)">
                <?= $u['is_active'] ? '🔒 Nonaktifkan' : '🔓 Aktifkan' ?>
              </button>
              <button class="btn-g btn-danger-g btn-sm-g"
                      onclick="hapusUser(<?= $u['id'] ?>, '<?= esc($u['nama']) ?>')">🗑️ Hapus</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- PAGINATION -->
<div class="pagination-bar mt-3">
  <?php if ($total_page > 1):
    $qs = http_build_query(['search' => $search, 'role' => $role]);
  ?>
  <?php if ($page > 1): ?>
  <a href="?<?= $qs ?>&page=<?= $page - 1 ?>" class="page-btn">‹ Prev</a>
  <?php endif; ?>
  <?php for ($p = max(1, $page-2); $p <= min($total_page, $page+2); $p++): ?>
  <a href="?<?= $qs ?>&page=<?= $p ?>" class="page-btn <?= $p == $page ? 'page-active' : '' ?>"><?= $p ?></a>
  <?php endfor; ?>
  <?php if ($page < $total_page): ?>
  <a href="?<?= $qs ?>&page=<?= $page + 1 ?>" class="page-btn">Next ›</a>
  <?php endif; ?>
  <?php endif; ?>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL TAMBAH USER
════════════════════════════════════════════════════════════════ -->
<div id="modal-tambah" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:500px">
    <div class="modal-head">
      <div class="modal-head-inner">
        <div class="modal-icon-wrap">＋</div>
        <div>
          <h6 class="modal-title">Tambah User Baru</h6>
          <p class="modal-subtitle">Isi data lengkap untuk membuat akun baru</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-tambah')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body-gt">

      <div class="form-group">
        <label class="form-label-gt">Nama Lengkap <span class="required-dot">*</span></label>
        <div class="modal-input-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input type="text" id="t-nama" class="modal-input" placeholder="Masukkan nama lengkap">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-gt">Username <span class="required-dot">*</span></label>
        <div class="modal-input-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </span>
          <input type="text" id="t-username" class="modal-input" placeholder="Username untuk login" autocomplete="off">
        </div>
        <span class="form-hint">Digunakan untuk masuk ke sistem</span>
      </div>

      <div class="form-group">
        <label class="form-label-gt">Password <span class="required-dot">*</span></label>
        <div class="modal-input-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input type="password" id="t-password" class="modal-input modal-input-pw" placeholder="Minimal 6 karakter" autocomplete="new-password">
          <button type="button" class="pw-toggle" onclick="togglePw('t-password',this)" title="Tampilkan password">
            <svg class="eye-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-gt">Role <span class="required-dot">*</span></label>
        <div class="modal-select-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          <select id="t-role" class="modal-select" onchange="handleRoleChange('t-plant-wrap',this.value)">
            <option value="">— Pilih Role —</option>
            <option value="admin_gt">Admin GT</option>
            <option value="petugas_gt">Petugas GT</option>
            <option value="plant">Plant</option>
          </select>
          <span class="modal-select-arrow">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>
      </div>

      <div class="form-group" id="t-plant-wrap" style="display:none">
        <label class="form-label-gt">Plant <span class="required-dot">*</span></label>
        <div class="modal-select-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </span>
          <select id="t-plant" class="modal-select">
            <option value="">— Pilih Plant —</option>
            <?php foreach ($plants as $pl): ?>
            <option value="<?= $pl['id'] ?>"><?= esc($pl['nama_plant']) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="modal-select-arrow">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>
      </div>

      <div id="t-error" class="alert-error" style="display:none"></div>

      <div class="modal-actions">
        <button class="btn-g btn-out-g" onclick="closeModal('modal-tambah')">Batal</button>
        <button class="btn-g btn-navy-g" id="btn-simpan-tambah" onclick="simpanUser()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan User
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL EDIT USER
════════════════════════════════════════════════════════════════ -->
<div id="modal-edit" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:500px">
    <div class="modal-head">
      <div class="modal-head-inner">
        <div class="modal-icon-wrap">✏️</div>
        <div>
          <h6 class="modal-title">Edit User</h6>
          <p class="modal-subtitle">Perbarui informasi akun pengguna</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-edit')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body-gt">
      <input type="hidden" id="e-id">

      <div class="form-group">
        <label class="form-label-gt">Nama Lengkap <span class="required-dot">*</span></label>
        <div class="modal-input-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input type="text" id="e-nama" class="modal-input" placeholder="Nama lengkap">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-gt">Username <span class="required-dot">*</span></label>
        <div class="modal-input-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </span>
          <input type="text" id="e-username" class="modal-input" autocomplete="off">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-gt">Password Baru <span class="badge-optional">Opsional</span></label>
        <div class="modal-input-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input type="password" id="e-password" class="modal-input modal-input-pw" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
          <button type="button" class="pw-toggle" onclick="togglePw('e-password',this)" title="Tampilkan password">
            <svg class="eye-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <span class="form-hint">Biarkan kosong jika tidak ingin mengubah password</span>
      </div>

      <div class="form-group">
        <label class="form-label-gt">Role <span class="required-dot">*</span></label>
        <div class="modal-select-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          <select id="e-role" class="modal-select" onchange="handleRoleChange('e-plant-wrap',this.value)">
            <option value="admin_gt">Admin GT</option>
            <option value="petugas_gt">Petugas GT</option>
            <option value="plant">Plant</option>
          </select>
          <span class="modal-select-arrow">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>
      </div>

      <div class="form-group" id="e-plant-wrap" style="display:none">
        <label class="form-label-gt">Plant <span class="required-dot">*</span></label>
        <div class="modal-select-wrap">
          <span class="modal-field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </span>
          <select id="e-plant" class="modal-select">
            <option value="">— Pilih Plant —</option>
            <?php foreach ($plants as $pl): ?>
            <option value="<?= $pl['id'] ?>"><?= esc($pl['nama_plant']) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="modal-select-arrow">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>
      </div>

      <div id="e-error" class="alert-error" style="display:none"></div>

      <div class="modal-actions">
        <button class="btn-g btn-out-g" onclick="closeModal('modal-edit')">Batal</button>
        <button class="btn-g btn-navy-g" id="btn-simpan-edit" onclick="updateUser()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Perubahan
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL HAPUS
════════════════════════════════════════════════════════════════ -->
<div id="modal-hapus" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-head modal-head-danger">
      <div class="modal-head-inner">
        <div class="modal-icon-wrap">🗑️</div>
        <div>
          <h6 class="modal-title">Konfirmasi Hapus</h6>
          <p class="modal-subtitle">Tindakan ini tidak dapat dibatalkan</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-hapus')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body-gt hapus-body">
      <div class="hapus-icon">⚠️</div>
      <p class="hapus-text">Yakin ingin menghapus user</p>
      <p id="hapus-nama" class="hapus-nama"></p>
      <p class="hapus-sub">Data user akan dihapus permanen dari sistem dan tidak bisa dipulihkan.</p>
      <input type="hidden" id="hapus-id">
      <div class="modal-actions" style="justify-content:center">
        <button class="btn-g btn-out-g" onclick="closeModal('modal-hapus')">Batal</button>
        <button class="btn-g btn-danger-g" onclick="konfirmasiHapus()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          Ya, Hapus User
        </button>
      </div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL RIWAYAT HAPUS
════════════════════════════════════════════════════════════════ -->
<div id="modal-riwayat-hapus" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:680px">
    <div class="modal-head">
      <div class="modal-head-inner">
        <div class="modal-icon-wrap">🕓</div>
        <div>
          <h6 class="modal-title">Riwayat Aktivitas Hapus User</h6>
          <p class="modal-subtitle">100 aktivitas terakhir</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-riwayat-hapus')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body-gt" style="max-height:60vh; overflow-y:auto">
      <table style="width:100%; border-collapse:collapse; font-size:.82rem">
        <thead>
          <tr style="text-align:left; border-bottom:2px solid #e5e7eb">
            <th style="padding:.5rem .4rem">User Dihapus</th>
            <th style="padding:.5rem .4rem">Tipe</th>
            <th style="padding:.5rem .4rem">Dihapus Oleh</th>
            <th style="padding:.5rem .4rem">Alasan</th>
            <th style="padding:.5rem .4rem">Waktu</th>
          </tr>
        </thead>
        <tbody id="riwayat-hapus-body">
          <tr><td colspan="5" style="padding:1rem .4rem; color:#9ca3af; text-align:center">Memuat data...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL INFO HAPUS GAGAL / TAWARAN HAPUS PAKSA
════════════════════════════════════════════════════════════════ -->
<div id="modal-info-hapus" class="modal-overlay" style="display:none">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head modal-head-danger">
      <div class="modal-head-inner">
        <div class="modal-icon-wrap">⚠️</div>
        <div>
          <h6 class="modal-title">Tidak Bisa Dihapus</h6>
          <p class="modal-subtitle">Baca informasi berikut</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-info-hapus')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body-gt hapus-body">
      <p id="info-hapus-msg" class="hapus-text" style="text-align:left"></p>

      <div id="info-hapus-alasan-wrap" style="display:none; text-align:left; margin-top:.6rem">
        <label style="font-size:.8rem; font-weight:700; color:#374151">Alasan hapus paksa (opsional)</label>
        <textarea id="info-hapus-alasan" rows="2" style="width:100%; margin-top:.3rem; padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; font-family:inherit" placeholder="Contoh: akun duplikat, sudah resign, dll."></textarea>
      </div>

      <input type="hidden" id="info-hapus-id">
      <div class="modal-actions" style="justify-content:center; margin-top:1rem">
        <button class="btn-g btn-out-g" onclick="closeModal('modal-info-hapus')">OK, Mengerti</button>
        <button id="btn-tetap-hapus" class="btn-g btn-danger-g" style="display:none" onclick="forceHapusUser()">
          Tetap Hapus
        </button>
      </div>
    </div>
  </div>
</div>


<!-- TOAST -->
<div id="toast-notif" class="toast-notif" style="display:none">
  <span id="toast-icon"></span>
  <span id="toast-msg"></span>
</div>


<style>
/* ═══════════════════════════════════════════════════════
   ROLE & STATUS BADGES
═══════════════════════════════════════════════════════ */
.role-badge {
  display:inline-block;
  padding:3px 11px;
  border-radius:20px;
  font-size:.72rem;
  font-weight:700;
  letter-spacing:.03em;
}
.rb-admin   { background:#1e3a5f; color:#fff; }
.rb-petugas { background:#0ea5e9; color:#fff; }
.rb-plant   { background:#10b981; color:#fff; }

.status-chip {
  display:inline-block;
  padding:3px 11px;
  border-radius:20px;
  font-size:.72rem;
  font-weight:700;
}
.chip-aktif    { background:#dcfce7; color:#166534; }
.chip-nonaktif { background:#fee2e2; color:#991b1b; }

/* ═══════════════════════════════════════════════════════
   ACTION BUTTONS
═══════════════════════════════════════════════════════ */
.btn-warn-g   { background:#f59e0b!important; color:#fff!important; border-color:#f59e0b!important; }
.btn-warn-g:hover { background:#d97706!important; }
.btn-ok-g     { background:#10b981!important; color:#fff!important; border-color:#10b981!important; }
.btn-ok-g:hover   { background:#059669!important; }
.btn-danger-g { background:#ef4444!important; color:#fff!important; border-color:#ef4444!important; }
.btn-danger-g:hover { background:#dc2626!important; }

/* ═══════════════════════════════════════════════════════
   PAGINATION
═══════════════════════════════════════════════════════ */
.pagination-bar { display:flex; align-items:center; gap:.5rem; justify-content:center; }
.page-btn {
  background:#fff;
  border:1.5px solid var(--border,#dde3f0);
  border-radius:8px;
  padding:.38rem .9rem;
  font-weight:600;
  font-size:.82rem;
  color:var(--navy);
  cursor:pointer;
  text-decoration:none;
  transition:.18s;
  display:inline-block;
}
.page-btn:hover { background:var(--navy); color:#fff; border-color:var(--navy); }
.page-active    { background:var(--navy)!important; color:#fff!important; border-color:var(--navy)!important; }

/* ═══════════════════════════════════════════════════════
   USERNAME CODE
═══════════════════════════════════════════════════════ */
.username-code {
  font-size:.78rem;
  color:var(--navy);
  background:#f0f2f8;
  padding:2px 7px;
  border-radius:5px;
  font-family:ui-monospace, 'Cascadia Code', monospace;
}

/* ═══════════════════════════════════════════════════════
   FILTER BAR
═══════════════════════════════════════════════════════ */
.filter-bar {
  background:#fff;
  border:1.5px solid #e4e9f5;
  border-radius:14px;
  padding:.85rem 1.1rem;
  display:flex;
  gap:.8rem;
  flex-wrap:wrap;
  align-items:flex-end;
  box-shadow:0 2px 10px rgba(30,58,95,.06);
}
.filter-field{
  display:flex;
  flex-direction:column;
  gap:6px;
}
.filter-label {
  font-size:.72rem;
  font-weight:700;
  color:var(--navy);
  letter-spacing:.04em;
  text-transform:uppercase;
}
.btn-reset-g {
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:.45rem 1rem;
  background:#fef2f2;
  border:1.5px solid #fca5a5;
  border-radius:9px;
  color:#dc2626;
  font-size:.78rem;
  font-weight:700;
  cursor:pointer;
  transition:.18s;
  white-space:nowrap;
}
.btn-reset-g:hover { background:#fee2e2; border-color:#ef4444; }

/* INPUT FILTER */
.form-control-gt,
.form-select-gt{
  height:44px;
  padding:.6rem .9rem;
  border:1.5px solid #dde3f0;
  border-radius:10px;
  font-size:.9rem;
  color:#1f2937;
  background:#fff;
  outline:none;
  transition:.18s;
  box-sizing:border-box;
}

.form-control-gt{
  min-width:250px;
}

.form-control-gt:focus,
.form-select-gt:focus{
  border-color:var(--navy,#1e3a5f);
  box-shadow:0 0 0 3px rgba(30,58,95,.1);
}

/* supaya icon search ga nabrak */
.form-control-icon{
  padding-left:38px !important;
}

/* select role */
.form-select-gt{
  min-width:200px;
  appearance:none;
  -webkit-appearance:none;
  padding-right:38px !important;
}

/* ═══════════════════════════════════════════════════════
   FILTER BAR — input & select wrapper (existing, tidak diubah)
═══════════════════════════════════════════════════════ */
.input-icon-wrap {
  position:relative;
  display:inline-flex;
  align-items:center;
}
.input-icon {
  position:absolute;
  left:10px;
  display:flex;
  align-items:center;
  pointer-events:none;
  color:#94a3b8;
}
.form-control-icon { padding-left:34px !important; }

.filter-bar .select-wrap {
  position:relative;
  display:inline-flex;
  align-items:center;
  min-width:160px;
}
.filter-bar .form-select-gt {
  -webkit-appearance:none;
  appearance:none;
  padding-right:32px !important;
  width:100%;
}
.select-arrow {
  position:absolute;
  right:10px;
  display:flex;
  align-items:center;
  pointer-events:none;
  color:#64748b;
}

/* ═══════════════════════════════════════════════════════
   MODAL — INPUT & SELECT FIELDS (isolated, tidak konflik)
═══════════════════════════════════════════════════════ */
.modal-input-wrap {
  position:relative;
  display:flex;
  align-items:center;
  width:100%;
}
.modal-field-icon {
  position:absolute;
  left:12px;
  top:50%;
  transform:translateY(-50%);
  display:flex;
  align-items:center;
  pointer-events:none;
  color:#94a3b8;
  z-index:2;
}
.modal-input {
  width:100%;
  padding:.6rem .85rem .6rem 38px;
  border:1.5px solid #dde3f0;
  border-radius:10px;
  font-size:.85rem;
  color:#1f2937;
  background:#fff;
  outline:none;
  transition:border-color .18s, box-shadow .18s;
  box-sizing:border-box;
}
.modal-input:focus {
  border-color:var(--navy, #1e3a5f);
  box-shadow:0 0 0 3px rgba(30,58,95,.1);
}
.modal-input::placeholder { color:#b0bac9; }

/* Password input — ruang kanan untuk toggle button */
.modal-input-pw { padding-right:40px !important; }

/* Select di modal */
.modal-select-wrap {
  position:relative;
  display:flex;
  align-items:center;
  width:100%;
}
.modal-select {
  width:100%;
  padding:.6rem 36px .6rem 38px;
  border:1.5px solid #dde3f0;
  border-radius:10px;
  font-size:.85rem;
  color:#1f2937;
  background:#fff;
  outline:none;
  -webkit-appearance:none;
  appearance:none;
  cursor:pointer;
  transition:border-color .18s, box-shadow .18s;
  box-sizing:border-box;
}
.modal-select:focus {
  border-color:var(--navy, #1e3a5f);
  box-shadow:0 0 0 3px rgba(30,58,95,.1);
}
.modal-select-arrow {
  position:absolute;
  right:12px;
  top:50%;
  transform:translateY(-50%);
  display:flex;
  align-items:center;
  pointer-events:none;
  color:#64748b;
}

/* ═══════════════════════════════════════════════════════
   PASSWORD TOGGLE BUTTON
═══════════════════════════════════════════════════════ */
.pw-toggle {
  position:absolute;
  right:10px;
  top:50%;
  transform:translateY(-50%);
  background:none;
  border:none;
  cursor:pointer;
  padding:4px;
  display:flex;
  align-items:center;
  color:#94a3b8;
  border-radius:4px;
  transition:.15s;
  z-index:3;
}
.pw-toggle:hover { color:var(--navy, #1e3a5f); background:#f0f2f8; }

/* ═══════════════════════════════════════════════════════
   FORM HELPERS
═══════════════════════════════════════════════════════ */
.form-group {
  display:flex;
  flex-direction:column;
  gap:6px;
  margin-bottom:1rem;
}
.form-label-gt {
  font-size:.75rem;
  font-weight:700;
  color:var(--navy, #1e3a5f);
  display:flex;
  align-items:center;
  gap:6px;
}
.required-dot { color:#ef4444; font-weight:900; }
.badge-optional {
  background:#f1f5f9;
  color:#64748b;
  font-size:.65rem;
  font-weight:600;
  padding:1px 7px;
  border-radius:10px;
  letter-spacing:.03em;
}
.form-hint { font-size:.72rem; color:#94a3b8; margin-top:1px; }

/* ═══════════════════════════════════════════════════════
   ALERT ERROR
═══════════════════════════════════════════════════════ */
.alert-error {
  color:#991b1b;
  font-size:.82rem;
  background:#fef2f2;
  padding:.6rem .9rem;
  border-radius:9px;
  border:1.5px solid #fca5a5;
  margin-bottom:.8rem;
  display:flex;
  align-items:flex-start;
  gap:7px;
}
.alert-error::before {
  content:'⚠️';
  font-size:.85rem;
  flex-shrink:0;
  margin-top:1px;
}

/* ═══════════════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════════════ */
.modal-overlay {
  position:fixed;
  inset:0;
  background:rgba(15,32,68,.55);
  backdrop-filter:blur(3px);
  -webkit-backdrop-filter:blur(3px);
  z-index:5000;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:1rem;
}
.modal-box {
  background:#fff;
  border-radius:18px;
  width:100%;
  max-height:90vh;
  overflow-y:auto;
  box-shadow:0 24px 64px rgba(0,0,0,.22);
  animation:modalIn .22s cubic-bezier(.34,1.56,.64,1);
}
@keyframes modalIn {
  from { opacity:0; transform:scale(.94) translateY(12px); }
  to   { opacity:1; transform:scale(1) translateY(0); }
}

.modal-head {
  background:var(--navy, #1e3a5f);
  color:#fff;
  padding:.9rem 1.2rem;
  border-radius:18px 18px 0 0;
  display:flex;
  align-items:center;
  justify-content:space-between;
  position:sticky;
  top:0;
  z-index:10;
}
.modal-head-danger { background:#ef4444; }
.modal-head-inner  { display:flex; align-items:center; gap:12px; }
.modal-icon-wrap {
  width:36px; height:36px;
  background:rgba(255,255,255,.15);
  border-radius:10px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:1rem;
  flex-shrink:0;
}
.modal-title    { margin:0; font-weight:700; font-size:.9rem; line-height:1.2; }
.modal-subtitle { margin:0; font-size:.72rem; opacity:.7; margin-top:1px; }
.modal-close {
  background:rgba(255,255,255,.1);
  border:none;
  color:rgba(255,255,255,.85);
  width:30px; height:30px;
  border-radius:8px;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  transition:.15s;
  flex-shrink:0;
}
.modal-close:hover { background:rgba(255,255,255,.22); color:#fff; }

.modal-body-gt { padding:1.4rem 1.4rem 1.2rem; }

.modal-actions {
  display:flex;
  gap:8px;
  justify-content:flex-end;
  margin-top:1.2rem;
  padding-top:1rem;
  border-top:1.5px solid #f1f5f9;
}

/* ═══════════════════════════════════════════════════════
   MODAL HAPUS
═══════════════════════════════════════════════════════ */
.hapus-body  { text-align:center; padding:1.8rem 1.5rem 1.4rem; }
.hapus-icon  { font-size:2.5rem; margin-bottom:.8rem; }
.hapus-text  { font-size:.95rem; color:#374151; margin:0; }
.hapus-nama  { font-size:1.05rem; font-weight:800; color:var(--navy, #1e3a5f); margin:.4rem 0 .8rem; }
.hapus-sub   { font-size:.78rem; color:#9ca3af; margin:0 0 1.5rem; line-height:1.5; }
.hapus-body .modal-actions { border-top:none; padding-top:0; margin-top:0; }

/* ═══════════════════════════════════════════════════════
   TOAST
═══════════════════════════════════════════════════════ */
.toast-notif {
  position:fixed;
  bottom:24px;
  right:24px;
  z-index:9999;
  padding:12px 18px;
  border-radius:12px;
  font-size:.85rem;
  font-weight:600;
  color:#fff;
  box-shadow:0 8px 24px rgba(0,0,0,.18);
  max-width:320px;
  display:flex;
  align-items:center;
  gap:9px;
  transition:opacity .3s, transform .3s;
}

/* ═══════════════════════════════════════════════════════
   TABLE MISC
═══════════════════════════════════════════════════════ */
.card-header-g {
  background:var(--navy, #1e3a5f);
  color:#fff;
  padding:.8rem 1.2rem;
  font-weight:700;
  font-size:.85rem;
  border-radius:12px 12px 0 0;
}
.tbl-empty { text-align:center; padding:2rem; color:#9ca3af; }
.mb-3 { margin-bottom:1rem; }
.mt-3 { margin-top:1rem; }

/* ============================================================
   RESPONSIVE — HP (desktop/laptop tidak berubah)
   ============================================================ */
@media (max-width:640px) {
  .filter-bar { flex-direction:column; align-items:stretch !important; gap:.7rem; }
  .filter-bar .filter-field { width:100%; align-self:stretch !important; }
  .filter-field input, .filter-field select, .filter-field .form-control-gt, .filter-field .form-select-gt, .filter-bar .select-wrap { width:100%; min-width:0 !important; }
  .modal-box { max-width:100% !important; width:100% !important; margin:0 8px; }
  .modal-body-gt, .modal-body { padding:.9rem !important; }
  .tbl-g th, .tbl-g td { padding:.5rem .6rem; font-size:.78rem; }
}
</style>


<script>
// ── Modal helpers ──────────────────────────────────────────────────────────────
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) {
      if (e.target === this) this.style.display = 'none';
    });
  });
});

function showToast(msg, ok) {
  var t   = document.getElementById('toast-notif');
  var ico = document.getElementById('toast-icon');
  var txt = document.getElementById('toast-msg');
  ico.textContent = ok !== false ? '✓' : '✕';
  txt.textContent = msg;
  t.style.background = ok !== false ? '#16a34a' : '#ef4444';
  t.style.display    = 'flex';
  t.style.opacity    = '1';
  t.style.transform  = 'translateY(0)';
  setTimeout(function() {
    t.style.opacity   = '0';
    t.style.transform = 'translateY(8px)';
    setTimeout(function() { t.style.display = 'none'; }, 320);
  }, 3000);
}

function togglePw(inputId, btn) {
  var inp      = document.getElementById(inputId);
  var isHidden = inp.type === 'password';
  inp.type     = isHidden ? 'text' : 'password';
  btn.innerHTML = isHidden
    ? '<svg class="eye-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
    : '<svg class="eye-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
  btn.style.color = isHidden ? 'var(--navy, #1e3a5f)' : '';
}

function handleRoleChange(wrapId, val) {
  document.getElementById(wrapId).style.display = val === 'plant' ? 'flex' : 'none';
}

// ── Filter ────────────────────────────────────────────────────────────────────
var debounceTimer;
document.getElementById('inp-search').addEventListener('input', function() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(applyFilter, 350);
});
document.getElementById('inp-role').addEventListener('change', applyFilter);
document.getElementById('btn-reset').addEventListener('click', function() {
  document.getElementById('inp-search').value = '';
  document.getElementById('inp-role').value   = '';
  applyFilter();
});

function applyFilter() {
  var search = document.getElementById('inp-search').value.trim();
  var role   = document.getElementById('inp-role').value;
  document.getElementById('btn-reset').style.display = (search || role) ? 'inline-flex' : 'none';
  window.location.href = '/user?search=' + encodeURIComponent(search) + '&role=' + encodeURIComponent(role) + '&page=1';
}

// ── Tambah ────────────────────────────────────────────────────────────────────
function openTambah() {
  ['t-nama','t-username','t-password'].forEach(function(id){ document.getElementById(id).value = ''; });
  document.getElementById('t-role').value  = '';
  document.getElementById('t-plant').value = '';
  document.getElementById('t-plant-wrap').style.display = 'none';
  document.getElementById('t-error').style.display      = 'none';
  var btn = document.getElementById('btn-simpan-tambah');
  btn.disabled  = false;
  btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan User';
  openModal('modal-tambah');
}

function simpanUser() {
  var btn = document.getElementById('btn-simpan-tambah');
  btn.disabled  = true;
  btn.innerHTML = '⏳ Menyimpan...';
  document.getElementById('t-error').style.display = 'none';

  fetch('/user/simpan', {
    method: 'POST',
    body: new URLSearchParams({
      nama:     document.getElementById('t-nama').value.trim(),
      username: document.getElementById('t-username').value.trim(),
      password: document.getElementById('t-password').value,
      role:     document.getElementById('t-role').value,
      plant_id: document.getElementById('t-plant').value,
    })
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if (data.success) {
      closeModal('modal-tambah');
      showToast(data.message);
      setTimeout(function(){ location.reload(); }, 900);
    } else {
      var el = document.getElementById('t-error');
      el.innerHTML     = data.message;
      el.style.display = 'flex';
      btn.disabled     = false;
      btn.innerHTML    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan User';
    }
  })
  .catch(function(){
    btn.disabled  = false;
    btn.innerHTML = '⚠️ Coba Lagi';
  });
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function openEdit(id) {
  document.getElementById('e-error').style.display = 'none';
  document.getElementById('e-password').value      = '';
  fetch('/user/get/' + id)
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (!data.success) { showToast(data.message, false); return; }
      var u = data.data;
      document.getElementById('e-id').value       = u.id;
      document.getElementById('e-nama').value     = u.nama;
      document.getElementById('e-username').value = u.username;
      document.getElementById('e-role').value     = u.role;
      var pw = document.getElementById('e-plant-wrap');
      if (u.role === 'plant') {
        pw.style.display = 'flex';
        document.getElementById('e-plant').value = u.plant_id || '';
      } else {
        pw.style.display = 'none';
      }
      var btn = document.getElementById('btn-simpan-edit');
      btn.disabled  = false;
      btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Perubahan';
      openModal('modal-edit');
    });
}

function updateUser() {
  var id  = document.getElementById('e-id').value;
  var btn = document.getElementById('btn-simpan-edit');
  btn.disabled  = true;
  btn.innerHTML = '⏳ Menyimpan...';
  document.getElementById('e-error').style.display = 'none';

  fetch('/user/update/' + id, {
    method: 'POST',
    body: new URLSearchParams({
      nama:     document.getElementById('e-nama').value.trim(),
      username: document.getElementById('e-username').value.trim(),
      password: document.getElementById('e-password').value,
      role:     document.getElementById('e-role').value,
      plant_id: document.getElementById('e-plant').value,
    })
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if (data.success) {
      closeModal('modal-edit');
      showToast(data.message);
      setTimeout(function(){ location.reload(); }, 900);
    } else {
      var el = document.getElementById('e-error');
      el.innerHTML     = data.message;
      el.style.display = 'flex';
      btn.disabled     = false;
      btn.innerHTML    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Perubahan';
    }
  })
  .catch(function(){
    btn.disabled  = false;
    btn.innerHTML = '⚠️ Coba Lagi';
  });
}

// ── Toggle Status ─────────────────────────────────────────────────────────────
function toggleStatus(id, isActive) {
  if (!confirm('Yakin ingin ' + (isActive ? 'menonaktifkan' : 'mengaktifkan') + ' user ini?')) return;
  fetch('/user/toggle-status/' + id, { method:'POST' })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (!data.success) { showToast(data.message, false); return; }
      showToast(data.message);
      var chip = document.getElementById('status-' + id);
      var btn  = document.getElementById('btn-toggle-' + id);
      if (data.is_active) {
        chip.textContent = 'Aktif';
        chip.className   = 'status-chip chip-aktif';
        btn.textContent  = '🔒 Nonaktifkan';
        btn.className    = 'btn-g btn-warn-g btn-sm-g';
        btn.setAttribute('onclick', 'toggleStatus(' + id + ',1)');
      } else {
        chip.textContent = 'Nonaktif';
        chip.className   = 'status-chip chip-nonaktif';
        btn.textContent  = '🔓 Aktifkan';
        btn.className    = 'btn-g btn-ok-g btn-sm-g';
        btn.setAttribute('onclick', 'toggleStatus(' + id + ',0)');
      }
    });
}

// ── Hapus ─────────────────────────────────────────────────────────────────────
function hapusUser(id, nama) {
  document.getElementById('hapus-id').value         = id;
  document.getElementById('hapus-nama').textContent = '"' + nama + '"';
  openModal('modal-hapus');
}

function openRiwayatHapus() {
  openModal('modal-riwayat-hapus');
  var tbody = document.getElementById('riwayat-hapus-body');
  tbody.innerHTML = '<tr><td colspan="5" style="padding:1rem .4rem; color:#9ca3af; text-align:center">Memuat data...</td></tr>';

  fetch('/user/riwayat-hapus')
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (!data.success || !data.data.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="padding:1rem .4rem; color:#9ca3af; text-align:center">Belum ada aktivitas hapus.</td></tr>';
        return;
      }
      var rows = data.data.map(function(log){
        var tipeLabel = log.tipe_hapus === 'force_delete'
          ? '<span class="role-badge rb-plant">Hapus Paksa</span>'
          : '<span class="role-badge rb-admin">Hapus Permanen</span>';
        return '<tr style="border-bottom:1px solid #f1f5f9">'
          + '<td style="padding:.5rem .4rem"><b>' + log.user_nama + '</b><br><span style="color:#9ca3af">@' + log.user_username + '</span></td>'
          + '<td style="padding:.5rem .4rem">' + tipeLabel + '</td>'
          + '<td style="padding:.5rem .4rem">' + (log.deleted_by_nama || '-') + '</td>'
          + '<td style="padding:.5rem .4rem">' + (log.alasan || '<span style="color:#9ca3af">-</span>') + '</td>'
          + '<td style="padding:.5rem .4rem; white-space:nowrap">' + log.created_at + '</td>'
          + '</tr>';
      }).join('');
      tbody.innerHTML = rows;
    })
    .catch(function(){
      tbody.innerHTML = '<tr><td colspan="5" style="padding:1rem .4rem; color:#ef4444; text-align:center">Gagal memuat data.</td></tr>';
    });
}

function konfirmasiHapus() {
  var id = document.getElementById('hapus-id').value;
  fetch('/user/hapus/' + id, { method:'POST' })
    .then(function(r){ return r.json(); })
    .then(function(data){
      closeModal('modal-hapus');
      if (data.success) {
        showToast(data.message);
        var row = document.getElementById('row-' + id);
        if (row) row.style.cssText = 'opacity:0;transition:.3s';
        setTimeout(function(){ if (row) row.remove(); }, 300);
      } else {
        document.getElementById('info-hapus-id').value = id;
        document.getElementById('info-hapus-msg').textContent = data.message;
        document.getElementById('info-hapus-alasan-wrap').style.display = data.blocked ? 'block' : 'none';
        document.getElementById('btn-tetap-hapus').style.display = data.blocked ? 'inline-flex' : 'none';
        openModal('modal-info-hapus');
      }
    });
}

function forceHapusUser() {
  var id     = document.getElementById('info-hapus-id').value;
  var alasan = document.getElementById('info-hapus-alasan').value;

  fetch('/user/force-hapus/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'alasan=' + encodeURIComponent(alasan)
  })
    .then(function(r){ return r.json(); })
    .then(function(data){
      closeModal('modal-info-hapus');
      showToast(data.message, data.success);
      if (data.success) {
        var row = document.getElementById('row-' + id);
        if (row) row.style.cssText = 'opacity:0;transition:.3s';
        setTimeout(function(){ if (row) row.remove(); }, 300);
      }
    });
}
</script>

<?php
function roleBadge($role) {
  $map = [
    'admin_gt'   => ['rb-admin',   'Admin GT'],
    'petugas_gt' => ['rb-petugas', 'Petugas GT'],
    'plant'      => ['rb-plant',   'Plant'],
  ];
  list($cls, $lbl) = isset($map[$role]) ? $map[$role] : ['rb-admin', $role];
  return '<span class="role-badge ' . $cls . '">' . $lbl . '</span>';
}
?>

<?= $this->endSection() ?>