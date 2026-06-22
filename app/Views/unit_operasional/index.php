<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 justify-content-between">
                    <div class="col-lg-6 col-md-12">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                    <path d="M21 21l-6 -6" />
                                </svg>
                            </span>
                            <input type="text" value="<?= $keyword ?>" id="keyword" class="form-control" placeholder="Temukan Unit Operasional..." autofocus>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <form action="<?= base_url('/unit-operasional/store') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="row g-1">
                                <div class="col">
                                    <input type="text" id="tambah-unit" name="nama" class="form-control <?= validation_show_error('nama') ? 'is-invalid' : '' ?>" placeholder="Tambah Unit Operasional Baru" autocomplete="off" value="<?= old('nama') ?>">
                                    <?php if (validation_show_error('nama')) : ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('nama') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" type="submit">Tambah</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards align-items-start">
            <div class="col-lg-12">
                <?= $this->include('unit_operasional/hasil-pencarian') ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Box - Delete -->
<div class="modal modal-blur fade" id="modal-danger" tabindex="-1" role="dialog" aria-hidden="true">
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
                <div class="text-muted">Apakah Anda yakin ingin menghapus unit operasional <strong><span id="modal-name" class="text-danger">ini</span></strong>? Pegawai pada unit ini tidak akan terhapus, namun unitnya menjadi kosong.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                Batal
                            </a></div>
                        <div class="col">
                            <form action="" method="post" class="d-inline" id="form-hapus">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger w-100">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#keyword').on('keyup', function() {
            $.get('cari-unit-operasional?keyword=' + $('#keyword').val(), function(data) {
                $('#data-unit').html(data);
            })
        })

        $('body').on('click', '.btn-hapus', function(e) {
            e.preventDefault();
            var nama = $(this).data('name');
            var id = $(this).data('id');
            $('#modal-name').html(nama);
            $('#modal-danger').modal('show');
            $('#form-hapus').attr('action', '/unit-operasional/' + id);
        });
    })
</script>
<?= $this->endSection() ?>
