<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        // Kalau sudah login, redirect ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = md5($this->request->getPost('password'));

        $db   = \Config\Database::connect();
        $user = $db->table('users')
                   ->where('username', $username)
                   ->where('password', $password)
                   ->where('is_active', 1)
                   ->get()->getRowArray();

        if ($user) {
            session()->set([
                'logged_in'   => true,
                'user_id'     => $user['id'],
                'nama'        => $user['nama'],
                'username'    => $user['username'],
                'role'        => $user['role'],
                'plant_id'    => $user['plant_id'],
                // Penanda "login baru" dipakai layout untuk menandai sessionStorage
                // di browser/aplikasi. Lihat app/Views/layout/main.php.
                'fresh_login' => true,
            ]);
            return redirect()->to('/dashboard');
        } else {
            session()->setFlashdata('error', 'Username atau password salah!');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}