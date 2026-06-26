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
                                <?php if ($nama !== '' || $filter_unit !== '' || $filter_bulan !== date('m') || (string)$filter_tahun !== date('Y')) : ?>
                                <div class="col-auto align-self-end">
                                    <a href="<?= base_url('laporan-presensi-bulanan') ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                                <?php endif; ?>
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
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title m-0">Presensi Bulan <strong><?= date('F Y', strtotime($data_bulan)); ?></strong></h3>
                        <button type="button" class="btn btn-danger btn-sm" id="btn-delete-selected" style="display: none;" data-bs-toggle="modal" data-bs-target="#modal-hapus-bulk">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 7l16 0" />
                                <path d="M10 11l0 6" />
                                <path d="M14 11l0 6" />
                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                            <span id="delete-selected-count">Hapus (0)</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                               <tr class="text-center">
                                    <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="select-all" title="Pilih Semua"></th>
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
                                    <th>Aksi</th>
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
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="<?= $data->id ?>" data-name="<?= esc($data->nama) ?>">
                                            </td>
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
                                            <td class="text-center">
                                                <a href="#" class="badge bg-danger btn-hapus" data-id="<?= $data->id ?>" data-name="<?= esc($data->nama) ?>">hapus</a>
                                            </td>
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

<!-- Modal Hapus Per Baris -->
<div class="modal modal-blur fade" id="modal-hapus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
                <h3>Hapus?</h3>
                <div class="text-muted">Apakah Anda yakin ingin menghapus data presensi <strong><span id="modal-hapus-name" class="text-danger">ini</span></strong>? Data yang sudah dihapus tidak dapat dikembalikan.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form action="" method="post" class="d-inline" id="form-hapus">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="redirect_to" value="bulanan">
                                <button type="submit" class="btn btn-danger w-100">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus Bulk -->
<div class="modal modal-blur fade" id="modal-hapus-bulk" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
                <h3>Hapus Dipilih?</h3>
                <div class="text-muted">Apakah Anda yakin ingin menghapus <strong><span id="bulk-delete-count" class="text-danger">0</span></strong> data presensi? Data yang sudah dihapus tidak dapat dikembalikan.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form action="<?= base_url('laporan-presensi/bulk-delete') ?>" method="post" class="d-inline" id="form-bulk-hapus">
                                <?= csrf_field() ?>
                                <input type="hidden" name="ids" id="bulk-ids" value="">
                                <input type="hidden" name="redirect_to" value="bulanan">
                                <button type="submit" class="btn btn-danger w-100">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var filterForm = document.querySelector('form[method="get"]');

        // Isi opsi tahun secara dinamis
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

        // Auto-submit saat bulan, tahun, atau unit berubah
        ['page_filter_bulan', 'filter_tahun'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() {
                filterForm.submit();
            });
        });

        var unitEl = document.getElementById('id_unit');
        if (unitEl) unitEl.addEventListener('change', function() {
            filterForm.submit();
        });

        // Auto-submit saat ketik nama (debounce 500ms)
        var debounce;
        document.getElementById('nama').addEventListener('input', function() {
            clearTimeout(debounce);
            debounce = setTimeout(function() { filterForm.submit(); }, 500);
        });

        // Sinkronkan nilai filter ke form export sebelum submit
        document.getElementById('exportForm').addEventListener('submit', function() {
            this.querySelector('[name="filter_bulan"]').value = document.getElementById('page_filter_bulan').value;
            this.querySelector('[name="filter_tahun"]').value = document.getElementById('filter_tahun').value;
            this.querySelector('[name="nama"]').value = document.getElementById('nama').value;
            if (unitEl) this.querySelector('[name="id_unit"]').value = unitEl.value;
        });

        // Checkbox — select all
        document.addEventListener('change', function(e) {
            if (e.target.id === 'select-all') {
                document.querySelectorAll('.row-checkbox').forEach(function(cb) {
                    cb.checked = e.target.checked;
                });
                updateDeleteButton();
            } else if (e.target.classList.contains('row-checkbox')) {
                updateSelectAll();
                updateDeleteButton();
            }
        });

        function updateSelectAll() {
            var all = document.querySelectorAll('.row-checkbox');
            var checked = document.querySelectorAll('.row-checkbox:checked');
            var sa = document.getElementById('select-all');
            if (!sa) return;
            if (all.length > 0 && all.length === checked.length) {
                sa.checked = true; sa.indeterminate = false;
            } else if (checked.length > 0) {
                sa.indeterminate = true;
            } else {
                sa.checked = false; sa.indeterminate = false;
            }
        }

        function updateDeleteButton() {
            var count = document.querySelectorAll('.row-checkbox:checked').length;
            var btn = document.getElementById('btn-delete-selected');
            var label = document.getElementById('delete-selected-count');
            if (count > 0) {
                btn.style.display = '';
                label.textContent = 'Hapus (' + count + ')';
            } else {
                btn.style.display = 'none';
            }
        }

        // Bulk delete — populate modal before show
        document.getElementById('btn-delete-selected').addEventListener('click', function() {
            var ids = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(function(cb) { ids.push(cb.value); });
            document.getElementById('bulk-delete-count').textContent = ids.length;
            document.getElementById('bulk-ids').value = ids.join(',');
        });

        // Per-row hapus
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-hapus');
            if (!btn) return;
            e.preventDefault();
            document.getElementById('modal-hapus-name').textContent = btn.dataset.name;
            document.getElementById('form-hapus').action = '<?= base_url('laporan-presensi/') ?>' + btn.dataset.id;
            var modal = new bootstrap.Modal(document.getElementById('modal-hapus'));
            modal.show();
        });
    });
</script>
<?= $this->endSection() ?>