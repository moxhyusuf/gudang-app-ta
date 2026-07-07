<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\PenerimaanModel;
use App\Models\BookingModel;
use App\Models\MutasiModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = session()->get('role');

        if ($role === 'plant') {
            return $this->dashboardPlant();
        }

        $materialModel   = new MaterialModel();
        $penerimaanModel = new PenerimaanModel();
        $bookingModel    = new BookingModel();
        $mutasiModel     = new MutasiModel();

        $data = [
            'title'              => 'Dashboard',
            'role'               => $role,
            'nama'               => session()->get('nama'),
            'total_material'     => $materialModel->countAktif(),
            'total_stok_kritis'  => $materialModel->countKritis(),
            'penerimaan_bulan'   => $penerimaanModel->countBulanIni(),
            'booking_pending'    => $bookingModel->countPending(),
            'stok_kritis'        => $materialModel->getStokKritisList(5),
            'aktivitas'          => $mutasiModel->getAktivitasTerbaru(6),
            'booking_list'       => $role === 'admin_gt'
                                        ? $bookingModel->getPendingList(5)
                                        : [],
        ];

        return view('dashboard/index', $data);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function dashboardPlant()
    {
        $userId          = session()->get('user_id');
        $materialModel   = new MaterialModel();
        $bookingModel    = new BookingModel();

        $data = [
            'title'                => 'Dashboard',
            'role'                 => session()->get('role'),
            'nama'                 => session()->get('nama'),
            'total_material'       => $materialModel->countAktif(),
            'total_stok_kritis'    => $materialModel->countKritis(),
            'booking_aktif'        => $bookingModel->countAktifByUser($userId),
            'booking_aktif_detail' => $bookingModel->getAktifByUser($userId, 5),
            'riwayat_booking'      => $bookingModel->getRiwayatByUser($userId, 5),
            'stok_kritis'          => $materialModel->getStokKritisList(5),
        ];

        return view('dashboard/plant', $data);
    }
}