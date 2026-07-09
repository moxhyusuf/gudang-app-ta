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

    // ── Auto-kategorisasi rak lama yang formatnya SUDAH sesuai ──────────────────
    // Rak lama (kategori_id NULL) yang kode-nya berformat "PREFIX.BARIS.KOLOM"
    // (mis. "A.4.1.1", "A.4.1.2", "A.4.2.1") tapi PREFIX-nya ("A.4") belum
    // terdaftar sebagai kategori resmi, dikelompokkan per PREFIX lalu dijadikan
    // kategori resmi baru — batas baris & kolomnya diambil PERSIS dari nilai
    // baris/kolom terbesar yang sudah pernah dipakai di kelompok itu. Rak yang
    // kode-nya memang tidak berformat (mis. "R-2", "RAK PLAT") tidak disentuh,
    // tetap tampil sebagai rak lama/bebas biasa.
    //
    // Idempoten & aman dipanggil berkali-kali: begitu suatu PREFIX sudah resmi
    // (kategori_id ter-update), rak-rak tsb otomatis tidak lagi masuk hitungan
    // di pemanggilan berikutnya karena query dasarnya sudah mensyaratkan
    // kategori_id IS NULL.
    public function promosikanRakLamaSesuaiFormat()
    {
        $rows = $this->db->query("
            SELECT id, kode_rak FROM rak WHERE is_active = 1 AND kategori_id IS NULL
        ")->getResultArray();

        if (!$rows) return;

        $daftarKategori   = $this->db->table('rak_kategori')->select('kode_kategori')->where('is_active', 1)->get()->getResultArray();
        $kodeKategoriList = array_map(function ($k) { return $k['kode_kategori']; }, $daftarKategori);

        // Kelompokkan per PREFIX kategori yang tersirat dari format kode_rak-nya.
        $groups = [];
        foreach ($rows as $r) {
            if (!preg_match('/^(.+)\.(\d+)\.(\d+).*$/', $r['kode_rak'], $m)) continue;

            $prefix = $m[1];
            if (in_array($prefix, $kodeKategoriList, true)) continue; // sudah kategori resmi, biar ditangani jalur lain

            $baris = max(1, (int)$m[2]);
            $kolom = max(1, (int)$m[3]);

            if (!isset($groups[$prefix])) {
                $groups[$prefix] = ['ids' => [], 'max_baris' => $baris, 'max_kolom' => $kolom];
            }
            $groups[$prefix]['ids'][]    = $r['id'];
            $groups[$prefix]['max_baris'] = max($groups[$prefix]['max_baris'], $baris);
            $groups[$prefix]['max_kolom'] = max($groups[$prefix]['max_kolom'], $kolom);
        }

        foreach ($groups as $prefix => $g) {
            $existing = $this->findByKode($prefix);
            if ($existing) {
                $kategoriId = $existing['id'];
                // Kalau kategori ini ternyata sudah ada tapi batasnya lebih kecil
                // dari yang sebenarnya sudah dipakai rak lama, ikut diperbesar.
                if ((int)$existing['max_baris'] < $g['max_baris'] || (int)$existing['max_kolom'] < $g['max_kolom']) {
                    $this->db->table('rak_kategori')->where('id', $kategoriId)->update([
                        'max_baris'  => max((int)$existing['max_baris'], $g['max_baris']),
                        'max_kolom'  => max((int)$existing['max_kolom'], $g['max_kolom']),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                $this->db->table('rak_kategori')->insert([
                    'kode_kategori' => $prefix,
                    'zona'          => strtok($prefix, '.'),
                    'max_baris'     => $g['max_baris'],
                    'max_kolom'     => $g['max_kolom'],
                    'keterangan'    => 'Otomatis terkategorikan dari rak lama',
                    'is_active'     => 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
                $kategoriId = $this->db->insertID();
            }

            $this->db->table('rak')->whereIn('id', $g['ids'])->update(['kategori_id' => $kategoriId]);
        }
    }

    // ── Rak lama/bebas: lokasi rak yang SUDAH ADA di tabel `rak` tapi TIDAK
    // terikat kategori (kategori_id kosong), jadi tidak wajib ikut format
    // baris/kolom. Contoh: rak-rak di zona R yang sudah terisi material sejak
    // awal. Tetap ditampilkan di picker lokasi rak supaya user bisa memilih
    // rak ini langsung, tanpa mengubah alur kategori yang sudah ada. ───────────
    public function getRakBebas($search = '')
    {
        // ── Daftar kode kategori aktif, dipakai sebagai referensi "sudah punya kategori" ──
        $daftarKategori   = $this->db->table('rak_kategori')->select('kode_kategori')->where('is_active', 1)->get()->getResultArray();
        $kodeKategoriList = array_map(function ($k) { return $k['kode_kategori']; }, $daftarKategori);

        $where = "r.is_active = 1 AND r.kategori_id IS NULL";
        $binds = [];
        if ($search !== '') {
            $where .= " AND r.kode_rak LIKE ?";
            $binds[] = '%' . $search . '%';
        }

        $rows = $this->db->query("
            SELECT r.id, r.kode_rak, r.zona
            FROM rak r
            WHERE {$where}
            ORDER BY r.kode_rak ASC
        ", $binds)->getResultArray();

        // Buang rak yang kode-nya sebenarnya "milik" kategori yang sudah ada — dicek
        // dari data kategori aktif sungguhan, bukan cuma nebak dari pola teks. Data lama
        // ternyata dipakai dengan format akhiran yang macam-macam (mis. "K.34.2.4 (kotak)",
        // "L.44.1.1 Ki", "L.44.1.2 Ka"), jadi kalau kode rak diawali "KODE_KATEGORI." lalu
        // angka baris, berarti itu sebenarnya cell dari kategori itu juga — tidak perlu
        // ditampilkan dobel sebagai "rak bebas". Rak yang memang tanpa kategori sama
        // sekali (mis. "R-2", "L-Prob", "RAK PLAT") tetap muncul seperti biasa.
        return array_values(array_filter($rows, function ($r) use ($kodeKategoriList) {
            foreach ($kodeKategoriList as $kode) {
                if (preg_match('/^' . preg_quote($kode, '/') . '\.\d/', $r['kode_rak'])) {
                    return false;
                }
            }
            return true;
        }));
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