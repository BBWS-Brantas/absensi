<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use Myth\Auth\Models\UserModel;

/**
 * Edit password pegawai oleh admin atau head.
 * Pegawai ditentukan lewat username pada URL (bukan diketik manual).
 * Admin hanya bisa mengakses pegawai dalam unitnya; head bisa mengakses semua unit.
 */
class ResetPasswordPegawai extends BaseController
{
    protected $pegawaiModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->pegawaiModel = new PegawaiModel();
    }

    private function pastikanDalamUnit($id_unit_pegawai)
    {
        $id_unit_scope = current_unit_id();
        if ($id_unit_scope !== null && (int) $id_unit_pegawai !== $id_unit_scope) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data pegawai tidak ditemukan di unit Anda');
        }
    }

    /**
     * Menampilkan form edit password untuk pegawai terpilih.
     */
    public function index($username)
    {
        $data_pegawai = $this->pegawaiModel->getPegawai($username)['pegawai'];

        if (empty($data_pegawai)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Pegawai ' . $username . ' Tidak Ditemukan');
        }

        $this->pastikanDalamUnit($data_pegawai->id_unit);

        $data = [
            'title'        => 'Edit Password ' . $data_pegawai->nama,
            'user_profile' => $this->usersModel->getUserInfo(user_id()),
            'data_pegawai' => $data_pegawai,
        ];

        return view('reset_password_pegawai/index', $data);
    }

    /**
     * Menyimpan password baru untuk pegawai terpilih.
     */
    public function update($username)
    {
        $data_pegawai = $this->pegawaiModel->getPegawai($username)['pegawai'];

        if (empty($data_pegawai)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Pegawai ' . $username . ' Tidak Ditemukan');
        }

        $this->pastikanDalamUnit($data_pegawai->id_unit);

        $rules = [
            'new_password' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Mohon isi password baru',
                ],
            ],
            'confirm_new_password' => [
                'rules'  => 'required|matches[new_password]',
                'errors' => [
                    'required' => 'Mohon konfirmasi password baru',
                    'matches'  => 'Konfirmasi password tidak sama dengan password baru',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/reset-password-pegawai/' . $username)->withInput();
        }

        $users = new UserModel();
        $user  = $users->where('username', $username)->first();

        if (! $user) {
            session()->setFlashdata('gagal', 'Akun pegawai tidak ditemukan.');
            return redirect()->to('/data-pegawai');
        }

        // Entity User otomatis mem-hash password saat di-set (setPassword()).
        $user->password = $this->request->getVar('new_password');
        $users->save($user);

        session()->setFlashdata('berhasil', 'Password untuk ' . $user->username . ' berhasil diubah.');
        return redirect()->to('/data-pegawai');
    }
}
