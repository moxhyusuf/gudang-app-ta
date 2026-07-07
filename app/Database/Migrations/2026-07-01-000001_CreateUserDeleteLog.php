<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserDeleteLog extends Migration
{
    public function up()
    {
        // ── Tambah kolom penanda "dihapus" di tabel users ──────────────────
        // Dipakai untuk soft-delete: user disembunyikan dari daftar,
        // tapi barisnya tetap ada supaya histori transaksi (bon_header, dll)
        // yang masih merujuk ke user ini tidak rusak.
        $fields = [
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'is_active',
            ],
        ];
        $this->forge->addColumn('users', $fields);

        // ── Tabel log audit untuk aksi hapus user ──────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'comment'  => 'ID user yang dihapus',
            ],
            'user_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Snapshot nama user yang dihapus',
            ],
            'user_username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Snapshot username yang dihapus',
            ],
            'tipe_hapus' => [
                'type'       => 'ENUM',
                'constraint' => ['hard_delete', 'force_delete'],
                'null'       => false,
                'comment'    => 'hard_delete = baris benar2 hilang, force_delete = soft delete krn punya relasi data',
            ],
            'deleted_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'comment'  => 'ID user yang melakukan penghapusan',
            ],
            'deleted_by_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Snapshot nama user yang melakukan penghapusan',
            ],
            'alasan' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('user_delete_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_delete_log', true);
        $this->forge->dropColumn('users', 'is_deleted');
    }
}
