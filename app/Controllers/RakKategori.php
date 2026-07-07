<?php

namespace App\Controllers;

use App\Models\RakKategoriModel;

class RakKategori extends BaseController
{
    // ── Halaman kelola kategori rak (di dalam menu Mapping) ─────────────────────
    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return redirect()->to('/dashboard');
        }

        $model = new RakKategoriModel();

        $data = [
            'title'     => 'Kelola Kategori Rak',
            'role'      => $role,
            'kategoris' => $model->getAll(),
        ];

        return view('mapping/rak', $data);
    }

    // ── AJAX: daftar kategori rak (dipakai picker lokasi rak) ───────────────────
    // Sengaja tidak dibatasi role admin karena dipakai juga di form Penerimaan
    public function list()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $model  = new RakKategoriModel();
        $search = $this->request->getGet('search') ?? '';

        return $this->response->setJSON([
            'kategori' => $model->getAll($search),
        ]);
    }

    // ── AJAX: tambah kategori rak baru ───────────────────────────────────────────
    // Tidak dibatasi role admin — bisa dipakai petugas saat input penerimaan
    // jika rak/kategori memang belum terdaftar.
    public function simpan()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $json  = $this->request->getJSON(true);
        $model = new RakKategoriModel();
        $result = $model->simpanBaru([
            'kode_kategori' => $json['kode_kategori'] ?? '',
            'max_baris'     => $json['max_baris']     ?? 1,
            'max_kolom'     => $json['max_kolom']     ?? 1,
            'keterangan'    => $json['keterangan']    ?? '',
        ]);

        return $this->response->setJSON($result);
    }

    // ── AJAX: update kategori rak ────────────────────────────────────────────────
    public function update($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $json  = $this->request->getJSON(true);
        $model = new RakKategoriModel();
        $result = $model->updateData($id, [
            'kode_kategori' => $json['kode_kategori'] ?? '',
            'max_baris'     => $json['max_baris']     ?? 1,
            'max_kolom'     => $json['max_kolom']     ?? 1,
            'keterangan'    => $json['keterangan']    ?? '',
        ]);

        return $this->response->setJSON($result);
    }

    // ── AJAX: tambah baris / kolom (perluas batas) ───────────────────────────────
    // Tidak dibatasi role admin — inilah fitur "tambah baris/kolom" yang juga
    // tersedia langsung dari form input penerimaan.
    public function perluas($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $json  = $this->request->getJSON(true);
        $tipe  = ($json['tipe'] ?? 'baris') === 'kolom' ? 'kolom' : 'baris';
        $tambah = (int)($json['tambah'] ?? 1);

        $model  = new RakKategoriModel();
        $result = $model->perluas($id, $tipe, $tambah);

        return $this->response->setJSON($result);
    }

    // ── AJAX: hapus kategori rak ──────────────────────────────────────────────────
    public function hapus($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $model  = new RakKategoriModel();
        $result = $model->hapus($id);

        return $this->response->setJSON($result);
    }

    // ── AJAX: import massal dari teks tempel ─────────────────────────────────────
    public function import()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/mapping');

        $role = session()->get('role');
        if (!in_array($role, ['petugas_gt', 'admin_gt'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $json  = $this->request->getJSON(true);
        $model = new RakKategoriModel();
        $result = $model->importBaris($json['teks'] ?? '');

        return $this->response->setJSON($result);
    }
}