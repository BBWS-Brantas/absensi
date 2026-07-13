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

        th.sub {
            font-style: italic;
            font-weight: normal;
            font-size: 8px;
        }

        img.foto {
            width: 4cm;
            height: 6cm;
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
                <th rowspan="2">No</th>
                <th rowspan="2">Nama TPM</th>
                <th rowspan="2">Unit Operasional</th>
                <th rowspan="2">Jabatan</th>
                <th rowspan="2">Tanggal</th>
                <th>Foto Masuk</th>
                <th>Foto Keluar</th>
            </tr>
            <tr>
                <th class="sub">Ukuran Foto 4 x 6</th>
                <th class="sub">Ukuran Foto 4 x 6</th>
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
                        <td><?= $r['foto_masuk'] ? '<img class="foto" src="' . $r['foto_masuk'] . '">' : '-' ?></td>
                        <td><?= $r['foto_keluar'] ? '<img class="foto" src="' . $r['foto_keluar'] . '">' : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7">Tidak ada data presensi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
