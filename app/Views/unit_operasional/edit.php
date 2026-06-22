<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards align-items-start">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <form action="<?= base_url('/unit-operasional/update') ?>" method="post">
                        <?= csrf_field() ?>

                        <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                        <input type="hidden" name="slug" value="<?= $unit['slug'] ?>">
                        <input type="hidden" name="nama_lama" value="<?= esc($unit['nama'], 'attr') ?>">
                        <div class="card-body">
                            <label class="form-label">Nama Unit Operasional</label>
                            <div class="mb-3">
                                <input type="text" name="nama" class="form-control <?= validation_show_error('nama') ? 'is-invalid' : '' ?>" placeholder="e.g. OP V" autocomplete="off" value="<?= old('nama', $unit['nama']) ?>">
                                <?php if (validation_show_error('nama')) : ?>
                                    <div class="invalid-feedback">
                                        <?= validation_show_error('nama') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="<?= base_url('unit-operasional') ?>" class="btn btn-link">Batal</a>
                                <button class="btn btn-warning ms-auto" type="submit">Edit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
