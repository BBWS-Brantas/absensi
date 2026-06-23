<?php

namespace App\Controllers;

use App\Models\KetidakhadiranModel;
use App\Models\UsersModel;
use App\Models\PresensiModel;
use App\Models\LokasiPresensiModel;
use App\Models\LokasiPresensiPegawaiModel;

class Home extends BaseController
{
    protected $usersModel;
    protected $lokasiModel;
    protected $lokasiPegawaiModel;
    protected $presensiModel;
    protected $ketidakhadiranModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->lokasiModel = new LokasiPresensiModel();
        $this->lokasiPegawaiModel = new LokasiPresensiPegawaiModel();
        $this->presensiModel = new PresensiModel();
        $this->ketidakhadiranModel = new KetidakhadiranModel();
    }

    public function index(): string
    {
        $user_profile = $this->usersModel->getUserInfo(user_id());
        $id_pegawai = $user_profile->id_pegawai;

        // Lokasi yang ter-assign ke pegawai (ditampilkan sebagai dropdown).
        // Presensi cukup sekali sehari: pegawai memilih SATU lokasi.
        $daftar_lokasi = $this->lokasiPegawaiModel->getLokasiByPegawai($id_pegawai);

        $presensi_masuk = $this->presensiModel->cekPresensiMasuk($id_pegawai, date('Y-m-d'));
        $jumlah_presensi_masuk = $this->presensiModel->cekPresensiMasuk($id_pegawai, date('Y-m-d'), true);
        $status_ketidakhadiran = $this->ketidakhadiranModel->getDataIzinHariIni($id_pegawai);

        // Jika sudah presensi masuk hari ini, ambil lokasi tempat check-in
        // (untuk jam_pulang + label lokasi pada kartu keluar).
        $lokasi_checkin = null;
        if ($presensi_masuk && !empty($presensi_masuk->id_lokasi_presensi)) {
            $lokasi_checkin = $this->lokasiModel->getWhere(['id' => $presensi_masuk->id_lokasi_presensi])->getFirstRow();
        }

        $data = [
            'title' => 'Home',
            'user_profile' => $user_profile,
            'daftar_lokasi' => $daftar_lokasi,
            'lokasi_checkin' => $lokasi_checkin,
            'jumlah_presensi_masuk' => $jumlah_presensi_masuk,
            'jam_pulang' => $lokasi_checkin->jam_pulang ?? null,
            'data_presensi_masuk' => $presensi_masuk,
            'status_ketidakhadiran' =>  $status_ketidakhadiran,
        ];

        return view('home/index', $data);
    }

    public function getWaktu()
    {
        // Zona waktu mengikuti lokasi yang dipilih (kartu). Fallback: lokasi pertama, lalu Asia/Jakarta.
        $id_lokasi = $this->request->getGet('id_lokasi');
        $zona = null;

        if ($id_lokasi) {
            $lokasi = $this->lokasiModel->getWhere(['id' => $id_lokasi])->getFirstRow();
            $zona = $lokasi->zona_waktu ?? null;
        }

        if (!$zona) {
            $user_profile = $this->usersModel->getUserInfo(user_id());
            $daftar_lokasi = $this->lokasiPegawaiModel->getLokasiByPegawai($user_profile->id_pegawai);
            $zona = $daftar_lokasi[0]->zona_waktu ?? null;
        }

        if ($zona && in_array($zona, timezone_identifiers_list())) {
            date_default_timezone_set($zona);
        } else {
            date_default_timezone_set('Asia/Jakarta');
        }

        $waktu = [
            'tanggal' => date('j'),
            'bulan' => date('F'),
            'tahun' => date('Y'),
            'jam' => date('H'),
            'menit' => date('i'),
            'detik' => date('s')
        ];

        return $this->response->setJSON($waktu);
    }
}
