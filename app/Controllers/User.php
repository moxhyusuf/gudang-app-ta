<?php

namespace App\Controllers;

use App\Models\UserModel;

class User extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ── Halaman utama ─────────────────────────────────────────────────────────
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $role   = $this->request->getGet('role')   ?? '';
        $page   = (int)($this->request->getGet('page') ?? 1);
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        $total      = $this->userModel->countFiltered($search, $role);
        $users      = $this->userModel->getAllWithPlant($search, $role, $limit, $offset);
        $total_page = ceil($total / $limit);

        // Ambil daftar plant untuk form tambah/edit
        $plants = \Config\Database::connect()
            ->table('plants')->where('is_active', 1)->get()->getResultArray();

        return view('user/index', compact(
            'users', 'total', 'total_page', 'page',
            'search', 'role', 'plants'
        ));
    }

    // ── Simpan user baru ──────────────────────────────────────────────────────
    public function simpan()
    {
        $nama     = trim($this->request->getPost('nama'));
        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');
        $role     = $this->request->getPost('role');
        $plant_id = $this->request->getPost('plant_id') ?: null;

        // Validasi
        if (!$nama || !$username || !$password || !$role) {
            return $this->json(['success' => false, 'message' => 'Semua field wajib diisi.']);
        }
        if (strlen($password) < 6) {
            return $this->json(['success' => false, 'message' => 'Password minimal 6 karakter.']);
        }
        if ($this->userModel->usernameExists($username)) {
            return $this->json(['success' => false, 'message' => 'Username "' . $username . '" sudah digunakan (termasuk oleh akun yang sudah dihapus). Gunakan username lain.']);
        }
        // Role plant wajib pilih plant
        if ($role === 'plant' && !$plant_id) {
            return $this->json(['success' => false, 'message' => 'Role Plant wajib memilih Plant.']);
        }

        try {
            $this->userModel->insert([
                'nama'      => $nama,
                'username'  => $username,
                'password'  => md5($password),
                'role'      => $role,
                'plant_id'  => $plant_id,
                'is_active' => 1,
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->json(['success' => false, 'message' => 'Gagal menyimpan user: username kemungkinan sudah dipakai. Coba username lain.']);
        }

        return $this->json(['success' => true, 'message' => 'User berhasil ditambahkan.']);
    }

    // ── Tambah plant baru (dipanggil dari form Tambah/Edit User) ───────────────
    public function tambahPlant()
    {
        $nama = trim((string) $this->request->getPost('nama_plant'));

        if (!$nama) {
            return $this->json(['success' => false, 'message' => 'Nama plant wajib diisi.']);
        }
        if (strlen($nama) < 2) {
            return $this->json(['success' => false, 'message' => 'Nama plant minimal 2 karakter.']);
        }

        $db = \Config\Database::connect();

        $existing = $db->table('plants')->where('nama_plant', $nama)->get()->getRowArray();
        if ($existing) {
            return $this->json(['success' => false, 'message' => 'Plant dengan nama tersebut sudah ada.']);
        }

        // Kolom `kode_plant` di tabel `plants` bersifat unique. Kita buatkan
        // kode secara otomatis dari nama plant (mis. "Pack Area" -> "PACK_AREA"),
        // lalu pastikan tidak bentrok dengan kode yang sudah ada dengan
        // menambahkan angka di belakang bila perlu (PACK_AREA2, PACK_AREA3, ...).
        $baseKode = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $nama));
        $baseKode = trim($baseKode, '_');
        if ($baseKode === '') {
            $baseKode = 'PLANT';
        }

        $kode = $baseKode;
        $suffix = 2;
        while ($db->table('plants')->where('kode_plant', $kode)->get()->getRowArray()) {
            $kode = $baseKode . $suffix;
            $suffix++;
        }

        try {
            $db->table('plants')->insert([
                'nama_plant' => $nama,
                'kode_plant' => $kode,
                'is_active'  => 1,
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->json(['success' => false, 'message' => 'Gagal menambah plant. Coba ulangi dengan nama yang sedikit berbeda.']);
        }
        $id = $db->insertID();

        return $this->json([
            'success' => true,
            'message' => 'Plant "' . $nama . '" berhasil ditambahkan.',
            'data'    => ['id' => $id, 'nama_plant' => $nama],
        ]);
    }

    // ── Ambil data satu user (untuk form edit) ────────────────────────────────
    public function get($id)
    {
        $user = $this->userModel->getById($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }
        unset($user['password']);
        return $this->json(['success' => true, 'data' => $user]);
    }

    // ── Update user ───────────────────────────────────────────────────────────
    // Catatan: sengaja TIDAK menerima/mengubah field password di sini.
    // Admin tidak diperbolehkan mengubah password milik user lain — user
    // hanya bisa mengganti password miliknya sendiri lewat halaman Profil
    // (Profil::updatePassword), yang mewajibkan password lama sebagai verifikasi.
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        $nama     = trim($this->request->getPost('nama'));
        $username = trim($this->request->getPost('username'));
        $role     = $this->request->getPost('role');
        $plant_id = $this->request->getPost('plant_id') ?: null;

        if (!$nama || !$username || !$role) {
            return $this->json(['success' => false, 'message' => 'Nama, username, dan role wajib diisi.']);
        }
        if ($this->userModel->usernameExists($username, $id)) {
            return $this->json(['success' => false, 'message' => 'Username "' . $username . '" sudah digunakan user lain (termasuk akun yang sudah dihapus).']);
        }
        if ($role === 'plant' && !$plant_id) {
            return $this->json(['success' => false, 'message' => 'Role Plant wajib memilih Plant.']);
        }

        $data = [
            'nama'     => $nama,
            'username' => $username,
            'role'     => $role,
            'plant_id' => $plant_id,
        ];

        try {
            $ok = $this->userModel->update($id, $data);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->json(['success' => false, 'message' => 'Gagal menyimpan perubahan: username kemungkinan sudah dipakai.']);
        }
        if ($ok === false) {
            return $this->json(['success' => false, 'message' => 'Gagal menyimpan perubahan: ' . implode(' ', $this->userModel->errors())]);
        }

        return $this->json(['success' => true, 'message' => 'User berhasil diperbarui.']);
    }

    // ── Toggle aktif/nonaktif ─────────────────────────────────────────────────
    public function toggleStatus($id)
    {
        // Jangan nonaktifkan diri sendiri
        if ((int)$id === (int)session()->get('user_id')) {
            return $this->json(['success' => false, 'message' => 'Tidak dapat menonaktifkan akun sendiri.']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        $msg = $newStatus ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';
        return $this->json(['success' => true, 'message' => $msg, 'is_active' => $newStatus]);
    }

    // ── Hapus user ────────────────────────────────────────────────────────────
    public function hapus($id)
    {
        if ((int)$id === (int)session()->get('user_id')) {
            return $this->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        try {
            $this->userModel->delete($id);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Gagal hapus karena user masih punya relasi data (mis. pernah membuat bon)
            return $this->json([
                'success' => false,
                'blocked' => true, // beri tahu frontend supaya menampilkan opsi "Tetap Hapus"
                'message' => 'User "' . $user['nama'] . '" tidak dapat dihapus permanen karena masih memiliki riwayat transaksi (bon/data terkait) di sistem. Menghapus paksa akan tetap menyembunyikan user ini dari daftar, namun aktivitas penghapusan akan dicatat.',
            ]);
        }

        // Hapus berhasil total (user memang belum punya relasi data apa pun)
        $this->logHapus($user, 'hard_delete', null);

        return $this->json(['success' => true, 'message' => 'User berhasil dihapus.']);
    }

    // ── Hapus paksa (soft delete) untuk user yang masih punya relasi data ──────
    public function forceHapus($id)
    {
        if ((int)$id === (int)session()->get('user_id')) {
            return $this->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $user = $this->userModel->find($id);
        if (!$user || (int)($user['is_deleted'] ?? 0) === 1) {
            return $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        $alasan = trim((string) $this->request->getPost('alasan'));

        // Soft delete: sembunyikan dari daftar, tapi baris tetap ada supaya
        // relasi data (bon_header, dll) yang merujuk ke user ini tidak rusak.
        $this->userModel->update($id, [
            'is_deleted' => 1,
            'is_active'  => 0,
        ]);

        $this->logHapus($user, 'force_delete', $alasan ?: null);

        return $this->json([
            'success' => true,
            'message' => 'User "' . $user['nama'] . '" berhasil dihapus dari daftar. Aktivitas ini tercatat atas nama ' . session()->get('nama') . '.',
        ]);
    }

    // ── Catat siapa yang melakukan aksi hapus ───────────────────────────────────
    private function logHapus(array $user, string $tipe, ?string $alasan): void
    {
        \Config\Database::connect()->table('user_delete_log')->insert([
            'user_id'         => $user['id'],
            'user_nama'       => $user['nama'],
            'user_username'   => $user['username'],
            'tipe_hapus'      => $tipe,
            'deleted_by'      => session()->get('user_id'),
            'deleted_by_nama' => session()->get('nama'),
            'alasan'          => $alasan,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Riwayat aktivitas hapus user (untuk ditampilkan di modal) ──────────────
    public function riwayatHapus()
    {
        $logs = \Config\Database::connect()
            ->table('user_delete_log')
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return $this->json(['success' => true, 'data' => $logs]);
    }

    // ── Helper JSON response ──────────────────────────────────────────────────
    private function json($data)
    {
        return $this->response->setJSON($data);
    }
}