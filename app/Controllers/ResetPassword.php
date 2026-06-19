<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Myth\Auth\Models\UserModel;

/**
 * Reset password sederhana tanpa token / email.
 * Pengguna memasukkan email + password baru, lalu password langsung diperbarui.
 *
 * Catatan: controller ini sengaja TIDAK extend BaseController, karena
 * BaseController->initController() mengasumsikan ada user yang login
 * (mengakses lokasi_presensi milik user). Halaman ini bersifat publik.
 */
class ResetPassword extends Controller
{
    protected $helpers = ['url', 'form'];

    /**
     * Menampilkan form reset password.
     */
    public function index()
    {
        return view('auth/reset-password-baru');
    }

    /**
     * Memproses reset password: cari user berdasarkan email, lalu simpan password baru.
     */
    public function update()
    {
        $rules = [
            'email'                => 'required|valid_email',
            'new_password'         => 'required',
            'confirm_new_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        $users = new UserModel();
        $user  = $users->where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Email tidak ditemukan.');
        }

        // Entity User akan otomatis mem-hash password saat di-set (setPassword()).
        $user->password = $this->request->getPost('new_password');
        $users->save($user);

        return redirect()->to(url_to('login'))
            ->with('message', 'Password berhasil diperbarui. Silakan login dengan password baru Anda.');
    }
}
