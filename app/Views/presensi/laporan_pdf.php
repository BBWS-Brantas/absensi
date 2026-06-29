<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            font-family: "DejaVu Sans", sans-serif;
        }

        body {
            font-size: 9px;
            color: #000;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 14px;
        }

        table.meta {
            margin-bottom: 10px;
            font-size: 10px;
        }

        table.meta td {
            padding: 1px 6px 1px 0;
            vertical-align: top;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #333;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
        }

        table.data th {
            background: #f0f0f0;
        }

        td.left {
            text-align: left;
        }
    </style>
</head>

<body>
    <h2><?= esc($judul) ?></h2>
    <table class="meta">
        <?php foreach ($meta as $label => $value) : ?>
            <tr>
                <td><strong><?= esc($label) ?></strong></td>
                <td>: <?= esc($value) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama TPM</th>
                <th>Unit Operasional</th>
                <th>Jabatan</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Koordinat Masuk</th>
                <th>Jam Pulang</th>
                <th>Koordinat Pulang</th>
                <th>Total Jam Kerja</th>
                <th>Total Keterlambatan</th>
                <th>Keterangan Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rows)) : ?>
                <?php foreach ($rows as $r) : ?>
                    <tr>
                        <td><?= $r['no'] ?></td>
                        <td class="left"><?= esc($r['nama']) ?></td>
                        <td class="left"><?= esc($r['nama_unit']) ?></td>
                        <td class="left"><?= esc($r['jabatan']) ?></td>
                        <td><?= esc($r['tanggal']) ?></td>
                        <td><?= esc($r['jam_masuk']) ?></td>
                        <td><?= esc($r['koordinat_masuk']) ?></td>
                        <td><?= esc($r['jam_keluar']) ?></td>
                        <td><?= esc($r['koordinat_keluar']) ?></td>
                        <td><?= esc($r['total_jam_kerja']) ?></td>
                        <td><?= esc($r['total_keterlambatan']) ?></td>
                        <td class="left"><?= esc($r['keterangan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="12">Tidak ada data presensi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
