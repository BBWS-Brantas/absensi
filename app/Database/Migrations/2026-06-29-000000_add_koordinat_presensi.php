<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKoordinatPresensi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('presensi', [
            'lat_masuk'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'foto_masuk'],
            'lng_masuk'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'lat_masuk'],
            'lat_keluar' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'foto_keluar'],
            'lng_keluar' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'lat_keluar'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('presensi', ['lat_masuk', 'lng_masuk', 'lat_keluar', 'lng_keluar']);
    }
}
