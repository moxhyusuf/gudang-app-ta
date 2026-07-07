<?php

namespace App\Models;

class BonModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function generateNomor()
    {
        $tahun = date('Y');
        $row   = $this->db->query("
            SELECT COUNT(*) as cnt FROM bon_header
            WHERE YEAR(tanggal_bon) = ?
        ", [$tahun])->getRow();
        $urut = str_pad($row->cnt + 1, 3, '0', STR_PAD_LEFT);
        return "BON-{$tahun}-{$urut}";
    }

    public function countRiwayat()
    {
        return $this->db->query("SELECT COUNT(*) as cnt FROM bon_header")->getRow()->cnt;
    }

    public function getRiwayat($limit = 20, $offset = 0)
    {
        return $this->db->query("
            SELECT bh.id, bh.no_bon, bh.tanggal_bon, bh.nama_pengambil,
                   bh.keperluan, bh.status, bh.created_at,
                   p.nama_plant,
                   u.nama AS nama_petugas,
                   COUNT(bd.id) AS jml_item
            FROM bon_header bh
            LEFT JOIN plants p  ON p.id = bh.plant_id
            LEFT JOIN users u   ON u.id = bh.user_id
            LEFT JOIN bon_detail bd ON bd.header_id = bh.id
            GROUP BY bh.id, bh.no_bon, bh.tanggal_bon, bh.nama_pengambil,
                     bh.keperluan, bh.status, bh.created_at,
                     p.nama_plant, u.nama
            ORDER BY bh.created_at DESC
            LIMIT ? OFFSET ?
        ", [$limit, $offset])->getResultArray();
    }

    public function getDetail($headerId)
    {
        return $this->db->query("
            SELECT bd.*,
                   m.nama_material, m.kode_sap, m.satuan, m.is_tabung, m.batch,
                   t.no_tabung
            FROM bon_detail bd
            LEFT JOIN materials m ON m.id = bd.material_id
            LEFT JOIN tabung t    ON t.id = bd.tabung_id
            WHERE bd.header_id = ?
            ORDER BY bd.id ASC
        ", [$headerId])->getResultArray();
    }

    public function getStokTersedia($materialId)
    {
        $row = $this->db->query("
            SELECT stok, stok_booking, is_tabung, nama_material, satuan
            FROM materials WHERE id = ?
        ", [$materialId])->getRow();
        if (!$row) return null;
        return [
            'stok'          => $row->stok,
            'stok_booking'  => $row->stok_booking,
            'stok_tersedia' => $row->stok - $row->stok_booking,
            'is_tabung'     => (int)$row->is_tabung,
            'nama_material' => $row->nama_material,
            'satuan'        => $row->satuan,
        ];
    }

    public function getTabungTersedia($materialId)
    {
        return $this->db->query("
            SELECT t.id, t.no_tabung, t.tanggal_masuk, t.kondisi
            FROM tabung t
            WHERE t.material_id = ? AND t.status = 'tersedia'
            ORDER BY t.tanggal_masuk ASC
        ", [$materialId])->getResultArray();
    }

    public function getTabungFIFO($materialId, $jumlah)
    {
        return $this->db->query("
            SELECT t.id, t.no_tabung, t.tanggal_masuk, t.kondisi
            FROM tabung t
            WHERE t.material_id = ? AND t.status = 'tersedia'
            ORDER BY t.tanggal_masuk ASC
            LIMIT ?
        ", [$materialId, $jumlah])->getResultArray();
    }

    // ── BARU: ambil list requester yang punya stok material ini ───────────────
    public function getRequesterList($materialId)
    {
        return $this->db->query("
            SELECT requester, qty
            FROM material_kepemilikan
            WHERE material_id = ? AND qty > 0
            ORDER BY requester ASC
        ", [$materialId])->getResultArray();
    }

    public function simpan($header, $items)
    {
        $db = $this->db;

        try {
            $db->transException(true);
            $db->transStart();

            $noBon = $this->generateNomor();

            $db->table('bon_header')->insert([
                'no_bon'         => $noBon,
                'plant_id'       => $header['plant_id'],
                'user_id'        => session()->get('user_id'),
                'tanggal_bon'    => $header['tanggal_bon'],
                'nama_pengambil' => $header['nama_pengambil'],
                'keperluan'      => $header['keperluan'],
                'catatan'        => $header['catatan'] ?? null,
                'status'         => 'selesai',
            ]);
            $headerId = $db->insertID();

            foreach ($items as $urutan => $item) {
                $matId     = (int)$item['material_id'];
                $jumlah    = (int)$item['jumlah_keluar'];
                $isTabung  = (int)($item['is_tabung'] ?? 0);
                $requester = trim($item['requester'] ?? '');

                $mat = $db->query("SELECT stok, stok_booking FROM materials WHERE id = ?", [$matId])->getRow();
                if (!$mat) throw new \Exception("Material ID {$matId} tidak ditemukan");

                $stokSebelum = $mat->stok;
                $stokSesudah = $stokSebelum - $jumlah;

                if ($stokSesudah < 0) {
                    throw new \Exception("Stok tidak cukup untuk material ID {$matId}");
                }

                // ── Validasi qty kepemilikan requester ────────────────────
                if ($requester) {
                    $milik = $db->query("
                        SELECT qty FROM material_kepemilikan
                        WHERE material_id = ? AND requester = ?
                    ", [$matId, $requester])->getRow();

                    if (!$milik || $milik->qty < $jumlah) {
                        $tersedia = $milik ? $milik->qty : 0;
                        throw new \Exception("Stok milik '{$requester}' hanya {$tersedia}, tidak cukup untuk {$jumlah}");
                    }
                }

                $db->query("UPDATE materials SET stok = stok - ? WHERE id = ?", [$jumlah, $matId]);

                if ($isTabung) {
                    $tabungDipilih = $item['tabung_dipilih'] ?? [];
                    if (empty($tabungDipilih)) {
                        throw new \Exception("Tidak ada tabung dipilih untuk " . ($item['nama_material'] ?? 'material'));
                    }

                    foreach ($tabungDipilih as $idx => $tabung) {
                        $tabungId = $tabung['id'];

                        $cek = $db->query("SELECT id FROM tabung WHERE id = ? AND status = 'tersedia'", [$tabungId])->getRow();
                        if (!$cek) {
                            throw new \Exception("Tabung {$tabung['no_tabung']} tidak tersedia");
                        }

                        $db->table('bon_detail')->insert([
                            'header_id'     => $headerId,
                            'material_id'   => $matId,
                            'jumlah_keluar' => 1,
                            'stok_sebelum'  => $stokSebelum - $idx,
                            'stok_sesudah'  => $stokSebelum - $idx - 1,
                            'tabung_id'     => $tabungId,
                            'requester'     => $requester ?: null,
                            'is_fifo'       => $idx < 3 ? 1 : 0,
                            'urutan_fifo'   => $idx + 1,
                        ]);

                        $db->query("UPDATE tabung SET status = 'keluar', tanggal_keluar = ? WHERE id = ?", [
                            $header['tanggal_bon'], $tabungId,
                        ]);

                        $db->table('mutasi_stok')->insert([
                            'material_id'    => $matId,
                            'jenis'          => 'keluar',
                            'jumlah'         => -1,
                            'stok_sesudah'   => $stokSebelum - $idx - 1,
                            'referensi_tipe' => 'bon',
                            'referensi_id'   => $headerId,
                            'user_id'        => session()->get('user_id'),
                            'keterangan'     => "Bon {$noBon} | Tabung: {$tabung['no_tabung']}" . ($requester ? " | Req: {$requester}" : ''),
                        ]);
                    }
                } else {
                    $db->table('bon_detail')->insert([
                        'header_id'     => $headerId,
                        'material_id'   => $matId,
                        'jumlah_keluar' => $jumlah,
                        'stok_sebelum'  => $stokSebelum,
                        'stok_sesudah'  => $stokSesudah,
                        'tabung_id'     => null,
                        'requester'     => $requester ?: null,
                        'is_fifo'       => 0,
                        'urutan_fifo'   => null,
                    ]);

                    $db->table('mutasi_stok')->insert([
                        'material_id'    => $matId,
                        'jenis'          => 'keluar',
                        'jumlah'         => -$jumlah,
                        'stok_sesudah'   => $stokSesudah,
                        'referensi_tipe' => 'bon',
                        'referensi_id'   => $headerId,
                        'user_id'        => session()->get('user_id'),
                        'keterangan'     => "Bon {$noBon} | {$header['keperluan']}" . ($requester ? " | Req: {$requester}" : ''),
                    ]);
                }

                // ── Kurangi qty kepemilikan requester ─────────────────────
                if ($requester) {
                    $db->query("
                        UPDATE material_kepemilikan SET qty = qty - ?
                        WHERE material_id = ? AND requester = ?
                    ", [$jumlah, $matId, $requester]);
                }
            }

            $db->transComplete();
            return ['success' => true, 'no_bon' => $noBon];

        } catch (\Throwable $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}