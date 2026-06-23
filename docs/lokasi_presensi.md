## Fitur Lokasi Presensi (Multi-Lokasi per Pegawai)

> **Status: SUDAH DIIMPLEMENTASI (2026-06-22).** Tabel pivot `lokasi_presensi_pegawai`, kolom `lokasi_presensi.id_unit` & `presensi.id_lokasi_presensi` sudah dibuat; kolom lama `pegawai.id_lokasi_presensi` sudah dihapus. Untuk DB hasil import jalankan [`../../add-lokasi-multi.sql`](../../add-lokasi-multi.sql) (butuh `add-unit-operasional.sql` lebih dulu). Catatan penamaan: kolom FK memakai **`id_lokasi_presensi`** (mengikuti konvensi codebase), bukan `id_lokasi` seperti draft awal.
>
> **Update (2026-06-23): lokasi presensi keluar bisa dipilih sendiri.** Pegawai memilih lokasi saat presensi keluar yang **boleh berbeda** dari lokasi masuk. Ditambah kolom `presensi.id_lokasi_keluar` (lokasi masuk = `id_lokasi_presensi`, lokasi keluar = `id_lokasi_keluar`). Untuk DB hasil import jalankan [`../../add-lokasi-keluar.sql`](../../add-lokasi-keluar.sql).
>
> **Update (2026-06-23): assignment lokasi pakai soft delete.** Saat lokasi di-unselect di edit pegawai, assignment ditandai `active=0` (baris TIDAK dihapus) agar riwayat assignment tersimpan & bisa diaktifkan kembali. Ditambah kolom `lokasi_presensi_pegawai.active`. Untuk DB hasil import jalankan [`../../add-lokasi-pegawai-active.sql`](../../add-lokasi-pegawai-active.sql). Catatan: data presensi historis tidak terpengaruh oleh hard delete sekalipun (presensi simpan id lokasi mandiri); soft delete murni untuk riwayat assignment + reaktivasi.

### Existing fitur
- Saat ini 1 pegawai hanya terikat ke 1 lokasi presensi (`pegawai.id_lokasi_presensi`, relasi 1:1, NOT NULL).
- Lokasi presensi adalah **master data** (nama, alamat, tipe, latitude, longitude, radius, zona_waktu, jam_masuk, jam_pulang) yang hanya bisa di-set oleh admin/head.
- Saat presensi (masuk/keluar), sistem memvalidasi GPS pegawai terhadap **satu** lokasi yang ter-assign (geofence haversine: `meter > radius` ditolak). Zona waktu, jam_masuk, dan jam_pulang juga diambil dari lokasi tunggal itu.

### Request fitur
- Pegawai bisa absen di **lebih dari 1 lokasi**.
- Pegawai bisa memilih sendiri di lokasi mana dia absen saat presensi.

### Definisi "basepoint"
**Basepoint adalah properti milik LOKASI, bukan milik pegawai.** Setiap lokasi presensi punya **satu** basepoint = titik koordinat pusat geofence (`latitude`, `longitude`) lokasi itu. Contoh:
- Lokasi A → basepoint `(1232, -123123)` + radius A
- Lokasi B → basepoint `(3321312, -123123)` + radius B

Jadi basepoint = kolom `latitude`/`longitude` yang **sudah ada** di tabel `lokasi_presensi`. Tidak ada konsep "1 basepoint per pegawai" / "lokasi default per pegawai". Pegawai hanya di-assign ke beberapa lokasi; tiap lokasi membawa basepoint-nya sendiri.

### Aturan bisnis
- Admin bisa menambahkan **beberapa** lokasi presensi untuk masing-masing pegawai.
- **Lokasi presensi di-scope per unit:** setiap lokasi dimiliki tepat satu unit. Admin OP I hanya melihat & assign lokasi OP I, admin OP II hanya lokasi OP II, dst. Tidak ada lokasi yang dipakai lintas unit. Head melihat semua lokasi.
- Admin hanya bisa mengelola pegawai **di unit/wilayahnya sendiri** (mengikuti scoping unit operasional yang sudah ada — lihat `current_unit_id()`). Head global.
- Setiap pegawai punya **minimal 1 lokasi**.
- Saat presensi, pegawai memilih salah satu lokasi yang ter-assign untuknya; geofence divalidasi terhadap basepoint (lat/lon) + radius **lokasi yang dipilih** itu.

### Koreksi atas rencana teknis awal
> Rencana awal: "simpan `user_id` di tabel `lokasi_presensi`". **Tidak disarankan.**
`lokasi_presensi` adalah master data yang dipakai bersama banyak pegawai. Menaruh `user_id` di sana memaksa duplikasi baris (1 kantor dipakai 10 orang → 10 baris kantor yang sama) dan membuat edit radius/jam harus diulang ke banyak baris. Relasi yang benar untuk "banyak pegawai ↔ banyak lokasi" adalah **tabel pivot (junction)**.

Catatan: gunakan **`id_pegawai`**, bukan `user_id`. Di codebase, entitas karyawan adalah `pegawai`; `users` adalah tabel auth (Myth/Auth) yang terhubung ke `pegawai` lewat `users.id_pegawai`. Semua relasi domain memakai `id_pegawai`.

### Rencana teknis (revisi)

**1. Tabel pivot baru: `lokasi_presensi_pegawai`** (murni many-to-many, tanpa kolom basepoint)
```
id              INT PK AI
id_pegawai      INT  FK -> pegawai.id         (CASCADE)
id_lokasi       INT  FK -> lokasi_presensi.id (CASCADE)
created_at / updated_at / deleted_at
UNIQUE (id_pegawai, id_lokasi)   -- cegah duplikat assignment
```
- `lokasi_presensi` **tetap murni master data** — tidak ditambah kolom `user_id`/`id_pegawai`. Basepoint (lat/lon) sudah ada di tabel ini, tidak perlu kolom baru.
- Tidak ada flag `is_basepoint` di pivot: basepoint adalah milik lokasi, bukan relasi pegawai-lokasi.

**2. Hapus `pegawai.id_lokasi_presensi`**
- Kolom 1:1 lama dihapus. Foreign key `pegawai_id_lokasi_presensi_foreign` harus di-drop dulu sebelum kolomnya.

**2b. Tambah `id_unit` pada `lokasi_presensi` (scoping lokasi per unit)**
- Tambah kolom **`id_unit`** (FK -> `unit_operasional.id`, nullable) pada `lokasi_presensi`. **Setiap lokasi dimiliki oleh tepat satu unit.**
- Tujuan: admin OP I hanya bisa melihat & assign lokasi milik OP I, admin OP II hanya lokasi OP II, dst. Tidak ada lokasi yang dipakai lintas unit. Head melihat semua lokasi.
- Mengikuti pola scoping unit yang sudah ada (`current_unit_id()`, sama seperti `pegawai.id_unit`).

**3. Catat lokasi di setiap transaksi presensi**
- Tambah kolom **`id_lokasi_presensi`** (lokasi masuk) pada tabel `presensi`. Sejak 2026-06-23 ditambah juga **`id_lokasi_keluar`** (lokasi keluar — boleh berbeda dari lokasi masuk).
- Saat ini `presensi` tidak menyimpan lokasi sama sekali; dengan multi-lokasi ini wajib agar laporan tahu pegawai absen di mana.

**4. Perubahan alur presensi (Home / Presensi controller)**
- **Presensi cukup SEKALI sehari.** Pegawai memilih **satu** lokasi untuk presensi hari itu; satu kali check-in dianggap sudah hadir. (Bukan wajib presensi di semua lokasi.)
- Layar Home: lokasi yang ter-assign ditampilkan sebagai **dropdown `<select>`** di kartu Presensi Masuk; pegawai pilih satu lalu tekan tombol Masuk. Setelah masuk, kartu berubah jadi status "telah presensi masuk di {lokasi}".
- `Presensi::presensiMasuk`: validasi geofence terhadap basepoint (latitude/longitude) + radius **lokasi yang dipilih** (di-resolve server-side dari `id_lokasi_presensi`, diverifikasi `isAssigned`). Simpan `id_lokasi_presensi` ke baris presensi.
- `Presensi::presensiKeluar`: pegawai **memilih lokasi presensi keluar** lewat dropdown di kartu Presensi Keluar (default = lokasi masuk, tapi **boleh diganti**). Geofence divalidasi terhadap lokasi yang dipilih (resolve server-side dari `id_lokasi_presensi` yang disubmit, verifikasi `isAssigned`). Lokasi keluar disimpan ke kolom terpisah **`presensi.id_lokasi_keluar`** (lokasi masuk tetap di `id_lokasi_presensi`).
- jam_masuk_kantor (untuk hitung keterlambatan) ikut lokasi tempat check-in karena laporan join via `presensi.id_lokasi_presensi`. Gate "belum waktunya keluar" (jam_pulang) mengikuti **lokasi masuk** (jadwal hari kerja), bukan lokasi keluar.

**5. Ganti lookup berbasis nama → berbasis id**
- `Home::index`, `Home::getWaktu`, `Presensi` saat ini meng-resolve lokasi via `getWhere(['nama_lokasi' => $user_profile->lokasi_presensi])` (lookup by nama — rapuh & tidak unik). Dengan multi-lokasi ini harus diganti ke lookup **by id** dari lokasi yang dipilih pegawai.
- `UsersModel::getUserInfo` dan `PegawaiModel::getPegawai` melakukan `join lokasi_presensi ON lokasi_presensi.id = pegawai.id_lokasi_presensi` dan filter `lokasi_presensi.nama_lokasi`. Join 1:1 ini harus dilepas; ganti menjadi:
  - agregasi daftar lokasi (mis. `GROUP_CONCAT(nama_lokasi)` via join ke pivot) untuk kolom "Lokasi" di list pegawai, dan
  - filter "lokasi-presensi" di pencarian pegawai diarahkan ke pivot (`EXISTS (SELECT 1 FROM lokasi_presensi_pegawai ...)`).

**6. Scoping lokasi per unit (CRUD lokasi + assign ke pegawai)**
- **Halaman Lokasi Presensi** (`LokasiPresensi::index`/`add`/`store`/`update`): daftar lokasi difilter per unit — admin hanya melihat & mengelola lokasi unitnya; head melihat semua. `LokasiPresensiModel::getLokasi` menerima param `$id_unit` opsional (null = head, tanpa filter), dipanggil dengan `current_unit_id()`. Pada `store`/`update`, untuk admin `id_unit` di-**force** ke unit admin (nilai submit diabaikan), head bisa memilih unit — sama persis pola `Pegawai` store/update.
- **Cross-unit guard** pada `LokasiPresensi::edit`/`update`/`delete`/`detail`: tolak (404) jika admin mengakses lokasi di luar unitnya (mirror `Pegawai::pastikanDalamUnit()`).

**7. UI Admin (assign lokasi ke pegawai) — multi-select lokasi yang mengikuti unit terpilih**
Prinsip: pegawai punya **satu** unit, dan setiap lokasi milik satu unit → lokasi yang bisa dipilih **selalu mengikuti unit pegawai yang sedang dipilih**. Satu mekanisme, dua peran:

- **Admin:** field Unit ter-**lock & auto-terpilih ke unit admin sendiri** (sudah seperti sekarang — render disabled + hidden input). Multi-select lokasi langsung terisi lokasi unit itu saat halaman dibuka. Admin tidak perlu memilih unit.
- **Head:** field Unit editable. **Pilih unit dulu**, lalu multi-select lokasi memuat **hanya lokasi unit tsb**. Mengganti unit me-**reset/reload** pilihan lokasi (pilihan lama milik unit lama dibuang).

Implementasi:
- **AJAX endpoint baru:** `GET /lokasi-presensi/by-unit/(:num)` → `LokasiPresensi::byUnit($id_unit)` mengembalikan JSON daftar lokasi untuk unit itu. Gated `role:admin,head`; untuk admin, server **mengabaikan** `$id_unit` request dan memakai `current_unit_id()` (admin tidak boleh fetch lokasi unit lain).
- Saat `change` pada dropdown Unit (head), panggil endpoint, isi ulang multi-select. Untuk admin, panggil sekali saat load dengan unitnya.
- **Edit pegawai:** muat lokasi milik unit pegawai saat ini, lalu pre-check lokasi yang sudah ter-assign. Jika head mengganti unit, lokasi lintas-unit yang sebelumnya terpilih ikut ter-clear.

Guard & validasi (server-side, jangan andalkan filter dropdown):
- Setiap `id_lokasi` yang disubmit **harus** berada di `id_unit` pegawai yang dipilih — kalau tidak, tolak.
- Untuk admin, `id_unit` di-force ke unitnya; head bebas memilih unit.
- Minimal 1 lokasi dipilih.
- **Sinkronisasi pivot saat update (soft delete, bukan hard delete):** pada `Pegawai::update`, `syncLokasi` menandai lokasi yang di-uncheck sebagai `active=0` (baris assignment **tidak dihapus** → riwayat tetap tersimpan) dan meng-`active=1` lokasi yang dipilih (insert bila belum pernah ada, **restore** bila sebelumnya nonaktif — sehingga UNIQUE `(id_pegawai, id_lokasi_presensi)` tetap aman tanpa duplikat). (Pakai transaksi DB agar atomik.) `getLokasiByPegawai`/`getIdLokasiByPegawai`/`isAssigned` serta subquery daftar/filter lokasi di `PegawaiModel` semuanya difilter `active = 1` agar lokasi nonaktif tidak bocor ke presensi/laporan.

### Migrasi data (DB hasil import `o-present.sql`)
Migrasi TIDAK dijalankan ulang di atas DB import (lihat CLAUDE.md). Siapkan script SQL one-time, contoh `../../add-lokasi-multi.sql`, yang:
1. `CREATE TABLE lokasi_presensi_pegawai (...)` + unique index.
2. `ALTER TABLE lokasi_presensi ADD COLUMN id_unit INT NULL ...` (+ FK ke `unit_operasional`). Backfill `id_unit` lokasi lama sesuai unit pemiliknya (mis. dari unit pegawai yang memakainya, atau diisi manual oleh head).
3. Backfill pivot: `INSERT INTO lokasi_presensi_pegawai (id_pegawai, id_lokasi) SELECT id, id_lokasi_presensi FROM pegawai;` (lokasi tunggal lama jadi assignment pertama).
4. `ALTER TABLE presensi ADD COLUMN id_lokasi INT NULL ...` (+ FK; nullable agar baris lama valid).
5. Setelah verifikasi, drop FK lalu kolom `pegawai.id_lokasi_presensi`.
Untuk fresh install: migration baru + update `PegawaiSeeder`/`LokasiSeeder` (set `id_unit`) + seeder pivot.

### Dampak file (perlu disentuh)
- Model: `PegawaiModel` (join + filter + allowedFields), `LokasiPresensiModel` (allowedFields + `id_unit`, param `$id_unit` di `getLokasi`), `UsersModel::getUserInfo`, `PresensiModel`, + model baru `LokasiPresensiPegawaiModel`.
- Controller: `Home`, `Presensi` (masuk/keluar + simpan + laporan/Excel/PDF), `Pegawai` (store/update/detail/edit + guard unit + multi-select lokasi), `LokasiPresensi` (scoping unit di index/add/store/update + cross-unit guard di edit/update/delete/detail, force `id_unit` untuk admin, + method `byUnit` untuk AJAX).
- Routes: tambah `GET /lokasi-presensi/by-unit/(:num)` → `LokasiPresensi::byUnit/$1` (`role:admin,head`). **Letakkan SEBELUM** route wildcard `/lokasi-presensi/(:any)` (`detail`) agar tidak ketangkap duplikat.
- JS: handler `change` pada select Unit di form tambah/edit pegawai untuk reload multi-select lokasi.
- View: form tambah/edit pegawai (multi-select lokasi), `home/index` (pemilih lokasi), `presensi/presensi_masuk`, `presensi_keluar`, list & laporan pegawai/presensi (kolom lokasi).
- DB: migration + script `add-lokasi-multi.sql`, seeder.

### Edge case & pertanyaan terbuka
- Pegawai tanpa lokasi sama sekali → blok presensi dengan pesan jelas.
- Saat lokasi dihapus dari master, assignment pegawai ke lokasi itu ikut hilang (CASCADE).
- Laporan/rekap: apakah perlu difilter/digroup per lokasi? (disarankan ya, karena `id_lokasi` kini tersimpan di `presensi`).
- **[Sudah diputuskan]** Tidak ada lokasi default per pegawai. Home menampilkan **semua** lokasi yang ter-assign; pegawai memilih saat akan presensi. Jika hanya 1 lokasi, auto-pilih.
