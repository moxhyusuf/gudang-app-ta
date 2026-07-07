<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\NotifikasiModel;

class Booking extends BaseController
{
    public function index()
    {
        $bookingModel  = new BookingModel();
        $notifModel    = new NotifikasiModel();
        $userId        = session()->get('user_id');
        $role          = session()->get('role');

        // Cek kadaluarsa setiap halaman dibuka
        $bookingModel->prosesKadaluarsa();

        $db    = \Config\Database::connect();
        $page  = max(1, (int)($this->request->getGet('page') ?? 1));
        $limit = 10;
        $offset= ($page - 1) * $limit;

        // Ambil data plant milik user yang login
        $userPlant = $db->query("
            SELECT p.id, p.nama_plant FROM plants p
            INNER JOIN users u ON u.plant_id = p.id
            WHERE u.id = ? LIMIT 1
        ", [$userId])->getRowArray();

        $data = [
            'title'        => 'Booking Material',
            'role'         => $role,
            'nama'         => session()->get('nama'),
            'plants'       => $db->table('plants')->where('is_active', 1)->orderBy('nama_plant')->get()->getResultArray(),
            'no_booking'   => $bookingModel->generateNomor(),
            'user_plant_id'   => $userPlant['id']       ?? session()->get('plant_id') ?? '',
            'user_plant_nama' => $userPlant['nama_plant'] ?? '',
            'riwayat'      => $bookingModel->getRiwayatByUser($userId, $limit, $offset),
            'total'        => $bookingModel->countRiwayatByUser($userId),
            'current_page' => $page,
            'total_page'   => (int)ceil($bookingModel->countRiwayatByUser($userId) / $limit),
        ];

        return view('booking/index', $data);
    }


    
    // ── AJAX: cari material untuk booking ────────────────────────────────────
    public function cariMaterial()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');

$kode = trim($this->request->getGet('kode') ?? '');
    $id   = (int)($this->request->getGet('id')   ?? 0);
   $db   = \Config\Database::connect();

if ($id > 0) {
           $mat = $db->query("
               SELECT m.id, m.kode_sap, m.nama_material, m.satuan,
                      m.stok, m.stok_booking,
                      (m.stok - m.stok_booking) AS stok_tersedia, r.kode_rak
               FROM materials m LEFT JOIN rak r ON r.id = m.rak_id
               WHERE m.id = ? AND m.status = 'aktif' LIMIT 1
           ", [$id])->getRowArray();
       } else {
           $mat = $db->query("...WHERE m.kode_sap = ?...", [$kode])->getRowArray();
       }

        // Cek apakah kode 7xxxxxx
        if (substr($mat['kode_sap'], 0, 1) !== '7') {
            return $this->response->setJSON([
                'found'    => true,
                'blocked'  => true,
                'message'  => 'Booking hanya untuk material umum (kode 7xxxxxx). Untuk material selain itu, silahkan lakukan proses pada SAP.',
            ]);
        }

        if ($mat['stok_tersedia'] <= 0) {
            return $this->response->setJSON([
                'found'         => true,
                'blocked'       => false,
                'stok_habis'    => true,
                'material'      => $mat,
            ]);
        }

        return $this->response->setJSON([
            'found'      => true,
            'blocked'    => false,
            'stok_habis' => false,
            'material'   => $mat,
        ]);
    }

    public function searchMaterial()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');
 
        $q    = trim($this->request->getGet('q') ?? '');
        $db   = \Config\Database::connect();
 
        if (strlen($q) < 2) {
            return $this->response->setJSON(['materials' => []]);
        }
 
        $like = '%' . $q . '%';
        $mats = $db->query("
            SELECT m.id, m.kode_sap, m.nama_material, m.satuan,
                   m.stok, m.stok_booking,
                   (m.stok - m.stok_booking) AS stok_tersedia,
                   r.kode_rak
            FROM materials m
            LEFT JOIN rak r ON r.id = m.rak_id
            WHERE m.status = 'aktif'
              AND (m.kode_sap LIKE ? OR m.nama_material LIKE ?)
            ORDER BY
              CASE WHEN m.kode_sap LIKE ? THEN 0 ELSE 1 END,
              m.nama_material ASC
            LIMIT 30
        ", [$like, $like, $q . '%'])->getResultArray();
 
        return $this->response->setJSON(['materials' => $mats]);
    }
    // ── POST: submit booking ──────────────────────────────────────────────────
    public function simpan()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');

        $json   = $this->request->getJSON(true);
        $header = $json['header'] ?? [];
        $items  = $json['items']  ?? [];

        if (empty($header['plant_id']) || empty($header['tanggal_butuh'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lengkapi data booking!']);
        }
        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang masih kosong!']);
        }

        $bookingModel = new BookingModel();
        $result       = $bookingModel->simpan($header, $items);

        return $this->response->setJSON($result);
    }

    // ── AJAX: detail booking ──────────────────────────────────────────────────
    public function detail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');

        $bookingModel = new BookingModel();
        $db           = \Config\Database::connect();
        $userId       = session()->get('user_id');
        $role         = session()->get('role');

        $header = $db->query("
            SELECT bh.*, p.nama_plant, u.nama AS nama_user
            FROM booking_header bh
            LEFT JOIN plants p ON p.id = bh.plant_id
            LEFT JOIN users u  ON u.id = bh.user_id
            WHERE bh.id = ?
        ", [$id])->getRowArray();

        if (!$header) {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
        }

        // Plant hanya bisa lihat booking miliknya
        if ($role === 'plant' && (int)$header['user_id'] !== (int)$userId) {
            return $this->response->setJSON(['error' => 'Akses ditolak']);
        }

        return $this->response->setJSON([
            'header' => $header,
            'detail' => $bookingModel->getDetail($id),
        ]);
    }

    // ── POST: selesai (admin GT) ──────────────────────────────────────────────
    public function selesai($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');
        if (session()->get('role') === 'plant') {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $bookingModel = new BookingModel();
        return $this->response->setJSON($bookingModel->selesai($id));
    }

    // ── POST: batal (admin GT) ────────────────────────────────────────────────
    public function batal($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');
        if (session()->get('role') === 'plant') {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $json   = $this->request->getJSON(true);
        $alasan = trim($json['alasan'] ?? '');

        $bookingModel = new BookingModel();
        return $this->response->setJSON($bookingModel->batal($id, $alasan));
    }

    // ── AJAX: notifikasi ──────────────────────────────────────────────────────
    public function notifikasi()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');
        $notifModel = new NotifikasiModel();
        $userId     = session()->get('user_id');
        return $this->response->setJSON([
            'count' => $notifModel->countUnread($userId),
            'list'  => $notifModel->getUnread($userId),
        ]);
    }

    public function bacaNotif($id)
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');
        $notifModel = new NotifikasiModel();
        $notifModel->markRead(session()->get('user_id'), $id);
        return $this->response->setJSON(['success' => true]);
    }

    public function bacaSemuaNotif()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');
        $notifModel = new NotifikasiModel();
        $notifModel->markRead(session()->get('user_id'));
        return $this->response->setJSON(['success' => true]);
    }

    

    // ── AJAX: refresh riwayat (ditambahkan untuk fix refresh setelah booking) ──
    public function riwayatAjax()
    {
        if (!$this->request->isAJAX()) return redirect()->to('/booking');

        $bookingModel = new BookingModel();
        $userId = session()->get('user_id');
        $riwayat = $bookingModel->getRiwayatByUser($userId, 10, 0);

        $html = '';
        if (empty($riwayat)) {
            $html = '<tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:2rem">Belum ada riwayat booking</td></tr>';
        } else {
            foreach ($riwayat as $r) {
                $status = htmlspecialchars($r['status']);
                $sisa   = (int)$r['sisa_hari'];

                // Badge — samakan dengan bkBadge() di view
                $badgeMap = [
                    'pending'    => '<span class="badge-gt badge-pending">Pending</span>',
                    'selesai'    => '<span class="badge-gt badge-selesai">Selesai</span>',
                    'batal'      => '<span class="badge-gt badge-ditolak">Dibatalkan</span>',
                    'kadaluarsa' => '<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>',
                ];
                $badge = $badgeMap[$r['status']] ?? '<span class="badge-gt badge-umum">'.$status.'</span>';

                // Sisa hari — hanya tampil jika pending
                if ($r['status'] === 'pending') {
                    $sisaStyle = $sisa <= 1 ? 'color:#c0282d;font-weight:700' : 'color:#b45309';
                    $sisaTxt   = $sisa > 0 ? $sisa.' hari' : 'Hari ini!';
                } else {
                    $sisaStyle = 'color:#9ca3af';
                    $sisaTxt   = '—';
                }

                $html .= '<tr data-no="'.htmlspecialchars($r['no_booking']).'" data-status="'.$status.'">';
                $html .= '<td><span class="mono" style="font-size:.75rem;color:var(--navy3)">'.htmlspecialchars($r['no_booking']).'</span></td>';
                $html .= '<td>'.htmlspecialchars($r['tanggal_booking']).'</td>';
                $html .= '<td>'.(int)$r['jml_item'].' item</td>';
                $html .= '<td>'.$badge.'</td>';
                $html .= '<td>'.htmlspecialchars($r['tanggal_butuh'] ?? '—').'</td>';
                $html .= '<td style="'.$sisaStyle.'">'.$sisaTxt.'</td>';
                $html .= '<td><button class="btn-sm-g" onclick="showRwDetail('.(int)$r['id'].')">Detail</button></td>';
                $html .= '</tr>';
            }
        }

        return $this->response->setJSON(['html' => $html]);
    }

}