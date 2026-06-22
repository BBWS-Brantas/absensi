<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitOperasional extends Migration
{
    public function up()
    {
        // Unit Operasional (wilayah OP) — dimensi scoping role admin & pegawai
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama'       => ['type' => 'varchar', 'constraint' => 255],
            'slug'       => ['type' => 'varchar', 'constraint' => 255],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('unit_operasional', true);

        // Kolom id_unit pada pegawai — null agar aman untuk data lama (head tidak butuh unit)
        $this->forge->addColumn('pegawai', [
            'id_unit' => [
                'type'       => 'int',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id_lokasi_presensi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pegawai', 'id_unit');
        $this->forge->dropTable('unit_operasional', true);
    }
}
