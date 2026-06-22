<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitOperasionalSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama' => 'OP I',    'slug' => 'op-i'],
            ['nama' => 'OP II',   'slug' => 'op-ii'],
            ['nama' => 'OP III',  'slug' => 'op-iii'],
            ['nama' => 'OP IV',   'slug' => 'op-iv'],
            ['nama' => 'OP PIAT', 'slug' => 'op-piat'],
        ];

        $this->db->table('unit_operasional')->insertBatch($data);
    }
}
