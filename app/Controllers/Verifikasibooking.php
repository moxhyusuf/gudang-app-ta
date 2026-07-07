<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\NotifikasiModel;

class VerifikasiBooking extends BaseController
{
    // ── Halaman daftar booking pending ───────────────────────────────────────
    public function index()
    {
        if (session()->get('role') !== 'admin_gt') {
            return redirect()->to('/dashboard');
        }

        $db     = \Config\Database::connect();
        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        $status = $this->request->getGet('status') ?? 'pending';
        $cari   = trim($this->request->getGet('cari') ?? '');

        $where  = "WHERE 1=1";
        $params = [];

        if ($status !== 'semua') {
            $where   .= " AND bh.status = ?";
            $params[] = $status;
        }
        if ($cari !== '') {
            $where   .= " AND (bh.no_booking LIKE ? OR p.nama_plant LIKE ? OR u.nama LIKE ?)";
            $like     = '%' . $cari . '%';
            $params   = array_merge($params, [$like, $like, $like]);
        }

        $list = $db->query("
            SELECT bh.id, bh.no_booking, bh.tanggal_booking, bh.tanggal_butuh,
                   bh.status, bh.catatan, bh.created_at,
                   p.nama_plant, u.nama AS nama_user,
                   COUNT(bd.id) AS jml_item,
                   DATEDIFF(DATE_ADD(bh.tanggal_booking, INTERVAL 3 DAY), CURDATE()) AS sisa_hari
            FROM booking_header bh
            LEFT JOIN plants p          ON p.id  = bh.plant_id
            LEFT JOIN users u           ON u.id  = bh.user_id
            LEFT JOIN booking_detail bd ON bd.header_id = bh.id
            {$where}
            GROUP BY bh.id, bh.no_booking, bh.tanggal_booking, bh.tanggal_butuh,
                     bh.status, bh.catatan, bh.created_at, p.nama_plant, u.nama
            ORDER BY bh.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params)->getResultArray();

        $total = $db->query("
            SELECT COUNT(DISTINCT bh.id) as cnt
            FROM booking_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            LEFT JOIN users u  ON u.id = bh.user_id
            {$where}
        ", $params)->getRow()->cnt;

        $data = [
            'title'        => 'Verifikasi Booking',
            'role'         => session()->get('role'),
            'nama'         => session()->get('nama'),
            'list'         => $list,
            'total'        => $total,
            'current_page' => $page,
            'total_page'   => (int)ceil($total / $limit),
            'status'       => $status,
            'cari'         => $cari,
        ];

        return view('booking/verifikasi', $data);
    }

    // ── AJAX: selesai (barang sudah diambil) ──────────────────────────────────
    public function selesai($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/verifikasi-booking');
        if (session()->get('role') !== 'admin_gt') {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $db    = \Config\Database::connect();
        $notif = new NotifikasiModel();

        $bk = $db->query("
            SELECT bh.*, u.id AS user_id_plant
            FROM booking_header bh
            LEFT JOIN users u ON u.id = bh.user_id
            WHERE bh.id = ?
        ", [$id])->getRow();

        if (!$bk || $bk->status !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Booking tidak valid atau sudah diproses']);
        }

        try {
            $db->transBegin();

            $details = $db->query("SELECT * FROM booking_detail WHERE header_id = ?", [$id])->getResultArray();
            foreach ($details as $d) {
                // Hanya kurangi stok_booking — stok asli berkurang lewat proses pengeluaran
                $db->query("
                    UPDATE materials SET stok_booking = stok_booking - ?
                    WHERE id = ? AND stok_booking >= ?
                ", [$d['jumlah_booking'], $d['material_id'], $d['jumlah_booking']]);
                $db->query("UPDATE booking_detail SET status = 'selesai' WHERE id = ?", [$d['id']]);
            }

            $db->query("UPDATE booking_header SET status = 'selesai' WHERE id = ?", [$id]);
            $db->transCommit();

            // Notifikasi ke plant
            $notif->kirim(
                $bk->user_id_plant,
                'Booking Selesai',
                "Booking {$bk->no_booking} telah dikonfirmasi selesai oleh admin. Material sudah tercatat diambil.",
                'info',
                'booking',
                $id
            );

            return $this->response->setJSON(['success' => true, 'message' => 'Booking ditandai selesai']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ── AJAX: batal (kondisi urgent, setelah konfirmasi via telepon) ──────────
    public function batal($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/verifikasi-booking');
        if (session()->get('role') !== 'admin_gt') {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $json   = $this->request->getJSON(true);
        $alasan = trim($json['alasan'] ?? '');

        if ($alasan === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Alasan pembatalan wajib diisi']);
        }

        $db    = \Config\Database::connect();
        $notif = new NotifikasiModel();

        $bk = $db->query("
            SELECT bh.*, p.nama_plant, u.id AS user_id_plant
            FROM booking_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            LEFT JOIN users u  ON u.id = bh.user_id
            WHERE bh.id = ?
        ", [$id])->getRow();

        if (!$bk || $bk->status !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Booking tidak valid atau sudah diproses']);
        }

        try {
            $db->transBegin();

            // Kembalikan stok_booking
            $details = $db->query("SELECT * FROM booking_detail WHERE header_id = ?", [$id])->getResultArray();
            foreach ($details as $d) {
                $db->query("
                    UPDATE materials SET stok_booking = stok_booking - ?
                    WHERE id = ? AND stok_booking >= ?
                ", [$d['jumlah_booking'], $d['material_id'], $d['jumlah_booking']]);
                $db->query("UPDATE booking_detail SET status = 'dibatalkan' WHERE id = ?", [$d['id']]);
            }

            $catatanBaru = $bk->catatan ? $bk->catatan . ' | Batal: ' . $alasan : 'Batal: ' . $alasan;
            $db->query("UPDATE booking_header SET status = 'batal', catatan = ? WHERE id = ?", [$catatanBaru, $id]);
            $db->transCommit();

            // Notifikasi ke plant
            $notif->kirim(
                $bk->user_id_plant,
                'Booking Dibatalkan',
                "Booking {$bk->no_booking} dibatalkan oleh admin GT. Alasan: {$alasan}",
                'danger',
                'booking',
                $id
            );

            return $this->response->setJSON(['success' => true, 'message' => 'Booking berhasil dibatalkan']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ── AJAX: detail booking ──────────────────────────────────────────────────
    public function detail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/verifikasi-booking');
        if (session()->get('role') !== 'admin_gt') {
            return $this->response->setJSON(['error' => 'Akses ditolak']);
        }

        $db = \Config\Database::connect();

        $header = $db->query("
            SELECT bh.*, p.nama_plant, u.nama AS nama_user
            FROM booking_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            LEFT JOIN users u  ON u.id = bh.user_id
            WHERE bh.id = ?
        ", [$id])->getRowArray();

        if (!$header) {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
        }

        $detail = $db->query("
            SELECT bd.*, m.nama_material, m.kode_sap, m.satuan,
                   (m.stok - m.stok_booking) AS stok_tersedia
            FROM booking_detail bd
            LEFT JOIN materials m ON m.id = bd.material_id
            WHERE bd.header_id = ?
        ", [$id])->getResultArray();

        return $this->response->setJSON(['header' => $header, 'detail' => $detail]);
    }
}