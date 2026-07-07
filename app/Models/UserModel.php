<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'nama', 'username', 'password', 'role', 'plant_id', 'is_active', 'foto', 'is_deleted',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithPlant(string $search = '', string $role = '', int $limit = 15, int $offset = 0): array
    {
        $builder = $this->db->table('users u')
            ->select('u.id, u.nama, u.username, u.role, u.is_active, u.created_at, u.foto, p.nama_plant')
            ->join('plants p', 'p.id = u.plant_id', 'left')
            ->where('u.is_deleted', 0);

        if ($search) {
            $builder->groupStart()
                ->like('u.nama', $search)
                ->orLike('u.username', $search)
                ->groupEnd();
        }

        if ($role) {
            $builder->where('u.role', $role);
        }

        return $builder
            ->orderBy('u.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    public function countFiltered(string $search = '', string $role = ''): int
    {
        $builder = $this->db->table('users u')->where('u.is_deleted', 0);

        if ($search) {
            $builder->groupStart()
                ->like('u.nama', $search)
                ->orLike('u.username', $search)
                ->groupEnd();
        }

        if ($role) {
            $builder->where('u.role', $role);
        }

        return (int) $builder->countAllResults();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('users u')
            ->select('u.id, u.nama, u.username, u.role, u.plant_id, u.is_active, u.foto, p.nama_plant')
            ->join('plants p', 'p.id = u.plant_id', 'left')
            ->where('u.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $builder = $this->db->table('users')->where('username', $username)->where('is_deleted', 0);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}