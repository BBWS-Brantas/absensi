<?php

namespace App\Models;

use CodeIgniter\Model;

class PresensiModel extends Model
{
    protected $db, $builder;
    protected $table = 'presensi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_pegawai', 'id_lokasi_presensi', 'id_lokasi_keluar', 'tanggal_masuk', 'jam_masuk', 'foto_masuk', 'lat_masuk', 'lng_masuk', 'tanggal_keluar', 'jam_keluar', 'foto_keluar', 'lat_keluar', 'lng_keluar', 'keterangan'];
    protected $useTimestamps = true;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('presensi');
    }

    public function getDataPresensi($id_pegawai, $tanggal_dari = false, $tanggal_sampai = false, $print = false, $perPage = 10, $nama = false)
    {
        $pager = service('pager');
        $pager->setPath('rekap-presensi', 'rekap');

        $page = (@$_GET['page_rekap']) ? $_GET['page_rekap'] : 1;
        $offset = ($page - 1) * $perPage;

        $this->builder->select('presensi.*, pegawai.nip, pegawai.nama, presensi.id_lokasi_presensi, lokasi_presensi.nama_lokasi as lokasi_presensi, lokasi_presensi.jam_masuk as jam_masuk_kantor, unit_operasional.nama as nama_unit');
        $this->builder->join('pegawai', 'pegawai.id = presensi.id_pegawai');
        $this->builder->join('lokasi_presensi', 'lokasi_presensi.id = presensi.id_lokasi_presensi', 'left');
        $this->builder->join('unit_operasional', 'unit_operasional.id = pegawai.id_unit', 'left');
        $this->builder->where('presensi.id_pegawai', $id_pegawai);
        $this->builder->orderBy('presensi.tanggal_masuk', 'DESC');

        $total = 0;

        if ($tanggal_dari || $tanggal_sampai) {
            $this->builder->where('tanggal_masuk BETWEEN ' . "'" . $tanggal_dari . "'" . ' AND ' . "'" . $tanggal_sampai . "'");
        }

        if (!empty($nama)) {
            $this->builder->groupStart()
                ->like('pegawai.nama', $nama)
                ->orLike('pegawai.nip', $nama)
                ->groupEnd();
        }

        $countQuery = clone $this->builder;
        $total = $countQuery->countAllResults();

        if ($print) {
            $result = $this->builder->get()->getResult();
        } else {
            $result = $this->builder->get($perPage, $offset)->getResult();
        }

        return [
            'rekap-presensi' => $result,
            'links' => $pager->makeLinks($page, $perPage, $total, 'my_pagination', 0, 'rekap'),
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
        ];
    }

    public function cekPresensiMasuk($id_pegawai, $tanggal_hari_ini, $hitung = false)
    {
        $condition = [
            'id_pegawai' => $id_pegawai,
            'tanggal_masuk' => $tanggal_hari_ini,
        ];

        if ($hitung) {
            return $this->where($condition)->countAllResults();
        } else {
            return $this->getWhere($condition)->getFirstRow();
        }
    }

    public function getDataPresensiHarian($tanggal_dari = false, $tanggal_sampai = false, $print = false, $perPage = 10, $id_unit = null, $nama = false, $jabatan = false)
    {
        $pager = service('pager');
        $pager->setPath('laporan-presensi-harian', 'harian');

        $page = (@$_GET['page_harian']) ? $_GET['page_harian'] : 1;
        $offset = ($page - 1) * $perPage;

        $this->builder = $this->db->table('presensi');
        $this->builder->select('presensi.*, pegawai.nip, pegawai.nama, presensi.id_lokasi_presensi, lokasi_presensi.nama_lokasi as lokasi_presensi, lokasi_presensi.jam_masuk as jam_masuk_kantor, unit_operasional.nama as nama_unit, jabatan.jabatan');
        $this->builder->join('pegawai', 'pegawai.id = presensi.id_pegawai');
        $this->builder->join('lokasi_presensi', 'lokasi_presensi.id = presensi.id_lokasi_presensi', 'left');
        $this->builder->join('unit_operasional', 'unit_operasional.id = pegawai.id_unit', 'left');
        $this->builder->join('jabatan', 'jabatan.id = pegawai.id_jabatan', 'left');
        $this->builder->orderBy('tanggal_masuk', 'DESC');

        // Scoping per unit (admin): null = tanpa filter (head)
        if ($id_unit !== null) {
            $this->builder->where('pegawai.id_unit', $id_unit);
        }

        $total = 0;
        $tanggal_sekarang = date('Y-m-d');

        if ($tanggal_dari || $tanggal_sampai) {
            $this->builder->where('presensi.tanggal_masuk BETWEEN ' . "'" . $tanggal_dari . "'" . ' AND ' . "'" . $tanggal_sampai . "'");
        } else {
            $this->builder->where('presensi.tanggal_masuk = ' . "'" . $tanggal_sekarang . "'");
        }

        if (!empty($nama)) {
            $this->builder->groupStart()
                ->like('pegawai.nama', $nama)
                ->orLike('pegawai.nip', $nama)
                ->groupEnd();
        }

        if (!empty($jabatan)) {
            $this->builder->where('jabatan.jabatan', $jabatan);
        }

        $countQuery = clone $this->builder;
        $total = $countQuery->countAllResults();

        if ($print) {
            $result = $this->builder->get()->getResult();
        } else {
            $result = $this->builder->get($perPage, $offset)->getResult();
        }

        return [
            'laporan-harian' => $result,
            'links' => $pager->makeLinks($page, $perPage, $total, 'my_pagination', 0, 'harian'),
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
        ];
    }

    public function getDataPresensiBulanan($filter_bulan = false, $filter_tahun = false, $print = false, $perPage = 10, $id_unit = null, $nama = false, $jabatan = false)
    {
        $pager = service('pager');
        $pager->setPath('laporan-presensi-bulanan', 'bulanan');

        $page = (@$_GET['page_bulanan']) ? $_GET['page_bulanan'] : 1;
        $offset = ($page - 1) * $perPage;

        $this->builder = $this->db->table('presensi');
        $this->builder->select('presensi.*, pegawai.nip, pegawai.nama, presensi.id_lokasi_presensi, lokasi_presensi.nama_lokasi as lokasi_presensi, lokasi_presensi.jam_masuk as jam_masuk_kantor, unit_operasional.nama as nama_unit, jabatan.jabatan');
        $this->builder->join('pegawai', 'pegawai.id = presensi.id_pegawai');
        $this->builder->join('lokasi_presensi', 'lokasi_presensi.id = presensi.id_lokasi_presensi', 'left');
        $this->builder->join('unit_operasional', 'unit_operasional.id = pegawai.id_unit', 'left');
        $this->builder->join('jabatan', 'jabatan.id = pegawai.id_jabatan', 'left');
        $this->builder->orderBy('tanggal_masuk', 'DESC');

        // Scoping per unit (admin): null = tanpa filter (head)
        if ($id_unit !== null) {
            $this->builder->where('pegawai.id_unit', $id_unit);
        }

        $total = 0;
        $bulan_sekarang = date('Y-m');

        if ($filter_bulan || $filter_tahun) {
            $bulan_filter = $filter_tahun . '-' . $filter_bulan;
            $this->builder->where('DATE_FORMAT(presensi.tanggal_masuk, "%Y-%m") = ' . "'" . $bulan_filter . "'");
        } else {
            $this->builder->where('DATE_FORMAT(presensi.tanggal_masuk, "%Y-%m") = ' . "'" . $bulan_sekarang . "'");
        }

        if (!empty($nama)) {
            $this->builder->groupStart()
                ->like('pegawai.nama', $nama)
                ->orLike('pegawai.nip', $nama)
                ->groupEnd();
        }

        if (!empty($jabatan)) {
            $this->builder->where('jabatan.jabatan', $jabatan);
        }

        $countQuery = clone $this->builder;
        $total = $countQuery->countAllResults();

        if ($print) {
            $result = $this->builder->get()->getResult();
        } else {
            $result = $this->builder->get($perPage, $offset)->getResult();
        }

        return [
            'laporan-bulanan' => $result,
            'links' => $pager->makeLinks($page, $perPage, $total, 'my_pagination', 0, 'bulanan'),
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
        ];
    }

    public function getMinYear()
    {
        $builder = $this->db->table('presensi');
        $builder->selectMin('YEAR(tanggal_masuk)', 'min_year');
        $query = $builder->get();

        $result = $query->getRow();

        return $result ? $result->min_year : null;
    }

    public function getMinDate($id_pegawai = false)
    {
        $builder = $this->db->table('presensi');

        if ($id_pegawai) {
            $builder->where('presensi.id_pegawai', $id_pegawai);
        }

        $builder->selectMin('tanggal_masuk', 'min_date');
        $query = $builder->get();

        $result = $query->getRow();

        return $result ? $result->min_date : null;
    }

    public function getDataPresensiHariIni($id_unit = null)
    {
        $builder = $this->db->table('presensi');
        $builder->select('presensi.*');
        $builder->where('tanggal_masuk', date('Y-m-d'));

        // Scoping per unit (admin): null = tanpa filter (head)
        if ($id_unit !== null) {
            $builder->join('pegawai', 'pegawai.id = presensi.id_pegawai');
            $builder->where('pegawai.id_unit', $id_unit);
        }

        $query = $builder->get();
        return $query->getNumRows();
    }
}
