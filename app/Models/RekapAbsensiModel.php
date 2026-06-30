<?php

namespace App\Models;

use CodeIgniter\Model;

class RekapAbsensiModel extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getRekapBulanan(int $bulan, int $tahun, ?int $unitId, string $nama = '', string $jabatan = '', bool $all = false, int $perPage = 10): array
    {
        $firstDay = sprintf('%04d-%02d-01', $tahun, $bulan);
        $lastDay  = date('Y-m-t', strtotime($firstDay));

        $where = "WHERE p.deleted_at IS NULL";
        if ($unitId !== null) {
            $where .= " AND p.id_unit = ?";
        }
        if ($nama !== '') {
            $where .= " AND p.nama LIKE ?";
        }
        if ($jabatan !== '') {
            $where .= " AND j.jabatan = ?";
        }

        // Base query without ORDER BY so it can be safely wrapped for COUNT
        $baseSql = "
            SELECT
                p.id,
                p.nama,
                j.jabatan,
                u.nama AS unit_operasional,
                COUNT(DISTINCT pr.tanggal_masuk) AS total_kehadiran,
                COALESCE(SUM(
                    DATEDIFF(
                        LEAST(k.tanggal_berakhir, '$lastDay'),
                        GREATEST(k.tanggal_mulai, '$firstDay')
                    ) + 1
                ), 0) AS total_ijin_sakit_cuti
            FROM pegawai p
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            LEFT JOIN unit_operasional u ON p.id_unit = u.id
            LEFT JOIN presensi pr
                ON pr.id_pegawai = p.id
                AND YEAR(pr.tanggal_masuk) = ?
                AND MONTH(pr.tanggal_masuk) = ?
                AND pr.deleted_at IS NULL
            LEFT JOIN ketidakhadiran k
                ON k.id_pegawai = p.id
                AND k.status_pengajuan = 'APPROVED'
                AND k.tanggal_berakhir >= '$firstDay'
                AND k.tanggal_mulai <= '$lastDay'
                AND k.deleted_at IS NULL
            $where
            GROUP BY p.id, p.nama, j.jabatan, u.nama
        ";

        // $firstDay/$lastDay are server-generated date strings — safe to inline
        // Only $tahun, $bulan, and filter values remain as bound params
        $bindings = [$tahun, $bulan];
        if ($unitId !== null) {
            $bindings[] = $unitId;
        }
        if ($nama !== '') {
            $bindings[] = '%' . $nama . '%';
        }
        if ($jabatan !== '') {
            $bindings[] = $jabatan;
        }

        $total = (int) $this->db->query(
            "SELECT COUNT(*) AS total FROM ($baseSql) AS sub", $bindings
        )->getRow()->total;

        $pager = service('pager');
        $pager->setPath('rekap-absensi', 'rekap_absensi');
        $page   = (int) (($_GET['page_rekap_absensi'] ?? 1) ?: 1);
        $offset = ($page - 1) * $perPage;

        $orderSql = $baseSql . " ORDER BY u.nama, p.nama";

        if ($all) {
            $result = $this->db->query($orderSql, $bindings)->getResult();
        } else {
            // LIMIT/OFFSET are validated integers — safe to interpolate directly
            $result = $this->db->query($orderSql . " LIMIT $perPage OFFSET $offset", $bindings)->getResult();
        }

        return [
            'data'    => $result,
            'total'   => $total,
            'perPage' => $perPage,
            'page'    => $page,
            'links'   => $pager->makeLinks($page, $perPage, $total, 'my_pagination', 0, 'rekap_absensi'),
        ];
    }

    public static function hitungHariKerja(int $bulan, int $tahun, ?string $sampaiTanggal = null): int
    {
        $total      = 0;
        $jumlahHari = (int) date('t', mktime(0, 0, 0, $bulan, 1, $tahun));

        // Jika ada cutoff (misalnya hari ini) dan masih dalam bulan yang sama, batasi hitungan
        if ($sampaiTanggal !== null) {
            $cutoffYear  = (int) date('Y', strtotime($sampaiTanggal));
            $cutoffMonth = (int) date('n', strtotime($sampaiTanggal));
            $cutoffDay   = (int) date('j', strtotime($sampaiTanggal));
            if ($cutoffYear === $tahun && $cutoffMonth === $bulan) {
                $jumlahHari = min($jumlahHari, $cutoffDay);
            }
        }

        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
            // Hari kerja = Senin–Sabtu (skip Minggu / day N=7)
            if (date('N', mktime(0, 0, 0, $bulan, $hari, $tahun)) !== '7') {
                $total++;
            }
        }
        return $total;
    }

    public function getMinYear(): ?string
    {
        $builder = $this->db->table('presensi');
        $builder->selectMin('YEAR(tanggal_masuk)', 'min_year');
        $result = $builder->get()->getRow();
        return $result ? $result->min_year : null;
    }
}
