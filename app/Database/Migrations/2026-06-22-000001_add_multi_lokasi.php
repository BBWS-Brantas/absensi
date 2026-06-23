<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMultiLokasi extends Migration
{
    public function up()
    {
        // Pivot pegawai <-> lokasi_presensi (many-to-many)
        $this->forge->addField([
            'id'                 => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_pegawai'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'id_lokasi_presensi' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'active'             => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at'         => ['type' => 'datetime', 'null' => true],
            'updated_at'         => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // UNIQUE agar tidak ada assignment ganda pegawai-lokasi
        $this->forge->addKey(['id_pegawai', 'id_lokasi_presensi'], false, true);
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_lokasi_presensi', 'lokasi_presensi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('lokasi_presensi_pegawai', true);

        // Kolom id_unit pada lokasi_presensi (scoping per unit) — null aman utk data lama.
        // Tanpa FK, mengikuti pola kolom pegawai.id_unit.
        $this->forge->addColumn('lokasi_presensi', [
            'id_unit' => [
                'type'       => 'int',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tipe_lokasi',
            ],
        ]);

        // Kolom id_lokasi_presensi pada presensi (catat lokasi tiap check-in) — nullable.
        $this->forge->addColumn('presensi', [
            'id_lokasi_presensi' => [
                'type'       => 'int',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id_pegawai',
            ],
        ]);

        // Kolom id_lokasi_keluar pada presensi (lokasi saat presensi keluar; boleh
        // berbeda dari lokasi masuk) — nullable utk baris lama & yang belum keluar.
        $this->forge->addColumn('presensi', [
            'id_lokasi_keluar' => [
                'type'       => 'int',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id_lokasi_presensi',
            ],
        ]);

        // Backfill: assignment pivot + lokasi pada presensi historis (dari lokasi tunggal lama),
        // sebelum kolom 1:1 dihapus.
        $this->db->query('INSERT INTO `lokasi_presensi_pegawai` (`id_pegawai`, `id_lokasi_presensi`) SELECT `id`, `id_lokasi_presensi` FROM `pegawai` WHERE `id_lokasi_presensi` IS NOT NULL');
        $this->db->query('UPDATE `presensi` p JOIN `pegawai` pg ON pg.id = p.id_pegawai SET p.id_lokasi_presensi = pg.id_lokasi_presensi WHERE p.id_lokasi_presensi IS NULL');

        // Lepas relasi 1:1 lama pada pegawai (sekarang banyak lokasi via pivot)
        $this->forge->dropForeignKey('pegawai', 'pegawai_id_lokasi_presensi_foreign');
        $this->forge->dropColumn('pegawai', 'id_lokasi_presensi');
    }

    public function down()
    {
        // Kembalikan kolom 1:1 pada pegawai
        $this->forge->addColumn('pegawai', [
            'id_lokasi_presensi' => [
                'type'       => 'int',
                'constraint' => 11,
                'unsigned'   => true,
                'after'      => 'id_jabatan',
            ],
        ]);
        $this->db->query('ALTER TABLE `pegawai` ADD CONSTRAINT `pegawai_id_lokasi_presensi_foreign` FOREIGN KEY (`id_lokasi_presensi`) REFERENCES `lokasi_presensi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->forge->dropColumn('presensi', 'id_lokasi_keluar');
        $this->forge->dropColumn('presensi', 'id_lokasi_presensi');
        $this->forge->dropColumn('lokasi_presensi', 'id_unit');
        $this->forge->dropTable('lokasi_presensi_pegawai', true);
    }
}
