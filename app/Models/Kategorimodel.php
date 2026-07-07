<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table         = 'kategoris';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nama_kategori', 'keterangan'];

    public function getAll()
    {
        return $this->orderBy('nama_kategori', 'ASC')->findAll();
    }
}