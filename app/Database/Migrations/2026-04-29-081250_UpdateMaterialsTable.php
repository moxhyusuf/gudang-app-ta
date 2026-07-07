<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateMaterialsTable extends Migration
{
    public function up()
    {
        // 1. Hapus unique index kode_sap yang lama
        $this->db->query('ALTER TABLE materials DROP INDEX kode_sap');

        // 2. Tambah kolom batch
        $this->forge->addColumn('materials', [
            'batch' => [
                'type'       => 'ENUM',
                'constraint' => ['LOCAL','IMPORT','EXPLANT','REKONDISI','DEFECT','EXPROJECT','DAMAGE','UMUM'],
                'null'       => true,
                'after'      => 'kode_sap',
            ],
        ]);

        // 3. Tambah kolom material_group
        $this->forge->addColumn('materials', [
            'material_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'batch',
            ],
        ]);

        // 4. Tambah kolom is_tabung
        $this->forge->addColumn('materials', [
            'is_tabung' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'material_group',
            ],
        ]);

        // 5. Tambah unique key baru: kombinasi kode_sap + batch
        $this->db->query('ALTER TABLE materials ADD UNIQUE KEY unique_kode_batch (kode_sap, batch)');
    }

    public function down()
    {
        // Rollback
        $this->db->query('ALTER TABLE materials DROP INDEX unique_kode_batch');
        $this->forge->dropColumn('materials', 'batch');
        $this->forge->dropColumn('materials', 'material_group');
        $this->forge->dropColumn('materials', 'is_tabung');
        $this->db->query('ALTER TABLE materials ADD UNIQUE KEY kode_sap (kode_sap)');
    }
}