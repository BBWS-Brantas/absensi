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
                            <input type="hidden" name="id_lokasi_presensi" value="<?= $id_lokasi_presensi ?>">
                            <input type="hidden" name="tanggal_keluar" value="<?= $tanggal_keluar ?>">
                            <input type="hidden" name="jam_keluar" value="<?= $jam_keluar ?>">
                            <div class="mt-3">
                                <video id="camera" autoplay playsinline class="w-100" style="border-radius:4px; background:#000;"></video>
                                <canvas id="canvas" class="d-none"></canvas>
                                <img id="preview" class="d-none w-100" style="border-radius:4px;" alt="Hasil foto presensi">
                                <input type="file" name="foto" id="foto" accept="image/jpeg,image/png" class="d-none">
                                <small class="form-hint" id="camera-hint">Foto hanya dapat diambil langsung dari kamera. Posisikan diri Anda, lalu tekan "Ambil Foto".</small>
                                <div id="camera-error" class="text-danger mt-2 d-none"></div>
                            </div>
                            <button class="btn btn-secondary mt-3" type="button" id="capture-btn">Ambil Foto</button>
                            <button class="btn btn-secondary mt-3 d-none" type="button" id="retake-btn">Ulangi</button>
                            <button class="btn btn-primary mt-3 d-none" type="submit" id="submit-btn">Simpan Presensi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script language="JavaScript">
    // Ambil foto presensi langsung dari kamera (tidak dapat memilih file dari galeri/drive)
    (function () {
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const preview = document.getElementById('preview');
        const fotoInput = document.getElementById('foto');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const submitBtn = document.getElementById('submit-btn');
        const errorBox = document.getElementById('camera-error');
        let stream = null;

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = stream;
                video.classList.remove('d-none');
                captureBtn.classList.remove('d-none');
                errorBox.classList.add('d-none');
            } catch (err) {
                errorBox.textContent = 'Tidak dapat mengakses kamera: ' + err.message + '. Pastikan izin kamera diberikan dan halaman dibuka melalui HTTPS (atau localhost).';
                errorBox.classList.remove('d-none');
                captureBtn.classList.add('d-none');
            }
        }

        function stopCamera() {
            if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        }

        captureBtn.addEventListener('click', function () {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(function (blob) {
                const file = new File([blob], 'presensi.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                fotoInput.files = dt.files;
                preview.src = URL.createObjectURL(blob);
                preview.classList.remove('d-none');
                video.classList.add('d-none');
                captureBtn.classList.add('d-none');
                retakeBtn.classList.remove('d-none');
                submitBtn.classList.remove('d-none');
                stopCamera();
            }, 'image/jpeg', 0.9);
        });

        retakeBtn.addEventListener('click', function () {
            preview.classList.add('d-none');
            retakeBtn.classList.add('d-none');
            submitBtn.classList.add('d-none');
            fotoInput.value = '';
            startCamera();
        });

        startCamera();
    })();

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