<?php

namespace App\Models;

class RakModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── Peta Gudang: rak yang SUDAH punya minimal 1 material aktif, per zona ────
    public function getZonaGrid()
    {
        $rows = $this->db->query("
            SELECT r.id, r.kode_rak, r.zona,
                   COUNT(m.id) AS item_count,
                   SUM(CASE WHEN m.stok = 0 OR (m.safety_stock IS NOT NULL AND m.stok <= m.safety_stock) THEN 1 ELSE 0 END) AS kritis_count
            FROM rak r
            INNER JOIN materials m ON m.rak_id = r.id AND m.status = 'aktif'
            WHERE r.is_active = 1
            GROUP BY r.id, r.kode_rak, r.zona
            HAVING COUNT(m.id) > 0
            ORDER BY r.zona ASC, r.kode_rak ASC
        ")->getResultArray();

        $grouped = [];
        foreach ($rows as $r) {
            $zona = $r['zona'] !== null && $r['zona'] !== '' ? $r['zona'] : '-';
            if (!isset($grouped[$zona])) $grouped[$zona] = [];
            $grouped[$zona][] = [
                'id'         => (int)$r['id'],
                'kode_rak'   => $r['kode_rak'],
                'zona'       => $zona,
                'item_count' => (int)$r['item_count'],
                'status'     => ((int)$r['kritis_count'] > 0) ? 'kritis' : 'terisi',
                'type'       => 'rak',
            ];
        }

        // Kategori rak yang baru dibuat dan BELUM punya satupun lokasi rak
        // (belum pernah dipakai material) tetap ditampilkan sebagai kotak kosong.
        // NOTE: sebelumnya pakai "NOT IN (SELECT DISTINCT ...)" — diganti LEFT JOIN
        // + IS NULL karena jauh lebih cepat dan tetap MySQL-friendly saat kolom
        // kategori_id berisi NULL (NOT IN rawan false-negative kalau ada NULL,
        // dan performanya menurun drastis begitu tabel rak makin besar).
        $kategoriKosong = $this->db->query("
            SELECT rk.id, rk.kode_kategori, rk.zona
            FROM rak_kategori rk
            LEFT JOIN rak r ON r.kategori_id = rk.id
            WHERE rk.is_active = 1
              AND r.id IS NULL
            ORDER BY rk.zona ASC, rk.kode_kategori ASC
        ")->getResultArray();

        foreach ($kategoriKosong as $k) {
            $zona = $k['zona'] !== null && $k['zona'] !== '' ? $k['zona'] : '-';
            if (!isset($grouped[$zona])) $grouped[$zona] = [];
            $grouped[$zona][] = [
                'id'         => (int)$k['id'],
                'kode_rak'   => $k['kode_kategori'],
                'zona'       => $zona,
                'item_count' => 0,
                'status'     => 'kosong',
                'type'       => 'kategori',
            ];
        }

        ksort($grouped);
        return $grouped;
    }

    public function find($id)
    {
        return $this->db->table('rak')->where('id', $id)->get()->getRowArray();
    }

    public function findByKode($kode)
    {
        return $this->db->table('rak')->where('kode_rak', trim($kode))->get()->getRowArray();
    }

    // ── Detail satu rak + daftar material di dalamnya ───────────────────────────
    public function getDetail($id)
    {
        $rak = $this->find($id);
        if (!$rak) return null;

        $materials = $this->db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.stok, m.safety_stock,
                   CASE
                     WHEN m.stok = 0 THEN 'habis'
                     WHEN m.safety_stock IS NOT NULL AND m.stok <= m.safety_stock THEN 'kritis'
                     ELSE 'normal'
                   END AS kondisi_stok
            FROM materials m
            WHERE m.rak_id = ? AND m.status = 'aktif'
            ORDER BY m.nama_material ASC
        ", [$id])->getResultArray();

        $rak['materials']        = $materials;
        $rak['jumlah_material']  = count($materials);
        return $rak;
    }

    // ── Daftar zona unik yang sudah ada (untuk dropdown Edit Rak) ───────────────
    public function getZonaList()
    {
        $rows = $this->db->query("
            SELECT DISTINCT zona FROM rak WHERE is_active = 1 AND zona IS NOT NULL AND zona != '' ORDER BY zona ASC
        ")->getResultArray();
        return array_column($rows, 'zona');
    }

    // ── Update kode_rak / zona / keterangan sebuah lokasi rak ───────────────────
    public function updateRak($id, $data)
    {
        $rak = $this->find($id);
        if (!$rak) {
            return ['success' => false, 'message' => 'Rak tidak ditemukan'];
        }

        $kode = trim($data['kode_rak'] ?? $rak['kode_rak']);
        if ($kode === '') {
            return ['success' => false, 'message' => 'Kode rak wajib diisi'];
        }

        $dup = $this->findByKode($kode);
        if ($dup && (int)$dup['id'] !== (int)$id) {
            return ['success' => false, 'message' => 'Kode rak "' . $kode . '" sudah dipakai lokasi lain'];
        }

        $zona = trim($data['zona'] ?? '');
        if ($zona === '') $zona = strtok($kode, '.');

        $this->db->table('rak')->where('id', $id)->update([
            'kode_rak'   => $kode,
            'zona'       => $zona,
            'keterangan' => trim($data['keterangan'] ?? '') ?: null,
        ]);

        return ['success' => true, 'rak' => $this->find($id)];
    }

    // ── Material yang belum punya rak sama sekali ───────────────────────────────
    public function getUnassignedMaterials($search = '')
    {
        $conditions = ["m.status = 'aktif'", "m.rak_id IS NULL"];
        $binds = [];
        if ($search !== '') {
            $conditions[] = "(m.kode_sap LIKE ? OR m.nama_material LIKE ?)";
            $s = '%' . $search . '%';
            $binds = [$s, $s];
        }
        $where = implode(' AND ', $conditions);

        return $this->db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, k.nama_kategori
            FROM materials m
            LEFT JOIN kategoris k ON k.id = m.kategori_id
            WHERE {$where}
            ORDER BY m.nama_material ASC
        ", $binds)->getResultArray();
    }
}