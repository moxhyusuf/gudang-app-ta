<?php

namespace App\Models;

class PenerimaanModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── Resolve/validasi lokasi rak dari item penerimaan ────────────────────────
    // Mendukung 2 mode:
    //  1) Terstruktur: item punya rak_kategori_id + rak_baris + rak_kolom (opsional rak_detail)
    //     -> divalidasi ulang di server terhadap batas max_baris/max_kolom kategori,
    //        lalu kode_rak disusun ulang di server (tidak mempercayai kiriman client).
    //  2) Legacy: item hanya punya kode_rak bebas (dipakai fitur lama / edit inline)
    //     -> cari atau buat baris baru di tabel rak seperti sebelumnya.
    private function resolveRakId($db, $item)
    {
        if (!empty($item['rak_kategori_id']) && !empty($item['rak_baris']) && !empty($item['rak_kolom'])) {
            $kategori = $db->table('rak_kategori')->where('id', $item['rak_kategori_id'])->get()->getRowArray();
            if (!$kategori) {
                throw new \Exception('Kategori rak tidak ditemukan');
            }

            $baris = (int)$item['rak_baris'];
            $kolom = (int)$item['rak_kolom'];
            if ($baris < 1 || $baris > (int)$kategori['max_baris']) {
                throw new \Exception('Baris rak melebihi batas maksimal (' . $kategori['max_baris'] . ') untuk kategori ' . $kategori['kode_kategori']);
            }
            if ($kolom < 1 || $kolom > (int)$kategori['max_kolom']) {
                throw new \Exception('Kolom rak melebihi batas maksimal (' . $kategori['max_kolom'] . ') untuk kategori ' . $kategori['kode_kategori']);
            }

            $detail  = trim($item['rak_detail'] ?? '');
            $kodeRak = $kategori['kode_kategori'] . '.' . $baris . '.' . $kolom . ($detail !== '' ? '(' . $detail . ')' : '');

            $rak = $db->table('rak')->where('kode_rak', $kodeRak)->get()->getRowArray();
            if ($rak) {
                return $rak['id'];
            }

            $db->table('rak')->insert([
                'kode_rak'    => $kodeRak,
                'zona'        => $kategori['zona'] ?: strtok($kodeRak, '.'),
                'kategori_id' => $kategori['id'],
                'baris'       => $baris,
                'kolom'       => $kolom,
                'detail'      => $detail !== '' ? $detail : null,
                'is_active'   => 1,
            ]);
            return $db->insertID();
        }

        if (!empty($item['kode_rak'])) {
            $rak = $db->table('rak')->where('kode_rak', $item['kode_rak'])->get()->getRowArray();
            if ($rak) {
                return $rak['id'];
            }
            $db->table('rak')->insert([
                'kode_rak'  => $item['kode_rak'],
                'zona'      => strtok($item['kode_rak'], '.'),
                'is_active' => 1,
            ]);
            return $db->insertID();
        }

        return null;
    }

    public function generateNomor()
    {
        $tahun = date('Y');
        $row   = $this->db->query("
            SELECT COUNT(*) as cnt FROM penerimaan_header
            WHERE YEAR(tanggal_terima) = ?
        ", [$tahun])->getRow();
        $urut = str_pad($row->cnt + 1, 3, '0', STR_PAD_LEFT);
        return "SP-{$tahun}-{$urut}";
    }

    public function countBulanIni()
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM penerimaan_header
            WHERE tanggal_terima >= ? AND tanggal_terima <= ?
        ", [date('Y-m-01'), date('Y-m-t')])->getRow()->cnt;
    }

    public function countRiwayat()
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM penerimaan_header
        ")->getRow()->cnt;
    }

    public function getRiwayat($limit = 20, $offset = 0)
    {
        return $this->db->query("
            SELECT ph.id, ph.no_surat_penerimaan, ph.tanggal_terima,
                   ph.supplier_id, ph.user_id, ph.catatan, ph.created_at,
                   s.nama_supplier,
                   u.nama AS nama_petugas,
                   COUNT(pd.id) AS jml_item
            FROM penerimaan_header ph
            LEFT JOIN suppliers s          ON s.id  = ph.supplier_id
            LEFT JOIN users u              ON u.id  = ph.user_id
            LEFT JOIN penerimaan_detail pd ON pd.header_id = ph.id
            GROUP BY ph.id, ph.no_surat_penerimaan, ph.tanggal_terima,
                     ph.supplier_id, ph.user_id, ph.catatan, ph.created_at,
                     s.nama_supplier, u.nama
            ORDER BY ph.created_at DESC
            LIMIT ? OFFSET ?
        ", [$limit, $offset])->getResultArray();
    }

    public function getDetail($headerId)
    {
        return $this->db->query("
            SELECT pd.*,
                   m.nama_material, m.kode_sap, m.satuan, m.is_tabung, m.batch,
                   r.kode_rak, r.zona,
                   t.no_tabung
            FROM penerimaan_detail pd
            LEFT JOIN materials m ON m.id  = pd.material_id
            LEFT JOIN rak r       ON r.id  = pd.rak_id
            LEFT JOIN tabung t    ON t.id  = pd.tabung_id
            WHERE pd.header_id = ?
            ORDER BY pd.id ASC
        ", [$headerId])->getResultArray();
    }

    // ── EDIT: catat log perubahan ─────────────────────────────────────────────
    public function catatLog($headerId, $detailId, $aksi, $fieldDiubah, $keterangan = null)
    {
        $this->db->table('penerimaan_edit_log')->insert([
            'header_id'    => $headerId,
            'detail_id'    => $detailId,
            'aksi'         => $aksi,
            'field_diubah' => $fieldDiubah ? json_encode($fieldDiubah, JSON_UNESCAPED_UNICODE) : null,
            'keterangan'   => $keterangan,
            'user_id'      => session()->get('user_id'),
            'nama_user'    => session()->get('nama'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ── EDIT: ambil log perubahan untuk satu header ───────────────────────────
    public function getEditLog($headerId)
    {
        return $this->db->query("
            SELECT el.*, u.nama AS nama_user_join
            FROM penerimaan_edit_log el
            LEFT JOIN users u ON u.id = el.user_id
            WHERE el.header_id = ?
            ORDER BY el.created_at DESC
        ", [$headerId])->getResultArray();
    }

    // ── EDIT: update header penerimaan ────────────────────────────────────────
    public function editHeader($headerId, $data)
    {
        $db = $this->db;

        // Ambil data lama
        $lama = $db->query("SELECT * FROM penerimaan_header WHERE id = ?", [$headerId])->getRowArray();
        if (!$lama) return ['success' => false, 'message' => 'Header tidak ditemukan'];

        $fields  = [];
        $changes = [];

        if (isset($data['tanggal_terima']) && $data['tanggal_terima'] !== $lama['tanggal_terima']) {
            $fields['tanggal_terima'] = $data['tanggal_terima'];
            $changes['tanggal_terima'] = ['lama' => $lama['tanggal_terima'], 'baru' => $data['tanggal_terima']];
        }
        if (isset($data['supplier_id']) && (string)$data['supplier_id'] !== (string)$lama['supplier_id']) {
            $fields['supplier_id'] = $data['supplier_id'];
            $changes['supplier_id'] = ['lama' => $lama['supplier_id'], 'baru' => $data['supplier_id']];
        }
        if (array_key_exists('catatan', $data) && $data['catatan'] !== $lama['catatan']) {
            $fields['catatan'] = $data['catatan'];
            $changes['catatan'] = ['lama' => $lama['catatan'], 'baru' => $data['catatan']];
        }

        if (empty($fields)) return ['success' => true, 'message' => 'Tidak ada perubahan'];

        $db->table('penerimaan_header')->where('id', $headerId)->update($fields);
        $this->catatLog($headerId, null, 'edit_header', $changes, 'Edit header penerimaan');

        return ['success' => true];
    }

    // ── EDIT: update satu item detail ─────────────────────────────────────────
    public function editItem($headerId, $detailId, $data)
    {
        $db = $this->db;

        $lama = $db->query("
            SELECT pd.*, m.nama_material, m.kode_sap, m.stok
            FROM penerimaan_detail pd
            LEFT JOIN materials m ON m.id = pd.material_id
            WHERE pd.id = ? AND pd.header_id = ?
        ", [$detailId, $headerId])->getRowArray();

        if (!$lama) return ['success' => false, 'message' => 'Item tidak ditemukan'];

        try {
            $db->transBegin();

            $fields  = [];
            $changes = [];
            $jumlahLama = (int)$lama['jumlah_terima'];
            $jumlahBaru = isset($data['jumlah_terima']) ? (int)$data['jumlah_terima'] : $jumlahLama;

            if ($jumlahBaru !== $jumlahLama) {
                $fields['jumlah_terima'] = $jumlahBaru;
                $changes['jumlah_terima'] = ['lama' => $jumlahLama, 'baru' => $jumlahBaru];
                // Sesuaikan stok material
                $selisih = $jumlahBaru - $jumlahLama;
                $db->query("UPDATE materials SET stok = stok + ? WHERE id = ?", [$selisih, $lama['material_id']]);
                // Sesuaikan kepemilikan jika ada requester
                if ($lama['requester']) {
                    $db->query("
                        UPDATE material_kepemilikan SET qty = qty + ?
                        WHERE material_id = ? AND requester = ?
                    ", [$selisih, $lama['material_id'], $lama['requester']]);
                }
            }
            if (isset($data['kondisi']) && $data['kondisi'] !== $lama['kondisi']) {
                $fields['kondisi'] = $data['kondisi'];
                $changes['kondisi'] = ['lama' => $lama['kondisi'], 'baru' => $data['kondisi']];
            }
            if (isset($data['requester']) && $data['requester'] !== $lama['requester']) {
                // Sesuaikan kepemilikan: kurangi requester lama, tambah requester baru
                if ($lama['requester']) {
                    $db->query("
                        UPDATE material_kepemilikan SET qty = GREATEST(0, qty - ?)
                        WHERE material_id = ? AND requester = ?
                    ", [$jumlahBaru, $lama['material_id'], $lama['requester']]);
                }
                if (!empty($data['requester'])) {
                    $existing = $db->query("
                        SELECT id FROM material_kepemilikan WHERE material_id = ? AND requester = ?
                    ", [$lama['material_id'], $data['requester']])->getRow();
                    if ($existing) {
                        $db->query("
                            UPDATE material_kepemilikan SET qty = qty + ? WHERE material_id = ? AND requester = ?
                        ", [$jumlahBaru, $lama['material_id'], $data['requester']]);
                    } else {
                        $db->table('material_kepemilikan')->insert([
                            'material_id' => $lama['material_id'],
                            'requester'   => $data['requester'],
                            'qty'         => $jumlahBaru,
                        ]);
                    }
                }
                $fields['requester'] = $data['requester'] ?: null;
                $changes['requester'] = ['lama' => $lama['requester'], 'baru' => $data['requester']];
            }
            if (isset($data['rak_id']) && (string)$data['rak_id'] !== (string)$lama['rak_id']) {
                $fields['rak_id'] = $data['rak_id'] ?: null;
                $changes['rak_id'] = ['lama' => $lama['rak_id'], 'baru' => $data['rak_id']];
            }

            if (!empty($fields)) {
                $db->table('penerimaan_detail')->where('id', $detailId)->update($fields);
            }

            $db->transCommit();

            if (!empty($changes)) {
                $this->catatLog($headerId, $detailId, 'edit_item', $changes,
                    'Edit item: ' . ($lama['kode_sap'] ?? '') . ' ' . $lama['nama_material']);
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── EDIT: hapus satu item detail ──────────────────────────────────────────
    public function hapusItem($headerId, $detailId)
    {
        $db = $this->db;

        $item = $db->query("
            SELECT pd.*, m.nama_material, m.kode_sap
            FROM penerimaan_detail pd
            LEFT JOIN materials m ON m.id = pd.material_id
            WHERE pd.id = ? AND pd.header_id = ?
        ", [$detailId, $headerId])->getRowArray();

        if (!$item) return ['success' => false, 'message' => 'Item tidak ditemukan'];

        try {
            $db->transBegin();

            $jumlah = (int)$item['jumlah_terima'];

            // Kurangi stok material
            $db->query("UPDATE materials SET stok = GREATEST(0, stok - ?) WHERE id = ?",
                [$jumlah, $item['material_id']]);

            // Kurangi kepemilikan jika ada requester
            if ($item['requester']) {
                $db->query("
                    UPDATE material_kepemilikan SET qty = GREATEST(0, qty - ?)
                    WHERE material_id = ? AND requester = ?
                ", [$jumlah, $item['material_id'], $item['requester']]);
            }

            // Hapus baris detail
            $db->query("DELETE FROM penerimaan_detail WHERE id = ?", [$detailId]);

            $db->transCommit();

            $this->catatLog($headerId, $detailId, 'hapus_item', null,
                'Hapus item: ' . ($item['kode_sap'] ?? '—') . ' ' . $item['nama_material'] .
                ' (qty: ' . $jumlah . ')');

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── EDIT: tambah item baru ke header yang sudah ada ────────────────────────
    public function tambahItem($headerId, $item)
    {
        $db = $this->db;

        $header = $db->query("SELECT * FROM penerimaan_header WHERE id = ?", [$headerId])->getRowArray();
        if (!$header) return ['success' => false, 'message' => 'Header tidak ditemukan'];

        try {
            $db->transBegin();

            $matId     = $item['material_id'] ?? null;
            $jumlah    = (int)($item['jumlah_terima'] ?? 0);
            $requester = trim($item['requester'] ?? '');
            $rakId     = $item['rak_id'] ?? null;

            if ($jumlah < 1) throw new \Exception('Qty minimal 1');

            // Tambah stok
            $db->query("UPDATE materials SET stok = stok + ? WHERE id = ?", [$jumlah, $matId]);

            // Stok sesudah
            $stokRow     = $db->query("SELECT stok FROM materials WHERE id = ?", [$matId])->getRow();
            $stokSesudah = $stokRow ? $stokRow->stok : $jumlah;

            $db->table('penerimaan_detail')->insert([
                'header_id'        => $headerId,
                'material_id'      => $matId,
                'jumlah_terima'    => $jumlah,
                'kondisi'          => $item['kondisi'] ?? 'baik',
                'rak_id'           => $rakId,
                'is_material_baru' => 0,
                'tabung_id'        => null,
                'requester'        => $requester ?: null,
            ]);
            $newDetailId = $db->insertID();

            // Update kepemilikan
            if ($requester && $matId) {
                $existing = $db->query("
                    SELECT id FROM material_kepemilikan WHERE material_id = ? AND requester = ?
                ", [$matId, $requester])->getRow();
                if ($existing) {
                    $db->query("UPDATE material_kepemilikan SET qty = qty + ? WHERE material_id = ? AND requester = ?",
                        [$jumlah, $matId, $requester]);
                } else {
                    $db->table('material_kepemilikan')->insert([
                        'material_id' => $matId, 'requester' => $requester, 'qty' => $jumlah,
                    ]);
                }
            }

            // Mutasi stok
            $mat = $db->query("SELECT nama_material, kode_sap FROM materials WHERE id = ?", [$matId])->getRowArray();
            $db->table('mutasi_stok')->insert([
                'material_id'    => $matId,
                'jenis'          => 'masuk',
                'jumlah'         => $jumlah,
                'stok_sesudah'   => $stokSesudah,
                'referensi_tipe' => 'penerimaan',
                'referensi_id'   => $headerId,
                'user_id'        => session()->get('user_id'),
                'keterangan'     => "Tambah item ke {$header['no_surat_penerimaan']}" . ($requester ? " | Req: {$requester}" : ''),
            ]);

            $db->transCommit();

            $this->catatLog($headerId, $newDetailId, 'tambah_item', null,
                'Tambah item: ' . ($mat['kode_sap'] ?? '—') . ' ' . ($mat['nama_material'] ?? '') .
                ' (qty: ' . $jumlah . ')');

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getKepemilikan($materialId)
    {
        return $this->db->query("
            SELECT requester, qty, updated_at
            FROM material_kepemilikan
            WHERE material_id = ? AND qty > 0
            ORDER BY requester ASC
        ", [$materialId])->getResultArray();
    }

    // ── BARU: ambil daftar requester yang punya stok material ini ─────────────
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

            $noSurat = $this->generateNomor();

            $db->table('penerimaan_header')->insert([
                'no_surat_penerimaan' => $noSurat,
                'tanggal_terima'      => $header['tanggal_terima'],
                'supplier_id'         => $header['supplier_id'],
                'user_id'             => session()->get('user_id'),
                'catatan'             => $header['catatan'] ?? null,
            ]);
            $headerId = $db->insertID();

            foreach ($items as $item) {
                $matId      = $item['material_id'] ?? null;
                $isBaru     = (int)($item['is_material_baru'] ?? 0);
                $tanpakode  = (int)($item['tanpa_kode'] ?? 0);
                $jumlah     = (int)$item['jumlah_terima'];
                $rakId      = $item['rak_id'] ?? null;
                $requester  = trim($item['requisitioner'] ?? '');

                if ($tanpakode) {
                    $rakId = $this->resolveRakId($db, $item);

                    $db->table('materials')->insert([
                        'kode_sap'     => null,
                        'nama_material'=> $item['nama_material'],
                        'batch'        => $item['batch'] ?? 'UMUM',
                        'satuan'       => $item['satuan'],
                        'rak_id'       => $rakId,
                        'is_tabung'    => 0,
                        'stok'         => $jumlah,
                        'stok_booking' => 0,
                        'status'       => 'aktif',
                    ]);
                    $matId = $db->insertID();

                } elseif ($isBaru) {
                    $rakId = $this->resolveRakId($db, $item);

                    // Cek apakah material dengan kode_sap + batch sudah ada
                    $batchVal    = $item['batch'] ?? 'UMUM';
                    $existingMat = $db->query(
                        "SELECT id FROM materials WHERE kode_sap = ? AND batch = ? LIMIT 1",
                        [$item['kode_sap'], $batchVal]
                    )->getRow();

                    if ($existingMat) {
                        // Sudah ada — tambah stok, dan pastikan is_tabung sinkron
                        $matId = $existingMat->id;
                        $isTabungItem = (int)($item['is_tabung'] ?? 0);
                        $db->query(
                            "UPDATE materials SET stok = stok + ?, is_tabung = GREATEST(is_tabung, ?) WHERE id = ?",
                            [$jumlah, $isTabungItem, $matId]
                        );
                    } else {
                        // Belum ada — insert material baru
                        $db->table('materials')->insert([
                            'kode_sap'       => $item['kode_sap'],
                            'nama_material'  => $item['nama_material'],
                            'batch'          => $batchVal,
                            'material_group' => $item['material_group'] ?? null,
                            'satuan'         => $item['satuan'],
                            'kategori_id'    => $item['kategori_id'] ?? null,
                            'rak_id'         => $rakId,
                            'is_tabung'      => (int)($item['is_tabung'] ?? 0),
                            'stok'           => $jumlah,
                            'stok_booking'   => 0,
                            'status'         => 'aktif',
                        ]);
                        $matId = $db->insertID();
                    }

                } else {
                    $db->query("UPDATE materials SET stok = stok + ? WHERE id = ?", [$jumlah, $matId]);
                }

                // Hitung stok sesudah
                $stokRow     = $db->query("SELECT stok FROM materials WHERE id = ?", [$matId])->getRow();
                $stokSesudah = $stokRow ? $stokRow->stok : $jumlah;

                $tabungId = null;
                if (!empty($item['is_tabung']) && !empty($item['no_tabung_list'])) {
                    $firstTabungId = null;
                    foreach ($item['no_tabung_list'] as $noTabung) {
                        $noTabung = trim($noTabung);
                        if (!$noTabung) continue;
                        $db->table('tabung')->insert([
                            'no_tabung'     => $noTabung,
                            'material_id'   => $matId,
                            'tanggal_masuk' => $header['tanggal_terima'],
                            'kondisi'       => $item['kondisi'] ?? 'baik',
                            'status'        => 'tersedia',
                            'user_id'       => session()->get('user_id'),
                        ]);
                        if ($firstTabungId === null) {
                            $firstTabungId = $db->insertID();
                        }
                    }
                    $tabungId = $firstTabungId;
                }

                $db->table('penerimaan_detail')->insert([
                    'header_id'        => $headerId,
                    'material_id'      => $matId,
                    'jumlah_terima'    => $jumlah,
                    'kondisi'          => $item['kondisi'] ?? 'baik',
                    'rak_id'           => $rakId,
                    'is_material_baru' => ($isBaru || $tanpakode) ? 1 : 0,
                    'tabung_id'        => $tabungId,
                    'requester'        => $requester ?: null,
                ]);

                // ── UPDATE material_kepemilikan ───────────────────────────
                if ($requester && $matId) {
                    $existing = $db->query("
                        SELECT id, qty FROM material_kepemilikan
                        WHERE material_id = ? AND requester = ?
                    ", [$matId, $requester])->getRow();

                    if ($existing) {
                        $db->query("
                            UPDATE material_kepemilikan SET qty = qty + ?
                            WHERE material_id = ? AND requester = ?
                        ", [$jumlah, $matId, $requester]);
                    } else {
                        $db->table('material_kepemilikan')->insert([
                            'material_id' => $matId,
                            'requester'   => $requester,
                            'qty'         => $jumlah,
                        ]);
                    }
                }

                $db->table('mutasi_stok')->insert([
                    'material_id'    => $matId,
                    'jenis'          => 'masuk',
                    'jumlah'         => $jumlah,
                    'stok_sesudah'   => $stokSesudah,
                    'referensi_tipe' => 'penerimaan',
                    'referensi_id'   => $headerId,
                    'user_id'        => session()->get('user_id'),
                    'keterangan'     => "Penerimaan {$noSurat}" . ($requester ? " | Req: {$requester}" : ''),
                ]);
            }

            $db->transComplete();
            return ['success' => true, 'no_surat' => $noSurat];

        } catch (\Throwable $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage() . ' | ' . basename($e->getFile()) . ':' . $e->getLine(),
            ];
        }
    }
}