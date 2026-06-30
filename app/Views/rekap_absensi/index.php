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
                                        <label class="form-label">Bulan</label>
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
                                            <select name="filter_tahun" class="form-select filter_tahun" id="filter_tahun"></select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" name="nama" id="nama" class="form-control" placeholder="Cari nama..." value="<?= esc($nama) ?>">
                                </div>
                                <div class="col">
                                    <label for="filter_jabatan" class="form-label">Jabatan</label>
                                    <select name="filter_jabatan" id="filter_jabatan" class="form-select">
                                        <option value="">Semua Jabatan</option>
                                        <?php foreach ($daftar_jabatan as $jab) : ?>
                                            <option value="<?= esc($jab->jabatan) ?>" <?= ($filter_jabatan === $jab->jabatan) ? 'selected' : '' ?>><?= esc($jab->jabatan) ?></option>
                                        <?php endforeach; ?>
                                    </select>
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
                                <?php if ($nama !== '' || $filter_jabatan !== '' || $filter_unit !== '' || $filter_bulan !== date('m') || (string) $filter_tahun !== date('Y')) : ?>
                                <div class="col-auto align-self-end">
                                    <a href="<?= base_url('rekap-absensi') ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4 text-start text-lg-end">
                        <form id="exportForm" method="POST" action="<?= base_url('/rekap-absensi/export-excel') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="filter_bulan" value="<?= $filter_bulan ?>">
                            <input type="hidden" name="filter_tahun" value="<?= $filter_tahun ?>">
                            <input type="hidden" name="nama" id="exportNama" value="<?= esc($nama) ?>">
                            <input type="hidden" name="filter_jabatan" id="exportJabatan" value="<?= esc($filter_jabatan) ?>">
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
                            <button type="submit" class="btn btn-danger" formaction="<?= base_url('/rekap-absensi/export-pdf') ?>">
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
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title m-0">
                            Laporan Presensi Pegawai Bulan
                            <strong><?= date('F Y', mktime(0, 0, 0, (int) $filter_bulan, 1, (int) $filter_tahun)) ?></strong>
                        </h3>
                        <span class="text-muted">Data per <strong><?= date('d F Y') ?></strong></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="white-space: nowrap;">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Nama TPM</th>
                                        <th>Unit Operasional</th>
                                        <th>Jabatan</th>
                                        <th>Total Kehadiran</th>
                                        <th>Total Ijin/Sakit/Cuti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($data_rekap)) : ?>
                                    <?php $nomor = 1; ?>
                                    <?php foreach ($data_rekap as $row) : ?>
                                        <tr>
                                            <td class="text-center"><?= $nomor++ ?></td>
                                            <td><?= esc($row->nama) ?></td>
                                            <td class="text-center"><?= esc($row->unit_operasional ?? '-') ?></td>
                                            <td class="text-center"><?= esc($row->jabatan ?? '-') ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-success-lt"><?= (int) $row->total_kehadiran ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning-lt"><?= (int) $row->total_ijin_sakit_cuti ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="text-center">
                                        <td colspan="7">Belum ada data presensi untuk bulan ini.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label for="per_page" class="m-0 text-muted text-nowrap">Tampilkan</label>
                            <select id="per_page" class="form-select form-select-sm" style="width: auto;">
                                <option value="10" <?= $per_page == 10 ? 'selected' : '' ?>>10</option>
                                <option value="50" <?= $per_page == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $per_page == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <p class="m-0 text-muted text-nowrap">
                                Showing <span><?= ($perPage * ($currentPage - 1)) + 1 ?></span>
                                to <span><?= min($perPage * $currentPage, $total) ?></span>
                                of <span><?= $total ?></span> entries
                            </p>
                        </div>
                        <?= $pager ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var filterForm = document.querySelector('form[method="get"]');

        var selectTahuns = document.getElementsByClassName('filter_tahun');
        for (var i = 0; i < selectTahuns.length; i++) {
            var selectTahun = selectTahuns[i];
            var tahunSekarang = new Date().getFullYear();
            for (var tahun = <?= $tahun_mulai ?>; tahun <= tahunSekarang; tahun++) {
                var option = document.createElement('option');
                option.value = tahun;
                option.text = tahun;
                if (tahun == <?= $filter_tahun ?>) option.selected = true;
                selectTahun.add(option);
            }
        }

        ['page_filter_bulan', 'filter_tahun', 'filter_jabatan'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function () { filterForm.submit(); });
        });

        var unitEl = document.getElementById('id_unit');
        if (unitEl) unitEl.addEventListener('change', function () { filterForm.submit(); });

        document.getElementById('per_page').addEventListener('change', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('page_rekap_absensi');
            window.location.href = url.toString();
        });

        var debounce;
        document.getElementById('nama').addEventListener('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(function () { filterForm.submit(); }, 500);
        });

        document.getElementById('exportForm').addEventListener('submit', function () {
            this.querySelector('[name="filter_bulan"]').value = document.getElementById('page_filter_bulan').value;
            this.querySelector('[name="filter_tahun"]').value = document.getElementById('filter_tahun').value;
            this.querySelector('[name="nama"]').value = document.getElementById('nama').value;
            this.querySelector('[name="filter_jabatan"]').value = document.getElementById('filter_jabatan').value;
            if (unitEl) this.querySelector('[name="id_unit"]').value = unitEl.value;
        });
    });
</script>
<?= $this->endSection() ?>
