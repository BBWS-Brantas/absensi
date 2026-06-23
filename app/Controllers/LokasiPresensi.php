<?php

namespace App\Controllers;

use App\Models\LokasiPresensiModel;
use App\Models\UnitOperasionalModel;
use App\Models\UsersModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LokasiPresensi extends BaseController
{
    protected $usersModel;
    protected $lokasiModel;
    protected $unitModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->lokasiModel = new LokasiPresensiModel();
        $this->unitModel = new UnitOperasionalModel();
    }

    /**
     * Guard lintas-unit: admin hanya boleh menyentuh lokasi di unitnya.
     */
    private function pastikanDalamUnit($id_unit_lokasi)
    {
        $id_unit_scope = current_unit_id();
        if ($id_unit_scope !== null && (int) $id_unit_lokasi !== $id_unit_scope) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data lokasi tidak ditemukan di unit Anda');
        }
    }

    public function index(): string
    {
        $id_unit = current_unit_id();
        $lokasiModel = $this->lokasiModel->getLokasi(false, false, false, 10, $id_unit);
        $currentPage = $this->request->getVar('page_lokasi-presensi') ? $this->request->getVar('page_lokasi-presensi') : 1;

        $filter = [
            'keyword' => $this->request->getGet('keyword'),
            'tipe' => $this->request->getGet('tipe'),
            'waktu' => $this->request->getGet('waktu'),
        ];

        if (!empty($filter)) {
            if ($filter['keyword'] === null) {
                $filter['keyword'] = '';
            }
            if ($filter['tipe'] === null) {
                $filter['tipe'] = '';
            }
            if ($filter['waktu'] === null) {
                $filter['waktu'] = '';
            }
            $lokasiModel = $this->lokasiModel->getLokasi(false, $filter, false, 10, $id_unit);
        }

        $filtered = false;
        if (($filter['waktu'] !== null && $filter['waktu'] !== '') || ($filter['tipe'] !== null && $filter['tipe'] !== '')) {
            $filtered = true;
        }

        $data_lokasi = $lokasiModel['lokasi'];
        $pager = $lokasiModel['links'];
        $total = $lokasiModel['total'];
        $perPage = $lokasiModel['perPage'];

        $data = [
            'title' => 'Data Lokasi Presensi',
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'lokasi' => $data_lokasi,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'total' => $total,
            'perPage' => $perPage,
            'filter' => $filter,
            'isFiltered' => $filtered,
        ];

        return view('lokasi_presensi/index', $data);
    }

    public function dataLokasiExcel()
    {
        $filter = [
            'keyword' => $this->request->getPost('keyword'),
            'tipe' => $this->request->getPost('tipe'),
            'waktu' => $this->request->getPost('waktu'),
        ];

        $lokasiModel = $this->lokasiModel->getLokasi(false, $filter, true, 10, current_unit_id());
        $data_lokasi = $lokasiModel['lokasi'];

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        if ($filter['tipe'] === '') {
            $filter['tipe'] = 'Semua Tipe';
        }

        if ($filter['waktu'] === '') {
            $filter['waktu'] = 'Semua Zona Waktu';
        }

        $worksheet->setCellValue('A1', 'Data Lokasi Presensi');
        $worksheet->setCellValue('A3', 'Filter Tipe');
        $worksheet->setCellValue('A4', 'Filter Zona Waktu');
        $worksheet->setCellValue('C3', $filter['tipe']);
        $worksheet->setCellValue('C4', $filter['waktu']);
        $worksheet->setCellValue('A6', '#');
        $worksheet->setCellValue('B6', 'NAMA LOKASI');
        $worksheet->setCellValue('C6', 'ALAMAT');
        $worksheet->setCellValue('D6', 'TIPE');
        $worksheet->setCellValue('E6', 'LATITUDE');
        $worksheet->setCellValue('F6', 'LONGITUDE');
        $worksheet->setCellValue('G6', 'RADIUS (m)');
        $worksheet->setCellValue('H6', 'ZONA WAKTU');
        $worksheet->setCellValue('I6', 'JAM MASUK');
        $worksheet->setCellValue('J6', 'JAM PULANG');

        $worksheet->mergeCells('A1:J1');
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

        if (!empty($data_lokasi)) {
            // dd($data_lokasi);
            foreach ($data_lokasi as $data) {
                $worksheet->setCellValue('A' . $data_start_row, $nomor++);
                $worksheet->setCellValue('B' . $data_start_row, $data->nama_lokasi);
                $worksheet->setCellValue('C' . $data_start_row, $data->alamat_lokasi);
                $worksheet->setCellValue('D' . $data_start_row, $data->tipe_lokasi);
                $worksheet->setCellValue('E' . $data_start_row, $data->latitude);
                $worksheet->setCellValue('F' . $data_start_row, $data->longitude);
                $worksheet->setCellValue('G' . $data_start_row, $data->radius);
                $worksheet->setCellValue('H' . $data_start_row, $data->zona_waktu);
                $worksheet->setCellValue('I' . $data_start_row, $data->jam_masuk);
                $worksheet->setCellValue('J' . $data_start_row, $data->jam_pulang);

                $worksheet->getStyle('A' . $data_start_row - 1 . ':J' . $data_start_row)->applyFromArray($styleArray);
                $worksheet->getStyle('A' . $data_start_row - 1 . ':J' . $data_start_row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $worksheet->getStyle('D' . $data_start_row - 1 . ':J' . $data_start_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $worksheet->getStyle('A' . $data_start_row - 1 . ':A' . $data_start_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $worksheet->getStyle('C')->getAlignment()->setWrapText(true);
                $data_start_row++;
            }
        } else {
            $worksheet->setCellValue('A' . $data_start_row, 'Tidak Ada Data');
            $worksheet->getStyle('A' . $data_start_row - 1 . ':J' . $data_start_row)->applyFromArray($styleArray);
            $worksheet->mergeCells('A' . $data_start_row . ':J' . $data_start_row);
        }

        $columns = ['A', 'B', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        foreach ($columns as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }

        $worksheet->getStyle('A3:C4')->applyFromArray($styleArray);
        $worksheet->getStyle('A3:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $worksheet->getStyle('A3:A4')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff00');
        $worksheet->getStyle('A6:J6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A6:J6')->getFont()->setBold(true);
        $worksheet->getColumnDimension('C')->setWidth(150, 'px');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Data Lokasi Presensi_' . date('Y-m-d-His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function pencarianLokasi()
    {
        $currentPage = $this->request->getVar('page_lokasi-presensi') ? $this->request->getVar('page_lokasi-presensi') : 1;
        $filter = [
            'keyword' => $this->request->getGet('keyword'),
            'tipe' => $this->request->getGet('tipe'),
            'waktu' => $this->request->getGet('waktu'),
        ];

        if (empty($filter['keyword'])) {
            $filter['keyword'] = '';
        }

        $id_unit = current_unit_id();
        $hasil = $this->lokasiModel->getLokasi(false, $filter, false, 10, $id_unit);

        $data = [
            'lokasi' => $hasil['lokasi'],
            'currentPage' => $currentPage,
            'pager' => $hasil['links'],
            'total' => $hasil['total'],
            'perPage' => $hasil['perPage'],
        ];

        return view('lokasi_presensi/hasil-pencarian', $data);
    }

    public function detail($slug): string
    {
        $lokasi = $this->lokasiModel->getLokasi($slug, false, false, 10, current_unit_id())['lokasi'];

        if (empty($lokasi)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Lokasi ' . $slug . ' Tidak Ditemukan');
        }

        $data = [
            'title' => 'Detail ' . $lokasi['nama_lokasi'],
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'lokasi' => $lokasi,
        ];

        return view('lokasi_presensi/detail', $data);
    }

    public function add(): string
    {
        $id_unit_scope = current_unit_id();
        $data = [
            'title' => 'Tambah Data Lokasi Presensi',
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'unit' => $this->unitModel->findAll(),
            'is_admin' => $id_unit_scope !== null,
            'current_unit_id' => $id_unit_scope,
        ];

        return view('lokasi_presensi/tambah', $data);
    }

    public function store()
    {
        $rules = [
            'nama_lokasi' => [
                'rules' => 'required|is_unique[lokasi_presensi.nama_lokasi]',
                'errors' => [
                    'required' => 'mohon isi nama lokasi',
                    'is_unique' => 'lokasi sudah terdaftar',
                ]
            ],
            'alamat_lokasi' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'mohon isi alamat lokasi',
                ]
            ],
            'tipe_lokasi' => [
                'rules' => 'required|in_list[Pusat,Cabang]',
                'errors' => [
                    'required' => 'mohon pilih tipe lokasi',
                    'in_list' => 'mohon pilih pada pilihan yang tersedia',
                ]
            ],
            'latitude' => [
                'rules' => 'required|numeric|decimal',
                'errors' => [
                    'required' => 'mohon isi latitude lokasi',
                    'numeric' => 'mohon isi nilai latitude berupa angka yang valid',
                    'decimal' => 'mohon isi nilai latitude berupa angka yang valid',
                ]
            ],
            'longitude' => [
                'rules' => 'required|numeric|decimal',
                'errors' => [
                    'required' => 'mohon isi longitude lokasi',
                    'numeric' => 'mohon isi nilai latitude berupa angka yang valid',
                    'decimal' => 'mohon isi nilai latitude berupa angka yang valid',
                ]
            ],
            'radius' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'mohon isi radius presensi',
                    'numeric' => 'mohon isi tipe data angka',
                ]
            ],
            'zona_waktu' => [
                'rules' => 'required|valid_timezone',
                'errors' => [
                    'required' => 'mohon pilih zona waktu',
                    'valid_timezone' => 'mohon pilih zona waktu yang tersedia',
                ]
            ],
            'jam_masuk' => [
                'rules' => 'required|regex_match[/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/]',
                'errors' => [
                    'required' => 'mohon isi waktu untuk jam masuk',
                    'regex_match' => 'mohon isi dengan format waktu yang benar',
                ]
            ],
            'jam_pulang' => [
                'rules' => 'required|regex_match[/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/]',
                'errors' => [
                    'required' => 'mohon isi waktu untuk jam pulang',
                    'regex_match' => 'mohon isi dengan format waktu yang benar',
                ]
            ],
        ];

        $id_unit_scope = current_unit_id();
        // Head wajib memilih unit; admin unitnya dipaksa server-side
        if ($id_unit_scope === null) {
            $rules['unit'] = [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'mohon pilih unit operasional',
                    'numeric' => 'mohon pilih unit operasional yang tersedia',
                ]
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->to('/tambah-lokasi-presensi')->withInput();
        }

        $id_unit = ($id_unit_scope !== null) ? $id_unit_scope : (int) $this->request->getVar('unit');

        $newLokasi = $this->request->getVar('nama_lokasi');
        $slug = url_title($newLokasi, '-', true);

        $this->lokasiModel->save([
            'nama_lokasi' => $this->request->getVar('nama_lokasi'),
            'slug' => $slug,
            'alamat_lokasi' => $this->request->getVar('alamat_lokasi'),
            'tipe_lokasi' => $this->request->getVar('tipe_lokasi'),
            'id_unit' => $id_unit,
            'latitude' => $this->request->getVar('latitude'),
            'longitude' => $this->request->getVar('longitude'),
            'radius' => $this->request->getVar('radius'),
            'zona_waktu' => $this->request->getVar('zona_waktu'),
            'jam_masuk' => $this->request->getVar('jam_masuk'),
            'jam_pulang' => $this->request->getVar('jam_pulang'),
        ]);

        session()->setFlashdata('berhasil', 'Lokasi ' . $newLokasi . ' Berhasil Ditambahkan');
        return redirect()->to('/lokasi-presensi');
    }

    public function edit($slug): string
    {
        $id_unit_scope = current_unit_id();
        $lokasi = $this->lokasiModel->getLokasi($slug, false, false, 10, $id_unit_scope)['lokasi'];

        if (empty($lokasi)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Lokasi ' . $slug . ' Tidak Ditemukan');
        }

        $data = [
            'title' => 'Edit Data Lokasi Presensi ' . $lokasi['nama_lokasi'],
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'lokasi' => $lokasi,
            'unit' => $this->unitModel->findAll(),
            'is_admin' => $id_unit_scope !== null,
            'current_unit_id' => $id_unit_scope,
        ];

        return view('lokasi_presensi/edit', $data);
    }

    public function update()
    {
        $id = $this->request->getVar('id');
        $slug = $this->request->getVar('slug');

        $lokasi_db = $this->lokasiModel->getWhere(['id' => $id])->getFirstRow();
        $this->pastikanDalamUnit($lokasi_db->id_unit);
        $nama_lokasi_db = $lokasi_db->nama_lokasi;
        $nama_lokasi_edit = $this->request->getVar('nama_lokasi');

        if ($nama_lokasi_db === $nama_lokasi_edit) {
            $rules_nama_lokasi = 'required';
        } else {
            $rules_nama_lokasi = 'required|is_unique[lokasi_presensi.nama_lokasi]';
        }

        $rules = [
            'nama_lokasi' => [
                'rules' => $rules_nama_lokasi,
                'errors' => [
                    'required' => 'mohon isi nama lokasi',
                    'is_unique' => 'lokasi sudah terdaftar',
                ]
            ],
            'alamat_lokasi' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'mohon isi alamat lokasi',
                ]
            ],
            'tipe_lokasi' => [
                'rules' => 'required|in_list[Pusat,Cabang]',
                'errors' => [
                    'required' => 'mohon pilih tipe lokasi',
                    'in_list' => 'mohon pilih pada pilihan yang tersedia',
                ]
            ],
            'latitude' => [
                'rules' => 'required|numeric|decimal',
                'errors' => [
                    'required' => 'mohon isi latitude lokasi',
                    'numeric' => 'mohon isi nilai latitude berupa angka yang valid',
                    'decimal' => 'mohon isi nilai latitude berupa angka yang valid',
                ]
            ],
            'longitude' => [
                'rules' => 'required|numeric|decimal',
                'errors' => [
                    'required' => 'mohon isi longitude lokasi',
                    'numeric' => 'mohon isi nilai latitude berupa angka yang valid',
                    'decimal' => 'mohon isi nilai latitude berupa angka yang valid',
                ]
            ],
            'radius' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'mohon isi radius presensi',
                    'numeric' => 'mohon isi tipe data angka',
                ]
            ],
            'zona_waktu' => [
                'rules' => 'required|valid_timezone',
                'errors' => [
                    'required' => 'mohon pilih zona waktu',
                    'valid_timezone' => 'mohon pilih zona waktu yang tersedia',
                ]
            ],
            'jam_masuk' => [
                'rules' => 'required|regex_match[/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/]',
                'errors' => [
                    'required' => 'mohon isi waktu untuk jam masuk',
                    'regex_match' => 'mohon isi dengan format waktu yang benar',
                ]
            ],
            'jam_pulang' => [
                'rules' => 'required|regex_match[/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/]',
                'errors' => [
                    'required' => 'mohon isi waktu untuk jam pulang',
                    'regex_match' => 'mohon isi dengan format waktu yang benar',
                ]
            ],
        ];

        $id_unit_scope = current_unit_id();
        // Head wajib memilih unit; admin unitnya dipaksa server-side (tidak bisa pindah unit)
        if ($id_unit_scope === null) {
            $rules['unit'] = [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'mohon pilih unit operasional',
                    'numeric' => 'mohon pilih unit operasional yang tersedia',
                ]
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->to('/lokasi-presensi/edit/' . $slug)->withInput();
        }

        $id_unit = ($id_unit_scope !== null) ? (int) $lokasi_db->id_unit : (int) $this->request->getVar('unit');

        $newLokasi = $this->request->getVar('nama_lokasi');
        $slug = url_title($newLokasi, '-', true);

        $this->lokasiModel->save([
            'id' => $id,
            'nama_lokasi' => $this->request->getVar('nama_lokasi'),
            'slug' => $slug,
            'alamat_lokasi' => $this->request->getVar('alamat_lokasi'),
            'tipe_lokasi' => $this->request->getVar('tipe_lokasi'),
            'id_unit' => $id_unit,
            'latitude' => $this->request->getVar('latitude'),
            'longitude' => $this->request->getVar('longitude'),
            'radius' => $this->request->getVar('radius'),
            'zona_waktu' => $this->request->getVar('zona_waktu'),
            'jam_masuk' => $this->request->getVar('jam_masuk'),
            'jam_pulang' => $this->request->getVar('jam_pulang'),
        ]);

        session()->setFlashdata('berhasil', 'Lokasi ' . $newLokasi . ' Berhasil Diedit');
        return redirect()->to('/lokasi-presensi');
    }

    public function delete($id)
    {
        $lokasi_db = $this->lokasiModel->getWhere(['id' => $id])->getFirstRow();
        if ($lokasi_db) {
            $this->pastikanDalamUnit($lokasi_db->id_unit);
        }

        $this->lokasiModel->delete($id);

        session()->setFlashdata('berhasil', 'Data Lokasi Berhasil Dihapus');
        return redirect()->to('/lokasi-presensi');
    }

    /**
     * Endpoint AJAX: daftar lokasi untuk satu unit (untuk pemilih lokasi di form pegawai).
     * Admin: argumen diabaikan, dipaksa ke unitnya. Head: pakai unit yang diminta.
     */
    public function byUnit($id_unit = null)
    {
        $scope = current_unit_id();
        $unit = ($scope !== null) ? $scope : $id_unit;

        return $this->response->setJSON($this->lokasiModel->getByUnit($unit));
    }

    public function downloadTemplateImportLokasi()
    {
        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();

        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $headers = [
            'NAMA LOKASI',
            'ALAMAT',
            'TIPE (Pusat/Cabang)',
            'LATITUDE',
            'LONGITUDE',
            'RADIUS (m)',
            'ZONA WAKTU',
            'JAM MASUK (HH:MM)',
            'JAM PULANG (HH:MM)',
            'UNIT OPERASIONAL',
        ];

        foreach ($cols as $i => $col) {
            $ws->setCellValue($col . '1', $headers[$i]);
        }

        $ws->getStyle('A1:J1')->getFont()->setBold(true);
        $ws->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $ws->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');
        $ws->getStyle('A1:J1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Force H and I as text so Excel does not convert "08:00" to a time serial
        $ws->getStyle('H:I')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $sample = [
            'Kantor Pusat',
            'Jl. Merdeka No. 1, Jakarta',
            'Pusat',
            '-6.200000',
            '106.816666',
            '100',
            'Asia/Jakarta',
            '08:00',
            '17:00',
            'OP I',
        ];

        foreach ($cols as $i => $col) {
            if (in_array($col, ['H', 'I'])) {
                $ws->setCellValueExplicit(
                    $col . '2',
                    $sample[$i],
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            } else {
                $ws->setCellValue($col . '2', $sample[$i]);
            }
        }

        $ws->getStyle('A2:J2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        $ws->getStyle('A1:J2')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        foreach ($cols as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Lokasi_Presensi.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function importPreview()
    {
        $file = $this->request->getFile('file_import');

        if (!$file || !$file->isValid()) {
            session()->setFlashdata('gagal', 'File tidak valid atau tidak ditemukan.');
            return redirect()->to('/lokasi-presensi');
        }

        if (!in_array(strtolower($file->getClientExtension()), ['xlsx', 'xls'])) {
            session()->setFlashdata('gagal', 'Format file harus .xlsx atau .xls');
            return redirect()->to('/lokasi-presensi');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Exception $e) {
            session()->setFlashdata('gagal', 'File Excel tidak dapat dibaca.');
            return redirect()->to('/lokasi-presensi');
        }

        $ws         = $spreadsheet->getActiveSheet();
        $highestRow = $ws->getHighestRow();

        $id_unit_scope = current_unit_id();
        $allUnits      = [];

        if ($id_unit_scope === null) {
            foreach ($this->unitModel->where('deleted_at', null)->findAll() as $u) {
                $allUnits[strtolower(trim($u->nama))] = $u->id;
            }
        }

        $seenNama  = [];
        $rows      = [];
        $validRows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nama = trim((string) $ws->getCell('A' . $row)->getValue());
            if ($nama === '') continue;

            $alamat  = trim((string) $ws->getCell('B' . $row)->getValue());
            $tipe    = trim((string) $ws->getCell('C' . $row)->getValue());
            $lat     = trim((string) $ws->getCell('D' . $row)->getValue());
            $lng     = trim((string) $ws->getCell('E' . $row)->getValue());
            $radius  = trim((string) $ws->getCell('F' . $row)->getValue());
            $zona    = trim((string) $ws->getCell('G' . $row)->getValue());
            $unitNama = trim((string) $ws->getCell('J' . $row)->getValue());

            // jam_masuk / jam_pulang: use formatted value to handle both text and time-typed cells
            $masuk  = $this->normalizeJam($ws->getCell('H' . $row));
            $pulang = $this->normalizeJam($ws->getCell('I' . $row));

            $errors = [];

            if ($nama === '') {
                $errors[] = 'Nama lokasi wajib diisi';
            } elseif (isset($seenNama[strtolower($nama)])) {
                $errors[] = 'Nama lokasi duplikat dalam file';
            } elseif ($this->lokasiModel->where('nama_lokasi', $nama)->countAllResults() > 0) {
                $errors[] = 'Nama lokasi sudah terdaftar di sistem';
            }

            if ($alamat === '') {
                $errors[] = 'Alamat wajib diisi';
            }

            if (!in_array($tipe, ['Pusat', 'Cabang'])) {
                $errors[] = 'Tipe harus "Pusat" atau "Cabang"';
            }

            if ($lat === '' || !is_numeric($lat)) {
                $errors[] = 'Latitude harus berupa angka';
            }

            if ($lng === '' || !is_numeric($lng)) {
                $errors[] = 'Longitude harus berupa angka';
            }

            if ($radius === '' || !is_numeric($radius)) {
                $errors[] = 'Radius harus berupa angka';
            }

            if ($zona === '' || !in_array($zona, timezone_identifiers_list())) {
                $errors[] = 'Zona waktu tidak valid (contoh: Asia/Jakarta)';
            }

            if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $masuk)) {
                $errors[] = 'Jam masuk harus format HH:MM (contoh: 08:00)';
            }

            if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $pulang)) {
                $errors[] = 'Jam pulang harus format HH:MM (contoh: 17:00)';
            }

            $id_unit = $id_unit_scope;
            if ($id_unit_scope === null) {
                if ($unitNama === '') {
                    $errors[] = 'Unit operasional wajib diisi';
                } elseif (!isset($allUnits[strtolower($unitNama)])) {
                    $errors[] = 'Unit operasional "' . $unitNama . '" tidak ditemukan';
                } else {
                    $id_unit = $allUnits[strtolower($unitNama)];
                }
            }

            $isValid  = empty($errors);
            $rows[]   = [
                'row'          => $row,
                'nama_lokasi'  => $nama,
                'alamat_lokasi'=> $alamat,
                'tipe_lokasi'  => $tipe,
                'latitude'     => $lat,
                'longitude'    => $lng,
                'radius'       => $radius,
                'zona_waktu'   => $zona,
                'jam_masuk'    => $masuk,
                'jam_pulang'   => $pulang,
                'unit_nama'    => $unitNama,
                'id_unit'      => $id_unit,
                'status'       => $isValid ? 'valid' : 'invalid',
                'errors'       => $errors,
            ];

            if ($isValid) {
                $seenNama[strtolower($nama)] = true;
                $validRows[] = [
                    'nama_lokasi'   => $nama,
                    'slug'          => url_title($nama, '-', true),
                    'alamat_lokasi' => $alamat,
                    'tipe_lokasi'   => $tipe,
                    'latitude'      => $lat,
                    'longitude'     => $lng,
                    'radius'        => $radius,
                    'zona_waktu'    => $zona,
                    'jam_masuk'     => $masuk,
                    'jam_pulang'    => $pulang,
                    'id_unit'       => $id_unit,
                ];
            }
        }

        session()->set('lokasi_import_preview', $rows);
        session()->set('lokasi_import_valid', $validRows);

        $data = [
            'title'         => 'Preview Import Lokasi Presensi',
            'user_profile'  => $this->usersModel->getUserInfo(user_id()),
            'rows'          => $rows,
            'valid_count'   => count($validRows),
            'invalid_count' => count($rows) - count($validRows),
            'is_head'       => $id_unit_scope === null,
        ];

        return view('lokasi_presensi/import_preview', $data);
    }

    public function importSave()
    {
        $validRows = session()->get('lokasi_import_valid');

        if (empty($validRows)) {
            session()->setFlashdata('warning', 'Tidak ada data valid untuk disimpan.');
            return redirect()->to('/lokasi-presensi');
        }

        $inserted = 0;
        $skipped  = 0;

        foreach ($validRows as $row) {
            if ($this->lokasiModel->where('nama_lokasi', $row['nama_lokasi'])->countAllResults() > 0) {
                $skipped++;
                continue;
            }
            $this->lokasiModel->save($row);
            $inserted++;
        }

        session()->remove('lokasi_import_preview');
        session()->remove('lokasi_import_valid');

        $msg = $inserted . ' lokasi berhasil ditambahkan';
        if ($skipped > 0) {
            $msg .= ', ' . $skipped . ' dilewati karena nama sudah terdaftar';
        }

        session()->setFlashdata('berhasil', $msg);
        return redirect()->to('/lokasi-presensi');
    }

    private function normalizeJam(\PhpOffice\PhpSpreadsheet\Cell\Cell $cell): string
    {
        $value = $cell->getValue();

        // Excel stores time as a fractional day (float); convert back to H:i
        if (is_float($value) || (is_int($value) && $value >= 0 && $value < 1)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('H:i');
        }

        $str = trim((string) $value);

        // Normalize single-digit hour: "8:00" → "08:00"
        if (preg_match('/^(\d):(\d{2})$/', $str, $m)) {
            return '0' . $m[1] . ':' . $m[2];
        }

        return $str;
    }
}
