<?php

namespace App\Controllers;

use App\Models\BonModel;

class Pengeluaran extends BaseController
{
    public function index()
    {
        $bonModel = new BonModel();
        $db       = \Config\Database::connect();

        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $total  = $bonModel->countRiwayat();

        $data = [
            'title'        => 'Pengeluaran Material (Bon GI)',
            'role'         => session()->get('role'),
            'nama'         => session()->get('nama'),
            'plants'       => $db->table('plants')->where('is_active', 1)->orderBy('nama_plant')->get()->getResultArray(),
            'no_bon'       => $bonModel->generateNomor(),
            'riwayat'      => $bonModel->getRiwayat($limit, $offset),
            'current_page' => $page,
            'total_page'   => (int)ceil($total / $limit),
        ];

        return view('pengeluaran/index', $data);
    }

    // ── AJAX: cari material + cek stok + kepemilikan ─────────────────────────
    public function cariMaterial()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/pengeluaran');

        $kode = trim($this->request->getGet('kode') ?? '');
        $db   = \Config\Database::connect();

        // Ambil semua baris material dengan kode SAP ini (bisa multi-batch)
        $mats = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.batch,
                   m.stok, m.stok_booking, m.is_tabung,
                   (m.stok - m.stok_booking) AS stok_tersedia,
                   r.kode_rak, r.zona
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            WHERE m.kode_sap = ? AND m.status = 'aktif'
            ORDER BY m.batch ASC
        ", [$kode])->getResultArray();

        if (empty($mats)) {
            return $this->response->setJSON(['found' => false]);
        }

        // Kalau hanya satu batch, perilaku sama seperti sebelumnya
        // Kalau multi-batch, kirim array batch_list agar frontend bisa tampilkan pilihan
        $bonModel = new BonModel();

        $batchList = [];
        foreach ($mats as $mat) {
            $requesterList = $bonModel->getRequesterList($mat['id']);
            $entry = [
                'id'            => $mat['id'],
                'kode_sap'      => $mat['kode_sap'],
                'nama_material' => $mat['nama_material'],
                'satuan'        => $mat['satuan'],
                'batch'         => $mat['batch'] ?? 'UMUM',
                'stok'          => (int)$mat['stok'],
                'stok_booking'  => (int)$mat['stok_booking'],
                'stok_tersedia' => (int)$mat['stok_tersedia'],
                'is_tabung'     => (int)$mat['is_tabung'],
                'kode_rak'      => $mat['kode_rak'] ?? '—',
                'requester_list' => $requesterList,
            ];
            if ((int)$mat['is_tabung'] === 1) {
                $tabungs = $bonModel->getTabungTersedia($mat['id']);
                foreach ($tabungs as $i => &$t) {
                    $t['is_rekomendasi'] = $i < 3 ? 1 : 0;
                    $t['urutan_fifo']    = $i + 1;
                }
                unset($t);
                $entry['tabung_list'] = $tabungs;
            }
            $batchList[] = $entry;
        }

        // Gunakan entry pertama sebagai default material info
        $first = $mats[0];

        // Untuk single batch, ambil tabung_list dari batchList[0]
        $firstEntry = $batchList[0];

        return $this->response->setJSON([
            'found'          => true,
            'multi_batch'    => count($batchList) > 1,
            'batch_list'     => $batchList,
            'tabung_list'    => $firstEntry['tabung_list'] ?? [],
            'material'       => [
                'id'            => $first['id'],
                'kode_sap'      => $first['kode_sap'],
                'nama_material' => $first['nama_material'],
                'satuan'        => $first['satuan'],
                'batch'         => $first['batch'] ?? 'UMUM',
                'stok'          => (int)$first['stok'],
                'stok_booking'  => (int)$first['stok_booking'],
                'stok_tersedia' => (int)$first['stok_tersedia'],
                'is_tabung'     => (int)$first['is_tabung'],
                'kode_rak'      => $first['kode_rak'] ?? '—',
            ],
            'requester_list' => $bonModel->getRequesterList($first['id']),
        ]);
    }

    // ── AJAX: ambil list requester untuk material tertentu ───────────────────
    public function requesterList($materialId)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/pengeluaran');

        $bonModel = new BonModel();
        return $this->response->setJSON([
            'success' => true,
            'list'    => $bonModel->getRequesterList($materialId),
        ]);
    }

    // ── POST: simpan bon ──────────────────────────────────────────────────────
    public function simpan()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/pengeluaran');

        $json   = $this->request->getJSON(true);
        $header = $json['header'] ?? [];
        $items  = $json['items']  ?? [];

        if (empty($header['tanggal_bon']) || empty($header['plant_id'])
            || empty($header['nama_pengambil']) || empty($header['keperluan'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lengkapi header bon!']);
        }
        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang masih kosong!']);
        }

        $bonModel = new BonModel();
        return $this->response->setJSON($bonModel->simpan($header, $items));
    }

    // ── AJAX: cari material tanpa kode SAP (by nama) ─────────────────────────
    public function cariMaterialNama()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/pengeluaran');

        $q  = trim($this->request->getGet('q') ?? '');
        $db = \Config\Database::connect();

        if (strlen($q) < 2) {
            return $this->response->setJSON(['list' => []]);
        }

        // Hanya material tanpa kode SAP (kode_sap IS NULL atau kosong)
        $mats = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.batch,
                   m.stok, m.stok_booking, m.is_tabung,
                   (m.stok - m.stok_booking) AS stok_tersedia,
                   r.kode_rak
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            WHERE (m.kode_sap IS NULL OR m.kode_sap = '')
              AND m.status = 'aktif'
              AND m.nama_material LIKE ?
            ORDER BY m.nama_material ASC
            LIMIT 20
        ", ['%' . $q . '%'])->getResultArray();

        $bonModel = new BonModel();
        $list = [];
        foreach ($mats as $mat) {
            $entry = [
                'id'             => $mat['id'],
                'kode_sap'       => $mat['kode_sap'],
                'nama_material'  => $mat['nama_material'],
                'satuan'         => $mat['satuan'],
                'batch'          => $mat['batch'] ?? 'UMUM',
                'stok'           => (int)$mat['stok'],
                'stok_booking'   => (int)$mat['stok_booking'],
                'stok_tersedia'  => (int)$mat['stok_tersedia'],
                'is_tabung'      => (int)$mat['is_tabung'],
                'kode_rak'       => $mat['kode_rak'] ?? '—',
                'requester_list' => $bonModel->getRequesterList($mat['id']),
            ];
            if ((int)$mat['is_tabung'] === 1) {
                $tabungs = $bonModel->getTabungTersedia($mat['id']);
                foreach ($tabungs as $i => &$t) {
                    $t['is_rekomendasi'] = $i < 3 ? 1 : 0;
                    $t['urutan_fifo']    = $i + 1;
                }
                unset($t);
                $entry['tabung_list'] = $tabungs;
            } else {
                $entry['tabung_list'] = [];
            }
            $list[] = $entry;
        }

        return $this->response->setJSON(['list' => $list]);
    }

    // ── AJAX: detail satu bon ────────────────────────────────────────────────
    public function detail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/pengeluaran');

        $bonModel = new BonModel();
        $db       = \Config\Database::connect();

        $header = $db->query("
            SELECT bh.*, p.nama_plant, u.nama AS nama_petugas
            FROM bon_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            LEFT JOIN users u  ON u.id = bh.user_id
            WHERE bh.id = ?
        ", [$id])->getRowArray();

        if (!$header) {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
        }

        return $this->response->setJSON([
            'header' => $header,
            'detail' => $bonModel->getDetail($id),
        ]);
    }
}