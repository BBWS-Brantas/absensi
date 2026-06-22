<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Models\UnitOperasionalModel;

class UnitOperasional extends BaseController
{
    protected $usersModel;
    protected $unitModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->unitModel = new UnitOperasionalModel();
    }

    public function index(): string
    {
        $unitModel = $this->unitModel->getUnit();

        $currentPage = $this->request->getVar('page_unit') ? $this->request->getVar('page_unit') : 1;
        $keyword = $this->request->getGet('keyword');
        if (!empty($keyword)) {
            $unitModel = $this->unitModel->getUnit(false, $keyword);
        }
        if (empty($keyword)) {
            $keyword = '';
        }

        $data = [
            'title' => 'Data Unit Operasional',
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'data_unit' => $unitModel['unit'],
            'currentPage' => $currentPage,
            'pager' => $unitModel['links'],
            'total' => $unitModel['total'],
            'perPage' => $unitModel['perPage'],
            'keyword' => $keyword,
        ];

        return view('unit_operasional/index', $data);
    }

    public function pencarianUnit()
    {
        $currentPage = $this->request->getVar('page_unit') ? $this->request->getVar('page_unit') : 1;

        $keyword = $this->request->getGet('keyword');
        if (empty($keyword)) {
            $keyword = '';
        }

        $unitModel = $this->unitModel->getUnit(false, $keyword);

        $data = [
            'data_unit' => $unitModel['unit'],
            'currentPage' => $currentPage,
            'pager' => $unitModel['links'],
            'total' => $unitModel['total'],
            'perPage' => $unitModel['perPage'],
        ];

        return view('unit_operasional/hasil-pencarian', $data);
    }

    public function store()
    {
        $rules = [
            'nama' => [
                'rules' => 'required|is_unique[unit_operasional.nama]',
                'errors' => [
                    'required' => 'Mohon isi nama unit operasional baru',
                    'is_unique' => 'Nama unit operasional sudah terdaftar',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/unit-operasional')->withInput();
        }

        $nama = $this->request->getVar('nama');
        $slug = url_title($nama, '-', true);

        $this->unitModel->save([
            'nama' => $nama,
            'slug' => $slug,
        ]);

        session()->setFlashdata('berhasil', 'Data Unit Operasional ' . $nama . ' Berhasil Ditambahkan');
        return redirect()->to('/unit-operasional');
    }

    public function edit($slug)
    {
        $data_unit = $this->unitModel->getUnit($slug)['unit'];

        if (empty($data_unit)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Unit Operasional ' . $slug . ' Tidak Ditemukan');
        }

        $data = [
            'title' => 'Edit Data Unit Operasional',
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'unit' => $data_unit,
        ];

        return view('unit_operasional/edit', $data);
    }

    public function update()
    {
        $id = $this->request->getVar('id');
        $slug = $this->request->getVar('slug');

        $nama_lama = $this->request->getVar('nama_lama');
        $nama = $this->request->getVar('nama');
        // Izinkan nama tetap sama saat edit; hanya cek unik bila berubah
        if ($nama === $nama_lama) {
            $rules_nama = 'required';
        } else {
            $rules_nama = 'required|is_unique[unit_operasional.nama]';
        }

        $rules = [
            'nama' => [
                'rules' => $rules_nama,
                'errors' => [
                    'required' => 'Mohon isi nama unit operasional',
                    'is_unique' => 'Nama unit operasional sudah terdaftar',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/unit-operasional/' . $slug)->withInput();
        }

        $slug_baru = url_title($nama, '-', true);

        $this->unitModel->save([
            'id' => $id,
            'nama' => $nama,
            'slug' => $slug_baru,
        ]);

        session()->setFlashdata('berhasil', 'Data Unit Operasional ' . $nama . ' Berhasil Diupdate');
        return redirect()->to('/unit-operasional');
    }

    public function delete($id)
    {
        $this->unitModel->delete($id);

        session()->setFlashdata('berhasil', 'Data Unit Operasional Berhasil Dihapus');
        return redirect()->to('/unit-operasional');
    }
}
