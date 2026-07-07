<?php

namespace App\Controllers;

use App\Models\PenerimaanModel;
use App\Models\SupplierModel;
use App\Models\KategoriModel;

class Penerimaan extends BaseController
{
    public function index()
    {
        $penerimaanModel = new PenerimaanModel();
        $supplierModel   = new SupplierModel();
        $kategoriModel   = new KategoriModel();

        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $total  = $penerimaanModel->countRiwayat();

        $data = [
            'title'        => 'Penerimaan Material',
            'role'         => session()->get('role'),
            'nama'         => session()->get('nama'),
            'suppliers'    => $supplierModel->getAktif(),
            'kategoris'    => $kategoriModel->getAll(),
            'no_surat'     => $penerimaanModel->generateNomor(),
            'riwayat'      => $penerimaanModel->getRiwayat($limit, $offset),
            'current_page' => $page,
            'total_page'   => (int)ceil($total / $limit),
        ];

        return view('Penerimaan/index', $data);
    }

    // ── AJAX: cari material by kode SAP ──────────────────────────────────────
    public function cariMaterial()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $kode = trim($this->request->getGet('kode') ?? '');
        $db   = \Config\Database::connect();

        $mat = $db->query("
            SELECT m.*, r.kode_rak, r.zona
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            WHERE m.kode_sap = ? AND m.status = 'aktif'
            LIMIT 1
        ", [$kode])->getRowArray();

        if (!$mat) {
            return $this->response->setJSON(['found' => false]);
        }

        // Ambil kepemilikan yang sudah ada untuk material ini
        $kepemilikan = $db->query("
            SELECT requester, qty FROM material_kepemilikan
            WHERE material_id = ? AND qty > 0
            ORDER BY requester ASC
        ", [$mat['id']])->getResultArray();

        return $this->response->setJSON([
            'found'       => true,
            'material'    => [
                'id'            => $mat['id'],
                'kode_sap'      => $mat['kode_sap'],
                'nama_material' => $mat['nama_material'],
                'satuan'        => $mat['satuan'],
                'is_tabung'     => (int)$mat['is_tabung'],
                'stok'          => $mat['stok'],
                'rak_id'        => $mat['rak_id'],
                'kode_rak'      => $mat['kode_rak'] ?? '—',
                'batch'         => $mat['batch'] ?? 'UMUM',
                'kategori_id'   => $mat['kategori_id'] ?? null,
            ],
            'kepemilikan' => $kepemilikan,
        ]);
    }

    // ── AJAX: simpan supplier baru ────────────────────────────────────────────
    public function simpanSupplier()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $json = $this->request->getJSON(true);
        $nama = trim($json['nama_supplier'] ?? '');

        if (!$nama) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama supplier wajib diisi!']);
        }

        $supplierModel = new SupplierModel();
        $result        = $supplierModel->simpanBaru([
            'nama_supplier' => $nama,
            'alamat'        => trim($json['alamat'] ?? ''),
            'telepon'       => trim($json['telepon'] ?? ''),
        ]);

        return $this->response->setJSON(['success' => true, 'supplier' => $result]);
    }

    // ── POST: simpan penerimaan ───────────────────────────────────────────────
    public function simpan()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $json   = $this->request->getJSON(true);
        $header = $json['header'] ?? [];
        $items  = $json['items']  ?? [];

        if (empty($header['tanggal_terima']) || empty($header['supplier_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lengkapi header penerimaan!']);
        }
        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang masih kosong!']);
        }

        $penerimaanModel = new PenerimaanModel();
        $result = $penerimaanModel->simpan($header, $items);
        return $this->response->setJSON($result);
    }

    // ── AJAX: detail satu surat penerimaan ────────────────────────────────────
    public function detail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $penerimaanModel = new PenerimaanModel();
        $db              = \Config\Database::connect();

        $header = $db->query("
            SELECT ph.*, s.nama_supplier, u.nama AS nama_petugas
            FROM penerimaan_header ph
            LEFT JOIN suppliers s ON s.id = ph.supplier_id
            LEFT JOIN users u     ON u.id = ph.user_id
            WHERE ph.id = ?
        ", [$id])->getRowArray();

        if (!$header) {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
        }

        return $this->response->setJSON([
            'header' => $header,
            'detail' => $penerimaanModel->getDetail($id),
        ]);
    }

    // ── AJAX: kepemilikan per material ────────────────────────────────────────
    public function kepemilikan($materialId)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $db = \Config\Database::connect();

        $material = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.stok,
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
            WHERE material_id = ?
            ORDER BY requester ASC
        ", [$materialId])->getResultArray();

        return $this->response->setJSON([
            'material'    => $material,
            'kepemilikan' => $list,
        ]);
    }

    // ── AJAX: edit header penerimaan ─────────────────────────────────────────
    public function editHeader($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $json = $this->request->getJSON(true);
        $penerimaanModel = new PenerimaanModel();
        $result = $penerimaanModel->editHeader($id, $json);
        return $this->response->setJSON($result);
    }

    // ── AJAX: edit satu item detail penerimaan ───────────────────────────────
    public function editItem($headerId, $detailId)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $json = $this->request->getJSON(true);
        $penerimaanModel = new PenerimaanModel();
        $result = $penerimaanModel->editItem($headerId, $detailId, $json);
        return $this->response->setJSON($result);
    }

    // ── AJAX: hapus satu item detail penerimaan ──────────────────────────────
    public function hapusItem($headerId, $detailId)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $penerimaanModel = new PenerimaanModel();
        $result = $penerimaanModel->hapusItem($headerId, $detailId);
        return $this->response->setJSON($result);
    }

    // ── AJAX: tambah item ke penerimaan yang sudah ada ───────────────────────
    public function tambahItem($headerId)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $json = $this->request->getJSON(true);
        $penerimaanModel = new PenerimaanModel();
        $result = $penerimaanModel->tambahItem($headerId, $json);
        return $this->response->setJSON($result);
    }

    // ── AJAX: ambil log riwayat edit penerimaan ──────────────────────────────
    public function editLog($headerId)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/penerimaan');

        $penerimaanModel = new PenerimaanModel();
        return $this->response->setJSON([
            'log' => $penerimaanModel->getEditLog($headerId),
        ]);
    }

}
