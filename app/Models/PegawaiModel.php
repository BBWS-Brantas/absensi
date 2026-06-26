<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiModel extends Model
{
    protected $db, $builder;
    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nip', 'nama', 'jenis_kelamin', 'alamat', 'no_handphone', 'id_jabatan', 'id_unit', 'foto'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('pegawai');
    }

    public function getPegawai($username = false, $filter = false, $print = false, $perPage = 10, $id_unit = null)
    {
        $pager = service('pager');
        $pager->setPath('data-pegawai', 'pegawai');

        $page = (@$_GET['page_pegawai']) ? $_GET['page_pegawai'] : 1;
        $offset = ($page - 1) * $perPage;

        $this->builder->select('pegawai.*, users.id as id_user, users.id_pegawai, users.username, users.active, users.email, auth_groups.name as role, auth_groups.id as role_id, jabatan.jabatan, unit_operasional.nama as nama_unit');
        // Daftar lokasi (banyak) via subquery agar tak menggandakan baris / kena ONLY_FULL_GROUP_BY
        $this->builder->select('(SELECT GROUP_CONCAT(lp.nama_lokasi ORDER BY lp.nama_lokasi SEPARATOR ", ") FROM lokasi_presensi_pegawai lpp JOIN lokasi_presensi lp ON lp.id = lpp.id_lokasi_presensi WHERE lpp.id_pegawai = pegawai.id AND lpp.active = 1) as lokasi_presensi', false);
        $this->builder->join('users', 'users.id_pegawai = pegawai.id');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $this->builder->join('jabatan', 'jabatan.id = pegawai.id_jabatan');
        $this->builder->join('unit_operasional', 'unit_operasional.id = pegawai.id_unit', 'left');
        $this->builder->orderBy('nip', 'ASC');

        // Scoping per unit (admin): null = tanpa filter (head)
        if ($id_unit !== null) {
            $this->builder->where('pegawai.id_unit', $id_unit);
        }

        $total = 0;

        if ($username) {
            $countQuery = clone $this->builder;
            $total = $countQuery->where('username', $username)->countAllResults();
        } else if ($filter) {
            $filter_keyword = $filter['keyword'];
            $filter_role = $filter['role'];
            $filter_status = $filter['status'];
            $filter_jenisKelamin = $filter['jenis-kelamin'];
            $filter_jabatan = $filter['jabatan'];
            $filter_lokasi = $filter['lokasi-presensi'];

            if ($filter_role) {
                $this->builder->where('auth_groups.name', $filter_role);
            }

            if ($filter_status !== null && $filter_status !== '') {
                $this->builder->where('users.active', (int) $filter_status);
            }

            if ($filter_jenisKelamin) {
                $this->builder->where('pegawai.jenis_kelamin', $filter_jenisKelamin);
            }

            if ($filter_jabatan) {
                $this->builder->where('jabatan.jabatan', $filter_jabatan);
            }

            if ($filter_lokasi) {
                $this->builder->where('EXISTS (SELECT 1 FROM lokasi_presensi_pegawai lpp JOIN lokasi_presensi lp ON lp.id = lpp.id_lokasi_presensi WHERE lpp.id_pegawai = pegawai.id AND lpp.active = 1 AND lp.nama_lokasi = ' . $this->db->escape($filter_lokasi) . ')', null, false);
            }

            $filter_unit = $filter['unit-operasional'] ?? '';
            if ($filter_unit) {
                $this->builder->where('pegawai.id_unit', (int) $filter_unit);
            }

            if ($filter_keyword) {
                $this->builder->groupStart()
                    ->like('pegawai.nama', $filter_keyword)
                    ->orLike('users.username', $filter_keyword)
                    ->orLike('users.email', $filter_keyword)
                    ->groupEnd();
            }
        }

        $countQuery = clone $this->builder;
        $total = $countQuery->countAllResults();

        if ($print) {
            $result = $this->builder->get()->getResult();
        } else if ($username) {
            $result = $this->builder->where('username', $username)->get($perPage, $offset)->getRow();
        } else {
            $result = $this->builder->get($perPage, $offset)->getResult();
        }

        // $pager->makeLinks($page, $perPage, $total, 'template_name', $segment, 'my-group');
        return [
            'pegawai' => $result,
            'links' => $pager->makeLinks($page, $perPage, $total, 'my_pagination', 0, 'pegawai'),
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
        ];
    }

    public function getNIPPegawai()
    {
        $builder = $this->db->table('pegawai');
        $builder->selectMax('nip', 'latest_nip');

        $query = $builder->get();
        $result = $query->getRow();

        return $result->latest_nip;
    }

    public function getJumlahPegawaiAktif($id_unit = null)
    {
        $builder = $this->db->table('pegawai');
        $builder->select('pegawai.*, users.id as id_user, users.id_pegawai, users.username, users.active, users.email, auth_groups.name as role, auth_groups.id as role_id');
        $builder->join('users', 'users.id_pegawai = pegawai.id');
        $builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $builder->where('active', 1)->where("auth_groups.name NOT LIKE 'head'");

        if ($id_unit !== null) {
            $builder->where('pegawai.id_unit', $id_unit);
        }

        $query = $builder->get();
        return $query->getNumRows();
    }
}
