<?php

namespace App\Controllers;

use DateTime;
use App\Models\UsersModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Models\LokasiPresensiModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Presensi extends BaseController
{
    protected $usersModel;
    protected $lokasiModel;
    protected $presensiModel;
    protected $pegawaiModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->lokasiModel = new LokasiPresensiModel();
        $this->presensiModel = new PresensiModel();
        $this->pegawaiModel = new PegawaiModel();
    }

    /**
     * Aturan validasi foto presensi: wajib gambar asli (jpg/jpeg/png), maksimal 15 MB.
     * max_size dalam KB -> 15360 KB = 15 MB.
     */
    private function aturanFoto(): array
    {
        return [
            'foto' => [
                'rules' => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]|ext_in[foto,jpg,jpeg,png]|max_size[foto,15360]',
                'errors' => [
                    'uploaded' => 'Mohon ambil atau pilih foto presensi.',
                    'is_image' => 'File yang diunggah harus berupa gambar.',
                    'mime_in'  => 'Format foto harus JPG, JPEG, atau PNG.',
                    'ext_in'   => 'Ekstensi foto harus jpg, jpeg, atau png.',
                    'max_size' => 'Ukuran foto maksimal 15 MB.',
                ],
            ],
        ];
    }

    public function presensiMasuk()
    {
        $user_profile = $this->usersModel->getUserInfo(user_id());

        $latitude_pegawai = $this->request->getVar('latitude_pegawai');
        $longitude_pegawai = $this->request->getVar('longitude_pegawai');
        $latitude_kantor = $this->request->getVar('latitude_kantor');
        $longitude_kantor = $this->request->getVar('longitude_kantor');
        $radius = $this->request->getVar('radius');
        $zona_waktu = $this->request->getVar('zona_waktu');
        $tanggal_masuk = $this->request->getVar('tanggal_masuk');
        $jam_masuk = $this->request->getVar('jam_masuk');

        // Jika user menonaktifkan lokasi, maka arahkan kembali ke halaman home
        if (empty($latitude_pegawai) || empty($longitude_pegawai)) {
            session()->setFlashdata('gagal', 'Lokasi Anda tidak terdeteksi. Mohon aktifkan fitur lokasi di perangkat Anda dan refresh halaman ini.');
            return redirect()->to(base_url());
        }

        // Jika lokasi presensi tidak terdeteksi, maka arahkan kembali ke halaman home
        if (empty($latitude_kantor) || empty($longitude_kantor)) {
            session()->setFlashdata('gagal', 'Lokasi presensi tidak valid. Mohon hubungi Admin.');
            return redirect()->to(base_url());
        }

        // Cek Perbedaan Koordinat Pegawai dengan Lokasi Presensi
        $perbedaan_koordinat = $longitude_pegawai - $longitude_kantor;

        if (!$perbedaan_koordinat) {
            session()->setFlashdata('warning', 'Mohon refresh halaman ini.');
            return redirect()->to(base_url());
        }

        $jarak = sin(deg2rad($latitude_pegawai)) * sin(deg2rad($latitude_kantor)) + cos(deg2rad($latitude_pegawai)) * cos(deg2rad($latitude_kantor)) * cos(deg2rad($perbedaan_koordinat));
        $jarak = acos($jarak);
        $jarak = rad2deg($jarak);
        $mil = $jarak * 60 * 1.1515;
        $km = $mil * 1.609344;
        $meter = $km * 1000;

        if ($meter > $radius) {
            session()->setFlashdata('gagal', 'Anda berada di luar area kantor');
            return redirect()->to(base_url());
        }

        $data = [
            'title' => 'Presensi Masuk',
            'user_profile' => $user_profile,
            'latitude_pegawai' => $latitude_pegawai,
            'longitude_pegawai' => $longitude_pegawai,
            'latitude_kantor' => $latitude_kantor,
            'longitude_kantor' => $longitude_kantor,
            'radius' => $radius,
            'tanggal_masuk' => $tanggal_masuk,
            'jam_masuk' => $jam_masuk,
        ];

        return view('presensi/presensi_masuk', $data);
    }

    public function simpanPresensiMasuk()
    {
        // Validasi: harus benar-benar gambar (jpg/jpeg/png), maksimal 15 MB
        if (! $this->validate($this->aturanFoto())) {
            session()->setFlashdata('gagal', implode(' ', $this->validator->getErrors()));
            return redirect()->to(base_url());
        }

        $foto = $this->request->getFile('foto');

        $username = $this->request->getPost('username');
        $nama_foto = 'masuk-' . date('Y-m-d-H-i-s') . '-' . $username . '.' . $foto->getExtension();

        $foto->move(FCPATH . 'assets/img/foto_presensi/masuk/', $nama_foto);

        if (! $foto->hasMoved()) {
            session()->setFlashdata('gagal', 'Gagal menyimpan foto presensi masuk');
            return redirect()->to(base_url());
        }

        $id_pegawai = $this->request->getPost('id_pegawai');
        $tanggal_masuk = $this->request->getPost('tanggal_masuk');
        $jam_masuk = $this->request->getPost('jam_masuk');

        $this->presensiModel->save([
            'id_pegawai' => $id_pegawai,
            'tanggal_masuk' =>  $tanggal_masuk,
            'jam_masuk' => $jam_masuk,
            'foto_masuk' => $nama_foto,
        ]);

        session()->setFlashdata('berhasil', 'Presensi masuk berhasil disimpan');
        return redirect()->to(base_url());
    }

    public function presensiKeluar()
    {
        $user_profile = $this->usersModel->getUserInfo(user_id());
        $presensi_masuk = $this->presensiModel->cekPresensiMasuk($user_profile->id_pegawai, date('Y-m-d'));

        $latitude_pegawai = $this->request->getVar('latitude_pegawai');
        $longitude_pegawai = $this->request->getVar('longitude_pegawai');
        $latitude_kantor = $this->request->getVar('latitude_kantor');
        $longitude_kantor = $this->request->getVar('longitude_kantor');
        $radius = $this->request->getVar('radius');
        $zona_waktu = $this->request->getVar('zona_waktu');
        $tanggal_keluar = $this->request->getPost('tanggal_keluar');
        $jam_keluar = $this->request->getPost('jam_keluar');

        // Jika user menonaktifkan lokasi, maka arahkan kembali ke halaman home
        if (empty($latitude_pegawai) || empty($longitude_pegawai)) {
            session()->setFlashdata('gagal', 'Lokasi Anda tidak terdeteksi. Mohon aktifkan fitur lokasi di perangkat Anda dan refresh halaman ini.');
            return redirect()->to(base_url());
        }

        // Jika lokasi presensi tidak terdeteksi, maka arahkan kembali ke halaman home
        if (empty($latitude_kantor) || empty($longitude_kantor)) {
            session()->setFlashdata('gagal', 'Lokasi presensi tidak valid. Mohon hubungi Admin.');
            return redirect()->to(base_url());
        }

        // Cek Perbedaan Koordinat Pegawai dengan Lokasi Presensi
        $perbedaan_koordinat = $longitude_pegawai - $longitude_kantor;
        $jarak = sin(deg2rad($latitude_pegawai)) * sin(deg2rad($latitude_kantor)) + cos(deg2rad($latitude_pegawai)) * cos(deg2rad($latitude_kantor)) * cos(deg2rad($perbedaan_koordinat));
        $jarak = acos($jarak);
        $jarak = rad2deg($jarak);
        $mil = $jarak * 60 * 1.1515;
        $km = $mil * 1.609344;
        $meter = $km * 1000;

        if ($meter > $radius) {
            session()->setFlashdata('gagal', 'Anda berada di luar area kantor');
            return redirect()->to(base_url());
        }

        $data = [
            'title' => 'Presensi Keluar',
            'user_profile' => $user_profile,
            'latitude_pegawai' => $latitude_pegawai,
            'longitude_pegawai' => $longitude_pegawai,
            'latitude_kantor' => $latitude_kantor,
            'longitude_kantor' => $longitude_kantor,
            'radius' => $radius,
            'tanggal_keluar' => $tanggal_keluar,
            'jam_keluar' => $jam_keluar,
            'data_presensi_masuk' => $presensi_masuk,
        ];

        return view('presensi/presensi_keluar', $data);
    }

    public function simpanPresensiKeluar()
    {
        // Validasi: harus benar-benar gambar (jpg/jpeg/png), maksimal 15 MB
        if (! $this->validate($this->aturanFoto())) {
            session()->setFlashdata('gagal', implode(' ', $this->validator->getErrors()));
            return redirect()->to(base_url());
        }

        $foto = $this->request->getFile('foto');

        $username = $this->request->getPost('username');
        $nama_foto = 'keluar-' . date('Y-m-d-H-i-s') . '-' . $username . '.' . $foto->getExtension();

        $foto->move(FCPATH . 'assets/img/foto_presensi/keluar/', $nama_foto);

        if (! $foto->hasMoved()) {
            session()->setFlashdata('gagal', 'Gagal menyimpan foto presensi keluar');
            return redirect()->to(base_url());
        }

        $id_presensi = $this->request->getPost('id_presensi');
        $tanggal_keluar = $this->request->getPost('tanggal_keluar');
        $jam_keluar = $this->request->getPost('jam_keluar');
        $keterangan = $this->request->getPost('keterangan');

        $this->presensiModel->save([
            'id' => $id_presensi,
            'tanggal_keluar' =>  $tanggal_keluar,
            'jam_keluar' => $jam_keluar,
            'foto_keluar' => $nama_foto,
            'keterangan' => $keterangan,
        ]);

        session()->setFlashdata('berhasil', 'Presensi keluar berhasil disimpan');
        return redirect()->to(base_url());
    }

    public function rekapPresensiPegawai()
    {
        $currentPage = $this->request->getVar('page_rekap') ? $this->request->getVar('page_rekap') : 1;

        $user_profile = $this->usersModel->getUserInfo(user_id());
        $data_presensi_pegawai = $this->presensiModel->getDataPresensi($user_profile->id_pegawai);
        $data_lokasi_presensi_user = $this->lokasiModel->getWhere(['nama_lokasi' => $user_profile->lokasi_presensi])->getFirstRow();

        $tanggal_dari = $this->request->getGet('tanggal_dari');
        $tanggal_sampai = $this->request->getGet('tanggal_sampai');
        if (!empty($tanggal_dari) || !empty($tanggal_sampai)) {
            if ($tanggal_dari === '') {
                if ($this->presensiModel->getMinDate($user_profile->id_pegawai)) {
                    $tanggal_dari = $this->presensiModel->getMinDate($user_profile->id_pegawai);
                } else {
                    $tanggal_dari = date('Y-m-d');
                }
            }
            if ($tanggal_sampai === '') {
                $tanggal_sampai = date('Y-m-d');
            }
            $data_presensi_pegawai = $this->presensiModel->getDataPresensi($user_profile->id_pegawai, $tanggal_dari, $tanggal_sampai);
        }

        if (empty($tanggal_dari) || empty($tanggal_sampai)) {
            if ($this->presensiModel->getMinDate($user_profile->id_pegawai)) {
                $tanggal_dari = $this->presensiModel->getMinDate($user_profile->id_pegawai);
            } else {
                $tanggal_dari = date('Y-m-d');
            }
            $tanggal_sampai = date('Y-m-d');
            $data_tanggal = date('d F Y', strtotime($tanggal_dari)) . ' - ' . date('d F Y');
        } else {
            if ($tanggal_dari > $tanggal_sampai) {
                $tanggal_sampai = $tanggal_dari;
            }
            $data_tanggal = date('d F Y', strtotime($tanggal_dari)) . ' - ' . date('d F Y', strtotime($tanggal_sampai));
        }

        $data_presensi = $data_presensi_pegawai['rekap-presensi'];
        $pager = $data_presensi_pegawai['links'];
        $total = $data_presensi_pegawai['total'];
        $perPage = $data_presensi_pegawai['perPage'];

        $data = [
            'title' => 'Rekap Presensi',
            'user_profile' => $user_profile,
            'jam_masuk_kantor' => $data_lokasi_presensi_user->jam_masuk,
            'data_tanggal' => $data_tanggal,
            'data_presensi_pegawai' => $data_presensi,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'total' => $total,
            'perPage' => $perPage,
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
        ];

        return view('presensi/rekap_presensi', $data);
    }

    public function rekapPresensiPegawaiExcel()
    {
        $data_pegawai = $this->pegawaiModel->getPegawai(user()->username)['pegawai'];

        $tanggal_awal = $this->request->getPost('tanggal_awal');
        $tanggal_akhir = $this->request->getPost('tanggal_akhir');
        if ($tanggal_awal === '') {
            $tanggal_awal = $this->presensiModel->getMinDate($data_pegawai->id);
        }
        if ($tanggal_akhir === '') {
            $tanggal_akhir = date('Y-m-d');
        }
        $data_presensi = $this->presensiModel->getDataPresensi($data_pegawai->id_pegawai, $tanggal_awal, $tanggal_akhir, true)['rekap-presensi'];

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->setCellValue('A1', 'Rekap Presensi Pegawai');
        $worksheet->setCellValue('A3', 'Tanggal Awal');
        $worksheet->setCellValue('A4', 'Tanggal Akhir');
        $worksheet->setCellValue('C3', $tanggal_awal);
        $worksheet->setCellValue('C4', $tanggal_akhir);
        $worksheet->setCellValue('E3', 'Nama TPM');
        $worksheet->setCellValue('E4', 'NIP');
        $worksheet->setCellValue('F3', $data_pegawai->nama);
        $worksheet->setCellValue('F4', $data_pegawai->nip);
        $worksheet->setCellValue('A6', '#');
        $worksheet->setCellValue('B6', 'TANGGAL MASUK');
        $worksheet->setCellValue('C6', 'JAM MASUK');
        $worksheet->setCellValue('D6', 'JAM PULANG');
        $worksheet->setCellValue('E6', 'TOTAL JAM KERJA');
        $worksheet->setCellValue('F6', 'TOTAL JAM KETERLAMBATAN');

        $worksheet->mergeCells('A1:F1');
        $worksheet->mergeCells('A3:B3');
        $worksheet->mergeCells('A4:B4');

        $data_start_row = 7;
        $nomor = 1;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]
        ];

        if (!empty($data_presensi)) {
            foreach ($data_presensi as $data) {
                // TOTAL JAM KERJA
                $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($data->tanggal_masuk . ' ' . $data->jam_masuk));
                $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($data->tanggal_keluar . ' ' . $data->jam_keluar));

                $timestamp_masuk = strtotime($jam_tanggal_masuk);
                $timestamp_keluar = strtotime($jam_tanggal_keluar);

                // Selisih dalam format time
                $selisih = $timestamp_keluar - $timestamp_masuk;

                // Selisih dalam format jam
                $total_jam_kerja = floor($selisih / 3600);

                // Selisih dalam format menit
                $selisih_menit_kerja = floor(($selisih % 3600) / 60);

                // Format string
                $total_jam_kerja_format = sprintf("%d Jam %d Menit", $total_jam_kerja, $selisih_menit_kerja);

                if ($total_jam_kerja < 0) {
                    $total_jam_kerja_format = '0 Jam 0 Menit';
                }

                // TOTAL KETERLAMBATAN
                $jam_masuk = date('H:i:s', strtotime($data->jam_masuk));
                $timestamp_jam_masuk_real = strtotime($jam_masuk);

                $jam_masuk_kantor = $data->jam_masuk_kantor;
                $timestamp_jam_masuk_kantor = strtotime($jam_masuk_kantor);

                $terlambat = $timestamp_jam_masuk_real - $timestamp_jam_masuk_kantor;
                $total_jam_keterlambatan = floor($terlambat / 3600);
                $selisih_menit_keterlambatan = floor(($terlambat % 3600) / 60);

                $total_jam_keterlambatan_format = sprintf("%d Jam %d Menit", $total_jam_keterlambatan, $selisih_menit_keterlambatan);

                if ($total_jam_keterlambatan < 0) {
                    $total_jam_keterlambatan_format = 'On Time';
                }

                $worksheet->setCellValue('A' . $data_start_row, $nomor++);
                $worksheet->setCellValue('B' . $data_start_row, $data->tanggal_masuk);
                $worksheet->setCellValue('C' . $data_start_row, $data->jam_masuk);
                $worksheet->setCellValue('D' . $data_start_row, $data->jam_keluar);
                $worksheet->setCellValue('E' . $data_start_row, $total_jam_kerja_format);
                $worksheet->setCellValue('F' . $data_start_row, $total_jam_keterlambatan_format);

                $worksheet->getStyle('A' . $data_start_row - 1 . ':F' . $data_start_row)->applyFromArray($styleArray);

                $data_start_row++;
            }
        } else {
            $worksheet->setCellValue('A' . $data_start_row, 'Tidak Ada Data');
            $worksheet->mergeCells('A' . $data_start_row . ':F' . $data_start_row);
            $worksheet->getStyle('A' . $data_start_row - 1 . ':F' . $data_start_row)->applyFromArray($styleArray);
        }

        $worksheet->getColumnDimension('A')->setAutoSize(true);
        $worksheet->getColumnDimension('B')->setAutoSize(true);
        $worksheet->getColumnDimension('C')->setAutoSize(true);
        $worksheet->getColumnDimension('D')->setAutoSize(true);
        $worksheet->getColumnDimension('E')->setAutoSize(true);
        $worksheet->getColumnDimension('F')->setAutoSize(true);

        $worksheet->getStyle('A3:C4')->applyFromArray($styleArray);
        $worksheet->getStyle('E3:F4')->applyFromArray($styleArray);
        $worksheet->getStyle('A6:F6')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff00');
        $worksheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $worksheet->getStyle('C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Rekap Presensi Pegawai_' . $data_pegawai->nama . '_' . date('Y-m-d', strtotime($tanggal_awal)) . '_' . date('Y-m-d', strtotime($tanggal_akhir)) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function rekapPresensiPegawaiPdf()
    {
        $data_pegawai = $this->pegawaiModel->getPegawai(user()->username)['pegawai'];

        $tanggal_awal = $this->request->getPost('tanggal_awal');
        $tanggal_akhir = $this->request->getPost('tanggal_akhir');
        if ($tanggal_awal === '') {
            $tanggal_awal = $this->presensiModel->getMinDate($data_pegawai->id);
        }
        if ($tanggal_akhir === '') {
            $tanggal_akhir = date('Y-m-d');
        }

        $data_presensi = $this->presensiModel->getDataPresensi($data_pegawai->id_pegawai, $tanggal_awal, $tanggal_akhir, true)['rekap-presensi'];

        $rows = [];
        $nomor = 1;
        foreach ($data_presensi as $data) {
            $belum_keluar = ($data->tanggal_keluar === '0000-00-00' || $data->jam_keluar === '00:00:00');

            // Total jam kerja
            if ($belum_keluar) {
                $total_jam_kerja_format = '0 Jam 0 Menit';
            } else {
                $selisih = strtotime($data->tanggal_keluar . ' ' . $data->jam_keluar) - strtotime($data->tanggal_masuk . ' ' . $data->jam_masuk);
                $total_jam_kerja_format = ($selisih < 0)
                    ? '0 Jam 0 Menit'
                    : sprintf('%d Jam %d Menit', floor($selisih / 3600), floor(($selisih % 3600) / 60));
            }

            // Total keterlambatan
            $terlambat = strtotime(date('H:i:s', strtotime($data->jam_masuk))) - strtotime($data->jam_masuk_kantor);
            $total_keterlambatan_format = ($terlambat <= 0)
                ? 'On Time'
                : sprintf('%d Jam %d Menit', floor($terlambat / 3600), floor(($terlambat % 3600) / 60));

            $rows[] = [
                'no'                  => $nomor++,
                'tanggal'             => date('d F Y', strtotime($data->tanggal_masuk)),
                'jam_masuk'           => $data->jam_masuk,
                'jam_keluar'          => $belum_keluar ? '-' : $data->jam_keluar,
                'total_jam_kerja'     => $total_jam_kerja_format,
                'total_keterlambatan' => $total_keterlambatan_format,
                'keterangan'          => ! empty($data->keterangan) ? $data->keterangan : '-',
                'foto_masuk'          => $this->fotoDataUri('masuk', $data->foto_masuk),
                'foto_keluar'         => $belum_keluar ? null : $this->fotoDataUri('keluar', $data->foto_keluar),
            ];
        }

        $html = view('presensi/rekap_pdf', [
            'data_pegawai'  => $data_pegawai,
            'tanggal_awal'  => $tanggal_awal,
            'tanggal_akhir' => $tanggal_akhir,
            'rows'          => $rows,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $namaFile = 'Rekap Presensi_' . $data_pegawai->nama . '_'
            . date('Y-m-d', strtotime($tanggal_awal)) . '_' . date('Y-m-d', strtotime($tanggal_akhir)) . '.pdf';
        $dompdf->stream($namaFile, ['Attachment' => true]);
        exit();
    }

    /**
     * Membaca file foto presensi dan mengubahnya menjadi data URI base64
     * agar bisa ditanam langsung di PDF (dompdf). Mengembalikan null bila file tidak ada.
     */
    private function fotoDataUri($jenis, $namafile)
    {
        if (empty($namafile) || $namafile === '-') {
            return null;
        }

        $path = FCPATH . 'assets/img/foto_presensi/' . $jenis . '/' . $namafile;
        if (! is_file($path)) {
            return null;
        }

        $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'image/jpeg') : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Menyusun baris data presensi (termasuk foto sebagai data URI) untuk laporan PDF.
     */
    private function buildLaporanRows($data_presensi)
    {
        $rows = [];
        $nomor = 1;
        foreach ($data_presensi as $data) {
            $belum_keluar = ($data->tanggal_keluar === '0000-00-00' || $data->jam_keluar === '00:00:00');

            // Total jam kerja
            if ($belum_keluar) {
                $total_jam_kerja_format = '0 Jam 0 Menit';
            } else {
                $selisih = strtotime($data->tanggal_keluar . ' ' . $data->jam_keluar) - strtotime($data->tanggal_masuk . ' ' . $data->jam_masuk);
                $total_jam_kerja_format = ($selisih < 0)
                    ? '0 Jam 0 Menit'
                    : sprintf('%d Jam %d Menit', floor($selisih / 3600), floor(($selisih % 3600) / 60));
            }

            // Total keterlambatan
            $terlambat = strtotime(date('H:i:s', strtotime($data->jam_masuk))) - strtotime($data->jam_masuk_kantor);
            $total_keterlambatan_format = ($terlambat <= 0)
                ? 'On Time'
                : sprintf('%d Jam %d Menit', floor($terlambat / 3600), floor(($terlambat % 3600) / 60));

            $rows[] = [
                'no'                  => $nomor++,
                'nip'                 => $data->nip,
                'nama'                => $data->nama,
                'tanggal'             => date('d F Y', strtotime($data->tanggal_masuk)),
                'jam_masuk'           => $data->jam_masuk,
                'jam_keluar'          => $belum_keluar ? '-' : $data->jam_keluar,
                'total_jam_kerja'     => $total_jam_kerja_format,
                'total_keterlambatan' => $total_keterlambatan_format,
                'keterangan'          => (! empty($data->keterangan) && $data->keterangan !== '-') ? $data->keterangan : '-',
                'foto_masuk'          => $this->fotoDataUri('masuk', $data->foto_masuk),
                'foto_keluar'         => $belum_keluar ? null : $this->fotoDataUri('keluar', $data->foto_keluar),
            ];
        }

        return $rows;
    }

    public function laporanHarianPdf()
    {
        $tanggal = $this->request->getPost('tanggal');
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $data_presensi = $this->presensiModel->getDataPresensiHarian($tanggal, $tanggal, true, 10, current_unit_id())['laporan-harian'];

        $html = view('presensi/laporan_pdf', [
            'judul' => 'Laporan Presensi Harian',
            'meta'  => [
                'Tanggal' => date('d F Y', strtotime($tanggal)),
            ],
            'rows'  => $this->buildLaporanRows($data_presensi),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $namaFile = 'Laporan Presensi Harian_' . date('Y-m-d', strtotime($tanggal)) . '.pdf';
        $dompdf->stream($namaFile, ['Attachment' => true]);
        exit();
    }

    public function laporanBulananPdf()
    {
        $filter_bulan = $this->request->getPost('filter_bulan');
        $filter_tahun = $this->request->getPost('filter_tahun');
        if ($filter_tahun === '' || $filter_tahun === null) {
            $filter_tahun = date('Y');
        }
        if ($filter_bulan === '' || $filter_bulan === null) {
            $filter_bulan = date('m');
        }

        $data_presensi = $this->presensiModel->getDataPresensiBulanan($filter_bulan, $filter_tahun, true, 10, current_unit_id())['laporan-bulanan'];

        $html = view('presensi/laporan_pdf', [
            'judul' => 'Laporan Presensi Bulanan',
            'meta'  => [
                'Bulan' => date('F', mktime(0, 0, 0, (int) $filter_bulan, 1)),
                'Tahun' => $filter_tahun,
            ],
            'rows'  => $this->buildLaporanRows($data_presensi),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $namaFile = 'Laporan Presensi Bulanan_'
            . date('F-Y', mktime(0, 0, 0, (int) $filter_bulan, 1, (int) $filter_tahun)) . '.pdf';
        $dompdf->stream($namaFile, ['Attachment' => true]);
        exit();
    }

    public function laporanHarian()
    {
        $currentPage = $this->request->getVar('page_harian') ? $this->request->getVar('page_harian') : 1;

        $user_profile = $this->usersModel->getUserInfo(user_id());

        $tanggal = $this->request->getGet('tanggal');
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $data_presensi_pegawai = $this->presensiModel->getDataPresensiHarian($tanggal, $tanggal, false, 10, current_unit_id());
        $data_tanggal = date('d F Y', strtotime($tanggal));

        $data_presensi = $data_presensi_pegawai['laporan-harian'];
        $pager = $data_presensi_pegawai['links'];
        $total = $data_presensi_pegawai['total'];
        $perPage = $data_presensi_pegawai['perPage'];

        $data = [
            'title' => 'Laporan Presensi Harian',
            'user_profile' => $user_profile,
            'data_tanggal' => $data_tanggal,
            'data_presensi' => $data_presensi,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'total' => $total,
            'perPage' => $perPage,
            'tanggal' => $tanggal,
        ];

        return view('presensi/laporan_presensi_harian', $data);
    }

    public function laporanHarianExcel()
    {
        $tanggal = $this->request->getPOST('tanggal');
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $data_presensi = $this->presensiModel->getDataPresensiHarian($tanggal, $tanggal, true, 10, current_unit_id())['laporan-harian'];

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->setCellValue('A1', 'Laporan Presensi Harian');
        $worksheet->setCellValue('A3', 'Tanggal');
        $worksheet->setCellValue('C3', $tanggal);
        $worksheet->setCellValue('A6', '#');
        $worksheet->setCellValue('B6', 'NIP');
        $worksheet->setCellValue('C6', 'NAMA TPM');
        $worksheet->setCellValue('D6', 'TANGGAL MASUK');
        $worksheet->setCellValue('E6', 'JAM MASUK');
        $worksheet->setCellValue('F6', 'JAM PULANG');
        $worksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $worksheet->setCellValue('H6', 'TOTAL JAM KETERLAMBATAN');

        $worksheet->mergeCells('A1:H1');
        $worksheet->mergeCells('A3:B3');
        $worksheet->mergeCells('A4:B4');

        $data_start_row = 7;
        $nomor = 1;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]
        ];

        if (!empty($data_presensi)) {
            foreach ($data_presensi as $data) {
                // TOTAL JAM KERJA
                $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($data->tanggal_masuk . ' ' . $data->jam_masuk));
                $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($data->tanggal_keluar . ' ' . $data->jam_keluar));

                $timestamp_masuk = strtotime($jam_tanggal_masuk);
                $timestamp_keluar = strtotime($jam_tanggal_keluar);

                // Selisih dalam format time
                $selisih = $timestamp_keluar - $timestamp_masuk;

                // Selisih dalam format jam
                $total_jam_kerja = floor($selisih / 3600);

                // Selisih dalam format menit
                $selisih_menit_kerja = floor(($selisih % 3600) / 60);

                // Format string
                $total_jam_kerja_format = sprintf("%d Jam %d Menit", $total_jam_kerja, $selisih_menit_kerja);

                if ($total_jam_kerja < 0) {
                    $total_jam_kerja_format = '0 Jam 0 Menit';
                }

                // TOTAL KETERLAMBATAN
                $jam_masuk = date('H:i:s', strtotime($data->jam_masuk));
                $timestamp_jam_masuk_real = strtotime($jam_masuk);

                $jam_masuk_kantor = $data->jam_masuk_kantor;
                $timestamp_jam_masuk_kantor = strtotime($jam_masuk_kantor);

                $terlambat = $timestamp_jam_masuk_real - $timestamp_jam_masuk_kantor;
                $total_jam_keterlambatan = floor($terlambat / 3600);
                $selisih_menit_keterlambatan = floor(($terlambat % 3600) / 60);

                $total_jam_keterlambatan_format = sprintf("%d Jam %d Menit", $total_jam_keterlambatan, $selisih_menit_keterlambatan);

                if ($total_jam_keterlambatan < 0) {
                    $total_jam_keterlambatan_format = 'On Time';
                }

                $worksheet->setCellValue('A' . $data_start_row, $nomor++);
                $worksheet->setCellValue('B' . $data_start_row, $data->nip);
                $worksheet->setCellValue('C' . $data_start_row, $data->nama);
                $worksheet->setCellValue('D' . $data_start_row, $data->tanggal_masuk);
                $worksheet->setCellValue('E' . $data_start_row, $data->jam_masuk);
                $worksheet->setCellValue('F' . $data_start_row, $data->jam_keluar);
                $worksheet->setCellValue('G' . $data_start_row, $total_jam_kerja_format);
                $worksheet->setCellValue('H' . $data_start_row, $total_jam_keterlambatan_format);

                $worksheet->getStyle('A' . $data_start_row - 1 . ':H' . $data_start_row)->applyFromArray($styleArray);

                $data_start_row++;
            }
        } else {
            $worksheet->setCellValue('A' . $data_start_row, 'Tidak Ada Data');
            $worksheet->mergeCells('A' . $data_start_row . ':H' . $data_start_row);
            $worksheet->getStyle('A' . $data_start_row - 1 . ':H' . $data_start_row)->applyFromArray($styleArray);
        }

        $worksheet->getColumnDimension('A')->setAutoSize(true);
        $worksheet->getColumnDimension('B')->setAutoSize(true);
        $worksheet->getColumnDimension('C')->setAutoSize(true);
        $worksheet->getColumnDimension('D')->setAutoSize(true);
        $worksheet->getColumnDimension('E')->setAutoSize(true);
        $worksheet->getColumnDimension('F')->setAutoSize(true);
        $worksheet->getColumnDimension('G')->setAutoSize(true);
        $worksheet->getColumnDimension('H')->setAutoSize(true);

        $worksheet->getStyle('A3:C3')->applyFromArray($styleArray);
        $worksheet->getStyle('A6:H6')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff00');
        $worksheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan Presensi Harian_' . date('Y-m-d', strtotime($tanggal)) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function laporanBulanan()
    {
        $currentPage = $this->request->getVar('page_bulanan') ? $this->request->getVar('page_bulanan') : 1;

        $user_profile = $this->usersModel->getUserInfo(user_id());
        $id_unit = current_unit_id();
        $data_presensi_pegawai = $this->presensiModel->getDataPresensiBulanan(false, false, false, 10, $id_unit);

        $filter_bulan = $this->request->getGet('filter_bulan');
        $filter_tahun = $this->request->getGet('filter_tahun');
        if (!empty($filter_bulan) || !empty($filter_tahun)) {
            if ($filter_tahun === '') {
                $filter_tahun = date('Y');
            }
            if ($filter_bulan === '') {
                $filter_bulan = date('m');
            }
            $data_presensi_pegawai = $this->presensiModel->getDataPresensiBulanan($filter_bulan, $filter_tahun, false, 10, $id_unit);
        }

        if (empty($filter_bulan) || empty($filter_tahun)) {
            $data_bulan = date('Y-m');
        } else {
            $data_bulan = $filter_tahun . '-' . $filter_bulan;
        }

        if (empty($filter_bulan)) {
            $filter_bulan = date('m');
        }

        if (empty($filter_tahun)) {
            $filter_tahun = date('Y');
        }

        $data_presensi = $data_presensi_pegawai['laporan-bulanan'];
        $pager = $data_presensi_pegawai['links'];
        $total = $data_presensi_pegawai['total'];
        $perPage = $data_presensi_pegawai['perPage'];

        if ($this->presensiModel->getMinYear()) {
            $tahun_mulai = $this->presensiModel->getMinYear();
        } else {
            $tahun_mulai = date('Y');
        }

        $data = [
            'title' => 'Laporan Presensi Bulanan',
            'user_profile' => $user_profile,
            'tahun_mulai' => $tahun_mulai,
            'data_bulan' => $data_bulan,
            'data_presensi' => $data_presensi,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'total' => $total,
            'perPage' => $perPage,
            'filter_bulan' => $filter_bulan,
            'filter_tahun' => $filter_tahun,
        ];

        return view('presensi/laporan_presensi_bulanan', $data);
    }

    public function laporanBulananExcel()
    {
        $filter_bulan = $this->request->getPOST('filter_bulan');
        $filter_tahun = $this->request->getPOST('filter_tahun');
        if ($filter_tahun === '') {
            $filter_tahun = date('Y');
        }
        if ($filter_bulan === '') {
            $filter_bulan = date('m');
        }
        $data_presensi = $this->presensiModel->getDataPresensiBulanan($filter_bulan, $filter_tahun, true, 10, current_unit_id())['laporan-bulanan'];

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->setCellValue('A1', 'Laporan Presensi Bulanan');
        $worksheet->setCellValue('A3', 'Bulan');
        $worksheet->setCellValue('A4', 'Tahun');
        $worksheet->setCellValue('C3', date('F', strtotime($filter_bulan)));
        $worksheet->setCellValue('C4', $filter_tahun);
        $worksheet->setCellValue('A6', '#');
        $worksheet->setCellValue('B6', 'NIP');
        $worksheet->setCellValue('C6', 'NAMA TPM');
        $worksheet->setCellValue('D6', 'TANGGAL MASUK');
        $worksheet->setCellValue('E6', 'JAM MASUK');
        $worksheet->setCellValue('F6', 'JAM PULANG');
        $worksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $worksheet->setCellValue('H6', 'TOTAL JAM KETERLAMBATAN');

        $worksheet->mergeCells('A1:H1');
        $worksheet->mergeCells('A3:B3');
        $worksheet->mergeCells('A4:B4');

        $data_start_row = 7;
        $nomor = 1;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]
        ];

        if (!empty($data_presensi)) {
            foreach ($data_presensi as $data) {
                // TOTAL JAM KERJA
                $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($data->tanggal_masuk . ' ' . $data->jam_masuk));
                $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($data->tanggal_keluar . ' ' . $data->jam_keluar));

                $timestamp_masuk = strtotime($jam_tanggal_masuk);
                $timestamp_keluar = strtotime($jam_tanggal_keluar);

                // Selisih dalam format time
                $selisih = $timestamp_keluar - $timestamp_masuk;

                // Selisih dalam format jam
                $total_jam_kerja = floor($selisih / 3600);

                // Selisih dalam format menit
                $selisih_menit_kerja = floor(($selisih % 3600) / 60);

                // Format string
                $total_jam_kerja_format = sprintf("%d Jam %d Menit", $total_jam_kerja, $selisih_menit_kerja);

                if ($total_jam_kerja < 0) {
                    $total_jam_kerja_format = '0 Jam 0 Menit';
                }

                // TOTAL KETERLAMBATAN
                $jam_masuk = date('H:i:s', strtotime($data->jam_masuk));
                $timestamp_jam_masuk_real = strtotime($jam_masuk);

                $jam_masuk_kantor = $data->jam_masuk_kantor;
                $timestamp_jam_masuk_kantor = strtotime($jam_masuk_kantor);

                $terlambat = $timestamp_jam_masuk_real - $timestamp_jam_masuk_kantor;
                $total_jam_keterlambatan = floor($terlambat / 3600);
                $selisih_menit_keterlambatan = floor(($terlambat % 3600) / 60);

                $total_jam_keterlambatan_format = sprintf("%d Jam %d Menit", $total_jam_keterlambatan, $selisih_menit_keterlambatan);

                if ($total_jam_keterlambatan < 0) {
                    $total_jam_keterlambatan_format = 'On Time';
                }

                $worksheet->setCellValue('A' . $data_start_row, $nomor++);
                $worksheet->setCellValue('B' . $data_start_row, $data->nip);
                $worksheet->setCellValue('C' . $data_start_row, $data->nama);
                $worksheet->setCellValue('D' . $data_start_row, $data->tanggal_masuk);
                $worksheet->setCellValue('E' . $data_start_row, $data->jam_masuk);
                $worksheet->setCellValue('F' . $data_start_row, $data->jam_keluar);
                $worksheet->setCellValue('G' . $data_start_row, $total_jam_kerja_format);
                $worksheet->setCellValue('H' . $data_start_row, $total_jam_keterlambatan_format);

                $worksheet->getStyle('A' . $data_start_row - 1 . ':H' . $data_start_row)->applyFromArray($styleArray);

                $data_start_row++;
            }
        } else {
            $worksheet->setCellValue('A' . $data_start_row, 'Tidak Ada Data');
            $worksheet->mergeCells('A' . $data_start_row . ':H' . $data_start_row);
            $worksheet->getStyle('A' . $data_start_row - 1 . ':H' . $data_start_row)->applyFromArray($styleArray);
        }

        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($columns as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }

        $worksheet->getStyle('A3:C4')->applyFromArray($styleArray);
        $worksheet->getStyle('A3:A6')->getFont()->setBold(true);
        $worksheet->getStyle('A6:H6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A6:H6')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff00');
        $worksheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $dateTime = DateTime::createFromFormat('!n', $filter_bulan);
        $nama_bulan = $dateTime->format('F');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan Presensi Bulanan_' . $nama_bulan . '_' . $filter_tahun . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
