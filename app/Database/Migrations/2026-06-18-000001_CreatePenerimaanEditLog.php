<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenerimaanEditLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'header_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'comment'  => 'FK ke penerimaan_header.id',
            ],
            'detail_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'comment'  => 'FK ke penerimaan_detail.id, NULL jika aksi pada header',
            ],
            'aksi' => [
                'type'       => 'ENUM',
                'constraint' => ['edit_header', 'edit_item', 'hapus_item', 'tambah_item'],
                'null'       => false,
            ],
            'field_diubah' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'JSON: {"field": {"lama": ..., "baru": ...}}',
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'nama_user' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Snapshot nama user saat edit',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('header_id');
        $this->forge->createTable('penerimaan_edit_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('penerimaan_edit_log', true);
    }
}
