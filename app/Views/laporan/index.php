<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php
$dari     = $filter['dari']     ?? date('Y-m-01');
$sampai   = $filter['sampai']   ?? date('Y-m-d');
$plant_id = $filter['plant_id'] ?? '';
$kategori = $filter['kategori'] ?? '';
?>

<!-- ── Filter Bar ─────────────────────────────────────────────────────────── -->
<div class="lap-filter-card">
  <form id="filter-form" method="GET" action="/laporan">
    <div class="lap-filter-row">

      <div class="lap-filter-item">
        <label class="lap-label">Dari Tanggal</label>
        <input type="date" name="dari" id="f-dari" class="lap-input" value="<?= esc($dari) ?>">
      </div>

      <div class="lap-filter-item">
        <label class="lap-label">Sampai</label>
        <input type="date" name="sampai" id="f-sampai" class="lap-input" value="<?= esc($sampai) ?>">
      </div>

      <div class="lap-filter-item">
        <label class="lap-label">Plant</label>
        <select name="plant_id" id="f-plant" class="lap-input">
          <option value="">Semua Plant</option>
          <?php foreach ($plants as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $plant_id == $p['id'] ? 'selected' : '' ?>>
              <?= esc($p['nama_plant']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="lap-filter-item">
        <label class="lap-label">Kategori</label>
        <select name="kategori" id="f-kategori" class="lap-input">
          <option value="">Semua</option>
          <option value="penerimaan"  <?= $kategori === 'penerimaan'  ? 'selected' : '' ?>>Penerimaan</option>
          <option value="pengeluaran" <?= $kategori === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
          <option value="booking"     <?= $kategori === 'booking'     ? 'selected' : '' ?>>Booking</option>
          <option value="stok"        <?= $kategori === 'stok'        ? 'selected' : '' ?>>Stok &amp; Mutasi</option>
        </select>
      </div>

      <div class="lap-filter-actions">
        <button type="submit" class="lap-btn lap-btn-navy">🔍 Filter</button>
        <button type="button" class="lap-btn lap-btn-green" onclick="exportExcel()">↓ Excel</button>
        <button type="button" class="lap-btn lap-btn-red" onclick="exportPDF()">↓ PDF</button>
      </div>

    </div>
  </form>
</div>

<!-- ── Tab Navigation ─────────────────────────────────────────────────────── -->
<div class="lap-tabs">
  <button class="lap-tab active" onclick="switchTab('penerimaan', this)">Penerimaan</button>
  <button class="lap-tab" onclick="switchTab('pengeluaran', this)">Pengeluaran</button>
  <button class="lap-tab" onclick="switchTab('booking', this)">Booking</button>
  <button class="lap-tab" onclick="switchTab('stok', this)">Stok &amp; Mutasi</button>
</div>

<!-- ═══════════════════════════════════════════
     TAB: PENERIMAAN
═══════════════════════════════════════════════ -->
<div id="tab-penerimaan" class="lap-panel">

  <div class="lap-stats-row">
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Transaksi</div>
      <div class="lap-stat-val"><?= number_format($penerimaan_total) ?></div>
      <div class="lap-stat-sub">penerimaan periode ini</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Item Diterima</div>
      <div class="lap-stat-val"><?= number_format($penerimaan_unit) ?></div>
      <div class="lap-stat-sub">unit berbagai material</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Material Baru</div>
      <div class="lap-stat-val"><?= number_format($penerimaan_matbaru) ?></div>
      <div class="lap-stat-sub">kode material baru dibuat</div>
    </div>
  </div>

  <div class="lap-tbl-card">
    <div class="lap-tbl-wrap">
      <table class="lap-tbl">
        <thead><tr>
          <th>No. Surat</th><th>Tanggal</th><th>Vendor</th>
          <th>Material</th><th>Total Unit</th><th>Petugas</th>
        </tr></thead>
        <tbody id="tbody-penerimaan">
          <?php if (empty($penerimaan_list)): ?>
            <tr><td colspan="6" class="lap-empty">Tidak ada data pada periode ini</td></tr>
          <?php else: ?>
            <?php foreach ($penerimaan_list as $row): ?>
              <tr>
                <td><code class="lap-code"><?= esc($row['no_surat_penerimaan']) ?></code></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td><?= esc($row['vendor'] ?? '—') ?></td>
                <td><?php
                  $items = !empty($row['detail_item']) ? explode('||', $row['detail_item']) : [];
                  if (empty($items)) { echo '<span class="lap-muted">—</span>'; }
                  else { echo '<ul class="lap-item-list">'; foreach ($items as $i) { echo '<li>' . esc($i) . '</li>'; } echo '</ul>'; }
                ?></td>
                <td><strong><?= number_format($row['total_unit']) ?> unit</strong></td>
                <td><?= esc($row['petugas'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════
     TAB: PENGELUARAN
═══════════════════════════════════════════════ -->
<div id="tab-pengeluaran" class="lap-panel" style="display:none">

  <div class="lap-stats-row">
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Transaksi</div>
      <div class="lap-stat-val"><?= number_format($pengeluaran_total) ?></div>
      <div class="lap-stat-sub">bon pengeluaran periode ini</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Unit Keluar</div>
      <div class="lap-stat-val"><?= number_format($pengeluaran_unit) ?></div>
      <div class="lap-stat-sub">unit material dikeluarkan</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Jumlah Plant</div>
      <div class="lap-stat-val"><?= number_format($pengeluaran_plant) ?></div>
      <div class="lap-stat-sub">plant yang mengambil material</div>
    </div>
  </div>

  <div class="lap-tbl-card">
    <div class="lap-tbl-wrap">
      <table class="lap-tbl">
        <thead><tr>
          <th>No. Bon</th><th>Tanggal</th><th>Plant</th>
          <th>Material</th><th>Total Unit</th><th>Keperluan</th><th>Petugas</th>
        </tr></thead>
        <tbody id="tbody-pengeluaran">
          <?php if (empty($pengeluaran_list)): ?>
            <tr><td colspan="7" class="lap-empty">Tidak ada data pada periode ini</td></tr>
          <?php else: ?>
            <?php foreach ($pengeluaran_list as $row): ?>
              <tr>
                <td><code class="lap-code"><?= esc($row['no_bon']) ?></code></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td><?= esc($row['plant'] ?? '—') ?></td>
                <td><?php
                  $items = !empty($row['detail_item']) ? explode('||', $row['detail_item']) : [];
                  if (empty($items)) { echo '<span class="lap-muted">—</span>'; }
                  else { echo '<ul class="lap-item-list">'; foreach ($items as $i) { echo '<li>' . esc($i) . '</li>'; } echo '</ul>'; }
                ?></td>
                <td><strong><?= number_format($row['total_unit']) ?> unit</strong></td>
                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                    title="<?= esc($row['keperluan'] ?? '') ?>">
                  <?= esc($row['keperluan'] ?? '—') ?>
                </td>
                <td><?= esc($row['petugas'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════
     TAB: BOOKING
═══════════════════════════════════════════════ -->
<div id="tab-booking" class="lap-panel" style="display:none">

  <div class="lap-stats-row">
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Booking</div>
      <div class="lap-stat-val"><?= number_format($booking_total) ?></div>
      <div class="lap-stat-sub">permintaan booking</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Selesai</div>
      <div class="lap-stat-val"><?= number_format($booking_selesai) ?></div>
      <div class="lap-stat-sub">booking sudah diambil</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Pending / Proses</div>
      <div class="lap-stat-val"><?= number_format($booking_pending) ?></div>
      <div class="lap-stat-sub">masih menunggu proses</div>
    </div>
  </div>

  <div class="lap-tbl-card">
    <div class="lap-tbl-wrap">
      <table class="lap-tbl">
        <thead><tr>
          <th>No. Booking</th><th>Tanggal</th><th>Plant</th>
          <th>Material</th><th>Total Unit</th><th>Status</th><th>Pemohon</th><th>Catatan / Alasan</th>
        </tr></thead>
        <tbody id="tbody-booking">
          <?php if (empty($booking_list)): ?>
            <tr><td colspan="8" class="lap-empty">Tidak ada data pada periode ini</td></tr>
          <?php else: ?>
            <?php foreach ($booking_list as $row):
              $st = strtolower(trim($row['status'] ?? ''));

              // Alasan pembatalan tersimpan di catatan, format: "...| Batal: <alasan>"
              $catatanRaw = $row['catatan'] ?? '';
              $keterangan = '—';
              $isBatalFromCatatan = false;
              if (preg_match('/Batal\s*:\s*(.+)$/i', $catatanRaw, $m)) {
                  $keterangan = trim($m[1]);
                  $isBatalFromCatatan = true;
              } elseif ($catatanRaw !== '') {
                  $keterangan = $catatanRaw;
              }

              // Fallback: kalau kolom status tidak persis 'batal' tapi catatan menunjukkan pembatalan,
              // tetap anggap statusnya batal supaya badge & label konsisten dengan data sebenarnya.
              if ($st !== 'batal' && $isBatalFromCatatan) {
                  $st = 'batal';
              }

              $stMap = [
                'pending'    => ['label' => 'Pending',    'cls' => 'lap-badge-blue'],
                'disetujui'  => ['label' => 'Disetujui',  'cls' => 'lap-badge-yellow'],
                'selesai'    => ['label' => 'Selesai',    'cls' => 'lap-badge-green'],
                'batal'      => ['label' => 'Dibatalkan', 'cls' => 'lap-badge-red'],
                'kadaluarsa' => ['label' => 'Kadaluarsa', 'cls' => 'lap-badge-gray'],
              ];
              $stInfo = $stMap[$st] ?? ['label' => ($st !== '' ? $row['status'] : '—'), 'cls' => 'lap-badge-blue'];
            ?>
              <tr>
                <td><code class="lap-code"><?= esc($row['no_booking']) ?></code></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td><?= esc($row['plant'] ?? '—') ?></td>
                <td><?php
                  $items = !empty($row['detail_item']) ? explode('||', $row['detail_item']) : [];
                  if (empty($items)) { echo '<span class="lap-muted">—</span>'; }
                  else { echo '<ul class="lap-item-list">'; foreach ($items as $i) { echo '<li>' . esc($i) . '</li>'; } echo '</ul>'; }
                ?></td>
                <td><strong><?= number_format($row['total_unit']) ?> unit</strong></td>
                <td><span class="lap-badge <?= $stInfo['cls'] ?>"><?= esc($stInfo['label']) ?></span></td>
                <td><?= esc($row['pemohon'] ?? '—') ?></td>
                <td><?= $keterangan === '—' ? '<span class="lap-muted">—</span>' : esc($keterangan) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════
     TAB: STOK & MUTASI
═══════════════════════════════════════════════ -->
<div id="tab-stok" class="lap-panel" style="display:none">

  <div class="lap-stats-row">
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Material Aktif</div>
      <div class="lap-stat-val"><?= number_format($stok_total_material) ?></div>
      <div class="lap-stat-sub">jenis material tercatat</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Stok Tersedia</div>
      <div class="lap-stat-val"><?= number_format($stok_total_unit) ?></div>
      <div class="lap-stat-sub">unit di semua gudang</div>
    </div>
    <div class="lap-stat-card">
      <div class="lap-stat-label">Total Mutasi</div>
      <div class="lap-stat-val"><?= number_format($stok_mutasi) ?></div>
      <div class="lap-stat-sub">transaksi masuk &amp; keluar</div>
    </div>
  </div>

  <!-- ── Diagram 1: Material terlaris bulan ini ─────────────────────────────── -->
  <div class="lap-chart-card">
    <div class="lap-chart-head">
      <div>
        <div class="lap-chart-title">📤 Material Paling Banyak Keluar — <?= esc($bulan_ini_label) ?></div>
        <div class="lap-chart-sub">Top <?= count($stok_bulan_ini) ?> material berdasarkan total unit keluar bulan berjalan</div>
      </div>
    </div>
    <?php if (empty($stok_bulan_ini)): ?>
      <div class="lap-empty">Belum ada transaksi keluar bulan ini</div>
    <?php else: ?>
      <div class="lap-chart-wrap"><canvas id="chartBulanIni"></canvas></div>
    <?php endif; ?>
  </div>

  <!-- ── Diagram 2: Klasifikasi pergerakan material — slide rekap tahunan + 12 bulan ── -->
  <div class="lap-chart-card">
    <div class="lap-chart-head">
      <div>
        <div class="lap-chart-title">📊 Klasifikasi Pergerakan Material</div>
        <div class="lap-chart-sub" id="klsSlideSub">Rekap <?= $tahun_klasifikasi ? ('tahun ' . $tahun_klasifikasi) : '12 bulan terakhir' ?>, diurutkan dari total pemakaian tertinggi. Dipakai sebagai dasar usulan safety stock di bawah. Geser untuk lihat rincian per bulan.</div>
      </div>
      <div class="lap-legend">
        <span class="lap-legend-item"><i class="lap-dot-fast"></i>Fast Moving (<?= $count_fast ?>)</span>
        <span class="lap-legend-item"><i class="lap-dot-medium"></i>Medium Moving (<?= $count_medium ?>)</span>
        <span class="lap-legend-item"><i class="lap-dot-slow"></i>Slow Moving (<?= $count_slow ?>)</span>
      </div>
    </div>

    <?php if (empty($rekap_bergerak)): ?>
      <div class="lap-empty">Belum ada data pemakaian dalam 12 bulan terakhir</div>
    <?php else: ?>
      <div class="lap-kls-toolbar">
        <div class="lap-kls-nav">
          <button type="button" class="lap-btn-mini" id="klsPrev" onclick="klsGoto(_klsIndex-1)">‹ Sebelumnya</button>
          <select class="lap-kls-select" id="klsIndicator" onchange="klsGoto(parseInt(this.value, 10))">
            <option value="0">Rekap Tahunan (<?= $tahun_klasifikasi ? ('Tahun ' . $tahun_klasifikasi) : '12 Bulan Terakhir' ?>)</option>
            <?php foreach ($bulan_top_material as $idx => $bm): ?>
              <option value="<?= $idx + 1 ?>"><?= esc($bm['label_full']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="lap-btn-mini" id="klsNext" onclick="klsGoto(_klsIndex+1)">Selanjutnya ›</button>
        </div>
        <div class="lap-kls-export">
          <select class="lap-select-tahun" onchange="klsChangeTahun(this.value)">
            <option value="" <?= $tahun_klasifikasi === null ? 'selected' : '' ?>>12 Bulan Terakhir</option>
            <?php foreach ($tahun_options as $ty): ?>
              <option value="<?= $ty ?>" <?= $tahun_klasifikasi === $ty ? 'selected' : '' ?>>Tahun <?= $ty ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="lap-btn-mini" id="klsToggleTblBtn" onclick="klsToggleTable(this)">Tampilkan Tabel ▾</button>
          <button type="button" class="lap-btn lap-btn-green" onclick="exportKlasifikasiExcel()">↓ Excel</button>
          <button type="button" class="lap-btn lap-btn-red" onclick="exportKlasifikasiPDF()">↓ PDF</button>
        </div>
      </div>

      <div class="lap-kls-slider-viewport">
        <div class="lap-kls-slider-track" id="klsTrack">
          <div class="lap-kls-slide">
            <div class="lap-chart-wrap lap-chart-tall"><canvas id="chartTahunan"></canvas></div>
          </div>
          <?php foreach ($bulan_top_material as $idx => $bm): ?>
            <div class="lap-kls-slide">
              <?php if (empty($bm['items'])): ?>
                <div class="lap-empty">Tidak ada transaksi keluar pada <?= esc($bm['label_full']) ?></div>
              <?php else: ?>
                <div class="lap-chart-wrap"><canvas id="chartBulan<?= $idx ?>"></canvas></div>
                <div class="lap-kls-slide-tbl-wrap">
                  <table class="lap-tbl">
                    <thead><tr>
                      <th>Kode SAP</th><th>Nama Material</th><th>Kategori</th><th>Klasifikasi</th>
                      <th>Qty Keluar</th><th>Safety Stock Saat Ini</th><th>Usulan</th>
                    </tr></thead>
                    <tbody>
                      <?php
                      $klsMap = [
                        'fast'   => ['label' => 'Fast Moving',   'cls' => 'lap-badge-fast'],
                        'medium' => ['label' => 'Medium Moving', 'cls' => 'lap-badge-medium'],
                        'slow'   => ['label' => 'Slow Moving',   'cls' => 'lap-badge-slow'],
                      ];
                      foreach ($bm['items'] as $it):
                        $kls     = $klsMap[$it['klasifikasi']] ?? ['label' => '—', 'cls' => 'lap-badge-gray'];
                        $current = $it['safety_stock_saat_ini'];
                      ?>
                        <tr>
                          <td><code class="lap-code" style="font-size:.74rem"><?= esc($it['kode_sap'] ?? '—') ?></code></td>
                          <td><?= esc($it['nama_material']) ?></td>
                          <td><?= esc($it['kategori'] ?? '—') ?></td>
                          <td><span class="lap-badge <?= $kls['cls'] ?>"><?= $kls['label'] ?></span></td>
                          <td><?= number_format($it['qty']) ?> <?= esc($it['satuan']) ?></td>
                          <td><?= $current !== null ? number_format($current) : '<span class="lap-muted">belum diset</span>' ?></td>
                          <td><strong><?= number_format($it['safety_stock_usulan']) ?></strong> <?= esc($it['satuan']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="lap-kls-dots" id="klsDots"></div>
    <?php endif; ?>
  </div>

  <!-- ── Tabel usulan safety stock otomatis ─────────────────────────────────── -->
  <div class="lap-tbl-card">
    <div class="lap-tbl-card-head">
      <div class="lap-chart-title" style="font-size:.92rem">🎯 Usulan Safety Stock Otomatis</div>
      <div class="lap-chart-sub">Baris = tiap transaksi mutasi (masuk/keluar) material yang mengikuti diagram "Material Paling Banyak Keluar — <?= esc($bulan_ini_label) ?>" di atas, dalam periode filter tanggal. Klasifikasi &amp; usulan tetap dihitung dari pemakaian 12 bulan terakhir (setara service level ~95%). Klik "Terapkan" untuk update per material — tidak ada yang berubah otomatis tanpa persetujuan kamu.</div>
    </div>
    <div class="lap-tbl-wrap">
      <table class="lap-tbl">
        <thead><tr>
          <th>Tanggal Mutasi</th><th>Kode SAP</th><th>Nama Material</th>
          <th>Kategori</th><th>Klasifikasi</th><th>Stok</th><th>Keluar/Masuk</th><th>Plant</th>
          <th>Total Setahun</th><th>Safety Stock Saat Ini</th><th>Usulan</th><th>Aksi</th>
        </tr></thead>
        <tbody id="tbody-safety-stock">
          <?php if (empty($usulan_rows)): ?>
            <tr><td colspan="12" class="lap-empty">Belum ada data mutasi untuk dianalisa pada periode ini</td></tr>
          <?php else: ?>
            <?php foreach ($usulan_rows as $r):
              $klsMap = [
                'fast'   => ['label' => 'Fast Moving',    'cls' => 'lap-badge-fast'],
                'medium' => ['label' => 'Medium Moving',  'cls' => 'lap-badge-medium'],
                'slow'   => ['label' => 'Slow Moving',    'cls' => 'lap-badge-slow'],
                'none'   => ['label' => 'Tidak Bergerak', 'cls' => 'lap-badge-gray'],
              ];
              $kls     = $klsMap[$r['klasifikasi']];
              $current = $r['safety_stock_saat_ini'];
              $usulan  = $r['safety_stock_usulan'];
              $beda    = ($current === null) || ((int)$current !== (int)$usulan);
              $isKeluar = $r['jenis'] === 'keluar';
            ?>
              <tr data-material-id="<?= $r['material_id'] ?>">
                <td><?= date('d/m/Y H:i', strtotime($r['tanggal_mutasi'])) ?></td>
                <td><code class="lap-code" style="font-size:.74rem"><?= esc($r['kode_sap'] ?? '—') ?></code></td>
                <td><strong><?= esc($r['nama_material']) ?></strong></td>
                <td><?= esc($r['kategori'] ?? '—') ?></td>
                <td><span class="lap-badge <?= $kls['cls'] ?>"><?= $kls['label'] ?></span></td>
                <td><?= number_format($r['stok']) ?> <?= esc($r['satuan']) ?></td>
                <td style="color:<?= $isKeluar ? '#CE2626' : '#1a7f4b' ?>;font-weight:500">
                  <?= $isKeluar ? '-' : '+' ?><?= number_format(abs($r['jumlah'])) ?> <?= esc($r['satuan']) ?>
                </td>
                <td><?= esc($r['plant'] ?? '—') ?></td>
                <td><?= number_format($r['total_setahun']) ?> <?= esc($r['satuan']) ?></td>
                <td class="cell-current"><?= $current !== null ? number_format($current) : '<span class="lap-muted">belum diset</span>' ?></td>
                <td><strong><?= number_format($usulan) ?></strong> <?= esc($r['satuan']) ?></td>
                <td class="cell-aksi">
                  <?php if ($beda): ?>
                    <button type="button" class="lap-btn-mini" onclick="terapkanSafetyStock(this, <?= $r['material_id'] ?>, <?= (int)$usulan ?>)">Terapkan</button>
                  <?php else: ?>
                    <span class="lap-muted" style="font-style:normal">✓ Sesuai</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Detail lengkap per material — data asli, tidak dihapus, cuma dilipat ── -->
  <details class="lap-details">
    <summary>📋 Detail Stok per Material (Lengkap)</summary>
    <div class="lap-tbl-card" style="margin-top:.7rem">
      <div class="lap-tbl-wrap">
        <table class="lap-tbl">
          <thead><tr>
            <th>Kode SAP</th><th>Nama Material</th><th>Kategori</th>
            <th>Stok</th><th>Booking</th><th>Tersedia</th>
            <th>Masuk</th><th>Keluar</th><th>Rak</th>
          </tr></thead>
          <tbody id="tbody-stok-detail">
            <?php if (empty($stok_list)): ?>
              <tr><td colspan="9" class="lap-empty">Tidak ada data material</td></tr>
            <?php else: ?>
              <?php foreach ($stok_list as $row):
                $tersedia = (int)$row['stok_tersedia'];
              ?>
                <tr>
                  <td><code class="lap-code" style="font-size:.74rem"><?= esc($row['kode_sap'] ?? '—') ?></code></td>
                  <td><strong><?= esc($row['nama_material']) ?></strong></td>
                  <td><?= esc($row['kategori'] ?? '—') ?></td>
                  <td><?= number_format($row['stok']) ?> <?= esc($row['satuan'] ?? '') ?></td>
                  <td style="color:#8d9ab5"><?= number_format($row['stok_booking']) ?></td>
                  <td>
                    <?php if ($tersedia <= 0): ?>
                      <span style="font-weight:700;color:#CE2626"><?= number_format($tersedia) ?></span>
                    <?php else: ?>
                      <span style="font-weight:700;color:#1a7f4b"><?= number_format($tersedia) ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="color:#1a7f4b;font-weight:500">+<?= number_format($row['total_masuk']) ?></td>
                  <td style="color:#CE2626;font-weight:500">-<?= number_format($row['total_keluar']) ?></td>
                  <td style="font-size:.78rem">
                    <?= esc($row['kode_rak'] ?? '—') ?>
                    <?php if (!empty($row['zona'])): ?>
                      <span style="color:#8d9ab5">(<?= esc($row['zona']) ?>)</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </details>

</div>


<!-- ── Styles (semua class prefixed lap- agar tidak konflik) ──────────────── -->
<style>
/* ============================================================
   LAPORAN — visual refresh
   Prinsip: 1 aksen warna beda per kartu stat (navy/clay/hijau),
   header tabel jadi navy solid biar senada sama topbar, badge &
   tab dikasih micro-interaction halus. Struktur/class TIDAK diubah.
   ============================================================ */

/* Filter */
.lap-filter-card {
  position: relative;
  background: var(--surface-solid);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 1.1rem 1.3rem;
  margin-bottom: 1.2rem;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}
.lap-filter-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--navy) 0%, var(--clay) 100%);
}
.lap-filter-row {
  display: flex;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: .85rem;
}
.lap-filter-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 140px;
  flex: 1 1 140px;
}
.lap-label {
  font-size: .66rem;
  font-weight: 700;
  letter-spacing: .08em;
  color: var(--ink3);
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 5px;
}
.lap-label::before {
  font-size: .8rem;
  line-height: 1;
  opacity: .75;
}
.lap-filter-item:nth-child(1) .lap-label::before { content: '📅'; }
.lap-filter-item:nth-child(2) .lap-label::before { content: '📅'; }
.lap-filter-item:nth-child(3) .lap-label::before { content: '🏭'; }
.lap-filter-item:nth-child(4) .lap-label::before { content: '🏷️'; }
.lap-input {
  height: 38px;
  padding: 0 .8rem;
  border: 1.5px solid var(--border);
  border-radius: 9px;
  font-size: .83rem;
  font-family: var(--font);
  background: var(--bg);
  color: var(--ink2);
  outline: none;
  width: 100%;
  transition: border-color .15s, box-shadow .15s, background .15s;
}
.lap-input:hover {
  border-color: var(--border2);
}
.lap-input:focus {
  border-color: var(--navy);
  box-shadow: 0 0 0 3px var(--navy-bg);
  background: #fff;
}
.lap-filter-actions {
  display: flex;
  gap: .5rem;
  align-items: center;
  flex-shrink: 0;
  padding-top: 1.5rem;
}
.lap-btn {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  height: 38px;
  padding: 0 1rem;
  border: none;
  border-radius: 9px;
  font-size: .82rem;
  font-weight: 700;
  font-family: var(--font);
  cursor: pointer;
  white-space: nowrap;
  transition: opacity .15s, transform .12s, box-shadow .15s;
  box-shadow: 0 1px 2px rgba(28,37,53,.08);
}
.lap-btn:hover  { opacity: .92; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(28,37,53,.16); }
.lap-btn:active { transform: translateY(0) scale(.97); }
.lap-btn-navy  { background: linear-gradient(135deg, var(--navy2), var(--navy3)); color: #fff; }
.lap-btn-green { background: linear-gradient(135deg, #1a9c5a, #146b3f); color: #fff; }
.lap-btn-red   { background: linear-gradient(135deg, var(--clay3), var(--clay2)); color: #fff; }

/* Tabs */
.lap-tabs {
  display: flex;
  gap: 3px;
  padding: 5px;
  margin-bottom: 1.2rem;
  background: var(--surface-solid);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: var(--shadow-sm);
}
.lap-tab {
  flex: 1;
  position: relative;
  background: none;
  border: none;
  border-radius: 9px;
  padding: .6rem .9rem;
  font-size: .83rem;
  font-weight: 700;
  color: var(--ink3);
  cursor: pointer;
  font-family: var(--font);
  transition: background .18s, color .18s, transform .12s;
  white-space: nowrap;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.lap-tab::before {
  font-size: .9rem;
  opacity: .8;
}
.lap-tab:nth-child(1)::before { content: '📥'; }
.lap-tab:nth-child(2)::before { content: '📤'; }
.lap-tab:nth-child(3)::before { content: '🗓️'; }
.lap-tab:nth-child(4)::before { content: '📦'; }
.lap-tab:hover  { background: var(--navy-bg); color: var(--navy); }
.lap-tab.active {
  background: linear-gradient(135deg, var(--navy2), var(--navy3));
  color: #fff;
  box-shadow: 0 2px 8px rgba(28,45,79,.28);
}
.lap-tab.active::before { opacity: 1; }

/* Stats */
.lap-stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1.2rem;
}
.lap-stat-card {
  position: relative;
  overflow: hidden;
  background: var(--surface-solid);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 1.2rem 1.35rem;
  box-shadow: var(--shadow-sm);
  transition: transform .18s, box-shadow .18s;
}
.lap-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}
.lap-stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
}
.lap-stat-card::after {
  content: '';
  position: absolute;
  right: -18px; bottom: -18px;
  width: 84px; height: 84px;
  border-radius: 50%;
  opacity: .55;
}
/* Kartu 1 = navy, kartu 2 = clay, kartu 3 = hijau — berlaku di semua tab
   karena tiap panel selalu berisi persis 3 kartu stat */
.lap-stat-card:nth-child(1)::before { background: var(--navy); }
.lap-stat-card:nth-child(1)::after  { background: radial-gradient(circle, var(--navy-bg) 0%, transparent 72%); }
.lap-stat-card:nth-child(1) .lap-stat-val { color: var(--navy); }

.lap-stat-card:nth-child(2)::before { background: var(--clay); }
.lap-stat-card:nth-child(2)::after  { background: radial-gradient(circle, var(--clay-bg2) 0%, transparent 72%); }
.lap-stat-card:nth-child(2) .lap-stat-val { color: var(--clay2); }

.lap-stat-card:nth-child(3)::before { background: var(--green); }
.lap-stat-card:nth-child(3)::after  { background: radial-gradient(circle, var(--green-bg) 0%, transparent 72%); }
.lap-stat-card:nth-child(3) .lap-stat-val { color: var(--green); }

.lap-stat-label {
  position: relative;
  font-size: .66rem;
  font-weight: 800;
  letter-spacing: .08em;
  color: var(--ink3);
  text-transform: uppercase;
  margin-bottom: .5rem;
}
.lap-stat-val {
  position: relative;
  font-size: 2.15rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -1px;
  margin-bottom: .3rem;
  font-variant-numeric: tabular-nums;
}
.lap-stat-sub {
  position: relative;
  font-size: .75rem;
  color: var(--ink3);
}

/* Table */
.lap-tbl-card {
  background: var(--surface-solid);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.lap-tbl-wrap { overflow-x: auto; }
.lap-tbl {
  width: 100%;
  border-collapse: collapse;
  font-size: .83rem;
}
.lap-tbl thead tr {
  background: linear-gradient(135deg, var(--navy2), var(--navy3));
}
.lap-tbl th {
  padding: .8rem 1rem;
  text-align: left;
  font-size: .65rem;
  font-weight: 700;
  letter-spacing: .07em;
  color: rgba(255,255,255,.92);
  text-transform: uppercase;
  white-space: nowrap;
}
.lap-tbl td {
  padding: .75rem 1rem;
  border-bottom: 1px solid #f0ece2;
  vertical-align: middle;
}
.lap-tbl tbody tr:nth-child(even) td { background: rgba(160,130,100,.045); }
.lap-tbl tbody tr:last-child td { border-bottom: none; }
.lap-tbl tbody tr { transition: background .12s; }
.lap-tbl tbody tr:hover td { background: var(--clay-bg); }
.lap-empty {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--ink3);
  font-size: .84rem;
}
.lap-empty::before {
  content: '🗂️';
  display: block;
  font-size: 1.8rem;
  margin-bottom: .5rem;
  opacity: .5;
}

/* Code chip */
.lap-code {
  font-family: 'Inter', monospace;
  font-size: .78rem;
  font-weight: 600;
  color: var(--navy);
  background: var(--navy-bg);
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid var(--border);
  letter-spacing: .01em;
}

/* Badges */
.lap-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px 3px 8px;
  border-radius: 20px;
  font-size: .71rem;
  font-weight: 700;
  letter-spacing: .01em;
}
.lap-badge::before {
  content: '';
  width: 6px; height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}
.lap-badge-blue   { background: var(--blue-bg);   color: #1d4ed8; border: 1px solid var(--blue-border); }
.lap-badge-blue::before   { background: #2563eb; }
.lap-badge-green  { background: var(--green-bg);  color: #065f46; border: 1px solid var(--green-border); }
.lap-badge-green::before  { background: #16a34a; }
.lap-badge-red    { background: var(--red-bg);    color: #991b1b; border: 1px solid var(--red-border); }
.lap-badge-red::before    { background: #dc2626; }
.lap-badge-yellow { background: var(--amber-bg);  color: #92400e; border: 1px solid var(--amber-border); }
.lap-badge-yellow::before { background: #d97706; }
.lap-badge-gray   { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
.lap-badge-gray::before   { background: #9ca3af; }

/* Responsive */
@media (max-width: 768px) {
  .lap-stats-row  { grid-template-columns: 1fr; }
  .lap-filter-row { flex-direction: column; align-items: stretch; }
  .lap-filter-actions { padding-top: .4rem; flex-wrap: wrap; }
  .lap-tabs { overflow-x: auto; }
  .lap-tab  { flex: unset; font-size: .75rem; }
  .lap-stat-val { font-size: 1.8rem; }
}

/* Item list */
.lap-item-list {
  list-style: none;
  margin: 0; padding: 0;
  display: flex; flex-direction: column; gap: 3px;
}
.lap-item-list li {
  font-size: .78rem; color: var(--ink2);
  padding: 1px 0;
  display: flex; align-items: center; gap: 6px;
}
.lap-item-list li::before {
  content: "";
  display: inline-block;
  width: 5px; height: 5px; min-width: 5px;
  border-radius: 50%;
  background: var(--clay);
}
.lap-muted { color: var(--ink4); font-size: .8rem; font-style: italic; }

/* Print */
@media print {
  .lap-filter-card, .lap-tabs, .lap-stats-row { display: none !important; }
  .lap-panel { display: block !important; }
  .lap-tbl-card { box-shadow: none; border: 1px solid #ccc; }
  .lap-tbl th { background: #1a2744 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 9px; padding: 6px 8px; }
  .lap-tbl td { padding: 5px 8px; font-size: 11px; }
  .lap-item-list li { font-size: 11px; }
  .lap-code { background: none !important; border: none !important; }
  @page { margin: 1.5cm; size: A4 landscape; }
}

/* ============================================================
   ANALISA PERGERAKAN MATERIAL & USULAN SAFETY STOCK (tab Stok & Mutasi)
   ============================================================ */
:root {
  --fm-fast:   #c0392b;
  --fm-medium: #d97706;
  --fm-slow:   #2563eb;
}

.lap-chart-card {
  background: var(--surface-solid);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 1.2rem 1.3rem;
  margin-bottom: 1.1rem;
  box-shadow: var(--shadow-sm);
}
.lap-chart-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .8rem;
  margin-bottom: 1rem;
}
.lap-chart-title {
  font-size: 1rem;
  font-weight: 800;
  color: var(--ink);
  margin-bottom: .2rem;
}
.lap-chart-sub {
  font-size: .78rem;
  color: var(--ink3);
  max-width: 60ch;
}
.lap-chart-wrap { position: relative; height: 280px; }
.lap-chart-wrap.lap-chart-tall { height: 420px; }

.lap-legend {
  display: flex;
  flex-wrap: wrap;
  gap: .6rem .9rem;
  flex-shrink: 0;
}
.lap-legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: .74rem;
  font-weight: 600;
  color: var(--ink3);
}
.lap-legend-item i { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
.lap-dot-fast   { background: var(--fm-fast); }
.lap-dot-medium { background: var(--fm-medium); }
.lap-dot-slow   { background: var(--fm-slow); }

.lap-tbl-card-head {
  padding: 1.1rem 1.2rem .8rem;
}

.lap-badge-fast   { background: rgba(192,57,43,.1);  color: var(--fm-fast);   border: 1px solid rgba(192,57,43,.25); }
.lap-badge-fast::before   { background: var(--fm-fast); }
.lap-badge-medium { background: rgba(217,119,6,.1);  color: var(--fm-medium); border: 1px solid rgba(217,119,6,.25); }
.lap-badge-medium::before { background: var(--fm-medium); }
.lap-badge-slow   { background: rgba(37,99,235,.1);  color: var(--fm-slow);   border: 1px solid rgba(37,99,235,.25); }
.lap-badge-slow::before   { background: var(--fm-slow); }

.lap-btn-mini {
  height: 28px;
  padding: 0 .7rem;
  border: none;
  border-radius: 7px;
  background: var(--navy);
  color: #fff;
  font-size: .72rem;
  font-weight: 700;
  cursor: pointer;
  font-family: var(--font);
  transition: opacity .15s, transform .1s;
}
.lap-btn-mini:hover  { opacity: .88; }
.lap-btn-mini:active { transform: scale(.96); }
.lap-btn-mini:disabled { opacity: .5; cursor: not-allowed; }

.lap-details {
  background: var(--surface-solid);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 1rem 1.2rem;
  box-shadow: var(--shadow-sm);
}
.lap-details summary {
  cursor: pointer;
  font-weight: 700;
  font-size: .88rem;
  color: var(--navy);
  list-style: none;
  user-select: none;
}
.lap-details summary::-webkit-details-marker { display: none; }
.lap-details summary::after {
  content: '▾';
  float: right;
  transition: transform .2s;
}
.lap-details[open] summary::after { transform: rotate(180deg); }

@media (max-width: 768px) {
  .lap-chart-wrap { height: 240px; }
  .lap-chart-wrap.lap-chart-tall { height: 360px; }
  .lap-chart-head { flex-direction: column; }
}

@media print {
  .lap-chart-card, .lap-tbl-card:has(#tbody-safety-stock), .lap-details summary { display: none !important; }
}

/* Slider Klasifikasi Pergerakan Material */
.lap-kls-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .6rem;
  padding: 0 0 .6rem;
}
.lap-kls-nav {
  display: flex;
  align-items: center;
  gap: .6rem;
}
.lap-kls-select {
  font-size: .78rem;
  font-weight: 700;
  color: var(--navy);
  min-width: 190px;
  text-align: center;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: .4rem .6rem;
  background: #fff;
  cursor: pointer;
}
.lap-kls-export {
  display: flex;
  gap: .5rem;
  align-items: center;
}
.lap-select-tahun {
  font-size: .78rem;
  font-weight: 600;
  color: var(--navy);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: .4rem .6rem;
  background: #fff;
  cursor: pointer;
}
.lap-kls-slider-viewport {
  overflow-x: hidden;
  overflow-y: visible;
  width: 100%;
}
.lap-kls-slider-track {
  display: flex;
  align-items: flex-start;
  transition: transform .3s ease;
}
.lap-kls-slide {
  flex: 0 0 100%;
  min-width: 100%;
}
.lap-kls-slide-tbl-wrap {
  margin-top: .9rem;
  border-top: 1px solid var(--border);
  padding-top: .9rem;
  display: none;
}
.lap-kls-slider-track.show-table .lap-kls-slide-tbl-wrap {
  display: block;
}
.lap-kls-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  padding-top: .7rem;
  flex-wrap: wrap;
}
.lap-kls-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border);
  cursor: pointer;
  transition: background .15s, transform .15s;
}
.lap-kls-dot:hover   { background: var(--clay); }
.lap-kls-dot.active  { background: var(--navy); transform: scale(1.3); }
</style>

<!-- ── Scripts ─────────────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
var _currentTab = 'penerimaan';
var _stokChartsReady = false;

function switchTab(tab, btn) {
  document.querySelectorAll('.lap-panel').forEach(function(p) {
    p.style.display = 'none';
  });
  document.querySelectorAll('.lap-tab').forEach(function(b) {
    b.classList.remove('active');
  });
  document.getElementById('tab-' + tab).style.display = 'block';
  btn.classList.add('active');
  _currentTab = tab;

  // Canvas Chart.js tidak bisa dirender kalau parent-nya display:none,
  // jadi diagram baru diinisialisasi begitu tab Stok & Mutasi pertama kali dibuka.
  if (tab === 'stok' && !_stokChartsReady) {
    initStokCharts();
    _stokChartsReady = true;
  }
}

// ── Data untuk 2 diagram (dikirim dari controller via json_encode) ─────────
var _dataBulanIni = <?= json_encode(array_map(function ($r) {
    return ['nama' => $r['nama_material'], 'qty' => (int) $r['total_keluar']];
}, $stok_bulan_ini)) ?>;

var _dataTahunan = <?= json_encode(array_map(function ($r) {
    return [
        'nama'        => $r['nama_material'],
        'total'       => (int) $r['total_setahun'],
        'klasifikasi' => $r['klasifikasi'],
    ];
}, array_slice($rekap_bergerak, 0, 20))) ?>;

// ── Data untuk slide bulanan (Klasifikasi Pergerakan Material) ─────────────
var _dataBulanTop = <?= json_encode(array_map(function ($bm) {
    return [
        'label_full'  => $bm['label_full'],
        'label_short' => $bm['label_short'],
        'items'       => array_map(function ($it) {
            return [
                'nama'        => $it['nama_material'],
                'kode_sap'    => $it['kode_sap'] ?? '',
                'kategori'    => $it['kategori'] ?? '',
                'klasifikasi' => $it['klasifikasi'],
                'qty'         => (int) $it['qty'],
                'satuan'      => $it['satuan'],
                'ss_saat_ini' => $it['safety_stock_saat_ini'],
                'ss_usulan'   => (int) $it['safety_stock_usulan'],
            ];
        }, $bm['items']),
    ];
}, $bulan_top_material)) ?>;

// ── Tabel rekap tahunan lengkap (utk export Excel/PDF kartu klasifikasi) ───
var _rekapTahunanTable = <?= json_encode(array_map(function ($r) {
    return [
        'kode_sap'    => $r['kode_sap'] ?? '',
        'nama'        => $r['nama_material'],
        'kategori'    => $r['kategori'] ?? '',
        'klasifikasi' => $r['klasifikasi'],
        'total'       => (int) $r['total_setahun'],
        'satuan'      => $r['satuan'],
        'bulanan'     => array_values($r['bulanan']),
    ];
}, $rekap_bergerak)) ?>;
var _bulanLabelsShort = <?= json_encode($rekap_bulan_labels) ?>;
var _klsTahunLabel = <?= json_encode($tahun_klasifikasi ? ('Tahun ' . $tahun_klasifikasi) : '12 Bulan Terakhir') ?>;

function klsChangeTahun(val) {
  var url = new URL(window.location.href);
  if (val) { url.searchParams.set('tahun_klasifikasi', val); } else { url.searchParams.delete('tahun_klasifikasi'); }
  url.searchParams.set('tab', 'stok'); // tetap di tab Stok & Mutasi setelah reload
  window.location.href = url.toString();
}

function klsToggleTable(btn) {
  var track = document.getElementById('klsTrack');
  if (!track) return;
  var shown = track.classList.toggle('show-table');
  btn.textContent = shown ? 'Sembunyikan Tabel ▴' : 'Tampilkan Tabel ▾';
}

function initStokCharts() {
  var elBulanIni = document.getElementById('chartBulanIni');
  if (elBulanIni && _dataBulanIni.length) {
    new Chart(elBulanIni, {
      type: 'bar',
      data: {
        labels: _dataBulanIni.map(function(d){ return d.nama; }),
        datasets: [{
          label: 'Unit Keluar',
          data: _dataBulanIni.map(function(d){ return d.qty; }),
          backgroundColor: '#a05a42',
          borderRadius: 6,
          maxBarThickness: 26,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  klsInitDots();
  klsGoto(0);
}

// ── Slider "Klasifikasi Pergerakan Material": slide 0 = rekap tahunan,
//    slide 1..12 = top material keluar per bulan ───────────────────────────
var _klsIndex       = 0;
var _klsCharts       = {};
var _klsTotalSlides  = 1 + _dataBulanTop.length;

function klsBuildChart(index) {
  if (_klsCharts[index]) return;

  if (index === 0) {
    var elTahunan = document.getElementById('chartTahunan');
    if (!elTahunan || !_dataTahunan.length) return;
    var colorMap = { fast: '#c0392b', medium: '#d97706', slow: '#2563eb' };
    _klsCharts[0] = new Chart(elTahunan, {
      type: 'bar',
      data: {
        labels: _dataTahunan.map(function(d){ return d.nama; }),
        datasets: [{
          label: 'Total Setahun',
          data: _dataTahunan.map(function(d){ return d.total; }),
          backgroundColor: _dataTahunan.map(function(d){ return colorMap[d.klasifikasi] || '#94a3b8'; }),
          borderRadius: 6,
          maxBarThickness: 20,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  } else {
    var bm = _dataBulanTop[index - 1];
    var el = document.getElementById('chartBulan' + (index - 1));
    if (!el || !bm || !bm.items.length) return;
    _klsCharts[index] = new Chart(el, {
      type: 'bar',
      data: {
        labels: bm.items.map(function(d){ return d.nama; }),
        datasets: [{
          label: 'Unit Keluar',
          data: bm.items.map(function(d){ return d.qty; }),
          backgroundColor: '#a05a42',
          borderRadius: 6,
          maxBarThickness: 26,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }
}

function klsGoto(i) {
  if (i < 0) i = 0;
  if (i > _klsTotalSlides - 1) i = _klsTotalSlides - 1;
  _klsIndex = i;

  var track = document.getElementById('klsTrack');
  if (track) track.style.transform = 'translateX(-' + (i * 100) + '%)';

  var indicator = document.getElementById('klsIndicator');
  var sub       = document.getElementById('klsSlideSub');
  if (indicator) {
    indicator.value = String(i);
  }
  if (sub) {
    sub.textContent = i === 0
      ? ('Rekap ' + _klsTahunLabel + ', diurutkan dari total pemakaian tertinggi. Dipakai sebagai dasar usulan safety stock di bawah. Geser untuk lihat rincian per bulan.')
      : 'Material paling banyak keluar pada ' + _dataBulanTop[i - 1].label_full + '.';
  }

  var prevBtn = document.getElementById('klsPrev');
  var nextBtn = document.getElementById('klsNext');
  if (prevBtn) prevBtn.disabled = (i === 0);
  if (nextBtn) nextBtn.disabled = (i === _klsTotalSlides - 1);

  document.querySelectorAll('#klsDots .lap-kls-dot').forEach(function(d, idx) {
    d.classList.toggle('active', idx === i);
  });

  klsBuildChart(i);
}

function klsInitDots() {
  var wrap = document.getElementById('klsDots');
  if (!wrap) return;
  wrap.innerHTML = '';
  for (var i = 0; i < _klsTotalSlides; i++) {
    var dot = document.createElement('span');
    dot.className = 'lap-kls-dot' + (i === 0 ? ' active' : '');
    dot.onclick = (function(idx) { return function() { klsGoto(idx); }; })(i);
    wrap.appendChild(dot);
  }
}

// ── Export Excel kartu Klasifikasi: file .xlsx ASLI berisi gambar diagram +
//    TABEL EXCEL ASLI (pakai fitur Table bawaan ExcelJS: bergaris, header
//    berwarna, baris belang-seling, ada tombol filter). Sheet "Rekap Tahunan"
//    (bagian atas/paling depan file) = diagram rekap tahunan + tabelnya;
//    satu sheet per bulan = diagram "Material Paling Banyak Keluar" bulan itu
//    + tabel Rekap Usulan Safety Stock-nya. ──────────────────────────────
function exportKlasifikasiExcel() {
  if (!_rekapTahunanTable.length) { alert('Tidak ada data untuk diekspor.'); return; }
  if (typeof ExcelJS === 'undefined') { alert('Komponen Excel belum termuat, silakan muat ulang halaman lalu coba lagi.'); return; }
  var klsLabel = { fast: 'Fast Moving', medium: 'Medium Moving', slow: 'Slow Moving' };

  // Pastikan semua chart (tahunan + tiap bulan) sudah dibuat dulu sebelum
  // di-capture jadi gambar — chart yang baru pertama kali dibuat butuh
  // sedikit waktu utk selesai digambar (sama seperti proses export PDF).
  for (var i = 0; i < _klsTotalSlides; i++) { klsBuildChart(i); }

  setTimeout(function() {
    var workbook = new ExcelJS.Workbook();
    workbook.creator = 'Gudang Teknik';
    workbook.created = new Date();

    // Tempel gambar diagram (canvas Chart.js) ke worksheet, lalu kembalikan
    // baris (1-based) berikutnya yang aman dipakai buat tabel di bawahnya.
    function addChartImage(sheet, canvas, rowStart) {
      if (!canvas) return rowStart;
      var imgId = workbook.addImage({ base64: canvas.toDataURL('image/png'), extension: 'png' });
      sheet.addImage(imgId, { tl: { col: 0, row: rowStart - 1 }, ext: { width: 560, height: 260 } });
      return rowStart + 16; // beri jarak di bawah gambar sebelum tabel mulai
    }

    var tableNameCounter = {};
    function uniqueTableName(base) {
      var clean = base.replace(/[^A-Za-z0-9_]/g, '_').replace(/^[0-9]/, '_$&');
      if (!clean) clean = 'Tabel';
      var n = tableNameCounter[clean] = (tableNameCounter[clean] || 0) + 1;
      return n === 1 ? clean : (clean + '_' + n);
    }

    // ── Sheet 1 (paling atas/depan): Rekap Tahunan — diagram + TABEL ────
    var shTahunan = workbook.addWorksheet('Rekap Tahunan');
    shTahunan.getCell('A1').value = 'REKAP TAHUNAN (' + _klsTahunLabel + ')';
    shTahunan.getCell('A1').font = { bold: true, size: 13 };

    var rowSetelahChart1 = addChartImage(shTahunan, document.getElementById('chartTahunan'), 3);

    var headerTahunan = ['Kode SAP', 'Material', 'Kategori', 'Klasifikasi', 'Satuan', 'Total Setahun'].concat(_bulanLabelsShort);
    shTahunan.addTable({
      name: uniqueTableName('RekapTahunan'),
      ref: 'A' + rowSetelahChart1,
      headerRow: true,
      style: { theme: 'TableStyleMedium9', showRowStripes: true },
      columns: headerTahunan.map(function(h) { return { name: h, filterButton: true }; }),
      rows: _rekapTahunanTable.map(function(r) {
        return [r.kode_sap || '—', r.nama, r.kategori || '—', klsLabel[r.klasifikasi] || r.klasifikasi, r.satuan, r.total].concat(r.bulanan);
      })
    });

    for (var c1 = 1; c1 <= headerTahunan.length; c1++) {
      shTahunan.getColumn(c1).width = (c1 === 2) ? 30 : 14;
    }

    // ── Satu sheet per bulan: diagram + TABEL Rekap Usulan Safety Stock ─
    var usedNames = {};
    _dataBulanTop.forEach(function(bm, idx) {
      var rawName = (bm.label_short || ('Bulan' + (idx + 1))).replace(/[\\\/\?\*\[\]:]/g, '').substring(0, 28);
      var name = rawName, n = 1;
      while (usedNames[name]) { name = rawName + '_' + (++n); }
      usedNames[name] = true;

      var sh = workbook.addWorksheet(name);
      sh.getCell('A1').value = 'REKAP USULAN SAFETY STOCK — ' + bm.label_full;
      sh.getCell('A1').font = { bold: true, size: 13 };

      if (!bm.items.length) {
        sh.getCell('A3').value = 'Tidak ada transaksi keluar pada bulan ini';
        return;
      }

      var canvas         = document.getElementById('chartBulan' + idx);
      var rowSetelahChart = addChartImage(sh, canvas, 3);

      var headerBulan = ['Kode SAP', 'Nama Material', 'Kategori', 'Klasifikasi', 'Qty Keluar', 'Safety Stock Saat Ini', 'Usulan'];
      sh.addTable({
        name: uniqueTableName('Bulan_' + name),
        ref: 'A' + rowSetelahChart,
        headerRow: true,
        style: { theme: 'TableStyleMedium9', showRowStripes: true },
        columns: headerBulan.map(function(h) { return { name: h, filterButton: true }; }),
        rows: bm.items.map(function(it) {
          return [
            it.kode_sap || '—', it.nama, it.kategori || '—', klsLabel[it.klasifikasi] || it.klasifikasi,
            it.qty + ' ' + it.satuan,
            it.ss_saat_ini !== null && it.ss_saat_ini !== undefined ? it.ss_saat_ini : 'belum diset',
            it.ss_usulan + ' ' + it.satuan
          ];
        })
      });

      for (var c2 = 1; c2 <= headerBulan.length; c2++) {
        sh.getColumn(c2).width = (c2 === 2) ? 28 : 18;
      }
    });

    workbook.xlsx.writeBuffer().then(function(buffer) {
      var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
      var url  = URL.createObjectURL(blob);
      var a    = document.createElement('a');
      a.href     = url;
      a.download = 'klasifikasi-pergerakan-material-<?= esc($dari) ?>-sd-<?= esc($sampai) ?>.xlsx';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }).catch(function(err) {
      console.error(err);
      alert('Gagal membuat file Excel, silakan coba lagi.');
    });
  }, 350);
}

// ── Export PDF kartu Klasifikasi: tabel rekap tahunan + gambar diagram 12 bulan ─
function exportKlasifikasiPDF() {
  if (!_rekapTahunanTable.length) { alert('Tidak ada data untuk di-print.'); return; }
  var klsLabel = { fast: 'Fast Moving', medium: 'Medium Moving', slow: 'Slow Moving' };

  // Pastikan semua chart (tahunan + 12 bulan) sudah dibuat dulu. Chart yang
  // baru pertama kali dibuat butuh sedikit waktu utk selesai menggambar
  // (Chart.js "responsive" perlu 1 siklus render) — makanya proses ambil
  // gambarnya (toDataURL) ditunda sebentar biar tidak keburu nge-capture
  // canvas yang masih kosong.
  for (var i = 0; i < _klsTotalSlides; i++) { klsBuildChart(i); }

  setTimeout(function() {
  var tableRows = _rekapTahunanTable.map(function(r) {
    return '<tr><td><code>' + (r.kode_sap || '—') + '</code></td><td>' + r.nama + '</td><td>' + (r.kategori || '—') +
      '</td><td>' + (klsLabel[r.klasifikasi] || r.klasifikasi) + '</td><td>' + r.total.toLocaleString('id-ID') + ' ' + r.satuan + '</td></tr>';
  }).join('');

  var tableHtml = '<table><thead><tr><th>Kode SAP</th><th>Material</th><th>Kategori</th><th>Klasifikasi</th><th>Total Setahun</th></tr></thead><tbody>' + tableRows + '</tbody></table>';

  var chartImgs = '';
  var tahunanCanvas = document.getElementById('chartTahunan');
  if (tahunanCanvas) {
    chartImgs += '<div style="page-break-before:always"><h3>Rekap Tahunan (' + _klsTahunLabel + ')</h3><img src="' + tahunanCanvas.toDataURL('image/png') + '" style="width:100%;max-width:900px"></div>';
  }
  _dataBulanTop.forEach(function(bm, idx) {
    var c = document.getElementById('chartBulan' + idx);
    if (c && bm.items.length) {
      var bulanRows = bm.items.map(function(it) {
        return '<tr><td><code>' + (it.kode_sap || '—') + '</code></td><td>' + it.nama + '</td><td>' + (it.kategori || '—') +
          '</td><td>' + (klsLabel[it.klasifikasi] || it.klasifikasi) + '</td><td>' + it.qty.toLocaleString('id-ID') + ' ' + it.satuan + '</td></tr>';
      }).join('');
      // Versi cetak PDF cuma nampilin 5 kolom yang relevan (Kode SAP, Nama Material,
      // Kategori, Klasifikasi, Qty Keluar) — kolom Safety Stock Saat Ini & Usulan
      // sengaja tidak dicetak di PDF (tetap ada di layar & di Excel).
      var bulanTableHtml = '<table><thead><tr><th>Kode SAP</th><th>Nama Material</th><th>Kategori</th><th>Klasifikasi</th>' +
        '<th>Qty Keluar</th></tr></thead><tbody>' + bulanRows + '</tbody></table>';
      chartImgs += '<div style="page-break-before:always"><h3>' + bm.label_full + '</h3><img src="' + c.toDataURL('image/png') + '" style="width:100%;max-width:900px">' +
        '<h3>Rekap Usulan Safety Stock — ' + bm.label_full + '</h3>' + bulanTableHtml + '</div>';
    }
  });

  var iframe = document.getElementById('_print_frame_kls');
  if (!iframe) {
    iframe = document.createElement('iframe');
    iframe.id = '_print_frame_kls';
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none';
    document.body.appendChild(iframe);
  }

  var bodyHtml = '<h2>Klasifikasi Pergerakan Material</h2>' +
    '<p>Periode: <?= esc($dari) ?> s/d <?= esc($sampai) ?></p>' +
    '<h3>Tabel Rekap Tahunan</h3>' + tableHtml + chartImgs;

  var doc = iframe.contentDocument || iframe.contentWindow.document;
  doc.open();
  doc.write(
    '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Gudang Teknik — Klasifikasi Pergerakan Material</title><style>' +
    'body{font-family:Arial,sans-serif;font-size:11px;color:#222;padding:20px;margin:0}' +
    'h2{font-size:14px;color:#1a2744;margin:0 0 2px;font-weight:700}' +
    'h3{font-size:12px;color:#1a2744;margin:0 0 8px;font-weight:700;border-bottom:1px solid #ddd;padding-bottom:4px}' +
    'p{font-size:10px;color:#888;margin:0 0 14px}' +
    'table{border-collapse:collapse;width:100%;margin-bottom:10px}' +
    'thead tr{background:#1a2744;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
    'th{color:#fff;padding:7px 10px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}' +
    'td{border-bottom:1px solid #eee;padding:6px 10px;vertical-align:top;font-size:11px}' +
    'tr:nth-child(even) td{background:#f8f8f8;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
    'code{font-family:monospace;font-size:10px}' +
    'img{display:block;margin:0 auto 10px}' +
    'body::before{content:"";position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-15deg);width:55%;height:55%;background:url("/img/sasa-bg.png") center/contain no-repeat;opacity:0.07;pointer-events:none;z-index:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
    'body>*{position:relative;z-index:1}' +
    '@page{margin:1.5cm;size:A4 landscape}' +
    '</style></head><body>' +
    bodyHtml +
    '</body></html>'
  );
  doc.close();
  setTimeout(function() { iframe.contentWindow.focus(); iframe.contentWindow.print(); }, 400);
  }, 350);
}

// ── Terapkan usulan safety stock ke satu material ───────────────────────────
function terapkanSafetyStock(btn, materialId, usulan) {
  if (!confirm('Terapkan safety stock ' + usulan + ' untuk material ini?')) return;

  btn.disabled = true;
  var originalLabel = btn.textContent;
  btn.textContent = 'Menyimpan...';

  fetch('/laporan/terapkan-safety-stock', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'material_id=' + encodeURIComponent(materialId) + '&safety_stock=' + encodeURIComponent(usulan)
  })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        // Material bisa punya banyak baris transaksi — sinkronkan semuanya
        document.querySelectorAll('tr[data-material-id="' + materialId + '"]').forEach(function(row) {
          var cCurrent = row.querySelector('.cell-current');
          var cAksi    = row.querySelector('.cell-aksi');
          if (cCurrent) cCurrent.textContent = usulan.toLocaleString('id-ID');
          if (cAksi) cAksi.innerHTML = '<span class="lap-muted" style="font-style:normal">✓ Sesuai</span>';
        });
      } else {
        alert(res.message || 'Gagal menyimpan');
        btn.disabled = false;
        btn.textContent = originalLabel;
      }
    })
    .catch(function() {
      alert('Gagal menghubungi server. Coba lagi.');
      btn.disabled = false;
      btn.textContent = originalLabel;
    });
}

function buildQS() {
  return '?dari='     + encodeURIComponent(document.getElementById('f-dari').value)
       + '&sampai='   + encodeURIComponent(document.getElementById('f-sampai').value)
       + '&plant_id=' + encodeURIComponent(document.getElementById('f-plant').value)
       + '&kategori=' + encodeURIComponent(document.getElementById('f-kategori').value)
       + '&tab='      + encodeURIComponent(_currentTab);
}

// Export Excel: setiap tab sekarang menghasilkan file .xlsx ASLI (tabel
// bergaris, header berwarna, filter) — bukan CSV mentah lagi.
// - Stok & Mutasi: sheet "Usulan Safety Stock" di depan + sheet detail.
// - Penerimaan / Pengeluaran / Booking: satu sheet berisi tabel yang sama
//   persis seperti yang tampil di layar (termasuk daftar material per baris).
function exportExcel() {
  var tabMap = {
    penerimaan:  { tbody: 'tbody-penerimaan',  sheet: 'Penerimaan',  title: 'LAPORAN PENERIMAAN',  file: 'laporan-penerimaan' },
    pengeluaran: { tbody: 'tbody-pengeluaran',  sheet: 'Pengeluaran', title: 'LAPORAN PENGELUARAN', file: 'laporan-pengeluaran' },
    booking:     { tbody: 'tbody-booking',      sheet: 'Booking',     title: 'LAPORAN BOOKING',     file: 'laporan-booking' }
  };

  if (_currentTab === 'stok') {
    exportStokExcelDenganUsulan();
    return;
  }

  var cfg = tabMap[_currentTab];
  if (!cfg) { window.location.href = '/laporan/export-data' + buildQS() + '&format=csv'; return; }
  exportGenericTabExcel(cfg.tbody, cfg.sheet, cfg.title, cfg.file);
}

// Ambil isi 1 sel tabel jadi teks yang rapi buat Excel. Kalau selnya berisi
// daftar material (<ul class="lap-item-list">), tiap item dipisah baris baru
// (\n) supaya tetap kebaca sebagai list saat wrapText dinyalakan di Excel.
function _extractCellText(cell) {
  var ul = cell.querySelector('ul.lap-item-list');
  if (ul) {
    var items = Array.prototype.slice.call(ul.querySelectorAll('li')).map(function(li) {
      return li.textContent.trim();
    });
    return items.join('\n');
  }
  return cell.textContent.trim().replace(/[ \t]+/g, ' ');
}

function exportGenericTabExcel(tbodyId, sheetName, title, fileBase) {
  if (typeof ExcelJS === 'undefined') { alert('Komponen Excel belum termuat, silakan muat ulang halaman lalu coba lagi.'); return; }

  var tbodyEl = document.getElementById(tbodyId);
  var tableEl = tbodyEl ? tbodyEl.closest('table') : null;
  if (!tableEl) { alert('Tidak ada data untuk diekspor.'); return; }

  var headerCells = Array.prototype.slice.call(tableEl.querySelectorAll('thead th'));
  var header = headerCells.map(function(th) { return th.textContent.trim(); });

  var bodyRowsEl = Array.prototype.slice.call(tableEl.querySelectorAll('tbody tr'));
  var rows = [];
  var hasMultiline = false;
  bodyRowsEl.forEach(function(tr) {
    var cells = Array.prototype.slice.call(tr.children);
    if (cells.length < header.length) return; // lewati baris "tidak ada data" (colspan)
    var rowVals = cells.map(function(td) {
      var txt = _extractCellText(td);
      if (txt.indexOf('\n') !== -1) hasMultiline = true;
      return txt;
    });
    rows.push(rowVals);
  });

  if (!rows.length) { alert('Tidak ada data pada periode ini untuk diekspor.'); return; }

  var workbook = new ExcelJS.Workbook();
  workbook.creator = 'Gudang Teknik';
  workbook.created = new Date();

  var sh = workbook.addWorksheet(sheetName);
  sh.getCell('A1').value = title;
  sh.getCell('A1').font = { bold: true, size: 13 };

  sh.addTable({
    name: sheetName.replace(/[^A-Za-z0-9_]/g, '_'),
    ref: 'A3',
    headerRow: true,
    style: { theme: 'TableStyleMedium9', showRowStripes: true },
    columns: header.map(function(h) { return { name: h || '-', filterButton: true }; }),
    rows: rows
  });

  header.forEach(function(h, idx) {
    sh.getColumn(idx + 1).width = 22;
  });

  // Kalau ada sel berisi daftar material multi-baris, aktifkan wrapText biar
  // rapi kebaca (bukan jadi satu baris panjang tanpa pemisah).
  if (hasMultiline) {
    sh.eachRow({ includeEmpty: false }, function(row, rowNumber) {
      if (rowNumber < 4) return; // lewati judul & header tabel
      row.eachCell(function(cell) {
        if (typeof cell.value === 'string' && cell.value.indexOf('\n') !== -1) {
          cell.alignment = { wrapText: true, vertical: 'top' };
        }
      });
    });
  }

  workbook.xlsx.writeBuffer().then(function(buffer) {
    var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href     = url;
    a.download = fileBase + '-<?= esc($dari) ?>-sd-<?= esc($sampai) ?>.xlsx';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }).catch(function(err) {
    console.error(err);
    alert('Gagal membuat file Excel, silakan coba lagi.');
  });
}

function exportStokExcelDenganUsulan() {
  if (typeof ExcelJS === 'undefined') { alert('Komponen Excel belum termuat, silakan muat ulang halaman lalu coba lagi.'); return; }

  var tblUsulanEl = document.getElementById('tbody-safety-stock');
  tblUsulanEl = tblUsulanEl ? tblUsulanEl.closest('table') : null;
  var tblDetailEl = document.getElementById('tbody-stok-detail');
  tblDetailEl = tblDetailEl ? tblDetailEl.closest('table') : null;

  function extractTable(tableEl, keepIdx) {
    if (!tableEl) return { header: [], rows: [] };
    var headerCellsAll = Array.prototype.slice.call(tableEl.querySelectorAll('thead th'));
    var idxList = keepIdx || headerCellsAll.map(function(_, i) { return i; });
    var header  = idxList.map(function(i) { return headerCellsAll[i] ? headerCellsAll[i].textContent.trim() : ''; });
    var bodyRows = Array.prototype.slice.call(tableEl.querySelectorAll('tbody tr'));
    var rows = [];
    bodyRows.forEach(function(tr) {
      var cells = Array.prototype.slice.call(tr.children);
      if (cells.length < idxList.length) return; // lewati baris "belum ada data" (colspan)
      rows.push(idxList.map(function(i) { return cells[i] ? cells[i].textContent.trim().replace(/\s+/g, ' ') : ''; }));
    });
    return { header: header, rows: rows };
  }

  // Sama seperti versi cetak PDF: cuma 6 kolom relevan yang dipertahankan
  // (Tanggal Mutasi, Kode SAP, Nama Material, Stok, Keluar/Masuk, Plant).
  var usulan = extractTable(tblUsulanEl, [0, 1, 2, 5, 6, 7]);
  var detail = extractTable(tblDetailEl, null);

  if (!usulan.rows.length && !detail.rows.length) { alert('Tidak ada data untuk diekspor.'); return; }

  var workbook = new ExcelJS.Workbook();
  workbook.creator = 'Gudang Teknik';
  workbook.created = new Date();

  function buildSheet(name, title, tableData) {
    if (!tableData.rows.length) return;
    var sh = workbook.addWorksheet(name);
    sh.getCell('A1').value = title;
    sh.getCell('A1').font = { bold: true, size: 13 };
    sh.addTable({
      name: name.replace(/[^A-Za-z0-9_]/g, '_'),
      ref: 'A3',
      headerRow: true,
      style: { theme: 'TableStyleMedium9', showRowStripes: true },
      columns: tableData.header.map(function(h) { return { name: h || '-', filterButton: true }; }),
      rows: tableData.rows
    });
    tableData.header.forEach(function(h, idx) {
      sh.getColumn(idx + 1).width = (idx === 1 || idx === 2) ? 28 : 18;
    });
  }

  // Sheet paling atas/depan = Usulan Safety Stock, sesuai permintaan.
  buildSheet('Usulan Safety Stock', 'USULAN SAFETY STOCK — mengikuti diagram Material Paling Banyak Keluar', usulan);
  buildSheet('Detail Stok Material', 'DETAIL STOK PER MATERIAL (LENGKAP)', detail);

  workbook.xlsx.writeBuffer().then(function(buffer) {
    var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href     = url;
    a.download = 'laporan-stok-mutasi-<?= esc($dari) ?>-sd-<?= esc($sampai) ?>.xlsx';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }).catch(function(err) {
    console.error(err);
    alert('Gagal membuat file Excel, silakan coba lagi.');
  });
}

// Export PDF: print tab yang aktif
function exportPDF() {
  var tabLabel = {penerimaan:'Penerimaan',pengeluaran:'Pengeluaran',booking:'Booking',stok:'Stok & Mutasi'}[_currentTab] || _currentTab;
  var panel = document.getElementById('tab-' + _currentTab);
  if (!panel) { alert('Tidak ada data untuk di-print.'); return; }

  // Tab Stok & Mutasi sekarang punya 2 tabel (Usulan Safety Stock + Detail
  // Masuk/Keluar per material) — dua-duanya dicetak sebagai section terpisah.
  // Tab lain tetap 1 tabel seperti sebelumnya.
  var sections = [];
  if (_currentTab === 'stok') {
    var tblSafety = document.getElementById('tbody-safety-stock');
    var tblSafetyEl = tblSafety ? tblSafety.closest('table') : null;
    if (tblSafetyEl) {
      // Versi cetak cuma nampilin 6 kolom yang relevan di atas kertas:
      // Tanggal Mutasi, Kode SAP, Nama Material, Stok, Keluar/Masuk, Plant.
      // Kategori/Klasifikasi/Total Setahun/Safety Stock/Usulan/Aksi tetap ada
      // di layar, cuma tidak dicetak. Tabel detail per-material (~4000 baris)
      // juga tidak diikutkan — cukup rincian mutasinya saja.
      var keepIdx = [0, 1, 2, 5, 6, 7];
      var cloned = tblSafetyEl.cloneNode(true);
      cloned.querySelectorAll('tr').forEach(function(tr) {
        var cells = Array.prototype.slice.call(tr.children);
        for (var i = cells.length - 1; i >= 0; i--) {
          if (keepIdx.indexOf(i) === -1) tr.removeChild(cells[i]);
        }
      });
      sections.push({ title: 'Riwayat Mutasi Stok', table: cloned });
    }
  } else {
    var tbl = panel.querySelector('.lap-tbl');
    if (tbl) sections.push({ title: tabLabel, table: tbl });
  }

  if (!sections.length) { alert('Tidak ada tabel untuk di-print.'); return; }

  // Buat iframe tersembunyi — URL tetap /laporan, tidak buka about:blank
  var iframe = document.getElementById('_print_frame');
  if (!iframe) {
    iframe = document.createElement('iframe');
    iframe.id = '_print_frame';
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none';
    document.body.appendChild(iframe);
  }

  var bodyHtml = '<h2>Laporan ' + tabLabel + '</h2>' +
    '<p>Periode: <?= esc($dari) ?> s/d <?= esc($sampai) ?></p>';
  sections.forEach(function(sec, idx) {
    bodyHtml += (sections.length > 1 ? '<h3' + (idx > 0 ? ' style="margin-top:22px;page-break-before:auto"' : '') + '>' + sec.title + '</h3>' : '') +
      '<table>' + sec.table.innerHTML + '</table>';
  });

  var doc = iframe.contentDocument || iframe.contentWindow.document;
  doc.open();
  doc.write(
    '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Gudang Teknik — Laporan ' + tabLabel + '</title><style>' +
    'body{font-family:Arial,sans-serif;font-size:11px;color:#222;padding:20px;margin:0}' +
    'h2{font-size:14px;color:#1a2744;margin:0 0 2px;font-weight:700}' +
    'h3{font-size:12px;color:#1a2744;margin:0 0 8px;font-weight:700;border-bottom:1px solid #ddd;padding-bottom:4px}' +
    'p{font-size:10px;color:#888;margin:0 0 14px}' +
    'table{border-collapse:collapse;width:100%;margin-bottom:10px}' +
    'thead tr{background:#1a2744;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
    'th{color:#fff;padding:7px 10px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}' +
    'td{border-bottom:1px solid #eee;padding:6px 10px;vertical-align:top;font-size:11px}' +
    'tr:nth-child(even) td{background:#f8f8f8;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
    'ul{margin:0;padding:0;list-style:none}' +
    'ul li{display:flex;align-items:flex-start;gap:5px;padding:1px 0;font-size:11px}' +
    'ul li::before{content:"\u2022";color:#1a2744;font-weight:700;flex-shrink:0}' +
    'code{font-family:monospace;font-size:10px}' +
    '.lap-badge{display:inline-block;padding:1px 7px;border-radius:10px;font-size:9px;font-weight:700;border:1px solid #ccc}' +
    '.lap-badge-green{background:#ecfdf5;color:#065f46}.lap-badge-red{background:#fef2f2;color:#991b1b}' +
    '.lap-badge-blue{background:#eff6ff;color:#1d4ed8}.lap-badge-yellow{background:#fffbeb;color:#92400e}' +
    '.lap-badge-gray{background:#f3f4f6;color:#4b5563}' +
    '.lap-badge-fast{background:#fdecea;color:#c0392b}.lap-badge-medium{background:#fef3e0;color:#d97706}.lap-badge-slow{background:#e8f0fe;color:#2563eb}' +
    'body::before{content:"";position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-15deg);width:55%;height:55%;background:url("/img/sasa-bg.png") center/contain no-repeat;opacity:0.07;pointer-events:none;z-index:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
    'body>*{position:relative;z-index:1}' +
    '@page{margin:1.5cm;size:A4 landscape}' +
    '</style></head><body>' +
    bodyHtml +
    '</body></html>'
  );
  doc.close();
  setTimeout(function() { iframe.contentWindow.focus(); iframe.contentWindow.print(); }, 400);
}

// Aktifkan tab dari URL param
(function() {
  var t = new URLSearchParams(window.location.search).get('tab');
  if (t) {
    var btn = document.querySelector('.lap-tab[onclick*="\'' + t + '\'"]');
    if (btn) switchTab(t, btn);
  }
})();
</script>

<?= $this->endSection() ?>