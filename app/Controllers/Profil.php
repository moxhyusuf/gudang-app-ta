<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profil extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ── Halaman Profil ────────────────────────────────────────────────────────
    public function index()
    {
        $role = session()->get('role');
        if ($role === 'plant') {
            return redirect()->to('/dashboard');
        }

        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/dashboard');
        }

        return view('profil/index', [
            'title' => 'Profil Saya',
            'user'  => $user,
        ]);
    }

    // ── Upload Foto Profil ────────────────────────────────────────────────────
    public function updateFoto()
    {
        $role = session()->get('role');
        if ($role === 'plant') {
            return $this->json(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $userId = (int) session()->get('user_id');
        $foto   = $this->request->getFile('foto');

        if (!$foto || !$foto->isValid()) {
            return $this->json(['success' => false, 'message' => 'File tidak valid.']);
        }

        // Validasi tipe & ukuran
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($foto->getMimeType(), $allowedTypes)) {
            return $this->json(['success' => false, 'message' => 'Format file harus JPG, PNG, atau WebP.']);
        }
        if ($foto->getSize() > 2 * 1024 * 1024) {
            return $this->json(['success' => false, 'message' => 'Ukuran file maksimal 2MB.']);
        }

        // Hapus foto lama kalau ada
        $user = $this->userModel->find($userId);
        if (!empty($user['foto'])) {
            $oldPath = FCPATH . 'uploads/foto_profil/' . $user['foto'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Simpan foto baru
        $namaFile = 'profil_' . $userId . '_' . time() . '.' . $foto->getExtension();
        $foto->move(FCPATH . 'uploads/foto_profil/', $namaFile);

        $this->userModel->update($userId, ['foto' => $namaFile]);

        return $this->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'foto_url' => '/uploads/foto_profil/' . $namaFile,
        ]);
    }

    // ── Hapus Foto Profil ─────────────────────────────────────────────────────
    public function hapusFoto()
    {
        $role = session()->get('role');
        if ($role === 'plant') {
            return $this->json(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (!empty($user['foto'])) {
            $oldPath = FCPATH . 'uploads/foto_profil/' . $user['foto'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $this->userModel->update($userId, ['foto' => null]);
        }

        return $this->json(['success' => true, 'message' => 'Foto profil berhasil dihapus.']);
    }

    // ── Update Nama ───────────────────────────────────────────────────────────
    public function updateNama()
    {
        $role = session()->get('role');
        if ($role === 'plant') {
            return $this->json(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $userId = (int) session()->get('user_id');
        $nama   = trim($this->request->getPost('nama'));

        if (!$nama) {
            return $this->json(['success' => false, 'message' => 'Nama tidak boleh kosong.']);
        }
        if (strlen($nama) < 3) {
            return $this->json(['success' => false, 'message' => 'Nama minimal 3 karakter.']);
        }

        $this->userModel->update($userId, ['nama' => $nama]);
        session()->set('nama', $nama);

        return $this->json(['success' => true, 'message' => 'Nama berhasil diperbarui.', 'nama' => $nama]);
    }

    // ── Ganti Password ────────────────────────────────────────────────────────
    public function updatePassword()
    {
        $role = session()->get('role');
        if ($role === 'plant') {
            return $this->json(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $userId       = (int) session()->get('user_id');
        $passwordLama = $this->request->getPost('password_lama');
        $passwordBaru = $this->request->getPost('password_baru');
        $konfirmasi   = $this->request->getPost('konfirmasi');

        if (!$passwordLama || !$passwordBaru || !$konfirmasi) {
            return $this->json(['success' => false, 'message' => 'Semua field password wajib diisi.']);
        }

        $user = $this->userModel->find($userId);
        if (!$user || $user['password'] !== md5($passwordLama)) {
            return $this->json(['success' => false, 'message' => 'Password lama tidak sesuai.']);
        }

        if (strlen($passwordBaru) < 6) {
            return $this->json(['success' => false, 'message' => 'Password baru minimal 6 karakter.']);
        }

        if ($passwordBaru !== $konfirmasi) {
            return $this->json(['success' => false, 'message' => 'Konfirmasi password tidak cocok.']);
        }

        $this->userModel->update($userId, ['password' => md5($passwordBaru)]);

        return $this->json(['success' => true, 'message' => 'Password berhasil diperbarui.']);
    }

    // ── Helper JSON ───────────────────────────────────────────────────────────
    private function json($data)
    {
        return $this->response->setJSON($data);
    }
}