<?php $this->extend('layout/main'); ?>
<?php $this->section('content'); ?>

<meta name="csrf-token" content="<?= csrf_hash() ?>">

<style>
.profil-wrap {
  max-width: 1100px;
  margin: 0 auto;
}
.profil-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 20px;
  align-items: start;
}
@media(max-width:768px){
  .profil-grid { grid-template-columns: 1fr; }
}

.profil-info-card, .profil-edit-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.profil-card-hd {
  background: var(--navy);
  padding: 14px 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.profil-card-hd span {
  color: white;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.1px;
}
.profil-card-body { padding: 28px 24px; }

/* ── Avatar area ── */
.profil-avatar-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
}
.profil-avatar-ring {
  position: relative;
  width: 90px;
  height: 90px;
  cursor: pointer;
}
.profil-avatar-ring:hover .avatar-overlay { opacity: 1; }

.profil-avatar-img {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--border);
  display: block;
}
.profil-avatar-initials {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30px;
  font-weight: 800;
  color: white;
  letter-spacing: -1px;
  border: 3px solid transparent;
}
.av-admin   { background: var(--navy); }
.av-petugas { background: #1a7f4b; }

.avatar-overlay {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(0,0,0,.45);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3px;
  opacity: 0;
  transition: opacity .2s;
}
.avatar-overlay svg { width: 22px; height: 22px; color: white; }
.avatar-overlay span {
  font-size: 10px;
  color: rgba(255,255,255,.9);
  font-weight: 600;
  letter-spacing: 0.2px;
}

.btn-hapus-foto {
  font-size: 11px;
  color: var(--clay);
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font);
  font-weight: 600;
  padding: 0;
  text-decoration: underline;
}
.btn-hapus-foto:hover { color: var(--clay2); }

.profil-avatar-name {
  font-size: 18px;
  font-weight: 800;
  color: var(--navy);
  letter-spacing: -0.4px;
  text-align: center;
}
.profil-avatar-username {
  font-size: 12px;
  color: var(--ink3);
  font-weight: 500;
  margin-top: -6px;
}

.profil-info-rows { display: flex; flex-direction: column; gap: 0; }
.profil-info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid var(--bg2);
  font-size: 13px;
}
.profil-info-row:last-child { border-bottom: none; }
.profil-info-label { color: var(--ink3); font-weight: 500; }
.profil-info-val { color: var(--navy); font-weight: 600; text-align: right; }
.profil-info-val.aktif { color: #1a7f4b; }

.profil-right { display: flex; flex-direction: column; gap: 20px; }

/* Toast */
.gtsis-toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  background: var(--navy);
  color: white;
  padding: 12px 20px;
  border-radius: var(--r-lg);
  font-size: 13px;
  font-weight: 600;
  box-shadow: 0 8px 24px rgba(0,0,0,.2);
  z-index: 9999;
  opacity: 0;
  transform: translateY(12px);
  transition: all .25s;
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 220px;
}
.gtsis-toast.show { opacity: 1; transform: translateY(0); }
.gtsis-toast.toast-ok  { background: #1a7f4b; }
.gtsis-toast.toast-err { background: var(--clay); }
.toast-ico { font-size: 16px; }
</style>

<?php
  $role     = session()->get('role');
  $avClass  = $role === 'admin_gt' ? 'av-admin' : 'av-petugas';
  $words    = explode(' ', trim($user['nama']));
  $initials = strtoupper(implode('', array_map(fn($w) => $w[0], $words)));
  $initials = substr($initials, 0, 2);
  $hasFoto  = !empty($user['foto']) && file_exists(FCPATH . 'uploads/foto_profil/' . $user['foto']);
?>

<input type="file" id="inp-foto" accept="image/jpeg,image/png,image/webp" style="display:none">

<div class="profil-wrap">
  <div class="page-hd">
    <div class="page-hd-left">
      <h1>Profil &amp; Pengaturan Akun</h1>
      <p>Kelola informasi pribadi dan keamanan akun Anda</p>
    </div>
  </div>

  <div class="profil-grid">

    <!-- LEFT: Informasi Akun -->
    <div class="profil-info-card">
      <div class="profil-card-hd">
        <span>👤 Informasi Akun</span>
      </div>
      <div class="profil-card-body">
        <div class="profil-avatar-wrap">

          <div class="profil-avatar-ring" id="avatar-ring" onclick="document.getElementById('inp-foto').click()" title="Ganti foto profil">
            <?php if ($hasFoto): ?>
            <img src="/uploads/foto_profil/<?= esc($user['foto']) ?>" class="profil-avatar-img" id="profil-foto-img" alt="Foto profil">
            <?php else: ?>
            <div class="profil-avatar-initials <?= $avClass ?>" id="profil-avatar-initials"><?= esc($initials) ?></div>
            <?php endif; ?>

            <div class="avatar-overlay">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span>Ganti</span>
            </div>
          </div>

          <button class="btn-hapus-foto" id="btn-hapus-foto" style="<?= $hasFoto ? '' : 'display:none' ?>">
            Hapus foto
          </button>

          <div class="profil-avatar-name" id="profil-nama-display"><?= esc($user['nama']) ?></div>
          <div class="profil-avatar-username"><?= esc($user['username']) ?></div>
        </div>

        <div class="profil-info-rows">
          <div class="profil-info-row">
            <span class="profil-info-label">Role</span>
            <span class="profil-info-val"><?= ucwords(str_replace('_', ' ', esc($user['role']))) ?></span>
          </div>
          <div class="profil-info-row">
            <span class="profil-info-label">Status</span>
            <span class="profil-info-val aktif">Aktif</span>
          </div>
          <div class="profil-info-row">
            <span class="profil-info-label">Last Login</span>
            <span class="profil-info-val"><?= date('j/n/Y, H.i.s') ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Edit Forms -->
    <div class="profil-right">

      <div class="profil-edit-card">
        <div class="profil-card-hd"><span>✏️ Edit Nama</span></div>
        <div class="profil-card-body">
          <div class="mb-3">
            <label class="form-lbl">Nama Lengkap</label>
            <input type="text" class="form-inp" id="inp-nama" value="<?= esc($user['nama']) ?>" placeholder="Nama lengkap Anda">
          </div>
          <button class="btn-g btn-navy-g w-100" id="btn-simpan-nama">Simpan Nama</button>
        </div>
      </div>

      <div class="profil-edit-card">
        <div class="profil-card-hd"><span>🔒 Ganti Password</span></div>
        <div class="profil-card-body">
          <div class="mb-3">
            <label class="form-lbl">Password Lama <span style="color:var(--clay)">*</span></label>
            <input type="password" class="form-inp" id="inp-pw-lama" placeholder="••••••••">
          </div>
          <div class="mb-3">
            <label class="form-lbl">Password Baru <span style="color:var(--clay)">*</span></label>
            <input type="password" class="form-inp" id="inp-pw-baru" placeholder="••••••••">
          </div>
          <div class="mb-3">
            <label class="form-lbl">Konfirmasi <span style="color:var(--clay)">*</span></label>
            <input type="password" class="form-inp" id="inp-pw-konfirm" placeholder="••••••••">
          </div>
          <button class="btn-g btn-danger-g w-100" id="btn-ganti-pw">Ganti Password</button>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="gtsis-toast" id="gtsis-toast">
  <span class="toast-ico" id="toast-ico">✓</span>
  <span id="toast-msg"></span>
</div>

<script>
UnsavedGuard.watch('.profil-right', 'Ada perubahan nama/password yang belum disimpan. Yakin ingin pindah halaman?');

/* ── CSRF helper ──────────────────────────────────────────── */
function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

/* ── Toast ────────────────────────────────────────────────── */
function showToast(msg, type = 'ok') {
  const el = document.getElementById('gtsis-toast');
  el.classList.remove('toast-ok', 'toast-err', 'show');
  document.getElementById('toast-ico').textContent = type === 'ok' ? '✓' : '✕';
  document.getElementById('toast-msg').textContent = msg;
  el.classList.add(type === 'ok' ? 'toast-ok' : 'toast-err');
  setTimeout(() => el.classList.add('show'), 10);
  setTimeout(() => el.classList.remove('show'), 3200);
}

/* ── Helper: set avatar ke foto ────────────────────────────── */
function setAvatarFoto(url) {
  const ring = document.getElementById('avatar-ring');
  const old  = ring.querySelector('.profil-avatar-img, .profil-avatar-initials');
  if (old) old.remove();
  const img  = document.createElement('img');
  img.src    = url;
  img.className = 'profil-avatar-img';
  img.id     = 'profil-foto-img';
  img.alt    = 'Foto profil';
  ring.insertBefore(img, ring.querySelector('.avatar-overlay'));
  document.getElementById('btn-hapus-foto').style.display = '';
}

/* ── Helper: reset avatar ke inisial ──────────────────────── */
function setAvatarInitials(initials, avClass) {
  const ring = document.getElementById('avatar-ring');
  const old  = ring.querySelector('.profil-avatar-img, .profil-avatar-initials');
  if (old) old.remove();
  const div  = document.createElement('div');
  div.className = 'profil-avatar-initials ' + avClass;
  div.id     = 'profil-avatar-initials';
  div.textContent = initials;
  ring.insertBefore(div, ring.querySelector('.avatar-overlay'));
  document.getElementById('btn-hapus-foto').style.display = 'none';
}

/* ── Upload foto ──────────────────────────────────────────── */
document.getElementById('inp-foto').addEventListener('change', async function() {
  const file = this.files[0];
  if (!file) return;

  const allowed = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowed.includes(file.type)) {
    showToast('Format file harus JPG, PNG, atau WebP.', 'err');
    this.value = ''; return;
  }
  if (file.size > 2 * 1024 * 1024) {
    showToast('Ukuran file maksimal 2MB.', 'err');
    this.value = ''; return;
  }

  // Preview instan
  const reader = new FileReader();
  reader.onload = e => setAvatarFoto(e.target.result);
  reader.readAsDataURL(file);

  const fd = new FormData();
  fd.append('foto', file);
  try {
    const res  = await fetch('/profil/update-foto', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf() },
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      setAvatarFoto(data.foto_url);
      showToast(data.message, 'ok');
    } else {
      showToast(data.message, 'err');
    }
  } catch(e) {
    showToast('Gagal mengupload foto.', 'err');
  }
  this.value = '';
});

/* ── Hapus foto ───────────────────────────────────────────── */
document.getElementById('btn-hapus-foto').addEventListener('click', async function() {
  if (!confirm('Hapus foto profil?')) return;
  try {
    const res  = await fetch('/profil/hapus-foto', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf() }
    });
    const data = await res.json();
    if (data.success) {
      const nama     = document.getElementById('profil-nama-display').textContent.trim();
      const initials = nama.split(/\s+/).map(w => w[0].toUpperCase()).join('').slice(0, 2);
      setAvatarInitials(initials, '<?= $avClass ?>');
      showToast(data.message, 'ok');
    } else {
      showToast(data.message, 'err');
    }
  } catch(e) {
    showToast('Gagal menghapus foto.', 'err');
  }
});

/* ── Simpan Nama ──────────────────────────────────────────── */
document.getElementById('btn-simpan-nama').addEventListener('click', async function() {
  const btn  = this;
  const nama = document.getElementById('inp-nama').value.trim();
  if (!nama) { showToast('Nama tidak boleh kosong.', 'err'); return; }

  btn.disabled = true; btn.textContent = 'Menyimpan…';
  try {
    const fd = new FormData();
    fd.append('nama', nama);
    const res  = await fetch('/profil/update-nama', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf() },
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('profil-nama-display').textContent = data.nama;
      if (!document.getElementById('profil-foto-img')) {
        const initials = data.nama.trim().split(/\s+/).map(w => w[0].toUpperCase()).join('').slice(0, 2);
        const av = document.getElementById('profil-avatar-initials');
        if (av) av.textContent = initials;
      }
      showToast(data.message, 'ok');
      UnsavedGuard.markClean();
    } else {
      showToast(data.message, 'err');
    }
  } catch(e) {
    showToast('Terjadi kesalahan. Coba lagi.', 'err');
  } finally {
    btn.disabled = false; btn.textContent = 'Simpan Nama';
  }
});

/* ── Ganti Password ───────────────────────────────────────── */
document.getElementById('btn-ganti-pw').addEventListener('click', async function() {
  const btn     = this;
  const pwLama  = document.getElementById('inp-pw-lama').value;
  const pwBaru  = document.getElementById('inp-pw-baru').value;
  const konfirm = document.getElementById('inp-pw-konfirm').value;

  if (!pwLama || !pwBaru || !konfirm) { showToast('Semua field wajib diisi.', 'err'); return; }
  if (pwBaru !== konfirm) { showToast('Konfirmasi password tidak cocok.', 'err'); return; }
  if (pwBaru.length < 6)  { showToast('Password baru minimal 6 karakter.', 'err'); return; }

  btn.disabled = true; btn.textContent = 'Menyimpan…';
  try {
    const fd = new FormData();
    fd.append('password_lama', pwLama);
    fd.append('password_baru', pwBaru);
    fd.append('konfirmasi',    konfirm);
    const res  = await fetch('/profil/update-password', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf() },
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('inp-pw-lama').value    = '';
      document.getElementById('inp-pw-baru').value    = '';
      document.getElementById('inp-pw-konfirm').value = '';
      showToast(data.message, 'ok');
      UnsavedGuard.markClean();
    } else {
      showToast(data.message, 'err');
    }
  } catch(e) {
    showToast('Terjadi kesalahan. Coba lagi.', 'err');
  } finally {
    btn.disabled = false; btn.textContent = 'Ganti Password';
  }
});
</script>

<?php $this->endSection(); ?>