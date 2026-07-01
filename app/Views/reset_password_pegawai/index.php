<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-lg-6 col-md-12">
                <form action="<?= base_url('/reset-password-pegawai/' . $data_pegawai->username) ?>" method="post" autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit Password Pegawai</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Pegawai</label>
                                <input type="text" class="form-control" value="<?= esc($data_pegawai->nama) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIP</label>
                                <input type="text" class="form-control" value="<?= esc($data_pegawai->nip) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="<?= esc($data_pegawai->email) ?>" readonly>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input name="new_password" type="password" class="form-control <?= validation_show_error('new_password') ? 'is-invalid' : '' ?>" placeholder="Password Baru" autocomplete="off">
                                <?php if (validation_show_error('new_password')) : ?>
                                    <div class="invalid-feedback">
                                        <?= validation_show_error('new_password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input name="confirm_new_password" type="password" class="form-control <?= validation_show_error('confirm_new_password') ? 'is-invalid' : '' ?>" placeholder="Konfirmasi Password Baru" autocomplete="off">
                                <?php if (validation_show_error('confirm_new_password')) : ?>
                                    <div class="invalid-feedback">
                                        <?= validation_show_error('confirm_new_password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="<?= base_url('/data-pegawai') ?>" class="btn btn-link">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
