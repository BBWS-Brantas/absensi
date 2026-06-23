<?php

namespace App\Controllers;

use App\Models\UsersModel;
use CodeIgniter\Controller;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\CLIRequest;
use App\Models\LokasiPresensiModel;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['auth', 'form', 'unit'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    protected $usersModel;
    protected $lokasiModel;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();

        $this->usersModel = new UsersModel();
        $this->lokasiModel = new LokasiPresensiModel();

        $user_profile = $this->usersModel->getUserInfo(user_id());

        // Zona waktu default: ikuti lokasi pertama yang ter-assign (pegawai bisa banyak lokasi).
        // Fallback aman ke Asia/Jakarta bila tidak ada lokasi / belum login.
        $zona = null;
        if ($user_profile) {
            $lokasiPegawaiModel = new \App\Models\LokasiPresensiPegawaiModel();
            $daftar_lokasi = $lokasiPegawaiModel->getLokasiByPegawai($user_profile->id_pegawai);
            $zona = $daftar_lokasi[0]->zona_waktu ?? null;
        }

        if ($zona && in_array($zona, timezone_identifiers_list())) {
            date_default_timezone_set($zona);
        } else {
            date_default_timezone_set('Asia/Jakarta');
        }
    }
}
