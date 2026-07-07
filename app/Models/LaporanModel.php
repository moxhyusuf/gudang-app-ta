<?php

namespace App\Models;

class LaporanModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PENERIMAAN
    // ══════════════════════════════════════════════════════════════════════════

    public function getPenerimaan(array $f): array
    {
        $sql = "
            SELECT
                ph.id,
                ph.no_surat_penerimaan,
                ph.tanggal_terima                          AS tanggal,
                s.nama_supplier                            AS vendor,
                COUNT(pd.id)                               AS jml_item,
                COALESCE(SUM(pd.jumlah_terima), 0)         AS total_unit,
                u.nama                                     AS petugas,
                GROUP_CONCAT(CONCAT(m.nama_material, ' (', pd.jumlah_terima, ' ', m.satuan, ')') ORDER BY m.nama_material SEPARATOR '||') AS detail_item
            FROM penerimaan_header ph
            LEFT JOIN suppliers         s  ON s.id  = ph.supplier_id
            LEFT JOIN users             u  ON u.id  = ph.user_id
            LEFT JOIN penerimaan_detail pd ON pd.header_id = ph.id
            LEFT JOIN materials         m  ON m.id  = pd.material_id
            WHERE 1=1
        ";
        $b = [];
        if (!empty($f['dari']))    { $sql .= " AND ph.tanggal_terima >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai'])) { $sql .= " AND ph.tanggal_terima <= ?"; $b[] = $f['sampai']; }

        $sql .= " GROUP BY ph.id, ph.no_surat_penerimaan, ph.tanggal_terima,
                           s.nama_supplier, u.nama
                  ORDER BY ph.tanggal_terima DESC, ph.id DESC
                  LIMIT 200";

        return $this->db->query($sql, $b)->getResultArray();
    }

    public function countPenerimaan(array $f): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM penerimaan_header ph WHERE 1=1";
        $b = [];
        if (!empty($f['dari']))    { $sql .= " AND ph.tanggal_terima >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai'])) { $sql .= " AND ph.tanggal_terima <= ?"; $b[] = $f['sampai']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    public function sumUnitPenerimaan(array $f): int
    {
        $sql = "
            SELECT COALESCE(SUM(pd.jumlah_terima), 0) AS total
            FROM penerimaan_detail pd
            JOIN penerimaan_header ph ON ph.id = pd.header_id
            WHERE 1=1
        ";
        $b = [];
        if (!empty($f['dari']))    { $sql .= " AND ph.tanggal_terima >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai'])) { $sql .= " AND ph.tanggal_terima <= ?"; $b[] = $f['sampai']; }
        return (int)($this->db->query($sql, $b)->getRow()->total ?? 0);
    }

    public function countMaterialBaru(array $f): int
    {
        $sql = "
            SELECT COUNT(DISTINCT pd.material_id) AS cnt
            FROM penerimaan_detail pd
            JOIN penerimaan_header ph ON ph.id = pd.header_id
            WHERE pd.is_material_baru = 1
        ";
        $b = [];
        if (!empty($f['dari']))    { $sql .= " AND ph.tanggal_terima >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai'])) { $sql .= " AND ph.tanggal_terima <= ?"; $b[] = $f['sampai']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PENGELUARAN
    // ══════════════════════════════════════════════════════════════════════════

    public function getPengeluaran(array $f): array
    {
        $sql = "
            SELECT
                bh.id,
                bh.no_bon,
                bh.tanggal_bon                             AS tanggal,
                p.nama_plant                               AS plant,
                bh.nama_pengambil,
                COUNT(bd.id)                               AS jml_item,
                COALESCE(SUM(bd.jumlah_keluar), 0)         AS total_unit,
                bh.keperluan,
                u.nama                                     AS petugas,
                GROUP_CONCAT(CONCAT(m.nama_material, ' (', bd.jumlah_keluar, ' ', m.satuan, ')') ORDER BY m.nama_material SEPARATOR '||') AS detail_item
            FROM bon_header bh
            LEFT JOIN plants     p  ON p.id  = bh.plant_id
            LEFT JOIN users      u  ON u.id  = bh.user_id
            LEFT JOIN bon_detail bd ON bd.header_id = bh.id
            LEFT JOIN materials  m  ON m.id  = bd.material_id
            WHERE 1=1
        ";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bh.tanggal_bon >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bh.tanggal_bon <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bh.plant_id = ?";    $b[] = $f['plant_id']; }

        $sql .= " GROUP BY bh.id, bh.no_bon, bh.tanggal_bon,
                           p.nama_plant, bh.nama_pengambil, bh.keperluan, u.nama
                  ORDER BY bh.tanggal_bon DESC, bh.id DESC
                  LIMIT 200";

        return $this->db->query($sql, $b)->getResultArray();
    }

    public function countPengeluaran(array $f): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM bon_header bh WHERE 1=1";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bh.tanggal_bon >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bh.tanggal_bon <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bh.plant_id = ?";    $b[] = $f['plant_id']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    public function sumUnitPengeluaran(array $f): int
    {
        $sql = "
            SELECT COALESCE(SUM(bd.jumlah_keluar), 0) AS total
            FROM bon_detail bd
            JOIN bon_header bh ON bh.id = bd.header_id
            WHERE 1=1
        ";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bh.tanggal_bon >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bh.tanggal_bon <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bh.plant_id = ?";    $b[] = $f['plant_id']; }
        return (int)($this->db->query($sql, $b)->getRow()->total ?? 0);
    }

    public function countPlantPengeluaran(array $f): int
    {
        $sql = "SELECT COUNT(DISTINCT bh.plant_id) AS cnt FROM bon_header bh WHERE 1=1";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bh.tanggal_bon >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bh.tanggal_bon <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bh.plant_id = ?";    $b[] = $f['plant_id']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  BOOKING
    // ══════════════════════════════════════════════════════════════════════════

    public function getBooking(array $f): array
    {
        $sql = "
            SELECT
                bk.id,
                bk.no_booking,
                bk.tanggal_booking                         AS tanggal,
                bk.tanggal_butuh,
                bk.jenis,
                p.nama_plant                               AS plant,
                COUNT(bd.id)                               AS jml_item,
                COALESCE(SUM(bd.jumlah_booking), 0)        AS total_unit,
                bk.status,
                bk.catatan                                  AS catatan,
                u.nama                                     AS pemohon,
                GROUP_CONCAT(CONCAT(m.nama_material, ' (', bd.jumlah_booking, ' ', m.satuan, ')') ORDER BY m.nama_material SEPARATOR '||') AS detail_item
            FROM booking_header bk
            LEFT JOIN plants          p  ON p.id  = bk.plant_id
            LEFT JOIN users           u  ON u.id  = bk.user_id
            LEFT JOIN booking_detail  bd ON bd.header_id = bk.id
            LEFT JOIN materials       m  ON m.id  = bd.material_id
            WHERE 1=1
        ";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bk.tanggal_booking >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bk.tanggal_booking <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bk.plant_id = ?";         $b[] = $f['plant_id']; }

        $sql .= " GROUP BY bk.id, bk.no_booking, bk.tanggal_booking, bk.tanggal_butuh,
                           bk.jenis, p.nama_plant, bk.status, bk.catatan, u.nama
                  ORDER BY bk.tanggal_booking DESC, bk.id DESC
                  LIMIT 200";

        return $this->db->query($sql, $b)->getResultArray();
    }

    public function countBooking(array $f): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM booking_header bk WHERE 1=1";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bk.tanggal_booking >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bk.tanggal_booking <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bk.plant_id = ?";         $b[] = $f['plant_id']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    public function countBookingSelesai(array $f): int
    {
        // FIX: pakai WHERE 1=1 agar AND berikutnya tidak patah
        $sql = "SELECT COUNT(*) AS cnt FROM booking_header bk WHERE 1=1 AND bk.status = 'selesai'";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bk.tanggal_booking >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bk.tanggal_booking <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bk.plant_id = ?";         $b[] = $f['plant_id']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    public function countBookingPending(array $f): int
    {
        // FIX: pakai WHERE 1=1 agar AND berikutnya tidak patah
        $sql = "SELECT COUNT(*) AS cnt FROM booking_header bk WHERE 1=1 AND bk.status IN ('pending','disetujui')";
        $b = [];
        if (!empty($f['dari']))     { $sql .= " AND bk.tanggal_booking >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND bk.tanggal_booking <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bk.plant_id = ?";         $b[] = $f['plant_id']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  STOK & MUTASI
    // ══════════════════════════════════════════════════════════════════════════

    public function getStokMutasi(array $f): array
    {
        // FIX: subquery masuk/keluar sekarang ikut filter tanggal
        $dateBind = [];
        $dateWhere = "1=1";
        if (!empty($f['dari']))    { $dateWhere .= " AND DATE(ms.created_at) >= ?"; $dateBind[] = $f['dari']; }
        if (!empty($f['sampai'])) { $dateWhere .= " AND DATE(ms.created_at) <= ?"; $dateBind[] = $f['sampai']; }

        $sql = "
            SELECT
                m.id,
                m.kode_sap,
                m.nama_material,
                m.satuan,
                m.stok,
                m.stok_booking,
                (m.stok - m.stok_booking)                  AS stok_tersedia,
                r.kode_rak,
                r.zona,
                k.nama_kategori                            AS kategori,
                COALESCE(mi.total_masuk,  0)               AS total_masuk,
                COALESCE(mo.total_keluar, 0)               AS total_keluar
            FROM materials m
            LEFT JOIN rak       r ON r.id = m.rak_id
            LEFT JOIN kategoris k ON k.id = m.kategori_id
            LEFT JOIN (
                SELECT ms.material_id, SUM(ms.jumlah) AS total_masuk
                FROM mutasi_stok ms
                WHERE ms.jenis = 'masuk' AND $dateWhere
                GROUP BY ms.material_id
            ) mi ON mi.material_id = m.id
            LEFT JOIN (
                SELECT ms.material_id, ABS(SUM(ms.jumlah)) AS total_keluar
                FROM mutasi_stok ms
                WHERE ms.jenis = 'keluar' AND $dateWhere
                GROUP BY ms.material_id
            ) mo ON mo.material_id = m.id
            WHERE m.status = 'aktif'
        ";

        // Binding: dateBind dipakai 2x (untuk subquery masuk dan keluar)
        $b = array_merge($dateBind, $dateBind);

        if (!empty($f['kategori'])) { $sql .= " AND m.kategori_id = ?"; $b[] = $f['kategori']; }
        if (!empty($f['plant_id'])) { $sql .= " AND m.plant_id = ?";    $b[] = $f['plant_id']; }

        $sql .= " ORDER BY m.nama_material ASC LIMIT 500";

        return $this->db->query($sql, $b)->getResultArray();
    }

    public function countMaterial(array $f): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM materials m WHERE m.status = 'aktif'";
        $b = [];
        if (!empty($f['plant_id'])) { $sql .= " AND m.plant_id = ?";    $b[] = $f['plant_id']; }
        if (!empty($f['kategori'])) { $sql .= " AND m.kategori_id = ?"; $b[] = $f['kategori']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    public function sumStokTotal(array $f): int
    {
        $sql = "SELECT COALESCE(SUM(m.stok), 0) AS total FROM materials m WHERE m.status = 'aktif'";
        $b = [];
        if (!empty($f['plant_id'])) { $sql .= " AND m.plant_id = ?";    $b[] = $f['plant_id']; }
        if (!empty($f['kategori'])) { $sql .= " AND m.kategori_id = ?"; $b[] = $f['kategori']; }
        return (int)($this->db->query($sql, $b)->getRow()->total ?? 0);
    }

    public function countMutasi(array $f): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM mutasi_stok ms WHERE 1=1";
        $b = [];
        if (!empty($f['dari']))    { $sql .= " AND DATE(ms.created_at) >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai'])) { $sql .= " AND DATE(ms.created_at) <= ?"; $b[] = $f['sampai']; }
        return (int)($this->db->query($sql, $b)->getRow()->cnt ?? 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  ANALISA PERGERAKAN MATERIAL & USULAN SAFETY STOCK
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Top material paling banyak keluar BULAN INI (kalender bulan berjalan).
     * Dipakai untuk diagram "Material Terlaris Bulan Ini".
     */
    public function getPengeluaranBulanIni(int $limit = 10): array
    {
        return $this->db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan,
                   SUM(ABS(ms.jumlah)) AS total_keluar
            FROM mutasi_stok ms
            JOIN materials m ON m.id = ms.material_id
            WHERE ms.jenis = 'keluar'
              AND YEAR(ms.created_at)  = YEAR(CURDATE())
              AND MONTH(ms.created_at) = MONTH(CURDATE())
            GROUP BY m.id, m.kode_sap, m.nama_material, m.satuan
            ORDER BY total_keluar DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    /**
     * Detail transaksi mutasi (masuk & keluar) untuk sekumpulan material —
     * dipakai sebagai baris tabel "Usulan Safety Stock" (level transaksi,
     * bukan agregat per material). Plant hanya terisi untuk mutasi jenis
     * 'keluar' yang berasal dari bon (referensi_tipe = 'bon'); mutasi
     * 'masuk' dari penerimaan tidak punya plant sehingga bernilai NULL.
     */
    public function getMutasiUntukUsulan(array $materialIds, array $f): array
    {
        if (empty($materialIds)) { return []; }

        $placeholders = implode(',', array_fill(0, count($materialIds), '?'));
        $sql = "
            SELECT ms.material_id, ms.created_at AS tanggal_mutasi, ms.jenis, ms.jumlah, ms.stok_sesudah,
                   CASE WHEN ms.referensi_tipe = 'bon' THEN p.nama_plant ELSE NULL END AS plant
            FROM mutasi_stok ms
            LEFT JOIN bon_header bh ON bh.id = ms.referensi_id AND ms.referensi_tipe = 'bon'
            LEFT JOIN plants     p  ON p.id  = bh.plant_id
            WHERE ms.material_id IN ($placeholders)
        ";
        $b = $materialIds;
        if (!empty($f['dari']))     { $sql .= " AND DATE(ms.created_at) >= ?"; $b[] = $f['dari']; }
        if (!empty($f['sampai']))   { $sql .= " AND DATE(ms.created_at) <= ?"; $b[] = $f['sampai']; }
        if (!empty($f['plant_id'])) { $sql .= " AND bh.plant_id = ?";          $b[] = $f['plant_id']; }

        $sql .= " ORDER BY ms.created_at DESC LIMIT 500";

        return $this->db->query($sql, $b)->getResultArray();
    }

    /**
     * Rekap pemakaian 12 bulan terakhir (rolling, termasuk bulan berjalan) per
     * material aktif — dipakai untuk (1) diagram klasifikasi pergerakan material
     * dan (2) usulan safety stock otomatis.
     *
     * METODOLOGI (didokumentasikan supaya gampang di-review/diubah nanti):
     *
     *  - Window waktu : 12 bulan kalender terakhir termasuk bulan berjalan.
     *  - Klasifikasi  : berdasarkan peringkat total pemakaian setahun, di antara
     *                   material yang MEMANG bergerak (total keluar > 0):
     *                     Fast Moving    = 20% teratas
     *                     Medium Moving  = 30% berikutnya
     *                     Slow Moving    = 50% sisanya
     *                   Material aktif yang sama sekali tidak ada transaksi keluar
     *                   dalam 12 bulan terakhir dikelompokkan terpisah sebagai
     *                   "Tidak Bergerak" (bukan Slow Moving, supaya tidak
     *                   tercampur dengan yang sebenarnya masih jalan tipis-tipis).
     *  - Safety stock : dihitung dari VARIABILITAS pemakaian bulanan, bukan cuma
     *                   rata-rata — supaya material yang naik-turunnya tajam
     *                   otomatis dikasih buffer lebih besar walau rata-ratanya
     *                   sama dengan material yang stabil.
     *                     usulan = CEIL( Z * std_dev_bulanan )
     *                     Z = 1.65  (setara service level ~95%, standar umum
     *                                dipakai untuk safety stock non-kritis)
     */
    public function getRekapTahunan(?int $tahun = null): array
    {
        if ($tahun) {
            // Tahun kalender penuh (Jan–Des) sesuai pilihan filter
            $bulanList = [];
            for ($m = 1; $m <= 12; $m++) { $bulanList[] = sprintf('%04d-%02d', $tahun, $m); }
            $awal  = $tahun . '-01-01';
            $akhir = $tahun . '-12-31 23:59:59';
        } else {
            // Default: 12 bulan terakhir (rolling) dari bulan ini
            $bulanList = [];
            for ($i = 11; $i >= 0; $i--) {
                $bulanList[] = date('Y-m', strtotime("-{$i} months"));
            }
            $awal  = $bulanList[0] . '-01';
            $akhir = null;
        }

        $sql = "
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.safety_stock,
                   k.nama_kategori                      AS kategori,
                   DATE_FORMAT(ms.created_at, '%Y-%m')  AS bulan,
                   SUM(ABS(ms.jumlah))                  AS qty
            FROM mutasi_stok ms
            JOIN materials m       ON m.id = ms.material_id
            LEFT JOIN kategoris k  ON k.id = m.kategori_id
            WHERE ms.jenis = 'keluar'
              AND m.status = 'aktif'
              AND ms.created_at >= ?
        " . ($akhir ? " AND ms.created_at <= ?" : "") . "
            GROUP BY m.id, m.kode_sap, m.nama_material, m.satuan, m.safety_stock,
                     k.nama_kategori, bulan
        ";
        $binds = $akhir ? [$awal, $akhir] : [$awal];
        $rows  = $this->db->query($sql, $binds)->getResultArray();

        // Susun per material: 12 slot bulanan (default 0 kalau bulan itu tidak ada transaksi)
        $materials = [];
        foreach ($rows as $r) {
            $id = $r['id'];
            if (!isset($materials[$id])) {
                $materials[$id] = [
                    'id'                    => (int)$id,
                    'kode_sap'              => $r['kode_sap'],
                    'nama_material'         => $r['nama_material'],
                    'satuan'                => $r['satuan'],
                    'kategori'              => $r['kategori'],
                    'safety_stock_saat_ini' => $r['safety_stock'] !== null ? (int)$r['safety_stock'] : null,
                    'bulanan'               => array_fill_keys($bulanList, 0),
                ];
            }
            $materials[$id]['bulanan'][$r['bulan']] = (int)$r['qty'];
        }

        // Hitung total, rata-rata, standar deviasi, dan usulan safety stock
        $Z = 1.65; // service level ~95%
        foreach ($materials as &$mat) {
            $vals  = array_values($mat['bulanan']);
            $n     = count($vals);
            $total = array_sum($vals);
            $avg   = $n > 0 ? $total / $n : 0;

            $variance = 0;
            foreach ($vals as $v) { $variance += ($v - $avg) ** 2; }
            $stddev = $n > 1 ? sqrt($variance / ($n - 1)) : 0;

            $mat['total_setahun']       = $total;
            $mat['rata2_bulanan']       = round($avg, 1);
            $mat['stddev_bulanan']      = round($stddev, 1);
            $mat['safety_stock_usulan'] = (int) ceil($Z * $stddev);
            $mat['bulan_aktif']         = count(array_filter($vals, fn($v) => $v > 0));
        }
        unset($mat);

        // Pisahkan yang bergerak vs tidak bergerak, lalu klasifikasikan yang bergerak
        $bergerak      = array_values(array_filter($materials, fn($m) => $m['total_setahun'] > 0));
        $tidakBergerak = array_values(array_filter($materials, fn($m) => $m['total_setahun'] == 0));

        usort($bergerak, fn($a, $b) => $b['total_setahun'] <=> $a['total_setahun']);

        $countBergerak = count($bergerak);
        $fastCut       = (int) ceil($countBergerak * 0.2);
        $mediumCut     = (int) ceil($countBergerak * 0.5); // 20% fast + 30% medium

        foreach ($bergerak as $i => &$m) {
            if ($i < $fastCut)       { $m['klasifikasi'] = 'fast'; }
            elseif ($i < $mediumCut) { $m['klasifikasi'] = 'medium'; }
            else                     { $m['klasifikasi'] = 'slow'; }
        }
        unset($m);

        foreach ($tidakBergerak as &$m) { $m['klasifikasi'] = 'none'; }
        unset($m);

        usort($tidakBergerak, fn($a, $b) => strcmp($a['nama_material'], $b['nama_material']));

        return [
            'bulan_labels'   => $bulanList,
            'bergerak'       => $bergerak,
            'tidak_bergerak' => $tidakBergerak,
        ];
    }
}