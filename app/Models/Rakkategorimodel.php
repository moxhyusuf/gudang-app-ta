<?php

namespace App\Models;

class RakKategoriModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── Ambil semua kategori rak (untuk halaman kelola & picker) ───────────────
    public function getAll($search = '')
    {
        $builder = $this->db->table('rak_kategori')->where('is_active', 1);
        if ($search !== '') {
            $builder->like('kode_kategori', $search);
        }
        return $builder->orderBy('kode_kategori', 'ASC')->get()->getResultArray();
    }

    public function find($id)
    {
        return $this->db->table('rak_kategori')->where('id', $id)->get()->getRowArray();
    }

    public function findByKode($kode)
    {
        return $this->db->table('rak_kategori')
            ->where('kode_kategori', trim($kode))
            ->get()->getRowArray();
    }

    // ── Simpan kategori baru ────────────────────────────────────────────────────
    public function simpanBaru($data)
    {
        $kode = trim($data['kode_kategori'] ?? '');
        if ($kode === '') {
            return ['success' => false, 'message' => 'Nama/kode kategori rak wajib diisi'];
        }

        $existing = $this->findByKode($kode);
        if ($existing) {
            return ['success' => false, 'message' => 'Kategori rak "' . $kode . '" sudah ada', 'kategori' => $existing];
        }

        $maxBaris = max(1, (int)($data['max_baris'] ?? 1));
        $maxKolom = max(1, (int)($data['max_kolom'] ?? 1));

        $this->db->table('rak_kategori')->insert([
            'kode_kategori' => $kode,
            'zona'          => strtok($kode, '.'),
            'max_baris'     => $maxBaris,
            'max_kolom'     => $maxKolom,
            'keterangan'    => trim($data['keterangan'] ?? '') ?: null,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'kategori' => $this->find($this->db->insertID())];
    }

    // ── Update kategori ──────────────────────────────────────────────────────────
    public function updateData($id, $data)
    {
        $row = $this->find($id);
        if (!$row) {
            return ['success' => false, 'message' => 'Kategori rak tidak ditemukan'];
        }

        $kode = trim($data['kode_kategori'] ?? $row['kode_kategori']);
        if ($kode === '') {
            return ['success' => false, 'message' => 'Nama/kode kategori rak wajib diisi'];
        }

        $dup = $this->findByKode($kode);
        if ($dup && (int)$dup['id'] !== (int)$id) {
            return ['success' => false, 'message' => 'Kategori rak "' . $kode . '" sudah digunakan'];
        }

        $this->db->table('rak_kategori')->where('id', $id)->update([
            'kode_kategori' => $kode,
            'zona'          => strtok($kode, '.'),
            'max_baris'     => max(1, (int)($data['max_baris'] ?? $row['max_baris'])),
            'max_kolom'     => max(1, (int)($data['max_kolom'] ?? $row['max_kolom'])),
            'keterangan'    => trim($data['keterangan'] ?? '') ?: null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'kategori' => $this->find($id)];
    }

    // ── Tambah baris / kolom (perluas batas) ────────────────────────────────────
    public function perluas($id, $tipe, $tambah = 1)
    {
        $row = $this->find($id);
        if (!$row) {
            return ['success' => false, 'message' => 'Kategori rak tidak ditemukan'];
        }

        $tambah = max(1, (int)$tambah);
        $field  = $tipe === 'kolom' ? 'max_kolom' : 'max_baris';

        $this->db->table('rak_kategori')->where('id', $id)->update([
            $field       => (int)$row[$field] + $tambah,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'kategori' => $this->find($id)];
    }

    // ── Hapus (soft, hanya jika tidak dipakai rak manapun) ──────────────────────
    public function hapus($id)
    {
        $dipakai = $this->db->table('rak')->where('kategori_id', $id)->countAllResults();
        if ($dipakai > 0) {
            return ['success' => false, 'message' => 'Kategori rak masih dipakai ' . $dipakai . ' lokasi rak, tidak bisa dihapus'];
        }
        $this->db->table('rak_kategori')->where('id', $id)->delete();
        return ['success' => true];
    }

    // ── Import massal (paste teks: "KODE BARIS KOLOM" per baris) ───────────────
    public function importBaris($teks)
    {
        $lines   = preg_split('/\r\n|\r|\n/', trim((string)$teks));
        $sukses  = 0;
        $gagal   = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Pisahkan berdasarkan spasi/tab, format: KODE BARIS KOLOM [keterangan...]
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 3) {
                $gagal[] = $line . ' (format tidak lengkap, butuh: KODE BARIS KOLOM)';
                continue;
            }

            $kode  = $parts[0];
            $baris = (int)$parts[1];
            $kolom = (int)$parts[2];

            if ($baris < 1 || $kolom < 1) {
                $gagal[] = $line . ' (baris/kolom harus angka > 0)';
                continue;
            }

            $existing = $this->findByKode($kode);
            if ($existing) {
                $this->db->table('rak_kategori')->where('id', $existing['id'])->update([
                    'max_baris'  => $baris,
                    'max_kolom'  => $kolom,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->table('rak_kategori')->insert([
                    'kode_kategori' => $kode,
                    'zona'          => strtok($kode, '.'),
                    'max_baris'     => $baris,
                    'max_kolom'     => $kolom,
                    'is_active'     => 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            }
            $sukses++;
        }

        return ['success' => true, 'jumlah_sukses' => $sukses, 'gagal' => $gagal];
    }
}