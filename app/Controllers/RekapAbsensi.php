<?php

namespace App\Controllers;

use DateTime;
use App\Models\UsersModel;
use App\Models\UnitOperasionalModel;
use App\Models\RekapAbsensiModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class RekapAbsensi extends BaseController
{
    protected $usersModel;
    protected $unitModel;
    protected $rekapModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->unitModel  = new UnitOperasionalModel();
        $this->rekapModel = new RekapAbsensiModel();
    }

    public function index()
    {
        $user_profile = $this->usersModel->getUserInfo(user_id());

        $filter_bulan = $this->request->getGet('filter_bulan');
        $filter_tahun = $this->request->getGet('filter_tahun');

        if (empty($filter_bulan)) {
            $filter_bulan = date('m');
        }
        if (empty($filter_tahun)) {
            $filter_tahun = date('Y');
        }

        $filter_unit = $this->request->getGet('id_unit');
        $id_unit = (in_groups('head') && $filter_unit !== null && $filter_unit !== '')
            ? (int) $filter_unit
            : current_unit_id();

        $nama           = trim((string) $this->request->getGet('nama'));
        $filter_jabatan = trim((string) $this->request->getGet('filter_jabatan'));
        $per_page       = (int) $this->request->getGet('per_page');
        if (!in_array($per_page, [10, 50, 100])) {
            $per_page = 10;
        }

        $result     = $this->rekapModel->getRekapBulanan((int) $filter_bulan, (int) $filter_tahun, $id_unit, $nama, $filter_jabatan, false, $per_page);
        $data_rekap = $result['data'];
        $hari_kerja = RekapAbsensiModel::hitungHariKerja((int) $filter_bulan, (int) $filter_tahun, date('Y-m-d'));

        if ($this->rekapModel->getMinYear()) {
            $tahun_mulai = $this->rekapModel->getMinYear();
        } else {
            $tahun_mulai = date('Y');
        }

        $data = [
            'title'          => 'Laporan Presensi Pegawai',
            'user_profile'   => $user_profile,
            'data_rekap'     => $data_rekap,
            'filter_bulan'   => $filter_bulan,
            'filter_tahun'   => $filter_tahun,
            'hari_kerja'     => $hari_kerja,
            'tahun_mulai'    => $tahun_mulai,
            'daftar_unit'    => $this->unitModel->findAll(),
            'filter_unit'    => $filter_unit ?? '',
            'nama'           => $nama,
            'filter_jabatan' => $filter_jabatan,
            'daftar_jabatan' => db_connect()->table('jabatan')->orderBy('jabatan', 'ASC')->get()->getResult(),
            'total'          => $result['total'],
            'perPage'        => $result['perPage'],
            'currentPage'    => $result['page'],
            'pager'          => $result['links'],
            'per_page'       => $per_page,
        ];

        return view('rekap_absensi/index', $data);
    }

    public function exportExcel()
    {
        $filter_bulan = $this->request->getPost('filter_bulan') ?: date('m');
        $filter_tahun = $this->request->getPost('filter_tahun') ?: date('Y');

        $post_unit = $this->request->getPost('id_unit');
        $id_unit   = (in_groups('head') && $post_unit !== null && $post_unit !== '')
            ? (int) $post_unit
            : current_unit_id();

        $nama           = trim((string) $this->request->getPost('nama'));
        $filter_jabatan = trim((string) $this->request->getPost('filter_jabatan'));

        $data_rekap = $this->rekapModel->getRekapBulanan((int) $filter_bulan, (int) $filter_tahun, $id_unit, $nama, $filter_jabatan, true)['data'];
        $hari_kerja  = RekapAbsensiModel::hitungHariKerja((int) $filter_bulan, (int) $filter_tahun, date('Y-m-d'));

        $nama_bulan = (new DateTime())->setDate((int) $filter_tahun, (int) $filter_bulan, 1)->format('F');

        $spreadsheet = new Spreadsheet();
        $worksheet   = $spreadsheet->getActiveSheet();

        $worksheet->setCellValue('A1', 'Laporan Presensi Pegawai');
        $worksheet->setCellValue('A3', 'Bulan');
        $worksheet->setCellValue('A4', 'Tahun');
        $worksheet->setCellValue('C3', $nama_bulan);
        $worksheet->setCellValue('C4', $filter_tahun);
        $worksheet->setCellValue('A6', '#');
        $worksheet->setCellValue('B6', 'TANGGAL');
        $worksheet->setCellValue('C6', 'NAMA TPM');
        $worksheet->setCellValue('D6', 'UNIT OPERASIONAL');
        $worksheet->setCellValue('E6', 'JABATAN');
        $worksheet->setCellValue('F6', 'TOTAL KEHADIRAN');
        $worksheet->setCellValue('G6', 'TOTAL IJIN/SAKIT/CUTI');
        $worksheet->setCellValue('H6', 'TOTAL ALPHA');

        $worksheet->mergeCells('A1:H1');
        $worksheet->mergeCells('A3:B3');
        $worksheet->mergeCells('A4:B4');

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '00000000'],
                ],
            ],
        ];

        $data_start_row = 7;
        $nomor          = 1;
        $periode        = $nama_bulan . ' ' . $filter_tahun;

        if (!empty($data_rekap)) {
            foreach ($data_rekap as $row) {
                $alpha = max(0, $hari_kerja - (int) $row->total_kehadiran - (int) $row->total_ijin_sakit_cuti);

                $worksheet->setCellValue('A' . $data_start_row, $nomor++);
                $worksheet->setCellValue('B' . $data_start_row, $periode);
                $worksheet->setCellValue('C' . $data_start_row, $row->nama);
                $worksheet->setCellValue('D' . $data_start_row, $row->unit_operasional ?? '-');
                $worksheet->setCellValue('E' . $data_start_row, $row->jabatan ?? '-');
                $worksheet->setCellValue('F' . $data_start_row, (int) $row->total_kehadiran);
                $worksheet->setCellValue('G' . $data_start_row, (int) $row->total_ijin_sakit_cuti);
                $worksheet->setCellValue('H' . $data_start_row, $alpha);

                $worksheet->getStyle('A' . ($data_start_row - 1) . ':H' . $data_start_row)->applyFromArray($styleArray);

                $data_start_row++;
            }
        } else {
            $worksheet->setCellValue('A' . $data_start_row, 'Tidak Ada Data');
            $worksheet->mergeCells('A' . $data_start_row . ':H' . $data_start_row);
            $worksheet->getStyle('A' . ($data_start_row - 1) . ':H' . $data_start_row)->applyFromArray($styleArray);
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        $worksheet->getStyle('A3:C4')->applyFromArray($styleArray);
        $worksheet->getStyle('A6:H6')->getFont()->setBold(true);
        $worksheet->getStyle('A6:H6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff00');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan Presensi Pegawai_' . $nama_bulan . '_' . $filter_tahun . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function exportPdf()
    {
        $filter_bulan = $this->request->getPost('filter_bulan') ?: date('m');
        $filter_tahun = $this->request->getPost('filter_tahun') ?: date('Y');

        $post_unit = $this->request->getPost('id_unit');
        $id_unit   = (in_groups('head') && $post_unit !== null && $post_unit !== '')
            ? (int) $post_unit
            : current_unit_id();

        $nama           = trim((string) $this->request->getPost('nama'));
        $filter_jabatan = trim((string) $this->request->getPost('filter_jabatan'));

        $data_rekap = $this->rekapModel->getRekapBulanan((int) $filter_bulan, (int) $filter_tahun, $id_unit, $nama, $filter_jabatan, true)['data'];
        $nama_bulan = (new DateTime())->setDate((int) $filter_tahun, (int) $filter_bulan, 1)->format('F');

        $rows   = [];
        $nomor  = 1;
        foreach ($data_rekap as $row) {
            $rows[] = [
                'no'                  => $nomor++,
                'nama'                => $row->nama,
                'unit_operasional'    => $row->unit_operasional ?? '-',
                'jabatan'             => $row->jabatan ?? '-',
                'total_kehadiran'     => (int) $row->total_kehadiran,
                'total_ijin_sakit_cuti' => (int) $row->total_ijin_sakit_cuti,
            ];
        }

        $html = view('rekap_absensi/rekap_pdf', [
            'rows'         => $rows,
            'nama_bulan'   => $nama_bulan . ' ' . $filter_tahun,
            'filter_tahun' => $filter_tahun,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $namaFile = 'Laporan Presensi Pegawai_' . $nama_bulan . '_' . $filter_tahun . '.pdf';
        $dompdf->stream($namaFile, ['Attachment' => true]);
        exit();
    }
}
