<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UsersModel;
use App\Models\JabatanModel;
use App\Models\PegawaiModel;
use App\Models\UsersRoleModel;
use App\Models\LokasiPresensiModel;
use App\Models\UnitOperasionalModel;
use Myth\Auth\Models\PermissionModel;
use Myth\Auth\Controllers\AuthController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Pegawai extends BaseController
{
    protected $usersModel;
    protected $pegawaiModel;
    protected $jabatanModel;
    protected $roleModel;
    protected $lokasiModel;
    protected $unitModel;
    protected $usersRoleModel;
    protected $permissionModel;
    protected $foto_default;
    protected $auth;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->pegawaiModel = new PegawaiModel();
        $this->jabatanModel = new JabatanModel();
        $this->roleModel = new RoleModel();
        $this->lokasiModel = new LokasiPresensiModel();
        $this->unitModel = new UnitOperasionalModel();
        $this->usersRoleModel = new UsersRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->foto_default = 'default.jpg';
        $this->auth = new AuthController();
    }

    /**
     * Cegah admin mengakses pegawai di luar unitnya. Head ($id_unit_scope === null) dilewati.
     */
    private function pastikanDalamUnit($id_unit_pegawai)
    {
        $id_unit_scope = current_unit_id();
        if ($id_unit_scope !== null && (int) $id_unit_pegawai !== $id_unit_scope) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data pegawai tidak ditemukan di unit Anda');
        }
    }

    public function index(): string
    {
        $id_unit = current_unit_id();
        $pegawaiModel = $this->pegawaiModel->getPegawai(false, false, false, 10, $id_unit);
        $data_jabatan = $this->jabatanModel->get()->getResultArray();
        $data_lokasi = $this->lokasiModel->get()->getResultArray();
        $data_role = $this->roleModel->findAll();
        $currentPage = $this->request->getVar('page_pegawai') ? $this->request->getVar('page_pegawai') : 1;

        $filter = [
            'keyword' => $this->request->getGet('keyword'),
            'jabatan' => $this->request->getGet('jabatan'),
            'role' => $this->request->getGet('role'),
            'status' => $this->request->getGet('status'),
            'jenis-kelamin' => $this->request->getGet('jenis-kelamin'),
            'lokasi-presensi' => $this->request->getGet('lokasi-presensi'),
        ];

        if (!empty($filter)) {
            if ($filter['keyword'] === null) {
                $filter['keyword'] = '';
            }
            if ($filter['jabatan'] === null) {
                $filter['jabatan'] = '';
            }
            if ($filter['role'] === null) {
                $filter['role'] = '';
            }
            if ($filter['status'] === null) {
                $filter['status'] = '';
            }
            if ($filter['jenis-kelamin'] === null) {
                $filter['jenis-kelamin'] = '';
            }
            if ($filter['lokasi-presensi'] === null) {
                $filter['lokasi-presensi'] = '';
            }
            $pegawaiModel = $this->pegawaiModel->getPegawai(false, $filter, false, 10, $id_unit);
        }

        $filtered = false;
        if (($filter['jabatan'] !== null && $filter['jabatan'] !== '') || ($filter['role'] !== null && $filter['role'] !== '') || ($filter['status'] !== null && $filter['status'] !== '') || ($filter['jenis-kelamin'] !== null && $filter['jenis-kelamin'] !== '') || ($filter['lokasi-presensi'] !== null && $filter['lokasi-presensi'] !== '')) {
            $filtered = true;
        }

        $data_pegawai = $pegawaiModel['pegawai'];
        $pager = $pegawaiModel['links'];
        $total = $pegawaiModel['total'];
        $perPage = $pegawaiModel['perPage'];

        $data = [
            'title' => 'Data Pegawai',
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'data_pegawai' => $data_pegawai,
            'data_jabatan' => $data_jabatan,
            'data_lokasi' => $data_lokasi,
            'data_role' => $data_role,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'total' => $total,
            'perPage' => $perPage,
            'isFiltered' => $filtered,
            'filter' => $filter,
        ];

        return view('data_pegawai/index', $data, ['escape' => 'html']);
    }

    public function pencarianPegawai()
    {
        $currentPage = $this->request->getVar('page_pegawai') ? $this->request->getVar('page_pegawai') : 1;

        $filter = [
            'keyword' => $this->request->getGet('keyword'),
            'jabatan' => $this->request->getGet('jabatan'),
            'role' => $this->request->getGet('role'),
            'status' => $this->request->getGet('status'),
            'jenis-kelamin' => $this->request->getGet('jenis-kelamin'),
            'lokasi-presensi' => $this->request->getGet('lokasi-presensi'),
        ];

        if (empty($filter['keyword'])) {
            $filter['keyword'] = '';
        }

        $id_unit = current_unit_id();
        $hasil = $this->pegawaiModel->getPegawai(false, $filter, false, 10, $id_unit);

        $data = [
            'data_pegawai' => $hasil['pegawai'],
            'currentPage' => $currentPage,
            'pager' => $hasil['links'],
            'total' => $hasil['total'],
            'perPage' => $hasil['perPage'],
        ];

        return view('data_pegawai/hasil-pencarian', $data);
    }

    public function dataPegawaiExcel()
    {
        $filter = [
            'keyword' => $this->request->getPost('keyword'),
            'jabatan' => $this->request->getPost('jabatan'),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
            'jenis-kelamin' => $this->request->getPost('jenis-kelamin'),
            'lokasi-presensi' => $this->request->getPost('lokasi-presensi'),
        ];
        $pegawaiModel = $this->pegawaiModel->getPegawai(false, $filter, true, 10, current_unit_id());
        $data_pegawai = $pegawaiModel['pegawai'];

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        if ($filter['jabatan'] === '') {
            $filter['jabatan'] = 'Semua Jabatan';
        }
        if ($filter['role'] === '') {
            $filter['role'] = 'Semua Role';
        }
        if ($filter['status'] === '') {
            $filter['status'] = 'Semua Status';
        }
        if ($filter['jenis-kelamin'] === '') {
            $filter['jenis-kelamin'] = 'Semua Jenis Kelamin';
        }
        if ($filter['lokasi-presensi'] === '') {
            $filter['lokasi-presensi'] = 'Semua Lokasi Presensi';
        }

        $worksheet->setCellValue('A1', 'Data Pegawai');
        $worksheet->setCellValue('A3', 'Filter Jabatan');
        $worksheet->setCellValue('A4', 'Filter Role Akun');
        $worksheet->setCellValue('A5', 'Filter Status');
        $worksheet->setCellValue('A6', 'Filter Jenis Kelamin');
        $worksheet->setCellValue('A7', 'Filter Lokasi Presensi');
        $worksheet->setCellValue('C3', $filter['jabatan']);
        $worksheet->setCellValue('C4', $filter['role']);
        $worksheet->setCellValue('C5', $filter['status']);
        $worksheet->setCellValue('C6', $filter['jenis-kelamin']);
        $worksheet->setCellValue('C7', $filter['lokasi-presensi']);
        $worksheet->setCellValue('A9', '#');
        $worksheet->setCellValue('B9', 'NAMA');
        $worksheet->setCellValue('C9', 'NIP');
        $worksheet->setCellValue('D9', 'JABATAN');
        $worksheet->setCellValue('E9', 'ROLE AKUN');
        $worksheet->setCellValue('F9', 'USERNAME');
        $worksheet->setCellValue('G9', 'EMAIL');
        $worksheet->setCellValue('H9', 'NO. HANDPHONE');
        $worksheet->setCellValue('I9', 'ALAMAT');
        $worksheet->setCellValue('J9', 'JENIS KELAMIN');
        $worksheet->setCellValue('K9', 'LOKASI PRESENSI');
        $worksheet->setCellValue('L9', 'STATUS');

        $worksheet->mergeCells('A1:L1');
        $worksheet->mergeCells('A3:B3');
        $worksheet->mergeCells('A4:B4');
        $worksheet->mergeCells('A5:B5');
        $worksheet->mergeCells('A6:B6');
        $worksheet->mergeCells('A7:B7');

        $data_start_row = 10;
        $nomor = 1;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]
        ];

        if (!empty($data_pegawai)) {
            foreach ($data_pegawai as $data) {
                if ($data->active == 0) {
                    $status = 'Belum Aktivasi';
                } else if ($data->active == 1) {
                    $status = 'Sudah Aktivasi';
                }
                $worksheet->setCellValue('A' . $data_start_row, $nomor++);
                $worksheet->setCellValue('B' . $data_start_row, $data->nama);
                $worksheet->setCellValue('C' . $data_start_row, $data->nip);
                $worksheet->setCellValue('D' . $data_start_row, $data->jabatan);
                $worksheet->setCellValue('E' . $data_start_row, $data->role);
                $worksheet->setCellValue('F' . $data_start_row, $data->username);
                $worksheet->setCellValue('G' . $data_start_row, $data->email);
                $worksheet->setCellValue('H' . $data_start_row, $data->no_handphone);
                $worksheet->setCellValue('I' . $data_start_row, $data->alamat);
                $worksheet->setCellValue('J' . $data_start_row, $data->jenis_kelamin);
                $worksheet->setCellValue('K' . $data_start_row, $data->lokasi_presensi);
                $worksheet->setCellValue('L' . $data_start_row, $status);

                $worksheet->getStyle('A' . $data_start_row - 1 . ':L' . $data_start_row)->applyFromArray($styleArray);
                $worksheet->getStyle('A' . $data_start_row - 1 . ':L' . $data_start_row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                $worksheet->getStyle('I')->getAlignment()->setWrapText(true);
                $data_start_row++;
            }
        } else {
            $worksheet->setCellValue('A' . $data_start_row, 'Tidak Ada Data');
            $worksheet->mergeCells('A' . $data_start_row . ':L' . $data_start_row);
            $worksheet->getStyle('A' . $data_start_row - 1 . ':L' . $data_start_row)->applyFromArray($styleArray);
        }

        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L'];
        foreach ($columns as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }
        $worksheet->getColumnDimension('I')->setWidth(300, 'px');

        $worksheet->getStyle('A3:C7')->applyFromArray($styleArray);
        $worksheet->getStyle('A3:A7')->getFont()->setBold(true);
        $worksheet->getStyle('A3:C7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff00');
        $worksheet->getStyle('A9:L9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A9:L9')->getFont()->setBold(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Data Pegawai_' . date('Y-m-d-His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function detail($username): string
    {
        $data_pegawai = $this->pegawaiModel->getPegawai($username)['pegawai'];

        if (empty($data_pegawai)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Pegawai ' . $username . ' Tidak Ditemukan');
        }

        $this->pastikanDalamUnit($data_pegawai->id_unit);

        $data = [
            'title' => 'Detail Data Pegawai ' . $data_pegawai->nama,
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'data_pegawai' => $data_pegawai,
        ];

        return view('data_pegawai/detail', $data);
    }

    public function add(): string
    {
        $nip = $this->pegawaiModel->getNIPPegawai();
        if (!empty($nip)) {
            $nip = explode('-', $nip);
            $nomor_baru = (int)$nip[1] + 1;
            $nip_baru = 'PEG-' . str_pad($nomor_baru, 4, 0, STR_PAD_LEFT);
        } else {
            $nip_baru = 'PEG-0001';
        }

        $id_unit_scope = current_unit_id();
        $role_pegawai = $this->roleModel->where('name', 'pegawai')->first();

        $data = [
            'title' => 'Tambah Data Pegawai',
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'nip_baru' => $nip_baru,
            'jabatan' => $this->jabatanModel->getJabatan()['jabatan'],
            'role' => $this->roleModel->findAll(),
            'lokasi' => $this->lokasiModel->get()->getResultArray(),
            'unit' => $this->unitModel->findAll(),
            'current_unit_id' => $id_unit_scope,
            // Admin (unit ter-scope) tidak boleh memilih unit/role; head bebas
            'is_admin' => $id_unit_scope !== null,
            'role_pegawai_id' => $role_pegawai['id'] ?? 3,
        ];

        return view('data_pegawai/tambah', $data);
    }

    public function store()
    {
        $rules = [
            'nama' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi nama TPM',
                ]
            ],
            'jenis_kelamin' => [
                'rules' => 'required|in_list[Perempuan,Laki-laki]',
                'errors' => [
                    'required' => 'Mohon isi jenis kelamin pegawai',
                    'in_list' => 'Mohon pilih jenis kelamin yang tersedia',
                ]
            ],
            'alamat' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi alamat domisili pegawai',
                ]
            ],
            'no_handphone' => [
                'rules' => 'required|regex_match[/^(?:\+62|62|0)(?:\d{8,15})$/]',
                'errors' => [
                    'required' => 'Mohon isi nomor telepon pegawai',
                    'regex_match' => 'Mohon isi nomor telepon dengan 8-15 digit',
                ]
            ],
            'jabatan' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Mohon isi jabatan pegawai',
                    'numeric' => 'Mohon pilih jabatan pegawai yang tersedia',
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Mohon isi alamat email pegawai',
                    'valid_email' => 'Mohon isi alamat email yang valid',
                    'is_unique' => 'Alamat email sudah terdaftar',
                ]
            ],
            'lokasi_presensi' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Mohon isi lokasi untuk presensi pegawai',
                    'numeric' => 'Mohon pilih lokasi yang tersedia untuk presensi pegawai',
                ]
            ],
            'unit' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Mohon isi unit operasional pegawai',
                    'numeric' => 'Mohon pilih unit operasional yang tersedia',
                ]
            ],
            'username' => [
                'rules' => 'required|alpha_numeric|min_length[5]|max_length[30]|is_unique[users.username]',
                'errors' => [
                    'required' => 'Mohon isi username untuk akun pegawai',
                    'is_unique' => 'Username sudah terdaftar',
                    'min_length' => 'Username harus terdiri dari 5-30 karakter',
                    'max_length' => 'Username harus terdiri dari 5-30 karakter'
                ],
            ],
            'role' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Mohon isi role untuk akun pegawai',
                    'numeric' => 'Mohon pilih role yag tersedia untuk akun pegawai',
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/tambah-data-pegawai')->withInput();
        }

        $password_default = '123456';
        $password_default_hash = $this->usersModel->hashPassword($password_default);

        /*
        * Mengambil cara aktivasi yang dipilih
        */
        $caraAktivasi = $this->request->getPost('aktivasi');
        if ($caraAktivasi != 2) {
            /*
            * Jika aktivasi manual, active 0 dan buatkan activate_hash
            */
            $active = 0;
            $activate_hash = bin2hex(random_bytes(16));
        } else {
            /*
            * Jika aktivasi otomatis, active 1 dan tidak perlu buatkan activate_hash
            */
            $active = 1;
            $activate_hash = null;
        }

        // Admin hanya boleh menambah pegawai di unitnya sendiri & selalu role pegawai;
        // input unit/role dari form diabaikan untuk admin (head bebas memilih)
        $id_unit_scope = current_unit_id();
        $id_unit = ($id_unit_scope !== null) ? $id_unit_scope : (int) $this->request->getVar('unit');

        if ($id_unit_scope !== null) {
            $role_pegawai = $this->roleModel->where('name', 'pegawai')->first();
            $role_id = (int) ($role_pegawai['id'] ?? 3);
        } else {
            $role_id = (int) $this->request->getVar('role');
        }

        $this->pegawaiModel->save([
            'nip' => $this->request->getVar('nip_baru'),
            'nama' => $this->request->getVar('nama'),
            'jenis_kelamin' => $this->request->getVar('jenis_kelamin'),
            'alamat' => $this->request->getVar('alamat'),
            'no_handphone' => $this->request->getVar('no_handphone'),
            'id_jabatan' => $this->request->getVar('jabatan'),
            'id_lokasi_presensi' => $this->request->getVar('lokasi_presensi'),
            'id_unit' => $id_unit,
            'foto' => $this->foto_default,
        ]);

        // Mendapatkan ID terakhir dari model pegawai
        $id_pegawai = $this->pegawaiModel->insertID();

        $email = $this->request->getVar('email');
        $username =  $this->request->getVar('username');

        $this->usersModel->save([
            'id_pegawai' => $id_pegawai,
            'email' => $email,
            'username' => $username,
            'password_hash' => $password_default_hash,
            'active' => $active,
            'activate_hash' => $activate_hash,
        ]);

        // Mendapatkan ID terakhir dari model users
        $user_id = $this->usersModel->insertID();

        $this->usersRoleModel->save([
            'group_id' => $role_id,
            'user_id' => $user_id,
        ]);

        // Jika memilih cara aktivasi Melalui Email, kirim langsung Activation Email
        $this->auth->resendActivateAccount($this->request->getPost('email'));

        session()->setFlashdata('berhasil', 'Data pegawai berhasil ditambahkan');
        return redirect()->to('/data-pegawai');
    }

    public function edit($username): string
    {
        $data_pegawai = $this->pegawaiModel->getPegawai($username)['pegawai'];

        if (empty($data_pegawai)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Pegawai ' . $username . ' Tidak Ditemukan');
        }

        $this->pastikanDalamUnit($data_pegawai->id_unit);

        $data = [
            'title' => 'Edit Data Pegawai ' . $data_pegawai->nama,
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'data_pegawai' => $data_pegawai,
            'jabatan' => $this->jabatanModel->getJabatan()['jabatan'],
            'role' => $this->roleModel->findAll(),
            'lokasi' => $this->lokasiModel->get()->getResultArray(),
            'unit' => $this->unitModel->findAll(),
            'current_unit_id' => current_unit_id(),
            // Admin tidak boleh mengubah unit/role pegawai; head bebas
            'is_admin' => current_unit_id() !== null,
        ];

        return view('data_pegawai/edit', $data);
    }

    public function update()
    {
        $username_db = $this->request->getVar('username_db');
        $data_pegawai_db = $this->pegawaiModel->getPegawai($username_db)['pegawai'];
        $this->pastikanDalamUnit($data_pegawai_db->id_unit);
        $email_db = $data_pegawai_db->email;

        $email_input = $this->request->getVar('email');
        if ($email_db == $email_input) {
            $rules_email = 'required|valid_email';
        } else {
            $rules_email = 'required|valid_email|is_unique[users.email]';
        }

        $username_input = $this->request->getVar('username');
        if ($username_db == $username_input) {
            $rules_username = 'required|alpha_numeric|min_length[5]|max_length[30]';
        } else {
            $rules_username = 'required|alpha_numeric|min_length[5]|max_length[30]|is_unique[users.username]';
        }

        $rules = [
            'nama' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi nama TPM',
                ]
            ],
            'jenis_kelamin' => [
                'rules' => 'required|in_list[Perempuan,Laki-laki]',
                'errors' => [
                    'required' => 'Mohon isi jenis kelamin pegawai',
                    'in_list' => 'Mohon pilih jenis kelamin yang tersedia',
                ]
            ],
            'alamat' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi alamat domisili pegawai',
                ]
            ],
            'no_handphone' => [
                'rules' => 'required|regex_match[/^(?:\+62|62|0)(?:\d{8,15})$/]',
                'errors' => [
                    'required' => 'Mohon isi nomor telepon pegawai',
                    'regex_match' => 'Mohon isi nomor telepon dengan 8-15 digit',
                ]
            ],
            'jabatan' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi jabatan pegawai',
                ]
            ],
            'email' => [
                'rules' => $rules_email,
                'errors' => [
                    'required' => 'Mohon isi alamat email pegawai',
                    'valid_email' => 'Mohon isi alamat email yang valid',
                    'is_unique' => 'Alamat email sudah terdaftar',
                ]
            ],
            'lokasi_presensi' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi lokasi untuk presensi pegawai',
                ]
            ],
            'unit' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Mohon isi unit operasional pegawai',
                    'numeric' => 'Mohon pilih unit operasional yang tersedia',
                ]
            ],
            'username' => [
                'rules' => $rules_username,
                'errors' => [
                    'required' => 'Mohon isi username untuk akun pegawai',
                    'is_unique' => 'Username sudah terdaftar',
                    'min_length' => 'Username harus terdiri dari 5-30 karakter',
                    'max_length' => 'Username harus terdiri dari 5-30 karakter'
                ],
            ],
            'role' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mohon isi role untuk akun pegawai',
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/data-pegawai/edit/' . $username_db)->withInput();
        }

        // Admin tidak dapat memindahkan pegawai ke unit lain; head bebas mengubah unit
        $id_unit_scope = current_unit_id();
        $id_unit = ($id_unit_scope !== null) ? $id_unit_scope : (int) $this->request->getVar('unit');

        $this->pegawaiModel->save([
            'id' => $this->request->getVar('id'),
            'nip' => $this->request->getVar('nip'),
            'nama' => $this->request->getVar('nama'),
            'jenis_kelamin' => $this->request->getVar('jenis_kelamin'),
            'alamat' => $this->request->getVar('alamat'),
            'no_handphone' => $this->request->getVar('no_handphone'),
            'id_jabatan' => $this->request->getVar('jabatan'),
            'id_lokasi_presensi' => $this->request->getVar('lokasi_presensi'),
            'id_unit' => $id_unit,
        ]);

        $id_pegawai = $this->request->getVar('id_pegawai');
        $email = $this->request->getVar('email');
        $username =  $this->request->getVar('username');
        $id_user = $this->request->getVar('id_user');

        $this->usersModel->save([
            'id' => $id_user,
            'id_pegawai' => $id_pegawai,
            'email' => $email,
            'username' => $username,
        ]);

        // Admin tidak boleh mengubah role; paksa tetap seperti semula
        $role_db = $this->request->getVar('role_db');
        $role = ($id_unit_scope !== null) ? $role_db : $this->request->getVar('role');

        if ($role !== $role_db) {
            // Mendapatkan instance model Group milik myth/auth
            $groupModel = new \Myth\Auth\Models\GroupModel();

            $groupModel->addUserToGroup($id_user, (int)$role);
            $groupModel->removeUserFromGroup($id_user, (int)$role_db);
        }

        session()->setFlashdata('berhasil', 'Data pegawai ' . $data_pegawai_db->nama . ' berhasil diedit');
        return redirect()->to('/data-pegawai');
    }

    public function hapusFoto($username)
    {
        $pegawai_db = $this->pegawaiModel->getPegawai($username)['pegawai'];
        $this->pastikanDalamUnit($pegawai_db->id_unit);
        $foto_db = $pegawai_db->foto;

        if ($foto_db !== $this->foto_default) {
            $this->pegawaiModel->save([
                'id' => $pegawai_db->id,
                'foto' => $this->foto_default,
            ]);

            unlink('assets/img/user_profile/' . $foto_db);
        }

        session()->setFlashdata('berhasil', 'Foto pegawai ' . $pegawai_db->nama . ' berhasil dihapus');
        return redirect()->to(base_url('/data-pegawai/edit/' . $username));
    }

    public function delete($id)
    {
        $pegawai_db = $this->pegawaiModel->find($id);
        if (empty($pegawai_db)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Pegawai Tidak Ditemukan');
        }
        $this->pastikanDalamUnit($pegawai_db['id_unit']);

        $this->pegawaiModel->delete($id);

        session()->setFlashdata('berhasil', 'Data Pegawai Berhasil Dihapus');
        return redirect()->to('/data-pegawai');
    }
}
