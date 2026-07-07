<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
function statusLabel($s) {
    $map = [
        'pending'    => '<span class="badge-gt badge-pending">Pending</span>',
        'selesai'    => '<span class="badge-gt badge-selesai">Selesai</span>',
        'batal'      => '<span class="badge-gt badge-ditolak">Batal</span>',
        'kadaluarsa' => '<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>',
        'masuk'      => '<span class="badge-gt badge-normal">Masuk</span>',
        'keluar'     => '<span class="badge-gt badge-ditolak">Keluar</span>',
        'booking'    => '<span class="badge-gt badge-pending">Booking</span>',
    ];
    return $map[$s] ?? '<span class="badge-gt badge-umum">'.esc($s).'</span>';
}
?>

<!-- Page Header -->
<div class="page-hd">
  <div class="page-hd-left">
    <div class="breadcrumb-gt">GT-SIS · <span>Dashboard</span></div>
    <h1>Dashboard</h1>
    <p>Selamat datang, <strong><?= esc($nama) ?></strong>! Pantau aktivitas gudang teknik hari ini.</p>
  </div>
  <div class="date-chip">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <?= date('l, d F Y') ?>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stat-grid mb-4">

  <div class="stat-card">
    <div class="stat-icon navy">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
      </svg>
    </div>
    <div>
      <div class="stat-label">Total Material</div>
      <div class="stat-value"><?= number_format($total_material) ?></div>
      <div class="stat-sub">Material aktif di gudang</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="8 17 12 21 16 17"/>
        <line x1="12" y1="12" x2="12" y2="21"/>
        <path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/>
      </svg>
    </div>
    <div>
      <div class="stat-label">Penerimaan Bulan Ini</div>
      <div class="stat-value"><?= number_format($penerimaan_bulan) ?></div>
      <div class="stat-sub">Transaksi masuk bulan ini</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 6v6l4 2"/>
      </svg>
    </div>
    <div>
      <div class="stat-label">Booking Pending</div>
      <div class="stat-value" style="color:var(--amber)"><?= number_format($booking_pending) ?></div>
      <div class="stat-sub">Menunggu verifikasi</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon red">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <div>
      <div class="stat-label">Stok Kritis</div>
      <div class="stat-value" style="color:var(--red)"><?= number_format($total_stok_kritis) ?></div>
      <div class="stat-sub">Perlu perhatian</div>
    </div>
  </div>

</div>

<!-- 2-COL ROW: Booking Pending + Stok Kritis -->
<div class="dash-grid">

  <!-- Booking Pending -->
  <div class="card-g card-sasa">
    <div class="sh-g">
      <h2 class="card-title-g">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color:#b45309"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/><line x1="9" y1="15" x2="12" y2="15"/></svg>
        Booking Pending
      </h2>
      <a href="/verifikasi-booking" class="btn-g btn-out-g btn-sm-g">Lihat Semua →</a>
    </div>
    <?php if (empty($booking_list)): ?>
      <div class="empty-state">Tidak ada booking pending saat ini</div>
    <?php else: ?>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead>
          <tr>
            <th>No. Booking</th>
            <th>Plant</th>
            <th>Tgl. Booking</th>
            <th>Item</th>
            <th>Masa Aktif</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($booking_list as $b):
              $sisa = isset($b['sisa_hari']) ? (int)$b['sisa_hari'] : null;
              if ($sisa === null) {
                  $sisaHtml = '<span style="color:#9ca3af">—</span>';
              } elseif ($sisa <= 0) {
                  $sisaHtml = '<span style="background:#fee2e2;color:#dc2626;font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:20px">⚠️ Hari ini!</span>';
              } elseif ($sisa === 1) {
                  $sisaHtml = '<span style="background:#fef3c7;color:#b45309;font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:20px">⏰ Besok!</span>';
              } else {
                  $sisaHtml = '<span style="font-size:.78rem;color:#1d4ed8;font-weight:600">' . $sisa . ' hari</span>';
              }
          ?>
          <tr>
            <td><code class="mono"><?= esc($b['no_booking']) ?></code></td>
            <td><?= esc($b['nama_plant'] ?? '—') ?></td>
            <td style="white-space:nowrap"><?= esc($b['tanggal_booking']) ?></td>
            <td><?= (int)($b['jml_item'] ?? 0) ?></td>
            <td><?= $sisaHtml ?></td>
            <td><?= statusLabel('pending') ?></td>
            <td><a href="/verifikasi-booking" class="btn-aksi">Verifikasi</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Stok Kritis -->
  <div class="card-g card-sasa">
    <div class="sh-g">
      <h2 class="card-title-g">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color:#c0282d"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Material Stok Kritis
      </h2>
      <a href="/monitoring" class="btn-g btn-out-g btn-sm-g">Lihat Semua →</a>
    </div>
    <?php if (empty($stok_kritis)): ?>
      <div class="empty-state">✅ Semua stok dalam kondisi normal</div>
    <?php else: ?>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead>
          <tr><th>Material</th><th>Kode SAP</th><th>Stok</th><th>Min</th></tr>
        </thead>
        <tbody>
          <?php foreach ($stok_kritis as $m): ?>
          <tr>
            <td><?= esc($m['nama_material']) ?></td>
            <td><code class="mono"><?= esc($m['kode_sap']) ?></code></td>
            <td style="color:#c0282d;font-weight:600"><?= number_format($m['stok']) ?> <?= esc($m['satuan']) ?></td>
            <td style="color:var(--ink3)"><?= $m['safety_stock'] !== null ? number_format($m['safety_stock']) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- AKTIVITAS TERBARU -->
<div class="card-g mt-3">
  <div class="sh-g">
    <h2 class="card-title-g">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Aktivitas Terbaru
    </h2>
  </div>
  <?php if (empty($aktivitas)): ?>
    <div class="empty-state">Belum ada aktivitas</div>
  <?php else: ?>
  <div class="tbl-wrap">
    <table class="tbl-g">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Jenis</th>
          <th>Material</th>
          <th>Jumlah</th>
          <th>Stok Sesudah</th>
          <th>Petugas</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aktivitas as $a): ?>
        <tr>
          <td style="white-space:nowrap;color:var(--ink3);font-size:.78rem"><?= esc(date('d/m/y H:i', strtotime($a['created_at']))) ?></td>
          <td><?= statusLabel($a['jenis']) ?></td>
          <td><?= esc($a['nama_material'] ?? '—') ?></td>
          <td><?= number_format($a['jumlah']) ?> <?= esc($a['satuan'] ?? '') ?></td>
          <td style="font-weight:600"><?= number_format($a['stok_sesudah']) ?></td>
          <td><?= esc($a['petugas'] ?? '—') ?></td>
          <td style="font-size:.78rem;color:var(--ink3);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($a['keterangan'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<style>
/* ── Page header ─────────────────────────────────────────── */
.page-hd{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:1.25rem}
.page-hd-left h1{font-size:1.4rem;font-weight:700;color:var(--navy);margin:0 0 4px}
.page-hd-left p{font-size:.83rem;color:var(--ink3);margin:0}
.breadcrumb-gt{font-size:.72rem;color:var(--ink3);margin-bottom:4px}
.breadcrumb-gt span{color:var(--navy);font-weight:600}
.date-chip{font-size:.72rem;color:var(--ink3);background:#fff;padding:6px 14px;border-radius:20px;border:1px solid var(--border);white-space:nowrap;display:flex;align-items:center;gap:6px}

/* ── Stat grid ───────────────────────────────────────────── */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:.8rem}
.mb-4{margin-bottom:1.25rem}
.stat-card{background:#fff;border-radius:12px;border:1px solid var(--border);padding:1.1rem 1.2rem;display:flex;align-items:flex-start;gap:.9rem}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{width:20px;height:20px}
.stat-icon.navy{background:rgba(15,32,68,.08);color:var(--navy)}
.stat-icon.green{background:rgba(22,115,62,.08);color:#16733e}
.stat-icon.amber{background:rgba(180,83,9,.10);color:var(--amber)}
.stat-icon.red{background:rgba(192,40,45,.10);color:var(--red)}
.stat-label{font-size:.72rem;font-weight:700;color:#c0392b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px}
.stat-value{font-size:1.75rem;font-weight:800;color:var(--navy);line-height:1.1}
.stat-sub{font-size:.72rem;color:#9ca3af;margin-top:3px}

/* ── Dash 2-col grid ─────────────────────────────────────── */
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.mt-3{margin-top:1rem}

/* ── Cards ───────────────────────────────────────────────── */
.card-g{background:#fff;border-radius:12px;border:1px solid var(--border);overflow:hidden}
.sh-g{display:flex;align-items:center;justify-content:space-between;padding:.8rem 1rem;border-bottom:1px solid rgba(206,38,38,.15);background:rgba(206,38,38,.18);border-radius:12px 12px 0 0;margin:-20px -20px 16px -20px;padding:1rem 1.2rem}
.card-title-g{font-size:.85rem;font-weight:700;color:rgba(192,57,43,.75);margin:0;display:flex;align-items:center;gap:6px}
.btn-g{display:inline-flex;align-items:center;gap:4px;border-radius:8px;font-size:.75rem;font-weight:600;cursor:pointer;text-decoration:none;transition:background .15s}
.btn-out-g{background:#fff;border:1.5px solid var(--border);color:var(--navy)}
.btn-out-g:hover{background:var(--bg)}
.btn-sm-g{padding:.3rem .75rem}

/* ── Tables ──────────────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
.tbl-g{width:100%;border-collapse:collapse;font-size:.8rem}
.tbl-g thead tr{background:#f8f9fb}
.tbl-g th{padding:.55rem .9rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--ink3);text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;border-bottom:1px solid var(--border)}
.tbl-g td{padding:.6rem .9rem;color:var(--navy2);border-bottom:1px solid #f1f3f7;vertical-align:middle}
.tbl-g tbody tr:last-child td{border-bottom:none}
.tbl-g tbody tr:hover td{background:#fafbfd}

/* ── Misc ────────────────────────────────────────────────── */
.mono{font-family:monospace;font-size:.75rem;color:var(--navy3);background:#f1f3f7;padding:2px 6px;border-radius:4px}
.btn-aksi{font-size:.72rem;font-weight:600;padding:.25rem .65rem;border-radius:6px;background:#e8f0fe;color:#1a56d6;text-decoration:none;white-space:nowrap}
.btn-aksi:hover{background:#d1e3fd}
.empty-state{text-align:center;padding:2rem;color:#9ca3af;font-size:.83rem}

/* ── Badges ──────────────────────────────────────────────── */
.badge-gt{display:inline-flex;align-items:center;padding:.18rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700;white-space:nowrap}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-normal{background:#d1fae5;color:#1a7f4b}
.badge-selesai{background:#e0f2fe;color:#0c4a6e}
.badge-ditolak{background:#fee2e2;color:#991b1b}
.badge-kadaluarsa{background:#f3f4f6;color:#6b7280}
.badge-umum{background:#f3f4f6;color:#4b5563}

/* ── Responsive ──────────────────────────────────────────── */
@media(max-width:768px){
  .dash-grid{grid-template-columns:1fr}
  .stat-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:480px){
  .stat-grid{grid-template-columns:1fr}
}
</style>

<?= $this->endSection() ?>