<?= $this->extend('templates/index') ?>

<?= $this->section('pageBody') ?>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="row g-3">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card text-center">
                    <div class="card-body m-auto">
                        <div id="my_result"></div>
                        <div class="mt-3"><?= date('d F Y', strtotime($tanggal_masuk)) . ' - ' . $jam_masuk ?></div>
                        <form action="<?= base_url('/presensi-masuk/simpan') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="username" value="<?= $user_profile->username ?>">
                            <input type="hidden" name="id_pegawai" value="<?= $user_profile->id_pegawai ?>">
                            <input type="hidden" name="id_lokasi_presensi" value="<?= $id_lokasi_presensi ?>">
                            <input type="hidden" name="tanggal_masuk" value="<?= $tanggal_masuk ?>">
                            <input type="hidden" name="jam_masuk" value="<?= $jam_masuk ?>">
                            <input type="hidden" name="lat_masuk" value="<?= $latitude_pegawai ?>">
                            <input type="hidden" name="lng_masuk" value="<?= $longitude_pegawai ?>">
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

        async function drawWatermark(ctx, w, h, lat, lon) {
            // Indonesian date: "24 Juni 2026"
            const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const now = new Date();
            const dateText = now.getDate() + ' ' + bulan[now.getMonth()] + ' ' + now.getFullYear();

            // Coordinates: -8,0599S +111,8857E style
            const latAbs = Math.abs(lat).toFixed(4).replace('.', ',');
            const lonVal = lon.toFixed(4).replace('.', ',');
            const coordText = (lat < 0 ? '-' : '') + latAbs + (lat < 0 ? 'S' : 'N') + '  ' +
                              (lon >= 0 ? '+' : '') + lonVal + (lon >= 0 ? 'E' : 'W');

            // Reverse geocode address via Nominatim
            const addrLines = [];
            try {
                const resp = await fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lon + '&format=json&accept-language=id');
                const data = await resp.json();
                const a = data.address || {};
                const road    = a.road || a.pedestrian || a.footway || '';
                const village = a.suburb;
                const district = a.district;
                const county  = a.county || '';
                const city    = a.city || a.town || a.municipality || '';
                const province = 'Jawa Timur';
                [road, village, district, county, city, province].forEach(function(v) { if (v) addrLines.push(v); });
            } catch (e) {}

            // Logo — top-right
            const logo = new Image();
            logo.src = '<?= base_url("assets/img/company/new-logo.png") ?>';
            await new Promise(function(r) { logo.onload = r; logo.onerror = r; });
            if (logo.naturalWidth > 0) {
                const lh = Math.round(h * 0.09);
                const lp = Math.round(w * 0.02);
                const r  = lh / 2;
                const cx = w - r - lp;
                const cy = lp + r;
                ctx.save();
                ctx.beginPath();
                ctx.arc(cx, cy, r, 0, Math.PI * 2);
                ctx.closePath();
                ctx.clip();
                ctx.drawImage(logo, cx - r, cy - r, lh, lh);
                ctx.restore();
            }

            // Text — bottom-right, right-aligned, white with drop shadow
            const textLines = [dateText, coordText].concat(addrLines).concat(['P3-TGAI TAHAP I']);
            const fontSize = Math.max(14, Math.round(w * 0.028));
            const lineH = fontSize * 1.6;
            const pad = Math.round(w * 0.02);
            ctx.font = 'bold ' + fontSize + 'px Arial';
            ctx.textAlign = 'right';
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
            ctx.shadowBlur = 4;
            ctx.shadowOffsetX = 1;
            ctx.shadowOffsetY = 1;
            const startY = h - textLines.length * lineH - pad;
            textLines.forEach(function(line, i) {
                ctx.fillText(line, w - pad, startY + (i + 1) * lineH - fontSize * 0.3);
            });
            ctx.textAlign = 'left';
        }

        captureBtn.addEventListener('click', async function () {
            captureBtn.disabled = true;
            captureBtn.textContent = 'Memproses...';

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            await drawWatermark(ctx, canvas.width, canvas.height, latitude_pegawai, longitude_pegawai);

            canvas.toBlob(function (blob) {
                const file = new File([blob], 'presensi.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                fotoInput.files = dt.files;
                preview.src = URL.createObjectURL(blob);
                preview.classList.remove('d-none');
                video.classList.add('d-none');
                captureBtn.classList.add('d-none');
                captureBtn.disabled = false;
                captureBtn.textContent = 'Ambil Foto';
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