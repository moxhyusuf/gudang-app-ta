<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Permintaan: kode rak yang sama tidak boleh dobel, dan duplikat yang sudah
 * terlanjur ada (masing-masing punya materialnya sendiri) digabung jadi satu.
 *
 * Duplikat kode_rak selama ini bisa terbentuk karena proses "cek dulu baru
 * insert" di Mapping::update() dan PenerimaanModel::resolveRakId() punya
 * celah race condition: kalau 2 input dengan kode rak baru yang sama masuk
 * nyaris bersamaan, keduanya lolos pengecekan "belum ada" lalu sama-sama
 * membuat baris rak baru.
 *
 * Migration ini:
 *  1. Mencari semua kode_rak yang punya lebih dari satu baris di tabel `rak`.
 *  2. Untuk tiap grup duplikat, memilih 1 baris "utama" (yang paling banyak
 *     materialnya, atau kalau seri, yang id-nya paling kecil/paling lama).
 *  3. Memindahkan seluruh material dari baris duplikat lain ke baris utama.
 *  4. Melengkapi data baris utama (zona/kategori_id/baris/kolom/detail) dari
 *     duplikatnya kalau baris utama masih kosong di kolom itu.
 *  5. Menghapus baris-baris duplikat yang sudah tidak dipakai lagi.
 *  6. Menambahkan UNIQUE INDEX di kolom kode_rak supaya duplikat baru tidak
 *     bisa terbentuk lagi di masa depan (termasuk saat ada input bersamaan).
 *
 * Aman dijalankan berkali-kali: setelah duplikat pertama kali digabung dan
 * unique index ditambahkan, langkah 1-5 otomatis tidak menemukan apa-apa lagi
 * dan langkah 6 di-skip kalau index sudah ada.
 */
class MergeDuplicateRakAndUniqueConstraint extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('rak')) {
            return;
        }

        $this->mergeDuplicates();

        if (! $this->indexExists('rak', 'uniq_rak_kode_rak')) {
            // Kalau masih ada duplikat yang lolos (mis. kode_rak NULL, yang
            // secara SQL dianggap "tidak sama" satu sama lain), ADD UNIQUE
            // di bawah ini akan gagal dengan aman dan migration akan
            // menghentikan proses supaya bisa dicek manual — tidak ada data
            // yang rusak/hilang karena langkah di atas sudah idempotent.
            $this->db->query('ALTER TABLE `rak` ADD UNIQUE INDEX `uniq_rak_kode_rak` (`kode_rak`)');
        }
    }

    public function down()
    {
        if ($this->indexExists('rak', 'uniq_rak_kode_rak')) {
            $this->db->query('ALTER TABLE `rak` DROP INDEX `uniq_rak_kode_rak`');
        }
        // Penggabungan data duplikat tidak di-rollback (data sudah digabung
        // secara sengaja dan tidak ada cara aman untuk memisahkannya lagi).
    }

    private function mergeDuplicates(): void
    {
        $groups = $this->db->query("
            SELECT kode_rak, COUNT(*) AS jumlah
            FROM rak
            WHERE kode_rak IS NOT NULL AND kode_rak != ''
            GROUP BY kode_rak
            HAVING COUNT(*) > 1
        ")->getResultArray();

        foreach ($groups as $g) {
            $rows = $this->db->table('rak')
                ->where('kode_rak', $g['kode_rak'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            if (count($rows) < 2) {
                continue; // sudah tergabung oleh proses sebelumnya
            }

            // Hitung jumlah material tiap baris rak, untuk menentukan baris "utama"
            $counts = [];
            foreach ($rows as $r) {
                $counts[$r['id']] = (int) $this->db->table('materials')
                    ->where('rak_id', $r['id'])
                    ->countAllResults();
            }

            // Baris utama = yang materialnya paling banyak; kalau seri, id paling kecil (paling lama)
            usort($rows, function ($a, $b) use ($counts) {
                $ca = $counts[$a['id']];
                $cb = $counts[$b['id']];
                if ($ca !== $cb) {
                    return $cb <=> $ca; // terbanyak dulu
                }
                return $a['id'] <=> $b['id']; // id terkecil dulu
            });

            $utama     = $rows[0];
            $duplikats = array_slice($rows, 1);

            // Lengkapi kolom yang masih kosong di baris utama dari data duplikatnya
            $isian = [];
            foreach (['zona', 'kategori_id', 'baris', 'kolom', 'detail', 'keterangan'] as $kolom) {
                if (($utama[$kolom] ?? null) === null || $utama[$kolom] === '') {
                    foreach ($duplikats as $d) {
                        if (($d[$kolom] ?? null) !== null && $d[$kolom] !== '') {
                            $isian[$kolom] = $d[$kolom];
                            break;
                        }
                    }
                }
            }
            if (!empty($isian)) {
                $this->db->table('rak')->where('id', $utama['id'])->update($isian);
            }

            foreach ($duplikats as $d) {
                // Pindahkan semua material yang masih menunjuk ke rak duplikat ini ke rak utama
                $this->db->table('materials')
                    ->where('rak_id', $d['id'])
                    ->update(['rak_id' => $utama['id']]);

                // Baris duplikat sudah kosong (tidak ada material lagi yang menunjuk ke sini) — hapus
                $this->db->table('rak')->where('id', $d['id'])->delete();
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
