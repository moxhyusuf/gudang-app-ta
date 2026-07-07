<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
function statusLabelP($s) {
    $map = [
        'pending'    => '<span class="badge-gt badge-pending">Pending</span>',
        'selesai'    => '<span class="badge-gt badge-selesai">Selesai</span>',
        'batal'      => '<span class="badge-gt badge-ditolak">Batal</span>',
        'kadaluarsa' => '<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>',
    ];
    return isset($map[$s]) ? $map[$s] : '<span class="badge-gt badge-umum">'.esc($s).'</span>';
}
function kondisiLabelP($k) {
    $map = [
        'habis'  => '<span class="badge-gt badge-habis">Habis</span>',
        'kritis' => '<span class="badge-gt badge-kritis">Kritis</span>',
        'normal' => '<span class="badge-gt badge-normal">Normal</span>',
    ];
    return isset($map[$k]) ? $map[$k] : '<span class="badge-gt badge-normal">Normal</span>';
}
?>

<div class="page-hd">
  <div class="page-hd-left">
    <h1>Dashboard</h1>
    <p>Selamat datang, <strong><?= esc($nama) ?></strong>! Cek status material dan booking Anda.</p>
  </div>
  <div class="date-chip">📅 <?= date('l, d F Y') ?></div>
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
    <div class="stat-icon red">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <div>
      <div class="stat-label">Stok Kritis</div>
      <div class="stat-value" style="color:var(--red)"><?= $total_stok_kritis ?></div>
      <div class="stat-sub">Perlu perhatian</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
      </svg>
    </div>
    <div>
      <div class="stat-label">Booking Aktif Saya</div>
      <div class="stat-value" style="color:var(--amber)"><?= $booking_aktif ?></div>
      <div class="stat-sub">Pending / disetujui</div>
    </div>
  </div>
</div>

<!-- 2-COL GRID -->
<div class="dash-grid">
  <!-- Booking Aktif -->
  <div class="card-g card-sasa">
    <div class="sh-g">
      <h2 class="card-title" style="border:none;margin:0;padding:0;color:rgba(192,57,43,.75);">📋 Booking Aktif Saya</h2>
      <a href="/booking" class="btn-g btn-out-g btn-sm-g">Buat Booking →</a>
    </div>
    <?php if (empty($booking_aktif_detail)): ?>
      <div class="empty-state">Tidak ada booking aktif saat ini</div>
    <?php else: ?>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead><tr><th>No. Booking</th><th>Item</th><th>Tgl. Butuh</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($booking_aktif_detail as $b): ?>
          <tr>
            <td><code class="mono" style="font-size:.75rem"><?= esc($b['no_booking']) ?></code></td>
            <td><?= $b['jml_item'] ?></td>
            <td style="white-space:nowrap"><?= esc($b['tanggal_butuh'] ? $b['tanggal_butuh'] : '—') ?></td>
            <td><?= statusLabelP($b['status']) ?></td>
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
      <h2 class="card-title" style="border:none;margin:0;padding:0;color:rgba(192,57,43,.75);">⚠️ Material Stok Kritis</h2>
      <a href="/monitoring" class="btn-g btn-out-g btn-sm-g">Lihat Semua →</a>
    </div>
    <?php if (empty($stok_kritis)): ?>
      <div class="empty-state">✅ Semua stok dalam kondisi normal</div>
    <?php else: ?>
    <div class="tbl-wrap">
      <table class="tbl-g">
        <thead><tr><th>Nama Material</th><th>Kondisi</th><th>Stok</th></tr></thead>
        <tbody>
          <?php foreach ($stok_kritis as $m): ?>
          <tr>
            <td><?= esc($m['nama_material']) ?></td>
            <td><?= kondisiLabelP($m['kondisi_stok']) ?></td>
            <td><strong><?= number_format($m['stok']) ?></strong> <?= esc($m['satuan']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- RIWAYAT BOOKING -->
<div class="card-g mt-3">
  <div class="sh-g">
    <h2 class="card-title" style="border:none;margin:0;padding:0;color:rgba(192,57,43,.75);">🕐 Riwayat Booking Terakhir</h2>
    <a href="/booking?tab=riwayat" class="btn-g btn-out-g btn-sm-g">Lihat Semua →</a>
  </div>
  <?php if (empty($riwayat_booking)): ?>
    <div class="empty-state">Belum ada riwayat booking</div>
  <?php else: ?>
  <div class="tbl-wrap">
    <table class="tbl-g">
      <thead><tr><th>No. Booking</th><th>Item</th><th>Tgl. Booking</th><th>Tgl. Butuh</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($riwayat_booking as $b): ?>
        <tr>
          <td><code class="mono" style="font-size:.75rem"><?= esc($b['no_booking']) ?></code></td>
          <td><?= $b['jml_item'] ?></td>
          <td style="white-space:nowrap;color:#6b7280;font-size:.78rem">
            <?= esc(isset($b['tanggal_booking']) ? $b['tanggal_booking'] : $b['created_at']) ?>
          </td>
          <td style="white-space:nowrap"><?= esc($b['tanggal_butuh'] ? $b['tanggal_butuh'] : '—') ?></td>
          <td><?= statusLabelP($b['status']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<style>
.date-chip{font-size:12px;color:var(--text3);background:white;padding:6px 14px;border-radius:20px;border:1px solid var(--border);white-space:nowrap}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.8rem}
.stat-card{background:#fff;border-radius:12px;border:1px solid var(--border);padding:1.2rem;display:flex;align-items:flex-start;gap:.8rem}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{width:20px;height:20px}
.stat-icon.navy{background:rgba(15,32,68,.08);color:var(--navy)}
.stat-icon.red{background:rgba(192,40,45,.10);color:var(--red)}
.stat-icon.amber{background:rgba(180,83,9,.10);color:var(--amber)}
.stat-label{font-size:.75rem;color:#c0392b;font-weight:700;letter-spacing:.03em;text-transform:uppercase}
.stat-value{font-size:1.8rem;font-weight:800;color:var(--navy);line-height:1;margin-top:2px}
.stat-sub{font-size:.75rem;color:#9ca3af;margin-top:3px}
.sh-g{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.2rem;border-bottom:1px solid rgba(206,38,38,.15);background:rgba(206,38,38,.18);border-radius:12px 12px 0 0;margin:-20px -20px 16px -20px}
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
@media(max-width:768px){.dash-grid{grid-template-columns:1fr}.stat-grid{grid-template-columns:1fr 1fr}}
.badge-gt{display:inline-flex;align-items:center;gap:4px;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700}
.badge-habis{background:#fee2e2;color:#991b1b}
.badge-kritis{background:#fef2f2;color:#c0282d}
.badge-normal{background:#d1fae5;color:#1a7f4b}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-disetujui{background:#d1fae5;color:#065f46}
.badge-selesai{background:#e0f2fe;color:#0c4a6e}
.badge-ditolak{background:#fee2e2;color:#991b1b}
.badge-kadaluarsa{background:#f3f4f6;color:#6b7280}
.badge-umum{background:#f3f4f6;color:#4b5563}
.empty-state{text-align:center;padding:24px;color:var(--text3);font-size:13px}
.mt-3{margin-top:1rem}
</style>

<?= $this->endSection() ?>