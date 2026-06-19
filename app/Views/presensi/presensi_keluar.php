<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <form action="<?= base_url('/presensi-keluar/simpan') ?>" method="post" enctype="multipart/form-data">
    <div class="container-xl">
        <div class="row g-3">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <div id="map" style="opacity: none; position: absolute; top: -9999px;"></div>
                        <div class="mb-3">
                            <label class="form-label required">Keterangan Kegiatan</label>
                            <textarea class="form-control" name="keterangan" rows="4" style="resize: none;" required></textarea>
                            <small class="form-hint">Maksimal 200 karakter.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card text-center">
                    <div class="card-body m-auto">
                        <div id="my_result"></div>
                        <div class="mt-3"><?= date('d F Y', strtotime($tanggal_keluar)) . ' - ' . $jam_keluar ?></div>

                            <?= csrf_field() ?>
                            <input type="hidden" name="username" value="<?= $user_profile->username ?>">
                            <input type="hidden" name="id_presensi" value="<?= $data_presensi_masuk->id ?>">
                            <input type="hidden" name="tanggal_keluar" value="<?= $tanggal_keluar ?>">
                            <input type="hidden" name="jam_keluar" value="<?= $jam_keluar ?>">
                            <div class="mt-3">
                                <input type="file" name="foto" accept="image/jpeg,image/png" capture="environment" class="form-control" required>
                                <small class="form-hint">Ambil foto dari kamera atau pilih dari galeri. Format JPG/JPEG/PNG, maksimal 15 MB.</small>
                            </div>
                            <button class="btn btn-primary mt-3" type="submit" id="ambil-foto">Ambil Gambar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script language="JavaScript">
    let latitude_kantor = <?= $latitude_kantor ?>;
    let longitude_kantor = <?= $longitude_kantor ?>;

    let latitude_kantorPusat = <?= $latitude_kantor ?>;
    let longitude_kantorPusat = <?= $longitude_kantor ?>;

    let latitude_pegawai = <?= $latitude_pegawai ?>;
    let longitude_pegawai = <?= $longitude_pegawai ?>;

    let radius = <?= $radius ?>;

    var map = L.map('map').setView([latitude_kantor, longitude_kantor], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = L.marker([latitude_pegawai, longitude_pegawai]).addTo(map).bindPopup("Posisi Anda saat ini.");;

    var circle = L.circle([latitude_kantor, longitude_kantor], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.5,
        radius: radius
    }).addTo(map).bindPopup("Radius Presensi");
</script>
<?= $this->endSection() ?>