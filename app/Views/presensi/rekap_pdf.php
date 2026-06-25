<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            font-family: "DejaVu Sans", sans-serif;
        }

        body {
            font-size: 10px;
            color: #000;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        table.meta {
            margin-bottom: 10px;
            font-size: 11px;
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
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        table.data th {
            background: #f0f0f0;
        }

        img.foto {
            width: 70px;
            height: auto;
        }

        td.ket {
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Rekap Presensi Pegawai</h2>
    <table class="meta">
        <tr>
            <td><strong>Nama</strong></td>
            <td>: <?= esc($data_pegawai->nama) ?></td>
            <td style="width: 40px;"></td>
            <td><strong>Tanggal Awal</strong></td>
            <td>: <?= esc($tanggal_awal) ?></td>
        </tr>
        <tr>
            <td><strong>NIP</strong></td>
            <td>: <?= esc($data_pegawai->nip) ?></td>
            <td></td>
            <td><strong>Tanggal Akhir</strong></td>
            <td>: <?= esc($tanggal_akhir) ?></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Unit Operasional</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Foto Masuk</th>
                <th>Jam Pulang</th>
                <th>Foto Pulang</th>
                <th>Total Jam Kerja</th>
                <th>Total Keterlambatan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rows)) : ?>
                <?php foreach ($rows as $r) : ?>
                    <tr>
                        <td><?= $r['no'] ?></td>
                        <td><?= esc($r['nama_unit']) ?></td>
                        <td><?= esc($r['tanggal']) ?></td>
                        <td><?= esc($r['jam_masuk']) ?></td>
                        <td><?= $r['foto_masuk'] ? '<img class="foto" src="' . $r['foto_masuk'] . '">' : '-' ?></td>
                        <td><?= esc($r['jam_keluar']) ?></td>
                        <td><?= $r['foto_keluar'] ? '<img class="foto" src="' . $r['foto_keluar'] . '">' : '-' ?></td>
                        <td><?= esc($r['total_jam_kerja']) ?></td>
                        <td><?= esc($r['total_keterlambatan']) ?></td>
                        <td class="ket"><?= esc($r['keterangan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="10">Tidak ada data presensi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
