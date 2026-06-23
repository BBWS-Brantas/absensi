<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<div class="page-body">
    <div class="container-xl">

        <!-- Summary & actions -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="card-title mb-1">Preview Import Data Pegawai</h3>
                        <div class="text-muted">Periksa data sebelum disimpan. Baris tidak valid tidak akan disimpan. Password default: <strong>123456</strong>.</div>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <a href="<?= base_url('/data-pegawai') ?>" class="btn btn-link">Batal</a>
                        <?php if ($valid_count > 0) : ?>
                            <form action="<?= base_url('/data-pegawai/import/simpan') ?>" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">
                                    Simpan <?= $valid_count ?> Pegawai Valid
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
                                <th style="min-width:160px;">Nama</th>
                                <th>Jenis Kelamin</th>
                                <th style="min-width:200px;">Alamat</th>
                                <th>No Handphone</th>
                                <th>Jabatan</th>
                                <th>Username</th>
                                <th>Email</th>
                                <?php if ($is_head) : ?>
                                    <th>Unit</th>
                                <?php endif; ?>
                                <th style="min-width:180px;">Lokasi Presensi</th>
                                <th style="min-width:160px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)) : ?>
                                <tr>
                                    <td colspan="<?= $is_head ? 11 : 10 ?>" class="text-center py-4 text-muted">
                                        Tidak ada data ditemukan dalam file
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($rows as $r) : ?>
                                    <tr class="<?= $r['status'] === 'valid' ? 'table-success' : 'table-danger' ?>">
                                        <td class="text-center"><?= $r['row'] ?></td>
                                        <td><?= esc($r['nama']) ?></td>
                                        <td class="text-center"><?= esc($r['jenis_kelamin']) ?></td>
                                        <td><?= esc($r['alamat']) ?></td>
                                        <td class="text-center"><?= esc($r['no_handphone']) ?></td>
                                        <td class="text-center"><?= esc($r['jabatan']) ?></td>
                                        <td class="text-center"><?= esc($r['username']) ?></td>
                                        <td><?= esc($r['email']) ?></td>
                                        <?php if ($is_head) : ?>
                                            <td class="text-center"><?= esc($r['unit_nama']) ?></td>
                                        <?php endif; ?>
                                        <td><?= esc($r['lokasi_raw']) ?></td>
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
