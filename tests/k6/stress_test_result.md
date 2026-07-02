# Laporan Stress Test — Endpoint Login

**Target:** `https://simpati.p3tgai-kemenpu-bbwsbrantas.com`  
**Script:** `tests/k6/stress_test.js`  
**Tanggal:** 2026-07-02  

---

## Skenario

| Parameter | Nilai |
|-----------|-------|
| Executor | `ramping-vus` |
| Ramp-up | 0 → N VU selama 30 detik |
| Hold | N VU selama 1 menit |
| Ramp-down | N VU → 0 selama 15 detik |
| Total durasi | 1 menit 45 detik |
| Jeda antar iterasi | 1 detik |

**Setiap iterasi = 2 request:**
1. `GET /login` — ambil CSRF token + session cookie
2. `POST /login` — kirim kredensial

---

## Ringkasan Semua Run

| Run | Server | VU | Latensi p(95) | Error Rate | `login responded` | `login succeeded` | Hasil |
|-----|--------|----|--------------|------------|-------------------|-------------------|-------|
| 1 | absensi.denatek.my.id | 100 | ❌ 2.37s | ✅ 0% | ❌ 0% | — | ❌ GAGAL |
| 2 | absensi.denatek.my.id | 100 | ✅ 1.80s | ✅ 0% | ✅ 100% | ❌ 0% | ✅ LULUS |
| 3 | absensi.denatek.my.id | 100 | ✅ 1.79s | ✅ 0% | ✅ 100% | ❌ 0% | ✅ LULUS |
| 4 | simpati.p3tgai-* | 600 | ❌ 0ms* | ❌ 100% | ❌ 0% | ❌ 0% | ❌ GAGAL |
| 5 | simpati.p3tgai-* | 100 | ❌ N/A** | ❌ ~100% | ❌ ~4% | ❌ 0% | ❌ GAGAL |

> \* p(95)=0 karena semua request timeout sebelum sempat mendapat respons  
> \*\* Server collapse di detik ke-38, hanya 52 dari ~2.300 iterasi yang selesai sebelum EOF + timeout total

---

## Detail Per Run

### Run 1 — 100 VU (Script Awal)

| Metrik | Nilai |
|--------|-------|
| Iterasi selesai | 2.068 |
| Throughput | 39,0 req/s |
| avg response | 1.520ms |
| p(95) | **2.370ms** ❌ |
| max response | 3.160ms |
| Error rate | 0,00% |

**Masalah:** Script mengirim field `email` (bukan `login`) dan CSRF token dengan nama `_token` (bukan `csrf_test_name`). Server mengembalikan 303, namun check hanya menerima 302 — sehingga semua check gagal meski server normal.

---

### Run 2 & 3 — 100 VU (Check Diperbaiki)

| Metrik | Run 2 | Run 3 |
|--------|-------|-------|
| Iterasi selesai | 2.368 | 2.367 |
| Throughput | 44,7 req/s | 44,8 req/s |
| avg response | 1.260ms | 1.260ms |
| p(95) | **1.800ms** ✅ | **1.790ms** ✅ |
| max response | 2.280ms | 2.190ms |
| Error rate | 0,00% | 0,00% |

**Catatan:** `login succeeded` tetap 0% karena field yang dikirim masih salah (bug belum sepenuhnya diperbaiki di run ini — hanya check status code yang diperbaiki).

---

### Run 5 — 100 VU, Server Baru (simpati.p3tgai-kemenpu-bbwsbrantas.com)

| Metrik | Nilai |
|--------|-------|
| Iterasi selesai | ~52 (dari ~2.367 target) |
| Error pertama muncul | detik ke-10 (EOF saat ramp-up) |
| Server berhenti merespons | detik ke-38 (iterasi stuck di 52) |
| Jenis error | `EOF` → `dial: i/o timeout` |
| Error rate | ~100% ❌ |
| Data diterima | ~0 B setelah collapse |

**Kronologi:**
- 0–38 detik: Server masih merespons sebagian, 52 iterasi selesai dengan banyak EOF
- 38–56 detik: Server berhenti total — semua VU menggantung, 0 iterasi baru
- 56 detik+: `dial: i/o timeout` — server tidak menerima koneksi baru sama sekali

**Kesimpulan:** Server `simpati.p3tgai-kemenpu-bbwsbrantas.com` collapse pada **sekitar 30–40 VU concurrent**, jauh di bawah target 100 VU. Ini berbeda signifikan dengan `absensi.denatek.my.id` yang mampu menangani 100 VU dengan stabil.

---

### Run 4 — 600 VU, Server Baru (Script Diperbaiki + Beban Dinaikkan)

| Metrik | Nilai |
|--------|-------|
| Iterasi selesai | 600 |
| Iterasi terputus | 424 |
| Throughput | 13,2 req/s |
| avg response | 0ms (semua timeout) |
| p(95) | 0ms (semua timeout) |
| Error rate | **100%** ❌ |
| Data diterima | 0 B |

**Hasil:** Server **collapse total**. LiteSpeed menolak semua koneksi baru, seluruh request GET dan POST timeout. Server tidak merespons selama beberapa menit setelah test selesai.

---

## Root Cause: Kenapa Login Selalu Gagal (Run 1–3)

Ditemukan **dua bug** di script k6 melalui inspeksi HTML form produksi:

### Bug 1 — Field email salah nama
Form login CI4 menggunakan `name="login"` untuk input email, **bukan** `name="email"` seperti umumnya Laravel.

```html
<!-- Form produksi -->
<input type="email" name="login" placeholder="Alamat Email">
```

```js
// Script lama — SALAH
const payload = { email: EMAIL, password: PASS };

// Script baru — BENAR
const payload = { login: EMAIL, password: PASS };
```

### Bug 2 — Nama field CSRF token salah
Aplikasi menggunakan CodeIgniter 4 (bukan Laravel). Nama CSRF token CI4 adalah `csrf_test_name`, bukan `_token`.

```html
<!-- Form produksi -->
<input type="hidden" name="csrf_test_name" value="abe4c8d3...">
```

```js
// Script lama — SALAH
payload._token = token;

// Script baru — BENAR (dinamis, ambil nama dari HTML)
const csrfInput = doc.find('input[name="csrf_test_name"]');
payload[csrfInput.attr('name')] = csrfInput.attr('value');
```

---

## Perubahan Script

| File | Perubahan |
|------|-----------|
| `tests/k6/stress_test.js` | Field email: `email` → `login` |
| `tests/k6/stress_test.js` | CSRF field: `_token` → `csrf_test_name` (dinamis dari HTML) |
| `tests/k6/stress_test.js` | Status check: tambah `303` ke daftar status yang diterima |
| `tests/k6/stress_test.js` | Tambah check `login succeeded` (redirect ke non-/login) |

---

## Temuan Utama

### 1. Dua server dengan kapasitas sangat berbeda

| Server | Batas aman | Breaking point |
|--------|-----------|----------------|
| `absensi.denatek.my.id` | 100 VU (stabil, p95=1.79s) | Antara 100–600 VU |
| `simpati.p3tgai-kemenpu-bbwsbrantas.com` | < 30 VU (estimasi) | **~30–40 VU** (Run 5) |

Server `simpati` collapse lebih cepat dan lebih parah — sudah menunjukkan EOF di detik ke-10 saat masih ramp-up ke 33 VU.

### 2. Pola error berbeda mengindikasikan penyebab berbeda
- `EOF` = server menutup koneksi di tengah jalan → PHP-FPM kehabisan worker
- `dial: i/o timeout` = server tidak menerima koneksi baru sama sekali → LiteSpeed/kernel backlog penuh

Keduanya muncul secara berurutan di Run 5: server pertama menolak request, lalu berhenti menerima koneksi.

### 3. Server tidak pulih cepat setelah overload
Setelah collapse, server tidak dapat diakses selama beberapa menit. Tidak ada mekanisme auto-recovery yang cepat.

### 4. Performa baik di beban normal hanya untuk absensi.denatek.my.id
p(95) = 1.79s dengan buffer ~210ms sebelum threshold 2s. Cukup aman untuk penggunaan normal.

---

## Rekomendasi

| Prioritas | Item |
|-----------|------|
| Tinggi | Lakukan load test bertahap (150, 200, 300 VU) untuk menemukan breaking point yang tepat |
| Tinggi | Periksa konfigurasi LiteSpeed: max connections, PHP-FPM worker count, dan DB connection pool |
| Sedang | Tambahkan rate limiter di sisi server untuk login (sudah ada di CI4, pastikan dikonfigurasi dengan tepat) |
| Sedang | Implementasikan health check / auto-restart jika server crash |
| Rendah | Setelah login berhasil dikonfirmasi, tambahkan skenario post-login (check-in, dashboard) |
