<?php

namespace App\Models;

class NotifikasiModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── Kirim notifikasi ke user tertentu ─────────────────────────────────────
    public function kirim($userId, $judul, $pesan, $tipe = 'info', $refTipe = null, $refId = null)
    {
        $this->db->table('notifikasi')->insert([
            'user_id'        => $userId,
            'judul'          => $judul,
            'pesan'          => $pesan,
            'tipe'           => $tipe,
            'referensi_tipe' => $refTipe,
            'referensi_id'   => $refId,
            'is_read'        => 0,
        ]);
    }

    // ── Kirim ke semua admin_gt ───────────────────────────────────────────────
    public function kirimKeAdmin($judul, $pesan, $tipe = 'warning', $refTipe = null, $refId = null)
    {
        $admins = $this->db->query("
            SELECT id FROM users WHERE role = 'admin_gt' AND is_active = 1
        ")->getResultArray();

        foreach ($admins as $admin) {
            $this->kirim($admin['id'], $judul, $pesan, $tipe, $refTipe, $refId);
        }
    }

    // ── Ambil notifikasi user (belum dibaca) ──────────────────────────────────
    public function getUnread($userId)
    {
        return $this->db->query("
            SELECT * FROM notifikasi
            WHERE user_id = ? AND is_read = 0
            ORDER BY created_at DESC
            LIMIT 20
        ", [$userId])->getResultArray();
    }

    // ── Count notifikasi belum dibaca ─────────────────────────────────────────
    public function countUnread($userId)
    {
        return $this->db->query("
            SELECT COUNT(*) as cnt FROM notifikasi
            WHERE user_id = ? AND is_read = 0
        ", [$userId])->getRow()->cnt;
    }

    // ── Tandai sudah dibaca ───────────────────────────────────────────────────
    public function markRead($userId, $id = null)
    {
        if ($id) {
            $this->db->query("UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
        } else {
            $this->db->query("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?", [$userId]);
        }
    }
}