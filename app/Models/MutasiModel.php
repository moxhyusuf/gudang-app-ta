<?php

namespace App\Models;

use CodeIgniter\Model;

class MutasiModel extends Model
{
    protected $table      = 'mutasi_stok';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Aktivitas mutasi terbaru untuk dashboard.
     */
    public function getAktivitasTerbaru($limit = 6)
    {
        return $this->db->query("
            SELECT ms.created_at, ms.jenis, ms.jumlah, ms.stok_sesudah,
                   ms.keterangan, m.nama_material, m.satuan,
                   u.nama AS petugas
            FROM mutasi_stok ms
            LEFT JOIN materials m ON m.id = ms.material_id
            LEFT JOIN users u     ON u.id = ms.user_id
            ORDER BY ms.created_at DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }
}