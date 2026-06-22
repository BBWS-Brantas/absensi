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

### Per-unit (wilayah OP) role scoping — IMPLEMENTED

A role mapping (see project image) layers a **unit/region dimension** on top of the existing three roles. It does **not** add new Myth/Auth groups — it scopes `admin` and `pegawai` to one operational unit:

```
                         Role (Head) — PPK
        ┌──────────────┬──────────────┼──────────────┬──────────────┐
   Admin PPK OP I  Admin OP II   Admin OP III   Admin OP IV   Admin OP PIAT
        │              │              │              │              │
  Pegawai TPM     Pegawai TPM   Pegawai TPM    Pegawai TPM   Pegawai TPM
   OP I            OP II         OP III         OP IV         OP PIAT
```

Units (five): **OP I, OP II, OP III, OP IV, OP PIAT**. Mapping rules:

- **Head (PPK)** — global; sees **all** units' pegawai/admin data (*"Head bisa mengakses kesemua data pegawai dan admin"*). Matches today's global `head` behaviour — no scoping applied.
- **Admin (PPK OP _x_)** — `admin` group **scoped to one unit**; sees only its own unit's pegawai, presensi, laporan, and (if extended) approvals. **This is the main change** — today `admin` is global.
- **Pegawai (TPM OP _x_)** — `pegawai` group tagged with its unit. Already self-scoped today; only the unit tag is new.

**How it's wired (files):**

1. **Unit table + column.** [`unit_operasional`](app/Models/UnitOperasionalModel.php) (`id`, `nama` e.g. "OP I", `slug`, soft-delete) + **`id_unit`** (nullable) on `pegawai`, separate from `id_lokasi_presensi` so a unit is independent of physical check-in locations. Migration: [2026-06-22-000000_create_unit_operasional](app/Database/Migrations/2026-06-22-000000_create_unit_operasional.php). Five units seeded by [UnitOperasionalSeeder](app/Database/Seeds/UnitOperasionalSeeder.php).
2. **Resolve current unit.** Helper [app/Helpers/unit_helper.php](app/Helpers/unit_helper.php) → **`current_unit_id()`**: returns `null` for `head` (no scoping), else the user's `pegawai.id_unit` (`0` if unset → matches nothing). Auto-loaded via `$helpers` in [BaseController](app/Controllers/BaseController.php). Pegawai-facing screens stay self-scoped by `id_pegawai` and don't use it.
3. **Scoped queries.** Each admin-facing model method takes an optional trailing `$id_unit` param and adds `WHERE pegawai.id_unit = ?` only when non-null: `PegawaiModel::getPegawai`/`getJumlahPegawaiAktif`, `PresensiModel::getDataPresensiHarian`/`getDataPresensiBulanan`/`getDataPresensiHariIni`, `KetidakhadiranModel::getDataIzinHariIni`. Controllers pass `current_unit_id()`: `Admin::index` (dashboard stats), `Pegawai` (list/search/Excel), `Presensi` (laporan harian/bulanan + Excel + PDF).
4. **Cross-unit guard + admin write-lock.** `Pegawai::pastikanDalamUnit()` throws `PageNotFoundException` if an admin opens a pegawai outside its unit — applied to `detail`/`edit`/`update`/`delete`/`hapusFoto`. On `store`/`update`, for an **admin** both `id_unit` and `role` are **forced server-side** (submitted values ignored): unit = the admin's own unit, and on `store` role = `pegawai` (admins can only create pegawai accounts), on `update` role is kept unchanged. The `tambah.php`/`edit.php` views reflect this — for admins the Unit and Role selects render **disabled** with a hidden input carrying the forced value; `head` gets the normal editable dropdowns and may choose any unit/role.
5. **Seeded accounts.** [PegawaiSeeder](app/Database/Seeds/PegawaiSeeder.php): admin Tamani + pegawai Christoper → OP I, head Jaya → NULL.
6. **Unit CRUD (head-only).** [UnitOperasional](app/Controllers/UnitOperasional.php) controller + `unit_operasional/` views manage the units list at `/unit-operasional` (routes gated `role:head`, menu item under Master Data shown only to head). Mirrors the Jabatan CRUD pattern (inline add + live search + edit/soft-delete, with a `total_pegawai` count per unit). The data-pegawai list/search also show a **Unit Operasional** column for head.

**Applying to an imported DB.** Migrations are NOT re-run on top of the imported `o-present.sql`, so for an existing DB run the one-time script [`../../add-unit-operasional.sql`](../../add-unit-operasional.sql) (creates the table, adds `id_unit`, seeds the five units). Fresh installs instead use `php spark migrate && php spark db:seed UnitOperasionalSeeder`.

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
