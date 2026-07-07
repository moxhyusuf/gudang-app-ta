<?php

namespace App\Controllers;

use App\Models\KategoriModel;
use App\Models\RakKategoriModel;
use App\Models\RakModel;

class Mapping extends BaseController
{
    protected $perPage = 50;

    // ── Halaman utama (gabungan Mapping + Kelola Kategori Rak) ─────────────────

    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return redirect()->to('/dashboard');
        }

        $kategoriModel    = new KategoriModel();
        $rakKategoriModel = new RakKategoriModel();
        $rakModel         = new RakModel();

        $data = [
            'title'       => 'Mapping & Kategori Rak',
            'role'        => $role,
            'kategoris'   => $kategoriModel->getAll(),
            'zonaGrid'    => $rakModel->getZonaGrid(),
            'zonaList'    => $rakModel->getZonaList(),
            'unassigned'  => $rakModel->getUnassignedMaterials(),
        ];

        return view('mapping/index', $data);
    }

    // ── AJAX: data Peta Gudang (refresh setelah ada perubahan) ─────────────────

    public function zonaGrid()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $rakModel = new RakModel();
        return $this->response->setJSON(['zona' => $rakModel->getZonaGrid()]);
    }

    // ── AJAX: detail satu rak + daftar material di dalamnya ─────────────────────

    public function rakDetail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $rakModel = new RakModel();
        $detail   = $rakModel->getDetail($id);

        if (!$detail) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rak tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'rak' => $detail]);
    }

    // ── AJAX: detail kategori rak yang belum punya rak/material sama sekali ─────
    // (kotak kosong di Peta Gudang). Hanya info kategori, read-only — material
    // hanya bisa ditambahkan lewat menu Penerimaan.

    public function kategoriDetail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $rakKategoriModel = new RakKategoriModel();
        $kategori = $rakKategoriModel->find($id);

        if (!$kategori) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kategori rak tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'kategori' => $kategori]);
    }

    // ── AJAX: simpan perubahan Kode Rak / Zona / Keterangan ─────────────────────

    public function rakUpdate($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $json     = $this->request->getJSON(true);
        $rakModel = new RakModel();
        $result   = $rakModel->updateRak($id, [
            'kode_rak'   => $json['kode_rak']   ?? '',
            'zona'       => $json['zona']       ?? '',
            'keterangan' => $json['keterangan'] ?? '',
        ]);

        return $this->response->setJSON($result);
    }

    // ── AJAX: daftar material yang belum punya rak (dengan pencarian) ──────────

    public function unassigned()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $search   = $this->request->getGet('search') ?? '';
        $rakModel = new RakModel();

        return $this->response->setJSON(['materials' => $rakModel->getUnassignedMaterials($search)]);
    }

    // ── AJAX: data tabel (dibiarkan untuk kompatibilitas, tidak dipakai halaman) ─

    public function data()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $db     = \Config\Database::connect();
        $search = $this->request->getGet('search') ?? '';
        $katId  = $this->request->getGet('kat')    ?? '';
        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $this->perPage;

        [$total, $materials] = $this->queryMaterials($db, $search, $katId, $this->perPage, $offset);

        return $this->response->setJSON([
            'materials'    => $materials,
            'total'        => $total,
            'total_page'   => (int)ceil($total / $this->perPage),
            'current_page' => $page,
        ]);
    }

    // ── AJAX: ambil data satu material untuk modal edit ────────────────────────

    public function get($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $db  = \Config\Database::connect();
        $mat = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.kategori_id,
                   m.rak_id, m.batch, m.material_group, m.safety_stock, m.status, m.keterangan,
                   r.kode_rak, r.zona,
                   k.nama_kategori
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            LEFT JOIN kategoris k ON k.id = m.kategori_id
            WHERE m.id = ?
        ", [$id])->getRowArray();

        if (!$mat) {
            return $this->response->setJSON(['error' => 'Material tidak ditemukan']);
        }

        return $this->response->setJSON([
            'material' => $mat,
        ]);
    }

    // ── POST: simpan perubahan material ─────────────────────────────────────────

    public function update($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $json = $this->request->getJSON(true);

        $nama      = trim($json['nama_material'] ?? '');
        $kodeSap   = trim($json['kode_sap']      ?? '');
        $satuan    = trim($json['satuan']         ?? '');
        $katId     = $json['kategori_id']         ?? null;
        $kodeRak   = trim($json['kode_rak']       ?? '');
        $rakKatId  = $json['rak_kategori_id']     ?? null;
        $rakBaris  = $json['rak_baris']           ?? null;
        $rakKolom  = $json['rak_kolom']           ?? null;
        $rakDetail = trim($json['rak_detail']     ?? '');
        $safety    = $json['safety_stock']        ?? null;
        $keterangan = trim($json['keterangan']    ?? '');

        if (!$nama || !$kodeSap || !$satuan) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama, Kode SAP, dan Satuan wajib diisi']);
        }

        $db = \Config\Database::connect();

        // Cek duplikat kode SAP (selain diri sendiri)
        $cek = $db->query("SELECT id FROM materials WHERE kode_sap = ? AND id != ?", [$kodeSap, $id])->getRowArray();
        if ($cek) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kode SAP sudah digunakan material lain']);
        }

        // Proses rak — mode terstruktur (kategori + baris + kolom) atau freetext lama
        $rakId = null;
        if ($rakKatId && $rakBaris && $rakKolom) {
            $kategori = $db->table('rak_kategori')->where('id', $rakKatId)->get()->getRowArray();
            if (!$kategori) {
                return $this->response->setJSON(['success' => false, 'message' => 'Kategori rak tidak ditemukan']);
            }
            $baris = (int)$rakBaris;
            $kolom = (int)$rakKolom;
            if ($baris < 1 || $baris > (int)$kategori['max_baris']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Baris melebihi batas maksimal (' . $kategori['max_baris'] . ') untuk kategori ' . $kategori['kode_kategori']]);
            }
            if ($kolom < 1 || $kolom > (int)$kategori['max_kolom']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Kolom melebihi batas maksimal (' . $kategori['max_kolom'] . ') untuk kategori ' . $kategori['kode_kategori']]);
            }

            $kodeRak = $kategori['kode_kategori'] . '.' . $baris . '.' . $kolom . ($rakDetail !== '' ? '(' . $rakDetail . ')' : '');
            $rak = $db->table('rak')->where('kode_rak', $kodeRak)->get()->getRowArray();
            if ($rak) {
                $rakId = $rak['id'];
            } else {
                $db->table('rak')->insert([
                    'kode_rak'    => $kodeRak,
                    'zona'        => $kategori['zona'] ?: strtok($kodeRak, '.'),
                    'kategori_id' => $kategori['id'],
                    'baris'       => $baris,
                    'kolom'       => $kolom,
                    'detail'      => $rakDetail !== '' ? $rakDetail : null,
                ]);
                $rakId = $db->insertID();
            }
        } elseif ($kodeRak !== '') {
            $rak = $db->table('rak')->where('kode_rak', $kodeRak)->get()->getRowArray();
            if ($rak) {
                $rakId = $rak['id'];
            } else {
                $db->table('rak')->insert([
                    'kode_rak' => $kodeRak,
                    'zona'     => strtok($kodeRak, '.'),
                ]);
                $rakId = $db->insertID();
            }
        }

        $db->table('materials')->where('id', $id)->update([
            'nama_material' => $nama,
            'kode_sap'      => $kodeSap,
            'satuan'        => $satuan,
            'kategori_id'   => $katId ?: null,
            'rak_id'        => $rakId,
            'safety_stock'  => ($safety !== '' && $safety !== null) ? (int)$safety : null,
            'keterangan'    => $keterangan,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        // Ambil data terbaru untuk kembalikan ke JS
        $updated = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan, m.kategori_id,
                   m.safety_stock, m.keterangan,
                   r.kode_rak, r.zona,
                   k.nama_kategori
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            LEFT JOIN kategoris k ON k.id = m.kategori_id
            WHERE m.id = ?
        ", [$id])->getRowArray();

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Data material berhasil diperbarui',
            'material' => $updated,
        ]);
    }

    // ── Helper query ───────────────────────────────────────────────────────────

    private function queryMaterials($db, $search, $katId, $limit, $offset)
    {
        $conditions = ["m.status = 'aktif'"];
        $binds      = [];

        if ($search !== '') {
            $conditions[] = "(m.kode_sap LIKE ? OR m.nama_material LIKE ? OR r.kode_rak LIKE ?)";
            $s = '%' . $search . '%';
            $binds = array_merge($binds, [$s, $s, $s]);
        }
        if ($katId !== '') {
            $conditions[] = "m.kategori_id = ?";
            $binds[] = $katId;
        }

        $where = implode(' AND ', $conditions);

        $sql = "
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan,
                   m.safety_stock, m.keterangan,
                   r.kode_rak, r.zona,
                   k.nama_kategori
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            LEFT JOIN kategoris k ON k.id = m.kategori_id
            WHERE {$where}
            ORDER BY m.nama_material ASC
        ";

        $total     = $db->query("SELECT COUNT(*) as cnt FROM ({$sql}) sub", $binds)->getRow()->cnt;
        $materials = $db->query($sql . " LIMIT {$limit} OFFSET {$offset}", $binds)->getResultArray();

        return [$total, $materials];
    }
}