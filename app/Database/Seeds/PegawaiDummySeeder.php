<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PegawaiDummySeeder extends Seeder
{
    public function run()
    {
        // 20 dummy pegawai, 4 per unit (OP I–IV & OP PIAT, id_unit 1–5).
        // NIPs PEG-0020–PEG-0039 are safe — live DB only has up to PEG-0009.
        // id_jabatan 6 = TPM (pegawai), 2 = Sales Lead (admin).
        // INSERT IGNORE makes reruns safe.
        $data = [
            // ── OP I (id_unit = 1) ────────────────────────────────────────
            [
                'nip'           => 'PEG-0020',
                'id_jabatan'    => 2,
                'id_unit'       => 1,
                'nama'          => 'Hendra Setiawan',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Gatot Subroto Kav.32, Kuningan Timur, Jakarta Selatan 12950',
                'no_handphone'  => '081265778899',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0021',
                'id_jabatan'    => 6,
                'id_unit'       => 1,
                'nama'          => 'Budi Santoso',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Sudirman No.5, Karet Tengsin, Tanah Abang, Jakarta Pusat 10220',
                'no_handphone'  => '081311223344',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0022',
                'id_jabatan'    => 6,
                'id_unit'       => 1,
                'nama'          => 'Rina Marlina',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Pramuka Raya No.8, Utan Kayu Selatan, Jakarta Timur 13120',
                'no_handphone'  => '085544667788',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0023',
                'id_jabatan'    => 6,
                'id_unit'       => 1,
                'nama'          => 'Dedi Kurniawan',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Pegangsaan Dua No.1, Kelapa Gading, Jakarta Utara 14250',
                'no_handphone'  => '083344556677',
                'foto'          => 'default.jpg',
            ],

            // ── OP II (id_unit = 2) ───────────────────────────────────────
            [
                'nip'           => 'PEG-0024',
                'id_jabatan'    => 2,
                'id_unit'       => 2,
                'nama'          => 'Andika Pratama',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Raya Bekasi Km.18, Pulogadung, Jakarta Timur 13920',
                'no_handphone'  => '083322334455',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0025',
                'id_jabatan'    => 6,
                'id_unit'       => 2,
                'nama'          => 'Fitri Handayani',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. TB Simatupang No.88, Cilandak, Jakarta Selatan 12460',
                'no_handphone'  => '087788990011',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0026',
                'id_jabatan'    => 6,
                'id_unit'       => 2,
                'nama'          => 'Rizky Firmansyah',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Bintaro Utama Sektor 3A, Pesanggrahan, Jakarta Selatan 12330',
                'no_handphone'  => '081399001122',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0027',
                'id_jabatan'    => 6,
                'id_unit'       => 2,
                'nama'          => 'Dewi Kusuma Wardani',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. RS Fatmawati No.33, Cilandak, Jakarta Selatan 12430',
                'no_handphone'  => '082211223344',
                'foto'          => 'default.jpg',
            ],

            // ── OP III (id_unit = 3) ──────────────────────────────────────
            [
                'nip'           => 'PEG-0028',
                'id_jabatan'    => 2,
                'id_unit'       => 3,
                'nama'          => 'Fajar Hidayat',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Mangga Dua Raya No.15, Pademangan, Jakarta Utara 14430',
                'no_handphone'  => '082255667788',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0029',
                'id_jabatan'    => 6,
                'id_unit'       => 3,
                'nama'          => 'Sri Wahyuni',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Pemuda Raya No.13, Rawamangun, Jakarta Timur 13220',
                'no_handphone'  => '085533445566',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0030',
                'id_jabatan'    => 6,
                'id_unit'       => 3,
                'nama'          => 'Wahyu Nugroho',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Condet Raya No.4, Kramat Jati, Jakarta Timur 13520',
                'no_handphone'  => '081344556677',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0031',
                'id_jabatan'    => 6,
                'id_unit'       => 3,
                'nama'          => 'Indah Permata Sari',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Kebon Jeruk Raya No.1, Kebon Jeruk, Jakarta Barat 11530',
                'no_handphone'  => '087766778899',
                'foto'          => 'default.jpg',
            ],

            // ── OP IV (id_unit = 4) ───────────────────────────────────────
            [
                'nip'           => 'PEG-0032',
                'id_jabatan'    => 2,
                'id_unit'       => 4,
                'nama'          => 'Dimas Prasetyo',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Letjen S. Parman Kav.28, Grogol, Jakarta Barat 11470',
                'no_handphone'  => '082299001122',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0033',
                'id_jabatan'    => 6,
                'id_unit'       => 4,
                'nama'          => 'Nurul Aini',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Pluit Raya No.3, Penjaringan, Jakarta Utara 14450',
                'no_handphone'  => '083366778899',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0034',
                'id_jabatan'    => 6,
                'id_unit'       => 4,
                'nama'          => 'Reza Permana',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Tanjung Priok No.12, Tanjung Priok, Jakarta Utara 14310',
                'no_handphone'  => '085577889900',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0035',
                'id_jabatan'    => 6,
                'id_unit'       => 4,
                'nama'          => 'Yuni Astuti',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Kapuk Raya No.5, Penjaringan, Jakarta Utara 14460',
                'no_handphone'  => '081388990011',
                'foto'          => 'default.jpg',
            ],

            // ── OP PIAT (id_unit = 5) ─────────────────────────────────────
            [
                'nip'           => 'PEG-0036',
                'id_jabatan'    => 2,
                'id_unit'       => 5,
                'nama'          => 'Mega Puspita',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Daan Mogot KM.14, Kalideres, Jakarta Barat 11840',
                'no_handphone'  => '083300112233',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0037',
                'id_jabatan'    => 6,
                'id_unit'       => 5,
                'nama'          => 'Ahmad Fauzi Rahman',
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => 'Jl. Cendrawasih No.2, Cengkareng, Jakarta Barat 11720',
                'no_handphone'  => '085511223344',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0038',
                'id_jabatan'    => 6,
                'id_unit'       => 5,
                'nama'          => 'Lestari Wulandari',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Permata Hijau No.7, Pesanggrahan, Jakarta Selatan 12210',
                'no_handphone'  => '081322334455',
                'foto'          => 'default.jpg',
            ],
            [
                'nip'           => 'PEG-0039',
                'id_jabatan'    => 6,
                'id_unit'       => 5,
                'nama'          => 'Siti Rahayu',
                'jenis_kelamin' => 'Perempuan',
                'alamat'        => 'Jl. Ahmad Yani No.10, Cempaka Putih, Jakarta Pusat 10510',
                'no_handphone'  => '082233445566',
                'foto'          => 'default.jpg',
            ],
        ];

        $this->db->table('pegawai')->ignore(true)->insertBatch($data);
    }
}
