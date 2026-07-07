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
            return $this->json(['success' => false, 'message' => 'Username sudah digunakan.']);
        }
        // Role plant wajib pilih plant
        if ($role === 'plant' && !$plant_id) {
            return $this->json(['success' => false, 'message' => 'Role Plant wajib memilih Plant.']);
        }

        $this->userModel->insert([
            'nama'      => $nama,
            'username'  => $username,
            'password'  => md5($password),
            'role'      => $role,
            'plant_id'  => $plant_id,
            'is_active' => 1,
        ]);

        return $this->json(['success' => true, 'message' => 'User berhasil ditambahkan.']);
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
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        $nama     = trim($this->request->getPost('nama'));
        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');
        $role     = $this->request->getPost('role');
        $plant_id = $this->request->getPost('plant_id') ?: null;

        if (!$nama || !$username || !$role) {
            return $this->json(['success' => false, 'message' => 'Nama, username, dan role wajib diisi.']);
        }
        if ($this->userModel->usernameExists($username, $id)) {
            return $this->json(['success' => false, 'message' => 'Username sudah digunakan user lain.']);
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
        if ($password) {
            if (strlen($password) < 6) {
                return $this->json(['success' => false, 'message' => 'Password minimal 6 karakter.']);
            }
            $data['password'] = md5($password);
        }

        $this->userModel->update($id, $data);
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