<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use App\Models\MaterialModel;

class Laporan extends BaseController
{
    private array $bulanFull = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',    '04' => 'April',
        '05' => 'Mei',     '06' => 'Juni',     '07' => 'Juli',     '08' => 'Agustus',
        '09' => 'September','10' => 'Oktober', '11' => 'November', '12' => 'Desember',
    ];

    private array $bulanShort = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
        '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
        '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
    ];

    public function index()
    {
        $laporanModel = new LaporanModel();
        $db           = \Config\Database::connect();

        $dari     = $this->request->getGet('dari')     ?? date('Y-m-01');
        $sampai   = $this->request->getGet('sampai')   ?? date('Y-m-d');
        $plant_id = $this->request->getGet('plant_id') ?? '';
        $kategori = $this->request->getGet('kategori') ?? '';

        $filter = compact('dari', 'sampai', 'plant_id', 'kategori');

        // ── Filter tahun khusus utk card "Klasifikasi Pergerakan Material" ──
        // Kosong = default (12 bulan terakhir, rolling). Diisi = tahun kalender penuh Jan–Des.
        $tahunKlasifikasiRaw = $this->request->getGet('tahun_klasifikasi');
        $tahunKlasifikasi    = ($tahunKlasifikasiRaw !== null && $tahunKlasifikasiRaw !== '') ? (int) $tahunKlasifikasiRaw : null;
        $tahunSekarang       = (int) date('Y');
        $tahunOptions        = range($tahunSekarang, $tahunSekarang - 4);

        // ── Analisa pergerakan material 12 bulan terakhir (untuk diagram + usulan safety stock) ──
        $rekap        = $laporanModel->getRekapTahunan($tahunKlasifikasi);
        $rekapSemua   = array_merge($rekap['bergerak'], $rekap['tidak_bergerak']);
        $countFast    = count(array_filter($rekap['bergerak'], fn($m) => $m['klasifikasi'] === 'fast'));
        $countMedium  = count(array_filter($rekap['bergerak'], fn($m) => $m['klasifikasi'] === 'medium'));
        $countSlow    = count(array_filter($rekap['bergerak'], fn($m) => $m['klasifikasi'] === 'slow'));

        $bulanLabelsShort = array_map(function ($ym) {
            [$y, $m] = explode('-', $ym);
            return $this->bulanShort[$m] . ' ' . substr($y, 2);
        }, $rekap['bulan_labels']);

        // ── Top material keluar PER BULAN (12 slide), sumber & format sama
        //    dengan diagram "Material Paling Banyak Keluar" di atasnya ──
        $bulanTopMaterial = [];
        foreach ($rekap['bulan_labels'] as $ym) {
            [$y, $m] = explode('-', $ym);
            $items = [];
            foreach ($rekap['bergerak'] as $mat) {
                $qty = $mat['bulanan'][$ym] ?? 0;
                if ($qty > 0) {
                    $items[] = [
                        'id'                    => $mat['id'],
                        'kode_sap'              => $mat['kode_sap'],
                        'nama_material'         => $mat['nama_material'],
                        'kategori'              => $mat['kategori'],
                        'klasifikasi'           => $mat['klasifikasi'],
                        'satuan'                => $mat['satuan'],
                        'qty'                   => $qty,
                        'safety_stock_saat_ini' => $mat['safety_stock_saat_ini'],
                        'safety_stock_usulan'   => $mat['safety_stock_usulan'],
                    ];
                }
            }
            usort($items, fn($a, $b) => $b['qty'] <=> $a['qty']);
            $bulanTopMaterial[] = [
                'ym'          => $ym,
                'label_full'  => $this->bulanFull[$m] . ' ' . $y,
                'label_short' => $this->bulanShort[$m] . ' ' . substr($y, 2),
                'items'       => array_slice($items, 0, 10),
            ];
        }

        // ── Tabel Usulan Safety Stock: daftar & urutan sekarang mengikuti
        //    ranking diagram "Material Paling Banyak Keluar" BULAN INI —
        //    rumus rata²/std-dev/usulan tetap dari analisa 12 bulan (tidak
        //    berubah), cuma cakupan & urutan barisnya yang mengikuti diagram
        //    itu. Kalau bulan berjalan belum ada transaksi keluar sama
        //    sekali, fallback ke daftar analisa 12 bulan penuh supaya tabel
        //    tidak kosong. ──
        $stokBulanIni = $laporanModel->getPengeluaranBulanIni(10);
        $rekapById    = [];
        foreach ($rekapSemua as $mat) { $rekapById[$mat['id']] = $mat; }
        $rekapUsulan = [];
        foreach ($stokBulanIni as $s) {
            if (isset($rekapById[$s['id']])) { $rekapUsulan[] = $rekapById[$s['id']]; }
        }
        if (empty($rekapUsulan)) { $rekapUsulan = $rekapSemua; }

        // ── Ubah tabel usulan jadi level TRANSAKSI MUTASI: setiap baris = satu
        //    transaksi masuk/keluar, dilengkapi info agregat material (klasifikasi,
        //    total setahun, safety stock, usulan) yang sama untuk semua transaksi
        //    material tsb. Cakupan material tetap sama seperti $rekapUsulan di atas. ──
        $materialIdsUsulan = array_column($rekapUsulan, 'id');
        $mutasiUsulanRaw    = $laporanModel->getMutasiUntukUsulan($materialIdsUsulan, $filter);

        $usulanRows = [];
        foreach ($mutasiUsulanRaw as $row) {
            $mat = $rekapById[$row['material_id']] ?? null;
            if (!$mat) { continue; }
            $usulanRows[] = [
                'material_id'           => (int) $row['material_id'],
                'tanggal_mutasi'        => $row['tanggal_mutasi'],
                'kode_sap'              => $mat['kode_sap'],
                'nama_material'         => $mat['nama_material'],
                'kategori'              => $mat['kategori'],
                'klasifikasi'           => $mat['klasifikasi'],
                'stok'                  => (int) $row['stok_sesudah'],
                'jenis'                 => $row['jenis'],
                'jumlah'                => (int) $row['jumlah'],
                'plant'                 => $row['plant'],
                'satuan'                => $mat['satuan'],
                'total_setahun'         => $mat['total_setahun'],
                'safety_stock_saat_ini' => $mat['safety_stock_saat_ini'],
                'safety_stock_usulan'   => $mat['safety_stock_usulan'],
            ];
        }

        $data = [
            'title'   => 'Laporan',
            'role'    => session()->get('role'),
            'nama'    => session()->get('nama'),
            'filter'  => $filter,
            'plants'  => $db->table('plants')->where('is_active', 1)->orderBy('nama_plant')->get()->getResultArray(),

            'penerimaan_list'     => $laporanModel->getPenerimaan($filter),
            'penerimaan_total'    => $laporanModel->countPenerimaan($filter),
            'penerimaan_unit'     => $laporanModel->sumUnitPenerimaan($filter),
            'penerimaan_matbaru'  => $laporanModel->countMaterialBaru($filter),

            'pengeluaran_list'    => $laporanModel->getPengeluaran($filter),
            'pengeluaran_total'   => $laporanModel->countPengeluaran($filter),
            'pengeluaran_unit'    => $laporanModel->sumUnitPengeluaran($filter),
            'pengeluaran_plant'   => $laporanModel->countPlantPengeluaran($filter),

            'booking_list'        => $laporanModel->getBooking($filter),
            'booking_total'       => $laporanModel->countBooking($filter),
            'booking_selesai'     => $laporanModel->countBookingSelesai($filter),
            'booking_pending'     => $laporanModel->countBookingPending($filter),

            'stok_list'           => $laporanModel->getStokMutasi($filter),
            'stok_total_material' => $laporanModel->countMaterial($filter),
            'stok_total_unit'     => $laporanModel->sumStokTotal($filter),
            'stok_mutasi'         => $laporanModel->countMutasi($filter),

            // ── Data baru: analisa pergerakan material & usulan safety stock ──
            'bulan_ini_label'     => $this->bulanFull[date('m')] . ' ' . date('Y'),
            'stok_bulan_ini'      => $stokBulanIni,
            'rekap_bergerak'      => $rekap['bergerak'],
            'rekap_semua'         => $rekapUsulan,
            'usulan_rows'         => $usulanRows,
            'rekap_bulan_labels'  => $bulanLabelsShort,
            'bulan_top_material'  => $bulanTopMaterial,
            'tahun_klasifikasi'   => $tahunKlasifikasi,
            'tahun_options'       => $tahunOptions,
            'count_fast'          => $countFast,
            'count_medium'        => $countMedium,
            'count_slow'          => $countSlow,
        ];

        return view('laporan/index', $data);
    }

    /**
     * Export data sebagai file CSV — dipanggil via window.location (bukan AJAX/fetch)
     * URL: GET /laporan/export-data?dari=...&sampai=...&plant_id=...&tab=...&format=csv
     */
    public function exportData()
    {
        $laporanModel = new LaporanModel();

        $filter = [
            'dari'     => $this->request->getGet('dari')     ?? date('Y-m-01'),
            'sampai'   => $this->request->getGet('sampai')   ?? date('Y-m-d'),
            'plant_id' => $this->request->getGet('plant_id') ?? '',
            'kategori' => $this->request->getGet('kategori') ?? '',
        ];
        $tab = $this->request->getGet('tab') ?? 'penerimaan';

        switch ($tab) {
            case 'pengeluaran': $rows = $laporanModel->getPengeluaran($filter);  break;
            case 'booking':     $rows = $laporanModel->getBooking($filter);       break;
            case 'stok':        $rows = $laporanModel->getStokMutasi($filter);    break;
            default:            $rows = $laporanModel->getPenerimaan($filter);    break;
        }

        if (empty($rows)) {
            // Redirect balik dengan pesan jika tidak ada data
            return redirect()->to('/laporan?' . http_build_query($filter) . '&tab=' . $tab)
                             ->with('error', 'Tidak ada data untuk periode yang dipilih.');
        }

        // Buat CSV
        $filename = 'laporan-' . $tab . '-' . $filter['dari'] . '-sd-' . $filter['sampai'] . '.csv';
        $keys     = array_keys($rows[0]);

        $csv  = "\xEF\xBB\xBF"; // BOM utf-8 supaya Excel bisa baca huruf Indonesia
        $csv .= implode(',', array_map(fn($k) => '"' . strtoupper($k) . '"', $keys)) . "\n";

        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function ($v) {
                $v = $v ?? '';
                return '"' . str_replace('"', '""', $v) . '"';
            }, $row)) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($csv);
    }

    /**
     * Terapkan usulan safety stock ke satu material (dipanggil admin per baris,
     * tombol "Terapkan" di tabel Usulan Safety Stock — tidak ada penerapan massal
     * otomatis, sesuai keputusan: admin yang review & klik satu-satu).
     * POST /laporan/terapkan-safety-stock  (material_id, safety_stock)
     */
    public function terapkanSafetyStock()
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false, 'message' => 'Method tidak diizinkan',
            ]);
        }

        $id    = $this->request->getPost('material_id');
        $value = $this->request->getPost('safety_stock');

        if (! $id || $value === null || $value === '' || ! is_numeric($value) || (int) $value < 0) {
            return $this->response->setJSON([
                'success' => false, 'message' => 'Data tidak valid',
            ]);
        }

        $materialModel = new MaterialModel();
        $material      = $materialModel->find((int) $id);

        if (! $material) {
            return $this->response->setJSON([
                'success' => false, 'message' => 'Material tidak ditemukan',
            ]);
        }

        $materialModel->update((int) $id, ['safety_stock' => (int) $value]);

        return $this->response->setJSON([
            'success'       => true,
            'material_id'   => (int) $id,
            'safety_stock'  => (int) $value,
        ]);
    }
}