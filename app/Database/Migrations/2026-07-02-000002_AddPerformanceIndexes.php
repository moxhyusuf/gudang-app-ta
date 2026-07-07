<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan index yang dipakai berulang-ulang oleh query di menu
 * Mapping, Monitoring Stok, Penerimaan, Pengeluaran, dll — semuanya
 * JOIN/WHERE ke kolom yang sama (materials.rak_id, materials.kategori_id,
 * materials.status, rak.zona, rak.kategori_id, rak.is_active,
 * rak_kategori.is_active).
 *
 * Tanpa index ini, MySQL harus full table scan setiap kali salah satu
 * menu tsb dibuka — makin banyak data material/rak, makin lambat.
 *
 * Migration ini aman dijalankan berkali-kali: setiap index dicek dulu
 * apakah sudah ada sebelum ditambahkan, jadi tidak akan error walau
 * kolomnya sudah punya index dengan nama lain.
 */
class AddPerformanceIndexes extends Migration
{
    private array $indexes = [
        'materials'    => [
            'idx_materials_rak_id'      => ['rak_id'],
            'idx_materials_kategori_id' => ['kategori_id'],
            'idx_materials_status'      => ['status'],
            'idx_materials_status_rak'  => ['status', 'rak_id'],
        ],
        'rak'          => [
            'idx_rak_zona'        => ['zona'],
            'idx_rak_is_active'   => ['is_active'],
            'idx_rak_kategori_id' => ['kategori_id'],
        ],
        'rak_kategori' => [
            'idx_rakkategori_is_active' => ['is_active'],
        ],
    ];

    public function up()
    {
        foreach ($this->indexes as $table => $defs) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            foreach ($defs as $indexName => $columns) {
                if ($this->indexExists($table, $indexName)) {
                    continue;
                }
                $cols = implode('`, `', $columns);
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$cols}`)");
            }
        }
    }

    public function down()
    {
        foreach ($this->indexes as $table => $defs) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            foreach (array_keys($defs) as $indexName) {
                if ($this->indexExists($table, $indexName)) {
                    $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
                }
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
            [$table, $indexName]
        )->getRow();

        return $row && (int) $row->cnt > 0;
    }
}
