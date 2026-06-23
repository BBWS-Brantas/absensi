<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_pegawai', 'email', 'username', 'password_hash', 'active', 'activate_hash'];
    protected $useTimestamps = true;

    public function getUserInfo($userId)
    {
        // Tabel users
        $builder = $this->db->table('users');

        $builder->select('
            users.id as userid, 
            users.*,
            auth_groups.name as role,
            auth_groups.id as role_id,
            pegawai.*,
            jabatan.jabatan,
            unit_operasional.nama as nama_unit,
        ');

        // Join Tabel auth_groups_users
        $builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');

        // Join Tabel auth_groups
        $builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');

        // Join Tabel pegawai
        $builder->join('pegawai', 'pegawai.id = users.id_pegawai');

        // Join Tabel jabatan
        $builder->join('jabatan', 'jabatan.id = pegawai.id_jabatan');

        // Join Tabel unit_operasional (left join — head tidak punya unit)
        $builder->join('unit_operasional', 'unit_operasional.id = pegawai.id_unit', 'left');

        // Hanya untuk satu user, berdasarkan user_id yang sedang Login
        $builder->where('users.id', $userId);

        $query = $builder->get();
        return $query->getRow();
    }

    public function hashPassword($password_string)
    {
        return password_hash(base64_encode(hash('sha384', $password_string, true)), PASSWORD_DEFAULT);
    }
}
