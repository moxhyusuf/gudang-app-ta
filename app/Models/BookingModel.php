<?php

namespace App\Models;

class BookingModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── Untuk Dashboard plant ─────────────────────────────────────────────────
    public function countAktifByUser($userId)
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM booking_header
            WHERE user_id = ? AND status = 'pending'
        ", [$userId])->getRow()->cnt;
    }

    public function getAktifByUser($userId, $limit = 5)
    {
        return $this->db->query("
            SELECT bh.id, bh.no_booking, bh.tanggal_booking, bh.tanggal_butuh,
                   bh.status, COUNT(bd.id) AS jml_item,
                   DATEDIFF(DATE_ADD(bh.tanggal_booking, INTERVAL 3 DAY), CURDATE()) AS sisa_hari
            FROM booking_header bh
            LEFT JOIN booking_detail bd ON bd.header_id = bh.id
            WHERE bh.user_id = ? AND bh.status = 'pending'
            GROUP BY bh.id, bh.no_booking, bh.tanggal_booking, bh.tanggal_butuh, bh.status
            ORDER BY bh.created_at DESC
            LIMIT ?
        ", [$userId, $limit])->getResultArray();
    }

    // ── Untuk Dashboard admin_gt ──────────────────────────────────────────────
    public function countPending()
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM booking_header WHERE status = 'pending'
        ")->getRow()->cnt;
    }

    public function getPendingList($limit = 5)
    {
        return $this->db->query("
            SELECT bh.*, p.nama_plant, u.nama AS nama_user,
                   COUNT(bd.id) AS jml_item,
                   DATEDIFF(DATE_ADD(bh.tanggal_booking, INTERVAL 3 DAY), CURDATE()) AS sisa_hari
            FROM booking_header bh
            LEFT JOIN plants p          ON p.id = bh.plant_id
            LEFT JOIN users u           ON u.id = bh.user_id
            LEFT JOIN booking_detail bd ON bd.header_id = bh.id
            WHERE bh.status = 'pending'
            GROUP BY bh.id
            ORDER BY bh.created_at DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    // ── Generate nomor booking otomatis BK-YYYY-NNN ───────────────────────────
    public function generateNomor()
    {
        $tahun = date('Y');
        $row   = $this->db->query("
            SELECT COUNT(*) as cnt FROM booking_header
            WHERE YEAR(tanggal_booking) = ?
        ", [$tahun])->getRow();
        $urut = str_pad($row->cnt + 1, 3, '0', STR_PAD_LEFT);
        return "BK-{$tahun}-{$urut}";
    }

    // ── Cek dan proses booking kadaluarsa (dipanggil saat login/akses) ────────
    public function prosesKadaluarsa()
    {
        $notifModel = new NotifikasiModel();

        // Booking yang sudah kadaluarsa (lebih dari 3 hari, masih pending)
        $kadaluarsa = $this->db->query("
            SELECT bh.*, p.nama_plant
            FROM booking_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            WHERE bh.status = 'pending'
            AND bh.tanggal_booking < DATE_SUB(CURDATE(), INTERVAL 3 DAY)
        ")->getResultArray();

        foreach ($kadaluarsa as $bk) {
            // Kembalikan stok_booking
            $details = $this->db->query("
                SELECT * FROM booking_detail WHERE header_id = ?
            ", [$bk['id']])->getResultArray();

            foreach ($details as $d) {
                $this->db->query("
                    UPDATE materials SET stok_booking = stok_booking - ?
                    WHERE id = ? AND stok_booking >= ?
                ", [$d['jumlah_booking'], $d['material_id'], $d['jumlah_booking']]);
            }

            // Update status kadaluarsa
            $this->db->query("
                UPDATE booking_header SET status = 'kadaluarsa' WHERE id = ?
            ", [$bk['id']]);
        }

        // Booking yang akan kadaluarsa BESOK (H+2, kirim notif ke admin)
        $hampirKadaluarsa = $this->db->query("
            SELECT bh.*, p.nama_plant, u.nama AS nama_plant_user
            FROM booking_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            LEFT JOIN users u  ON u.id = bh.user_id
            WHERE bh.status = 'pending'
            AND bh.tanggal_booking = DATE_SUB(CURDATE(), INTERVAL 2 DAY)
            AND bh.id NOT IN (
                SELECT DISTINCT referensi_id FROM notifikasi
                WHERE referensi_tipe = 'booking_kadaluarsa'
                AND referensi_id IS NOT NULL
            )
        ")->getResultArray();

        foreach ($hampirKadaluarsa as $bk) {
            $notifModel->kirimKeAdmin(
                'Booking Akan Kadaluarsa',
                "Booking {$bk['no_booking']} dari plant {$bk['nama_plant']} akan kadaluarsa BESOK. Apakah barang sudah diambil? Segera lakukan konfirmasi Selesai atau Batal.",
                'warning',
                'booking_kadaluarsa',
                $bk['id']
            );
        }
    }

    // ── Submit booking baru ───────────────────────────────────────────────────
    public function simpan($header, $items)
    {
        $db = $this->db;

        try {
            $db->transBegin();

            $noBk = $this->generateNomor();

            $db->table('booking_header')->insert([
                'no_booking'      => $noBk,
                'plant_id'        => $header['plant_id'],
                'user_id'         => session()->get('user_id'),
                'tanggal_booking' => date('Y-m-d'),
                'tanggal_butuh'   => $header['tanggal_butuh'],
                'jenis'           => 'umum',
                'status'          => 'pending',
                'catatan'         => $header['catatan'] ?? null,
            ]);
            $headerId = $db->insertID();

            foreach ($items as $item) {
                $matId  = (int)$item['material_id'];
                $jumlah = (int)$item['jumlah_booking'];

                // Cek stok tersedia
                $mat = $db->query("
                    SELECT stok, stok_booking FROM materials WHERE id = ?
                ", [$matId])->getRow();

                if (!$mat) throw new \Exception("Material tidak ditemukan");

                $stokTersedia = $mat->stok - $mat->stok_booking;
                if ($jumlah > $stokTersedia) {
                    throw new \Exception("Stok tidak mencukupi untuk {$item['nama_material']}. Tersedia: {$stokTersedia}");
                }

                // Tambah stok_booking
                $db->query("
                    UPDATE materials SET stok_booking = stok_booking + ? WHERE id = ?
                ", [$jumlah, $matId]);

                $db->table('booking_detail')->insert([
                    'header_id'      => $headerId,
                    'material_id'    => $matId,
                    'jumlah_booking' => $jumlah,
                    'status'         => 'aktif',
                ]);
            }

            $db->transCommit();

            // Kirim notifikasi ke admin_gt
            $notifModel = new NotifikasiModel();
            $namaUser   = session()->get('nama') ?? 'Plant';
            $notifModel->kirimKeAdmin(
                'Booking Baru Masuk',
                "Booking {} dari {$namaUser} menunggu verifikasi Anda.",
                'info',
                'booking',
                $headerId
            );

            return ['success' => true, 'no_booking' => $noBk];

        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Riwayat booking milik plant user ─────────────────────────────────────
    public function getRiwayatByUser($userId, $limit = 20, $offset = 0)
    {
        return $this->db->query("
            SELECT bh.id, bh.no_booking, bh.tanggal_booking, bh.tanggal_butuh,
                   bh.status, bh.catatan, bh.created_at,
                   p.nama_plant,
                   COUNT(bd.id) AS jml_item,
                   DATEDIFF(DATE_ADD(bh.tanggal_booking, INTERVAL 3 DAY), CURDATE()) AS sisa_hari
            FROM booking_header bh
            LEFT JOIN plants p      ON p.id = bh.plant_id
            LEFT JOIN booking_detail bd ON bd.header_id = bh.id
            WHERE bh.user_id = ?
            GROUP BY bh.id, bh.no_booking, bh.tanggal_booking, bh.tanggal_butuh,
                     bh.status, bh.catatan, bh.created_at, p.nama_plant
            ORDER BY bh.created_at DESC
            LIMIT ? OFFSET ?
        ", [$userId, $limit, $offset])->getResultArray();
    }

    public function countRiwayatByUser($userId)
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM booking_header WHERE user_id = ?
        ", [$userId])->getRow()->cnt;
    }

    // ── Detail booking ────────────────────────────────────────────────────────
    public function getDetail($headerId)
    {
        return $this->db->query("
            SELECT bd.*, m.nama_material, m.kode_sap, m.satuan,
                   (m.stok - m.stok_booking) AS stok_tersedia
            FROM booking_detail bd
            LEFT JOIN materials m ON m.id = bd.material_id
            WHERE bd.header_id = ?
        ", [$headerId])->getResultArray();
    }

    // ── Selesai / Batal (admin GT) ────────────────────────────────────────────
    public function selesai($headerId)
    {
        $db  = $this->db;
        $bk  = $db->query("SELECT * FROM booking_header WHERE id = ?", [$headerId])->getRow();
        if (!$bk || $bk->status !== 'pending') {
            return ['success' => false, 'message' => 'Booking tidak valid atau sudah diproses'];
        }

        try {
            $db->transBegin();

            $details = $db->query("SELECT * FROM booking_detail WHERE header_id = ?", [$headerId])->getResultArray();
            foreach ($details as $d) {
                // Kurangi stok_booking (barang sudah diambil)
                $db->query("UPDATE materials SET stok_booking = stok_booking - ? WHERE id = ? AND stok_booking >= ?",
                    [$d['jumlah_booking'], $d['material_id'], $d['jumlah_booking']]);
                $db->query("UPDATE booking_detail SET status = 'selesai' WHERE id = ?", [$d['id']]);
            }

            $db->query("UPDATE booking_header SET status = 'selesai' WHERE id = ?", [$headerId]);
            $db->transCommit();
            return ['success' => true];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function batal($headerId, $alasan = '')
    {
        $db  = $this->db;
        $bk  = $db->query("SELECT * FROM booking_header WHERE id = ?", [$headerId])->getRow();
        if (!$bk || $bk->status !== 'pending') {
            return ['success' => false, 'message' => 'Booking tidak valid atau sudah diproses'];
        }

        try {
            $db->transBegin();

            $details = $db->query("SELECT * FROM booking_detail WHERE header_id = ?", [$headerId])->getResultArray();
            foreach ($details as $d) {
                // Kembalikan stok_booking
                $db->query("UPDATE materials SET stok_booking = stok_booking - ? WHERE id = ? AND stok_booking >= ?",
                    [$d['jumlah_booking'], $d['material_id'], $d['jumlah_booking']]);
                $db->query("UPDATE booking_detail SET status = 'dibatalkan' WHERE id = ?", [$d['id']]);
            }

            $catatan = $bk->catatan ? $bk->catatan . ' | Batal: ' . $alasan : 'Batal: ' . $alasan;
            $db->query("UPDATE booking_header SET status = 'batal', catatan = ? WHERE id = ?", [$catatan, $headerId]);
            $db->transCommit();
            return ['success' => true];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}