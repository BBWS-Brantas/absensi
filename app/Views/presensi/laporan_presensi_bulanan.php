<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 flex-column-reverse flex-lg-row align-items-end">
                    <div class="col-lg-8">
                        <form method="get">
                            <div class="row align-items-end g-1">
                                <div class="col">
                                    <div class="row">
                                         <label for="id_unit" class="form-label">Bulan</label>
                                        <div class="col">
                                            <select name="filter_bulan" id="page_filter_bulan" class="form-select">
                                                <option value="01" <?= $filter_bulan === '01' ? 'selected' : '' ?>>Januari</option>
                                                <option value="02" <?= $filter_bulan === '02' ? 'selected' : '' ?>>Februari</option>
                                                <option value="03" <?= $filter_bulan === '03' ? 'selected' : '' ?>>Maret</option>
                                                <option value="04" <?= $filter_bulan === '04' ? 'selected' : '' ?>>April</option>
                                                <option value="05" <?= $filter_bulan === '05' ? 'selected' : '' ?>>Mei</option>
                                                <option value="06" <?= $filter_bulan === '06' ? 'selected' : '' ?>>Juni</option>
                                                <option value="07" <?= $filter_bulan === '07' ? 'selected' : '' ?>>Juli</option>
                                                <option value="08" <?= $filter_bulan === '08' ? 'selected' : '' ?>>Agustus</option>
                                                <option value="09" <?= $filter_bulan === '09' ? 'selected' : '' ?>>September</option>
                                                <option value="10" <?= $filter_bulan === '10' ? 'selected' : '' ?>>Oktober</option>
                                                <option value="11" <?= $filter_bulan === '11' ? 'selected' : '' ?>>November</option>
                                                <option value="12" <?= $filter_bulan === '12' ? 'selected' : '' ?>>Desember</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <select name="filter_tahun" class="form-select filter_tahun" id="filter_tahun">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                     <label for="id_unit" class="form-label">Nama</label>
                                    <input type="text" name="nama" id="nama" class="form-control" placeholder="Cari nama atau NIP..." value="<?= esc($nama) ?>">
                                </div>
                                <?php if (in_groups(['head'])) : ?>
                                <div class="col">
                                    <label for="id_unit" class="form-label">Unit Operasional</label>
                                    <select name="id_unit" id="id_unit" class="form-select">
                                        <option value="">Semua Unit</option>
                                        <?php foreach ($daftar_unit as $unit) : ?>
                                            <option value="<?= $unit->id ?>" <?= ($filter_unit == $unit->id) ? 'selected' : '' ?>><?= esc($unit->nama) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4 text-start text-lg-end">
                        <form id="exportForm" method="POST" action="<?= base_url('/laporan-presensi-bulanan/excel') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="filter_bulan" value="<?= $filter_bulan ?>">
                            <input type="hidden" name="filter_tahun" value="<?= $filter_tahun ?>">
                            <input type="hidden" name="nama" id="exportNama" value="<?= esc($nama) ?>">
                            <input type="hidden" name="id_unit" id="exportIdUnit" value="<?= esc($filter_unit) ?>">
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
                            <button type="submit" class="btn btn-danger" formaction="<?= base_url('/laporan-presensi-bulanan/pdf') ?>">
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
                    <div class="card-body">
                        <h3 class="card-title">Presensi Bulan <strong><?= date('F Y', strtotime($data_bulan)); ?></strong></h3>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                               <tr class="text-center">
                                    <th>No</th>
                                    <th>ID TPM</th>
                                    <th>Nama TPM</th>
                                    <th>Unit Operasional</th>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
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
                                            <td class="text-center"><?= esc($data->nama_unit ?? '-') ?></td>
                                            <td class="text-center"><?= date('d F Y', strtotime($data->tanggal_masuk)) ?></td>
                                            <td class="text-center">
                                                <?= $data->jam_masuk ?> <br/>
                                                <a href="<?= base_url('assets/img/foto_presensi/masuk/' . $data->foto_masuk) ?>" target="_blank">Foto</a>
                                            </td>
                                            <td class="text-center">
                                                <?= $data->jam_keluar ?> <br/>
                                                <?php if ($data->jam_keluar === '00:00:00' || $data->foto_keluar === '-') : ?>
                                                    <spam class="text-center">-</spam>
                                                <?php else : ?>
                                                    <span class="text-center"><a href="<?= base_url('assets/img/foto_presensi/keluar/' . $data->foto_keluar) ?>" target="_blank">Foto</a></span>
                                                <?php endif; ?>
                                            </td>
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
                                        <td colspan="12">Belum ada data presensi.</td>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil elemen select
        var selectTahuns = document.getElementsByClassName('filter_tahun');

        for (var i = 0; i < selectTahuns.length; i++) {
            var selectTahun = selectTahuns[i];
            var tahunSekarang = new Date().getFullYear();
            for (var tahun = <?= $tahun_mulai ?>; tahun <= tahunSekarang; tahun++) {
                var option = document.createElement('option');
                option.value = tahun;
                option.text = tahun;
                if (tahun == <?= $filter_tahun ?>) {
                    option.selected = true;
                }
                selectTahun.add(option);
            }
        }
    });

    document.getElementById('exportForm').addEventListener('submit', function() {
        this.querySelector('[name="filter_bulan"]').value = document.getElementById('page_filter_bulan').value;
        this.querySelector('[name="filter_tahun"]').value = document.getElementById('filter_tahun').value;
        this.querySelector('[name="nama"]').value = document.getElementById('nama').value;
        var unitEl = document.getElementById('id_unit');
        if (unitEl) this.querySelector('[name="id_unit"]').value = unitEl.value;
    });
</script>
<?= $this->endSection() ?>