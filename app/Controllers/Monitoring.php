<?php

namespace App\Controllers;

use App\Models\MaterialModel;

class Monitoring extends BaseController
{
    protected $perPage = 100;

    // Nilai batch yang tersedia sebagai opsi filter
    private $batchOptions = ['LOCAL', 'IMPORT', 'EXPLANT', 'EXPROJECT', 'REKONDISI', 'DEFECT', 'DAMAGE', 'UMUM'];

    // ── Halaman utama ─────────────────────────────────────────────────────────

    public function index()
    {
        $role    = session()->get('role');
        $isPlant = $role === 'plant';

        $search  = $this->request->getGet('search')  ?? '';
        $batch   = $this->request->getGet('batch')   ?? '';
        $kondisi = $this->request->getGet('kondisi') ?? '';
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $page    = max(1, $page);
        $offset  = ($page - 1) * $this->perPage;

        $materialModel = new MaterialModel();

        $total     = $materialModel->countMonitoring($search, $batch, $kondisi, $isPlant);
        $materials = $materialModel->getMonitoringPaginated($search, $batch, $kondisi, $isPlant, $this->perPage, $offset);
        $totalPage = (int) ceil($total / $this->perPage);

        $data = [
            'title'           => 'Monitoring Persediaan',
            'role'            => $role,
            'batch_options'   => $this->batchOptions,
            'materials'       => $materials,
            'total'           => $total,
            'total_page'      => $totalPage,
            'current_page'    => $page,
            'per_page'        => $this->perPage,
            'filter_search'   => $search,
            'filter_batch'    => $batch,
            'filter_kondisi'  => $kondisi,
        ];

        return view('monitoring/index', $data);
    }

    // ── AJAX: data tabel (JSON) ───────────────────────────────────────────────
    // Dipanggil JS saat filter berubah atau ganti halaman.

    public function data()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/monitoring');
        }

        $role    = session()->get('role');
        $isPlant = $role === 'plant';

        $search  = $this->request->getGet('search')  ?? '';
        $batch   = $this->request->getGet('batch')   ?? '';
        $kondisi = $this->request->getGet('kondisi') ?? '';
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $page    = max(1, $page);
        $offset  = ($page - 1) * $this->perPage;

        $materialModel = new MaterialModel();

        $total     = $materialModel->countMonitoring($search, $batch, $kondisi, $isPlant);
        $materials = $materialModel->getMonitoringPaginated($search, $batch, $kondisi, $isPlant, $this->perPage, $offset);
        $totalPage = (int) ceil($total / $this->perPage);

        return $this->response->setJSON([
            'materials'    => $materials,
            'total'        => $total,
            'total_page'   => $totalPage,
            'current_page' => $page,
            'per_page'     => $this->perPage,
            'role'         => $role,
        ]);
    }

    // ── AJAX: kepemilikan stok per material (untuk semua role) ───────────────────

    public function kepemilikan($materialId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/monitoring');
        }

        $db = \Config\Database::connect();

        $material = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.stok, m.stok_booking,
                   r.kode_rak
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            WHERE m.id = ?
        ", [$materialId])->getRowArray();

        if (!$material) {
            return $this->response->setJSON(['error' => 'Material tidak ditemukan']);
        }

        $list = $db->query("
            SELECT requester, qty, updated_at
            FROM material_kepemilikan
            WHERE material_id = ? AND qty > 0
            ORDER BY requester ASC
        ", [$materialId])->getResultArray();

        return $this->response->setJSON([
            'material'    => $material,
            'kepemilikan' => $list,
        ]);
    }

    // ── AJAX: simpan / sesuaikan kepemilikan stok (admin & petugas gudang) ───────

    public function simpanKepemilikan($materialId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/monitoring');
        }

        // Penyesuaian kepemilikan hanya untuk pengelola gudang, bukan role plant
        if (session()->get('role') === 'plant') {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengubah kepemilikan stok.']);
        }

        $db = \Config\Database::connect();

        $requester         = trim((string) $this->request->getPost('requester'));
        $requesterOriginal = trim((string) $this->request->getPost('original_requester'));
        $qty               = (int) $this->request->getPost('qty');

        if ($requester === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama requester/pemilik wajib diisi.']);
        }
        if ($qty < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Qty tidak boleh negatif.']);
        }

        $material = $db->table('materials')->where('id', $materialId)->get()->getRowArray();
        if (!$material) {
            return $this->response->setJSON(['success' => false, 'message' => 'Material tidak ditemukan.']);
        }
        $stok = (int) $material['stok'];

        // Baris yang sedang diedit (dicari berdasarkan nama REQUESTER ASLI sebelum diubah).
        // Kosong jika ini penambahan requester baru (bukan edit).
        $editingRow = null;
        if ($requesterOriginal !== '') {
            $editingRow = $db->query("
                SELECT id, qty FROM material_kepemilikan WHERE material_id = ? AND requester = ?
            ", [$materialId, $requesterOriginal])->getRowArray();
        }

        // Baris lain yang kebetulan sudah memakai nama TUJUAN (requester baru)
        $targetRow = $db->query("
            SELECT id, qty FROM material_kepemilikan WHERE material_id = ? AND requester = ?
        ", [$materialId, $requester])->getRowArray();

        // Jika hasil rename bentrok dengan requester lain yang sudah ada (bukan baris yang sama), tolak
        if ($editingRow && $targetRow && (int) $editingRow['id'] !== (int) $targetRow['id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama requester "' . $requester . '" sudah dipakai oleh pemilik lain pada material ini. Gunakan nama lain, atau hapus salah satu terlebih dahulu.',
            ]);
        }

        $rowBeingSaved = $editingRow ?: $targetRow; // baris yang akan di-update, jika ada
        $excludeId     = $rowBeingSaved ? (int) $rowBeingSaved['id'] : null;

        // Total qty milik requester LAIN (di luar baris yang sedang diedit/ditulis), dibandingkan berdasarkan ID, bukan nama
        $totalLain = (int) $db->query("
            SELECT COALESCE(SUM(qty),0) AS t FROM material_kepemilikan
            WHERE material_id = ?" . ($excludeId ? " AND id != ?" : ""),
            $excludeId ? [$materialId, $excludeId] : [$materialId]
        )->getRow()->t;

        if ($totalLain + $qty > $stok) {
            $sisaMax = max(0, $stok - $totalLain);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Qty melebihi total stok yang ada. Maksimal untuk "' . $requester . '" saat ini adalah ' . $sisaMax . '.',
            ]);
        }

        if ($rowBeingSaved) {
            $db->table('material_kepemilikan')->where('id', $rowBeingSaved['id'])
                ->update(['requester' => $requester, 'qty' => $qty, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $db->table('material_kepemilikan')->insert([
                'material_id' => $materialId,
                'requester'   => $requester,
                'qty'         => $qty,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kepemilikan "' . $requester . '" berhasil disimpan.',
        ]);
    }

    // ── AJAX: histori mutasi per material ─────────────────────────────────────

    public function histori($materialId)
    {
        $materialModel = new MaterialModel();
        $material      = $materialModel->find($materialId);

        if (!$material) {
            return $this->response->setJSON(['error' => 'Material tidak ditemukan']);
        }

        $mutasi = $materialModel->getHistoriMutasi($materialId, 30);

        return $this->response->setJSON([
            'material' => $material,
            'mutasi'   => $mutasi,
        ]);
    }
}