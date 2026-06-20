<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row justify-content-between g-3 flex-column-reverse flex-lg-row align-items-end">
                    <div class="col-lg-8 col-md-12">
                        <form method="get">
                            <div class="row align-items-end g-1">
                                <div class="col">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= $tanggal ?>">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4 col-md-12 text-start text-lg-end">
                        <form id="exportForm" method="POST" action="<?= base_url('/laporan-presensi-harian/excel') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                            <button type="submit" class="btn btn-green">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-spreadsheet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    <path d="M8 11h8v7h-8z" />
                                    <path d="M8 15h8" />
                                    <path d="M11 11v7" />
                                </svg>
                                Export Excel
                            </button>
                            <button type="submit" class="btn btn-danger" formaction="<?= base_url('/laporan-presensi-harian/pdf') ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    <path d="M9 9l1 0" />
                                    <path d="M9 13l6 0" />
                                    <path d="M9 17l6 0" />
                                </svg>
                                Export PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-deck row-cards align-items-start">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Presensi <strong><?= $data_tanggal; ?></strong></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>ID TPM</th>
                                    <th>Nama TPM</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Foto Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Foto Pulang</th>
                                    <th>Total Jam Kerja</th>
                                    <th>Total Keterlambatan</th>
                                    <th>Keterangan Kegiatan</th>
                                </tr>
                                <?php if (!empty($data_presensi)) : ?>
                                    <?php $nomor = 1 + ($perPage * ($currentPage - 1)); ?>
                                    <?php foreach ($data_presensi as $data) : ?>
                                        <?php
                                        // TOTAL JAM KERJA
                                        $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($data->tanggal_masuk . ' ' . $data->jam_masuk));
                                        $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($data->tanggal_keluar . ' ' . $data->jam_keluar));

                                        $timestamp_masuk = strtotime($jam_tanggal_masuk);
                                        $timestamp_keluar = strtotime($jam_tanggal_keluar);

                                        // Selisih dalam format time
                                        $selisih = $timestamp_keluar - $timestamp_masuk;

                                        // Selisih dalam format jam
                                        $total_jam_kerja = floor($selisih / 3600);

                                        // Selisih dalam format menit
                                        $selisih_menit_kerja = floor(($selisih % 3600) / 60);

                                        // Format string
                                        $total_jam_kerja_format = sprintf("%d Jam %d Menit", $total_jam_kerja, $selisih_menit_kerja);

                                        // TOTAL KETERLAMBATAN
                                        $jam_masuk = date('H:i:s', strtotime($data->jam_masuk));
                                        $timestamp_jam_masuk_real = strtotime($jam_masuk);

                                        $jam_masuk_kantor = $data->jam_masuk_kantor;
                                        $timestamp_jam_masuk_kantor = strtotime($jam_masuk_kantor);

                                        $terlambat = $timestamp_jam_masuk_real - $timestamp_jam_masuk_kantor;
                                        $total_jam_keterlambatan = floor($terlambat / 3600);
                                        $selisih_menit_keterlambatan = floor(($terlambat % 3600) / 60);

                                        $total_jam_keterlambatan_format = sprintf("%d Jam %d Menit", $total_jam_keterlambatan, $selisih_menit_keterlambatan);
                                        ?>

                                        <tr>
                                            <td class="text-center"><?= $nomor++ ?></td>
                                            <td class="text-center"><?= $data->nip ?></td>
                                            <td><?= $data->nama ?></td>
                                            <td class="text-center"><?= date('d F Y', strtotime($data->tanggal_masuk)) ?></td>
                                            <td class="text-center"><?= $data->jam_masuk ?></td>
                                            <td class="text-center"><a href="<?= base_url('assets/img/foto_presensi/masuk/' . $data->foto_masuk) ?>" target="_blank">Lihat Foto</a></td>
                                            <td class="text-center"><?= $data->jam_keluar ?></td>
                                            <?php if ($data->jam_keluar === '00:00:00' || $data->foto_keluar === '-') : ?>
                                                <td class="text-center">-</td>
                                            <?php else : ?>
                                                <td class="text-center"><a href="<?= base_url('assets/img/foto_presensi/keluar/' . $data->foto_keluar) ?>" target="_blank">Lihat Foto</a></td>
                                            <?php endif; ?>
                                            <?php if ($data->tanggal_keluar === '0000-00-00') : ?>
                                                <td class="text-center">0 Jam 0 Menit</td>
                                            <?php else : ?>
                                                <td class="text-center"><?= $total_jam_kerja_format ?></td>
                                            <?php endif; ?>
                                            <?php if ($total_jam_keterlambatan_format < 0) :  ?>
                                                <td class="text-center"><span class="badge bg-success">On Time</span></td>
                                            <?php else : ?>
                                                <td class="text-center"><?= $total_jam_keterlambatan_format ?></td>
                                            <?php endif; ?>
                                            <td><?= !empty($data->keterangan) && $data->keterangan !== '-' ? esc($data->keterangan) : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="text-center">
                                        <td colspan="11">Belum ada data presensi.</td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <p class="m-0 text-muted">Showing <span><?= ($perPage * ($currentPage - 1)) + 1 ?></span> to <span><?= min($perPage * $currentPage, $total) ?></span> of <span><?= $total ?></span> entries</p>
                        <?= $pager; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sinkronkan rentang tanggal dari filter halaman ke form export saat diekspor
    document.getElementById('exportForm').addEventListener('submit', function() {
        this.querySelector('[name="tanggal"]').value = document.getElementById('tanggal').value;
    });
</script>
<?= $this->endSection() ?>