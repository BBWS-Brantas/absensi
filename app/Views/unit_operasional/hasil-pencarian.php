<div class="card" id="data-unit">
    <div class="card-body">
        <h3 class="card-title">Data Unit Operasional</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr class="text-center">
                    <th style="min-width: 50px; width: 60px;">No</th>
                    <th>Nama Unit Operasional</th>
                    <th style="min-width: 150px; width: 150px;">Total Pegawai</th>
                    <th style="min-width: 150px; width: 200px;">Aksi</th>
                </tr>
                <?php if (!empty($data_unit)) : ?>
                    <?php $nomor = 1 + ($perPage * ($currentPage - 1)); ?>
                    <?php foreach ($data_unit as $unit) : ?>
                        <tr>
                            <td class="text-center"><?= $nomor++ ?></td>
                            <td><?= esc($unit->nama); ?></td>
                            <td class="text-center"><?= $unit->total_pegawai; ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('unit-operasional/' . $unit->slug) ?>" class="badge bg-warning">
                                    Edit
                                </a>
                                <a href="#" class="badge bg-danger btn-hapus" data-bs-toggle="modal" data-bs-target="#modal-danger" data-id="<?= $unit->id ?>" data-name="<?= esc($unit->nama) ?>">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else : ?>
                    <tr class="text-center">
                        <td colspan="4">Belum ada data</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-between">
        <p class="m-0 text-muted">Showing <span><?= $total > 0 ? ($perPage * ($currentPage - 1)) + 1 : 0 ?></span> to <span><?= min($perPage * $currentPage, $total) ?></span> of <span><?= $total ?></span> entries</p>
        <?= $pager; ?>
    </div>
</div>
