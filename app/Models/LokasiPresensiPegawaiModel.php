<?php

namespace App\Models;

use CodeIgniter\Model;

class LokasiPresensiPegawaiModel extends Model
{
    protected $db, $builder;
    protected $table = 'lokasi_presensi_pegawai';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_pegawai', 'id_lokasi_presensi', 'active'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('lokasi_presensi_pegawai');
    }

    /**
     * Semua data lokasi (lokasi_presensi.*) yang ter-assign ke seorang pegawai.
     * Dipakai di Home (kartu lokasi) dan alur presensi.
     */
    public function getLokasiByPegawai($id_pegawai)
    {
        return $this->db->table('lokasi_presensi_pegawai')
            ->select('lokasi_presensi.*')
            ->join('lokasi_presensi', 'lokasi_presensi.id = lokasi_presensi_pegawai.id_lokasi_presensi')
            ->where('lokasi_presensi_pegawai.id_pegawai', $id_pegawai)
            ->where('lokasi_presensi_pegawai.active', 1)
            ->orderBy('lokasi_presensi.nama_lokasi', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Daftar id_lokasi_presensi yang ter-assign ke pegawai (untuk pre-check form + guard).
     */
    public function getIdLokasiByPegawai($id_pegawai)
    {
        $rows = $this->db->table('lokasi_presensi_pegawai')
            ->select('id_lokasi_presensi')
            ->where('id_pegawai', $id_pegawai)
            ->where('active', 1)
            ->get()
            ->getResultArray();

        return array_map(fn($r) => (int) $r['id_lokasi_presensi'], $rows);
    }

    /**
     * Sinkronkan assignment lokasi pegawai TANPA menghapus baris (soft delete):
     * lokasi yang dipilih -> active=1 (insert bila belum pernah ada / restore bila
     * sebelumnya nonaktif), lokasi yang tidak lagi dipilih -> active=0 sehingga
     * riwayat assignment tetap tersimpan. Pemanggil membungkus dengan transaksi DB.
     */
    public function syncLokasi($id_pegawai, array $idLokasi)
    {
        $idLokasi = array_values(array_unique(array_filter(array_map('intval', $idLokasi))));

        // Baris yang sudah pernah ada (aktif maupun nonaktif) — agar UNIQUE
        // (id_pegawai, id_lokasi_presensi) tidak dilanggar saat re-assign.
        $existing = $this->db->table('lokasi_presensi_pegawai')
            ->select('id, id_lokasi_presensi, active')
            ->where('id_pegawai', $id_pegawai)
            ->get()
            ->getResultArray();

        $byLokasi = [];
        foreach ($existing as $row) {
            $byLokasi[(int) $row['id_lokasi_presensi']] = $row;
        }

        $now = date('Y-m-d H:i:s');

        // Aktifkan / tambahkan lokasi yang dipilih
        foreach ($idLokasi as $id) {
            if (isset($byLokasi[$id])) {
                // Restore bila sebelumnya nonaktif
                if ((int) $byLokasi[$id]['active'] !== 1) {
                    $this->db->table('lokasi_presensi_pegawai')
                        ->where('id', $byLokasi[$id]['id'])
                        ->update(['active' => 1, 'updated_at' => $now]);
                }
            } else {
                $this->db->table('lokasi_presensi_pegawai')->insert([
                    'id_pegawai'         => $id_pegawai,
                    'id_lokasi_presensi' => $id,
                    'active'             => 1,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }

        // Nonaktifkan lokasi yang tidak lagi dipilih (riwayat tetap disimpan)
        foreach ($byLokasi as $lokasiId => $row) {
            if (!in_array($lokasiId, $idLokasi, true) && (int) $row['active'] === 1) {
                $this->db->table('lokasi_presensi_pegawai')
                    ->where('id', $row['id'])
                    ->update(['active' => 0, 'updated_at' => $now]);
            }
        }
    }

    /**
     * Apakah pegawai ter-assign ke lokasi tertentu (guard saat presensi).
     */
    public function isAssigned($id_pegawai, $id_lokasi)
    {
        return $this->db->table('lokasi_presensi_pegawai')
            ->where('id_pegawai', $id_pegawai)
            ->where('id_lokasi_presensi', $id_lokasi)
            ->where('active', 1)
            ->countAllResults() > 0;
    }
}
