# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**O-Present** — a geolocation-based employee attendance ("absensi/presensi") web app built on **CodeIgniter 4** with **Myth/Auth** for authentication. The UI and all domain language are in **Indonesian**.

This directory (`ABSENSI/o-present/`) is the application root — run all commands from here. A full DB dump for bootstrapping lives two levels up at the repo root (`../../o-present.sql`).

## Running with Docker (recommended — nothing installed on the host)

The whole stack runs in containers; PHP, Composer, and MySQL are **not** required on the host.

- `Dockerfile` — PHP 8.2 CLI + the extensions the app needs (`intl`, `mysqli`, `gd`, `zip`) + a bundled Composer. Its `CMD` runs `php spark serve --host 0.0.0.0 --port 8080`.
- `docker-compose.yml` — two services:
  - **app** — built from the Dockerfile, source mounted live at `./:/app`, published on `http://localhost:8080`.
  - **db** — `mysql:8.0`, root with empty password (matches `.env`), creates the `o-present` schema, and auto-imports `../../o-present.sql` on first start (only while the `db_data` volume is empty). Also published on host `:3306` for GUI clients.
- `.env` — copy of `env` with `database.default.hostname = db` (the compose service name, reachable over the container network — **not** `127.0.0.1`).

```bash
docker compose up -d --build     # start the full stack (build app image first time)
docker compose logs -f app       # tail app logs
docker compose down              # stop; keeps DB data in the db_data volume
docker compose down -v           # stop AND wipe DB (next `up` re-imports o-present.sql)

docker compose exec app composer <cmd>     # run composer inside the app container
docker compose exec app php spark <cmd>    # run spark inside the app container
```

**Editing code does NOT require a rebuild or restart.** Source is volume-mounted and `spark serve` re-reads PHP per request (no OPcache), so changes to controllers/models/views/routes/`.env` apply on the next browser refresh. Rebuild (`up -d --build`) only when the `Dockerfile` changes (e.g. a new PHP extension); after editing `composer.json`, run `docker compose exec app composer install`.

Because the dump already contains the full schema + data, **do not run migrations on top of an imported DB.** Use migrations/seeders only on a fresh empty database.

## Commands (when PHP/Composer are available, in Docker or on host)

```bash
composer install                 # install dependencies (no vendor/ is committed)
php spark serve                  # dev server at http://localhost:8080

php spark migrate                # run migrations (auth tables + o-present tables)
php spark db:seed UsersSeeder    # seed; also: JabatanSeeder, LokasiSeeder, PegawaiSeeder, AuthGroups*Seeder
php spark migrate:refresh --all  # drop + re-migrate everything

composer test                    # run full PHPUnit suite (alias for `vendor/bin/phpunit`)
vendor/bin/phpunit --filter MethodName     # run a single test
vendor/bin/phpunit tests/path/SomeTest.php # run one test file
```

For a host (non-Docker) run, copy `env` → `.env` and set `database.default.hostname = 127.0.0.1`. The MySQL database is named `o-present` (note the hyphen — needs backticks in raw SQL).

## Architecture

Standard CI4 MVC. Request flow: `Routes.php` → role filter → Controller → Model (query builder) → View.

- **Routing & access control** — All routes are explicitly declared in `app/Config/Routes.php` (no auto-routing) and gated by a `role:...` filter argument. There are **three roles**: `admin` (full access), `head` (approves absence requests, manages master data), and `pegawai` (employee — does check-in/out, views own records). The global `login` filter (in `app/Config/Filters.php`) protects everything; per-route `role:` filters layer on top. See **Roles & accounts** below for the per-role capability matrix. (The `role` alias maps to `Myth\Auth\Filters\RoleFilter`; the custom `app/Filters/RoleFilter.php` is unused dead code.)

- **Auth** — Provided by the `myth/auth` package. Use its helpers (`user_id()`, `logged_in()`, the `authenticate`/`authorize` services). Groups/permissions are seeded via `AuthGroups*Seeder` and `AuthPermissions*Seeder`. App-specific user data is joined through a custom `id_pegawai` column linking `users` → `pegawai`.

- **Domain models** (`app/Models/`) — each maps to a table; the Indonesian names matter:
  - `pegawai` = employees, `jabatan` = job positions, `lokasi_presensi` = attendance locations (each has GPS coords + radius + office start time), `presensi` = check-in/out records (photo + timestamp), `ketidakhadiran` = absence requests (with approval status + uploaded `surat_keterangan` letter).
  - Models commonly set up `$this->db` / `$this->builder` in the constructor and return paginated result sets directly (see `PresensiModel::getDataPresensi`), each with its own pager segment key (e.g. `page_rekap`, `page_harian`, `page_bulanan`) so multiple paginators coexist on one page.

- **Geofencing** — Check-in/out (`Presensi::presensiMasuk` / `presensiKeluar`) validates the employee's submitted lat/long against the assigned location using a haversine distance calc; if `meter > radius` it rejects with a flashdata error. Timezone (`zona_waktu`), date, and time all come from the client form.

- **Photo capture** — Check-in/out stores a webcam image (`image-cam` POST field, base64) as `foto_masuk` / `foto_keluar`. Absence letters upload to `public/assets/file/surat_keterangan_ketidakhadiran/`.

- **Excel export** — Report/list controllers have `*Excel` methods (routed as POST) that build spreadsheets with **PhpOffice\PhpSpreadsheet** and stream an `.xlsx` download. This is the standard pattern for every "laporan"/"rekap" screen.

- **Views** (`app/Views/`) — server-rendered PHP templates using the **Tabler** admin theme (assets under `public/assets/`). Organized by feature folder (`presensi/`, `data_pegawai/`, `ketidakhadiran/`, etc.) with shared `partials/` and `templates/`.

## Roles & accounts

Three Myth/Auth groups (`auth_groups`), assigned via the `auth_groups_users` join table — there is no role column on `users`. The shared navbar ([app/Views/partials/navbar.php](app/Views/partials/navbar.php)) shows/hides menu items with `in_groups()` checks (note: written as a mix of `!in_groups('head')` = "admin+pegawai" and positive checks). There is no single switching dashboard — two distinct landing pages exist: `/` (`Home::index`, employee check-in screen) and `/admin` (`Admin::index`, org-wide stats).

| Feature | pegawai | admin | head |
|---------|:---:|:---:|:---:|
| Home / check-in (`/`) | ✅ | ✅ | ❌ |
| Admin dashboard + Master Data + Laporan (`/admin`, `jabatan`, `lokasi-presensi`, `data-pegawai`, `laporan-*`) | ❌ | ✅ | ✅ |
| Rekap Presensi (own recap) | ✅ | ✅ | ❌ |
| Ketidakhadiran (submit leave) | ✅ | ✅ | ❌ |
| Kelola Ketidakhadiran (approve leave) | ❌ | ❌ | ✅ |

In short: **pegawai** = self-service, **head** = manager (stats/master-data/reports/approvals, no check-in), **admin** = everything except leave approval.

After login everyone is redirected to `/` (`$landingRoute` in [app/Config/Auth.php](app/Config/Auth.php), and [AuthController::attemptLogin](app/Controllers/AuthController.php)). That works for admin/pegawai, but `/` is `role:admin,pegawai`, so a **head** user gets bounced and must reach `/admin`. The custom [app/Filters/RoleFilter.php](app/Filters/RoleFilter.php) has "redirect head → /admin" logic for exactly this, but it is **dead code**: [app/Config/Filters.php](app/Config/Filters.php) aliases `role` to `Myth\Auth\Filters\RoleFilter`, not the custom one.

### Existing accounts

From the imported `o-present.sql` (passwords are unknown bcrypt hashes — to use one, set a known password, e.g. `php spark db:seed UsersSeeder`, or update `users.password_hash` directly):

| Role | Username | Email | Employee (`pegawai`) |
|------|----------|-------|----------------------|
| admin | `tamanindah` | tamani@present.com | Tamani Indah Permata |
| head | `jayaputra` | jaya@present.com | Jaya Wahyudi Putra |
| pegawai | `choland` | choland@present.com | Christoper Holand |
| pegawai | `asepTPM` | dsde194@gmail.com | Asep |

## Conventions

- Domain identifiers, comments, controller methods, and flashdata messages are in **Indonesian** — match this when adding features (e.g. a new employee field stays in Indonesian, not English).
- User feedback uses `session()->setFlashdata('gagal'|'warning'|'sukses', ...)` then redirect.
- Migrations are split: `*_create_auth_tables` (Myth/Auth) and `2024-02-02-091537_create_opresent_tables` (the six app tables). Add new schema via a new timestamped migration; don't edit existing ones once applied.

## Gotchas

- **Kint config / framework version** — `composer.lock` resolves `codeigniter4/framework` to v4.7.2, which bundles **Kint 6**. `app/Config/Kint.php` must use the Kint 6 API (it was regenerated from the framework's reference config). The old Kint 3/4 form using `AbstractRenderer::SORT_FULL` / `richSort` will fatal on boot with `Undefined constant ...::SORT_FULL`. If you see that error after a dependency change, re-sync `app/Config/Kint.php` from `vendor/codeigniter4/framework/app/Config/Kint.php`.
- **Login credentials** — the imported `o-present.sql` users have unknown bcrypt password hashes. See the **Roles & accounts** section for the account list and how to set a usable password.
