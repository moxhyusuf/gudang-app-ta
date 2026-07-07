<?php

namespace App\Models;

class SupplierModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAktif()
    {
        return $this->db->query("
            SELECT id, kode_supplier, nama_supplier
            FROM suppliers
            WHERE is_active = 1
            ORDER BY nama_supplier ASC
        ")->getResultArray();
    }

    public function simpanBaru($data)
    {
        $row  = $this->db->query("SELECT COUNT(*) as cnt FROM suppliers")->getRow();
        $kode = 'SUP-' . str_pad($row->cnt + 1, 3, '0', STR_PAD_LEFT);

        $this->db->table('suppliers')->insert([
            'kode_supplier' => $kode,
            'nama_supplier' => $data['nama_supplier'],
            'alamat'        => $data['alamat'] ?? null,
            'telepon'       => $data['telepon'] ?? null,
            'is_active'     => 1,
        ]);

        return [
            'id'            => $this->db->insertID(),
            'kode_supplier' => $kode,
            'nama_supplier' => $data['nama_supplier'],
        ];
    }
}