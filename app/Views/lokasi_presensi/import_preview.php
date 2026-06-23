<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<div class="page-body">
    <div class="container-xl">

        <!-- Summary & actions -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="card-title mb-1">Preview Import Lokasi Presensi</h3>
                        <div class="text-muted">Periksa data sebelum disimpan. Baris tidak valid tidak akan disimpan.</div>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <a href="<?= base_url('/lokasi-presensi') ?>" class="btn btn-link">Batal</a>
                        <?php if ($valid_count > 0) : ?>
                            <form action="<?= base_url('/lokasi-presensi/import/simpan') ?>" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">
                                    Simpan <?= $valid_count ?> Lokasi Valid
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <span class="badge bg-success-lt text-success" style="font-size: .875rem; padding: .4em .75em;">
                        <?= $valid_count ?> baris valid
                    </span>
                    <?php if ($invalid_count > 0) : ?>
                        <span class="badge bg-danger-lt text-danger" style="font-size: .875rem; padding: .4em .75em;">
                            <?= $invalid_count ?> baris tidak valid
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Preview table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-vcenter mb-0">
                        <thead>
                            <tr class="text-center">
                                <th>Baris</th>
                                <th style="min-width:160px;">Nama Lokasi</th>
                                <th style="min-width:220px;">Alamat</th>
                                <th>Tipe</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Radius (m)</th>
                                <th>Zona Waktu</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <?php if ($is_head) : ?>
                                    <th>Unit</th>
                                <?php endif; ?>
                                <th style="min-width:160px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)) : ?>
                                <tr>
                                    <td colspan="<?= $is_head ? 12 : 11 ?>" class="text-center py-4 text-muted">
                                        Tidak ada data ditemukan dalam file
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($rows as $r) : ?>
                                    <tr class="<?= $r['status'] === 'valid' ? 'table-success' : 'table-danger' ?>">
                                        <td class="text-center"><?= $r['row'] ?></td>
                                        <td><?= esc($r['nama_lokasi']) ?></td>
                                        <td><?= esc($r['alamat_lokasi']) ?></td>
                                        <td class="text-center"><?= esc($r['tipe_lokasi']) ?></td>
                                        <td class="text-center"><?= esc($r['latitude']) ?></td>
                                        <td class="text-center"><?= esc($r['longitude']) ?></td>
                                        <td class="text-center"><?= esc($r['radius']) ?></td>
                                        <td class="text-center"><?= esc($r['zona_waktu']) ?></td>
                                        <td class="text-center"><?= esc($r['jam_masuk']) ?></td>
                                        <td class="text-center"><?= esc($r['jam_pulang']) ?></td>
                                        <?php if ($is_head) : ?>
                                            <td class="text-center"><?= esc($r['unit_nama']) ?></td>
                                        <?php endif; ?>
                                        <td class="text-center">
                                            <?php if ($r['status'] === 'valid') : ?>
                                                <span class="badge bg-success">Valid</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger mb-1">Tidak Valid</span>
                                                <?php foreach ($r['errors'] as $err) : ?>
                                                    <div class="small text-danger text-center"><?= esc($err) ?></div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>
