<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LokasiPresensiPegawaiSeeder extends Seeder
{
    public function run()
    {
        // Assignment lokasi per pegawai (many-to-many).
        // Pegawai 3 (Christoper) sengaja diberi 2 lokasi untuk contoh multi-lokasi.
        $data = [
            ['id_pegawai' => 1, 'id_lokasi_presensi' => 1], // head Jaya
            ['id_pegawai' => 2, 'id_lokasi_presensi' => 1], // admin Tamani (OP I)
            ['id_pegawai' => 3, 'id_lokasi_presensi' => 1], // pegawai Christoper
            ['id_pegawai' => 3, 'id_lokasi_presensi' => 2], // pegawai Christoper (lokasi kedua)
        ];

        $this->db->table('lokasi_presensi_pegawai')->insertBatch($data);
    }
}
