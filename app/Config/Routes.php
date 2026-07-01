<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index', ['filter' => 'role:admin,pegawai']);
$routes->post('/waktu', 'Home::getWaktu');
$routes->get('/waktu', function () {
    return redirect()->to('/');
});
$routes->get('/admin', 'Admin::index', ['filter' => 'role:admin,head']);

$routes->get('/jabatan', 'Jabatan::index', ['filter' => 'role:admin,head']);
$routes->post('/jabatan/store', 'Jabatan::store', ['filter' => 'role:admin,head']);
$routes->get('/cari-jabatan', 'Jabatan::pencarianJabatan', ['filter' => 'role:admin,head']);
$routes->get('/jabatan/(:segment)', 'Jabatan::edit/$1', ['filter' => 'role:admin,head']);
$routes->post('/jabatan/update', 'Jabatan::update', ['filter' => 'role:admin,head']);
$routes->delete('/jabatan/(:num)', 'Jabatan::delete/$1', ['filter' => 'role:admin,head']);

$routes->get('/lokasi-presensi', 'LokasiPresensi::index', ['filter' => 'role:admin,head']);
$routes->get('/tambah-lokasi-presensi', 'LokasiPresensi::add', ['filter' => 'role:admin,head']);
$routes->post('/lokasi-presensi/store', 'LokasiPresensi::store', ['filter' => 'role:admin,head']);
$routes->get('/cari-lokasi', 'LokasiPresensi::pencarianLokasi', ['filter' => 'role:admin,head']);
$routes->get('/lokasi-presensi/edit/(:segment)', 'LokasiPresensi::edit/$1', ['filter' => 'role:admin,head']);
$routes->post('/lokasi-presensi/update', 'LokasiPresensi::update', ['filter' => 'role:admin,head']);
$routes->delete('/lokasi-presensi/(:num)', 'LokasiPresensi::delete/$1', ['filter' => 'role:admin,head']);
$routes->post('/lokasi-presensi/bulk-delete', 'LokasiPresensi::bulkDelete', ['filter' => 'role:admin,head']);
$routes->get('/lokasi-presensi/by-unit/(:num)', 'LokasiPresensi::byUnit/$1', ['filter' => 'role:admin,head']);
$routes->get('/lokasi-presensi/template-import', 'LokasiPresensi::downloadTemplateImportLokasi', ['filter' => 'role:admin,head']);
$routes->post('/lokasi-presensi/import/preview', 'LokasiPresensi::importPreview', ['filter' => 'role:admin,head']);
$routes->post('/lokasi-presensi/import/simpan', 'LokasiPresensi::importSave', ['filter' => 'role:admin,head']);
$routes->get('/lokasi-presensi/(:any)', 'LokasiPresensi::detail/$1', ['filter' => 'role:admin,head']);
$routes->post('/lokasi-presensi/excel', 'LokasiPresensi::dataLokasiExcel', ['filter' => 'role:admin,head']);

// Unit Operasional (wilayah OP) — CRUD khusus head
$routes->get('/unit-operasional', 'UnitOperasional::index', ['filter' => 'role:head']);
$routes->post('/unit-operasional/store', 'UnitOperasional::store', ['filter' => 'role:head']);
$routes->get('/cari-unit-operasional', 'UnitOperasional::pencarianUnit', ['filter' => 'role:head']);
$routes->post('/unit-operasional/update', 'UnitOperasional::update', ['filter' => 'role:head']);
$routes->delete('/unit-operasional/(:num)', 'UnitOperasional::delete/$1', ['filter' => 'role:head']);
$routes->get('/unit-operasional/(:segment)', 'UnitOperasional::edit/$1', ['filter' => 'role:head']);

$routes->get('/data-pegawai', 'Pegawai::index', ['filter' => 'role:admin,head']);
$routes->get('/tambah-data-pegawai', 'Pegawai::add', ['filter' => 'role:admin,head']);
$routes->post('/data-pegawai/store', 'Pegawai::store', ['filter' => 'role:admin,head']);
$routes->get('/cari-pegawai', 'Pegawai::pencarianPegawai', ['filter' => 'role:admin,head']);
$routes->get('/data-pegawai/edit/(:segment)', 'Pegawai::edit/$1', ['filter' => 'role:admin,head']);
$routes->post('/data-pegawai/update', 'Pegawai::update', ['filter' => 'role:admin,head']);
$routes->get('/data-pegawai/template-import', 'Pegawai::downloadTemplateImportPegawai', ['filter' => 'role:admin,head']);
$routes->post('/data-pegawai/import/preview', 'Pegawai::importPegawaiPreview', ['filter' => 'role:admin,head']);
$routes->post('/data-pegawai/import/simpan', 'Pegawai::importPegawaiSave', ['filter' => 'role:admin,head']);
$routes->post('/data-pegawai/bulk-delete', 'Pegawai::bulkDelete', ['filter' => 'role:admin,head']);
$routes->get('/data-pegawai/(:any)', 'Pegawai::detail/$1', ['filter' => 'role:admin,head']);
$routes->delete('/data-pegawai/(:num)', 'Pegawai::delete/$1', ['filter' => 'role:admin,head']);
$routes->post('/hapus-foto/(:segment)', 'Pegawai::hapusFoto/$1', ['filter' => 'role:admin,head']);
$routes->post('/data-pegawai/excel', 'Pegawai::dataPegawaiExcel', ['filter' => 'role:admin,head']);

$routes->post('/presensi-masuk', 'Presensi::presensiMasuk');
$routes->post('/presensi-masuk/simpan', 'Presensi::simpanPresensiMasuk');

$routes->post('/presensi-keluar', 'Presensi::presensiKeluar');
$routes->post('/presensi-keluar/simpan', 'Presensi::simpanPresensiKeluar');

$routes->get('/rekap-presensi', 'Presensi::rekapPresensiPegawai', ['filter' => 'role:admin,pegawai']);
$routes->get('/laporan-presensi-harian', 'Presensi::laporanHarian', ['filter' => 'role:admin,head']);
$routes->get('/laporan-presensi-bulanan', 'Presensi::laporanBulanan', ['filter' => 'role:admin,head']);
$routes->get('/rekap-absensi', 'RekapAbsensi::index', ['filter' => 'role:admin,head']);
$routes->post('/rekap-absensi/export-excel', 'RekapAbsensi::exportExcel', ['filter' => 'role:admin,head']);
$routes->post('/rekap-absensi/export-pdf', 'RekapAbsensi::exportPdf', ['filter' => 'role:admin,head']);
$routes->post('/laporan-presensi-harian/excel', 'Presensi::laporanHarianExcel', ['filter' => 'role:admin,head']);
$routes->post('/laporan-presensi-bulanan/excel', 'Presensi::laporanBulananExcel', ['filter' => 'role:admin,head']);
$routes->post('/laporan-presensi-harian/pdf', 'Presensi::laporanHarianPdf', ['filter' => 'role:admin,head']);
$routes->post('/laporan-presensi-bulanan/pdf', 'Presensi::laporanBulananPdf', ['filter' => 'role:admin,head']);
$routes->post('/rekap-presensi/excel', 'Presensi::rekapPresensiPegawaiExcel', ['filter' => 'role:admin,pegawai']);
$routes->post('/rekap-presensi/pdf', 'Presensi::rekapPresensiPegawaiPdf', ['filter' => 'role:admin,pegawai']);
$routes->delete('/laporan-presensi/(:num)', 'Presensi::hapusPresensi/$1', ['filter' => 'role:admin,head']);
$routes->post('/laporan-presensi/bulk-delete', 'Presensi::bulkHapusPresensi', ['filter' => 'role:admin,head']);

$routes->get('/ketidakhadiran', 'Ketidakhadiran::index', ['filter' => 'role:admin,pegawai']);
$routes->get('/pengajuan-ketidakhadiran', 'Ketidakhadiran::add', ['filter' => 'role:admin,pegawai']);
$routes->post('/pengajuan-ketidakhadiran/store', 'Ketidakhadiran::store', ['filter' => 'role:admin,pegawai']);
$routes->delete('/ketidakhadiran/(:num)', 'Ketidakhadiran::delete/$1', ['filter' => 'role:admin,pegawai']);
$routes->get('/ketidakhadiran/edit/(:num)', 'Ketidakhadiran::edit/$1', ['filter' => 'role:admin,pegawai']);
$routes->post('/ketidakhadiran/update', 'Ketidakhadiran::update', ['filter' => 'role:admin,pegawai']);
$routes->get('/cari-ketidakhadiran', 'Ketidakhadiran::pencarianKetidakhadiranPegawai', ['filter' => 'role:admin,pegawai']);
$routes->post('/ketidakhadiran/excel', 'Ketidakhadiran::dataKetidakhadiranExcel', ['filter' => 'role:admin,pegawai']);

$routes->get('/kelola-ketidakhadiran', 'Ketidakhadiran::kelolaKetidakhadiran', ['filter' => 'role:head']);
$routes->get('/kelola-ketidakhadiran/(:num)', 'Ketidakhadiran::kelolaKetidakhadiranAksi/$1', ['filter' => 'role:head']);
$routes->post('/kelola-ketidakhadiran/store', 'Ketidakhadiran::updateStatusKetidakhadiran', ['filter' => 'role:head']);
$routes->post('/kelola-ketidakhadiran/excel', 'Ketidakhadiran::kelolaKetidakhadiranExcel', ['filter' => 'role:head']);
$routes->get('/cari-data-ketidakhadiran', 'Ketidakhadiran::pencarianDataKetidakhadiran', ['filter' => 'role:head']);

$routes->get('/profile', 'UserProfile::index');
$routes->get('/profile/edit', 'UserProfile::editProfile');
$routes->post('/profile/hapus-foto', 'UserProfile::hapusFoto');
$routes->post('/profile/update', 'UserProfile::update');

$routes->post('/send-password-token', 'UserProfile::passwordToken');
$routes->post('/send-email-token', 'UserProfile::emailToken');

$routes->get('/change-email', 'UserProfile::changeEmail');
$routes->post('/update-email', 'UserProfile::attemptChangeEmail');

// Reset password sederhana (tanpa token/email) - publik, lihat exception filter 'login' di Config/Filters.php
$routes->get('/reset-password', 'ResetPassword::index');
$routes->post('/reset-password/update', 'ResetPassword::update');

// Reset password pegawai oleh admin/head, pegawai dipilih via username
$routes->get('/reset-password-pegawai/(:segment)', 'ResetPasswordPegawai::index/$1', ['filter' => 'role:admin,head']);
$routes->post('/reset-password-pegawai/(:segment)', 'ResetPasswordPegawai::update/$1', ['filter' => 'role:admin,head']);
