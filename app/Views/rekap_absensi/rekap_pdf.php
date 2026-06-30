<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 9px; color: #000; }
        h2 { margin: 0 0 4px; font-size: 14px; }
        table.meta { margin-bottom: 10px; font-size: 10px; }
        table.meta td { padding: 1px 6px 1px 0; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #333; padding: 3px 4px; text-align: center; vertical-align: middle; }
        table.data th { background: #f0f0f0; }
        td.left { text-align: left; }
    </style>
</head>
<body>
    <h2>Laporan Presensi Pegawai</h2>
    <table class="meta">
        <tr><td><strong>Bulan</strong></td><td>: <?= esc($nama_bulan) ?></td></tr>
        <tr><td><strong>Tahun</strong></td><td>: <?= esc($filter_tahun) ?></td></tr>
        <tr><td><strong>Data per</strong></td><td>: <?= date('d F Y') ?></td></tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama TPM</th>
                <th>Unit Operasional</th>
                <th>Jabatan</th>
                <th>Total Kehadiran</th>
                <th>Total Ijin/Sakit/Cuti</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)) : ?>
                <?php foreach ($rows as $r) : ?>
                    <tr>
                        <td><?= $r['no'] ?></td>
                        <td class="left"><?= esc($r['nama']) ?></td>
                        <td class="left"><?= esc($r['unit_operasional']) ?></td>
                        <td class="left"><?= esc($r['jabatan']) ?></td>
                        <td><?= $r['total_kehadiran'] ?></td>
                        <td><?= $r['total_ijin_sakit_cuti'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6">Tidak ada data presensi.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
