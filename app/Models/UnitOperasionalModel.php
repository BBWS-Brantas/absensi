<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitOperasionalModel extends Model
{
    protected $table = 'unit_operasional';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'slug'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
}
