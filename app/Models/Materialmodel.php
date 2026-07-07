<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table         = 'materials';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'kode_sap', 'batch', 'material_group', 'is_tabung',
        'nama_material', 'kategori_id', 'rak_id', 'satuan',
        'stok', 'stok_booking', 'safety_stock', 'plant_id',
        'status', 'keterangan',
    ];

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function countAktif()
    {
        return $this->where('status', 'aktif')->countAllResults();
    }

    public function countKritis()
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM materials
            WHERE status = 'aktif'
              AND (stok = 0 OR (safety_stock IS NOT NULL AND stok <= safety_stock))
        ")->getRow()->cnt;
    }

    public function getStokKritisList($limit = 5)
    {
        return $this->db->query("
            SELECT m.*,
                   CASE
                     WHEN m.stok = 0 THEN 'habis'
                     WHEN m.safety_stock IS NOT NULL AND m.stok <= m.safety_stock THEN 'kritis'
                     ELSE 'normal'
                   END AS kondisi_stok
            FROM materials m
            WHERE m.status = 'aktif'
              AND (m.stok = 0 OR (m.safety_stock IS NOT NULL AND m.stok <= m.safety_stock))
            ORDER BY m.stok ASC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    // ─── Monitoring ──────────────────────────────────────────────────────────

    private function buildMonitoringQuery($search = '', $batch = '', $kondisi = '', $isPlant = false)
    {
        // Raw query untuk hindari ambiguitas kolom is_tabung antara view dan tabel materials
        $conditions = ["v.status = 'aktif'"];
        $binds      = [];

        if ($search !== '') {
            if ($isPlant) {
                $conditions[] = "(v.kode_sap LIKE ? OR v.nama_material LIKE ? OR m.material_group LIKE ?)";
            } else {
                $conditions[] = "(v.kode_sap LIKE ? OR v.nama_material LIKE ? OR m.material_group LIKE ? OR v.kode_rak LIKE ?)";
            }
            $s     = '%' . $search . '%';
            $binds = $isPlant ? [$s, $s, $s] : [$s, $s, $s, $s];
        }

        if ($batch === '__NO_SAP__') {
            $conditions[] = "(v.kode_sap IS NULL OR TRIM(v.kode_sap) = '')";
        } elseif ($batch !== '') {
            $conditions[] = "m.batch = ?";
            $binds[]      = $batch;
        }

        if ($kondisi !== '') {
            $conditions[] = "v.kondisi_stok = ?";
            $binds[]      = $kondisi;
        }

        $where = implode(' AND ', $conditions);

        return [
            'sql'   => "SELECT v.id, v.kode_sap, v.nama_material, v.nama_kategori,
                               v.kode_rak, v.zona, v.satuan, v.stok, v.stok_booking,
                               v.stok_tersedia, v.safety_stock, v.kondisi_stok,
                               v.status, v.plant_pemilik, v.keterangan,
                               v.is_tabung,
                               m.batch, m.material_group, m.kategori_id
                        FROM v_stok_material v
                        LEFT JOIN materials m ON m.id = v.id
                        WHERE {$where}
                        ORDER BY v.nama_material ASC",
            'binds' => $binds,
        ];
    }

    public function countMonitoring($search = '', $batch = '', $kondisi = '', $isPlant = false)
    {
        $q      = $this->buildMonitoringQuery($search, $batch, $kondisi, $isPlant);
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM ({$q['sql']}) sub", $q['binds']);
        return $result->getRow()->cnt;
    }

    public function getMonitoringPaginated($search = '', $batch = '', $kondisi = '', $isPlant = false, $limit = 100, $offset = 0)
    {
        $q   = $this->buildMonitoringQuery($search, $batch, $kondisi, $isPlant);
        $sql = $q['sql'] . " LIMIT {$limit} OFFSET {$offset}";
        return $this->db->query($sql, $q['binds'])->getResultArray();
    }

    // ─── Histori Mutasi ───────────────────────────────────────────────────────

    public function getHistoriMutasi($materialId, $limit = 30)
    {
        return $this->db->query("
            SELECT ms.*, u.nama AS nama_user
            FROM mutasi_stok ms
            LEFT JOIN users u ON u.id = ms.user_id
            WHERE ms.material_id = ?
            ORDER BY ms.created_at DESC
            LIMIT ?
        ", [$materialId, $limit])->getResultArray();
    }
}