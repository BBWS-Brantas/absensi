<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nama TPM</th>
                                    <td><?= $data_ketidakhadiran->nama ?></td>
                                </tr>
                                <tr>
                                    <th>Tipe Ketidakhadiran</th>
                                    <td><?= $data_ketidakhadiran->tipe_ketidakhadiran ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Mulai</th>
                                    <td><?= date('d F Y', strtotime($data_ketidakhadiran->tanggal_mulai)) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Berakhir</th>
                                    <td><?= date('d F Y', strtotime($data_ketidakhadiran->tanggal_berakhir)) ?></td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td><?= esc($data_ketidakhadiran->deskripsi) ?></td>
                                </tr>
                                <tr>
                                    <th>Status Pengajuan</th>
                                    <td>
                                        <span class="badge <?php if ($data_ketidakhadiran->status_pengajuan === 'PENDING') {
                                            echo 'badge-outline text-yellow';
                                        } elseif ($data_ketidakhadiran->status_pengajuan === 'REJECTED') {
                                            echo 'badge-outline text-red';
                                        } else {
                                            echo 'badge-outline text-green';
                                        } ?>">
                                            <?= $data_ketidakhadiran->status_pengajuan ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Surat Keterangan</th>
                                    <td>
                                        <?php if (!empty($data_ketidakhadiran->file)) : ?>
                                            <a href="<?= base_url('assets/file/surat_keterangan_ketidakhadiran/' . $data_ketidakhadiran->file) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                    <path d="M12 17v-6" />
                                                    <path d="M9.5 14.5l2.5 2.5l2.5 -2.5" />
                                                </svg>
                                                Download File
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">Tidak ada file</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php if (!empty($data_ketidakhadiran->file)) : ?>
                            <?php
                                $fileUrl = base_url('assets/file/surat_keterangan_ketidakhadiran/' . $data_ketidakhadiran->file);
                                $ext = strtolower(pathinfo($data_ketidakhadiran->file, PATHINFO_EXTENSION));
                            ?>
                            <div class="mt-3">
                                <p class="text-muted mb-1" style="font-size:.8rem;">Preview Surat Keterangan</p>
                                <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) : ?>
                                    <img src="<?= $fileUrl ?>" alt="Surat Keterangan" class="img-fluid rounded border">
                                <?php elseif ($ext === 'pdf') : ?>
                                    <iframe src="<?= $fileUrl ?>" width="100%" height="480" class="rounded border" style="min-height:480px;"></iframe>
                                <?php else : ?>
                                    <p class="text-muted">Preview tidak tersedia untuk format <strong>.<?= esc($ext) ?></strong>. Gunakan tombol Download di atas.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?= base_url('/kelola-ketidakhadiran') ?>" class="btn btn-link">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
