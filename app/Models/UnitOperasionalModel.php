<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitOperasionalModel extends Model
{
    protected $db, $builder;
    protected $table = 'unit_operasional';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['nama', 'slug'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('unit_operasional');
    }

    public function getUnit($slug = false, $keyword = false, $perPage = 10)
    {
        $pager = service('pager');
        $pager->setPath('unit-operasional', 'unit');

        $page = (@$_GET['page_unit']) ? $_GET['page_unit'] : 1;
        $offset = ($page - 1) * $perPage;

        $this->builder->select('unit_operasional.*, COUNT(pegawai.id) as total_pegawai');
        $this->builder->join('pegawai', 'pegawai.id_unit = unit_operasional.id', 'left');
        $this->builder->where('unit_operasional.deleted_at', null);
        $this->builder->groupBy('unit_operasional.id');
        $this->builder->orderBy('unit_operasional.nama', 'ASC');

        $total = 0;

        if ($slug) {
            $countQuery = clone $this->builder;
            $total = $countQuery->where('slug', $slug)->countAllResults();

            $result = $this->builder->where('slug', $slug)->get($perPage, $offset)->getRowArray();
        } elseif ($keyword) {
            $countQuery = clone $this->builder;
            $total = $countQuery->like('unit_operasional.nama', $keyword)->countAllResults();

            $result = $this->builder->like('unit_operasional.nama', $keyword)->get($perPage, $offset)->getResult();
        } else {
            $result = $this->builder->get($perPage, $offset)->getResult();
            $total = $this->builder->countAllResults();
        }

        return [
            'unit' => $result,
            'links' => $pager->makeLinks($page, $perPage, $total, 'my_pagination', 0, 'unit'),
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
        ];
    }
}
