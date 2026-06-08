# Technical Audit Report — SIAK-Desa

**Project:** Sistem Informasi Administrasi Kependudukan Desa (SIAK-Desa)
**Auditor:** Senior Software Architect
**Audit Date:** 2026-03-08
**Codebase Branch:** main
**Framework:** Laravel 10.10 / PHP 8.1+ / MySQL

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Key Features Identified](#2-key-features-identified)
3. [Architecture Analysis](#3-architecture-analysis)
4. [Technical Evaluation](#4-technical-evaluation)
5. [Future Development Recommendations](#5-future-development-recommendations)

---

## 1. System Overview

SIAK-Desa adalah aplikasi web manajemen administrasi kependudukan tingkat desa yang dibangun di atas framework Laravel 10. Sistem ini dirancang untuk mengelola data penduduk, kartu keluarga, peristiwa kependudukan (kelahiran, kematian, pindah, datang), dan penerbitan surat administrasi, dengan kontrol akses berbasis wilayah (territory-based access control).

### Tech Stack

| Layer | Teknologi |
|---|---|
| Backend Framework | Laravel 10.10 |
| Language | PHP 8.1+ (strict types) |
| Database | MySQL / MariaDB |
| Frontend Templating | Blade + Tailwind CSS |
| PDF Generation | DomPDF (barryvdh/laravel-dompdf) |
| Excel Export | Maatwebsite Laravel Excel 3.x |
| DataTables | Yajra DataTables |
| Authentication | Laravel Sanctum (session-based) |
| Role Management | Spatie Laravel Permission |
| Queue | Laravel Queue (database driver) |

### Database Scale

- **39 migration files** mendefinisikan schema lengkap
- **4 SQL Views** untuk aggregasi data dashboard dan reporting
- Tabel utama: `penduduks`, `kartu_keluargas`, `kk_members`, `events`, `surat_terbits`, `audit_logs`
- Tabel master: `agamas`, `pendidikans`, `pekerjaans`, `jenis_surats`, `event_types`
- Tabel wilayah: `desas`, `rws`, `rts`

### User Roles

```
super_admin    — akses penuh ke semua desa
admin_desa     — akses ke seluruh data dalam satu desa
admin_rw       — akses dibatasi ke RW tertentu
admin_rt       — akses dibatasi ke RT tertentu
viewer         — read-only, tidak bisa create/edit/delete
```

---

## 2. Key Features Identified

### 2.1 Manajemen Data Penduduk

Modul penduduk (`penduduks`) menyimpan data lengkap meliputi NIK, nama, tanggal lahir, jenis kelamin, agama, pendidikan, pekerjaan, dan status perkawinan. Setiap penduduk terhubung ke Kartu Keluarga (`kartu_keluargas`) melalui `kk_members` dengan status `KEPALA_KELUARGA`, `ANGGOTA`, atau `NUMPANG`. Soft delete diimplementasikan pada tabel `penduduks` sehingga riwayat data tetap terjaga.

### 2.2 Manajemen Kartu Keluarga

KK memiliki status `AKTIF` atau `NONAKTIF` dan terhubung ke wilayah RT. Sistem mendukung operasi pindah RT (`PindahRtService`) yang secara atomik memindahkan seluruh anggota KK ke RT tujuan dengan mencatat peristiwa `PINDAH` dan `DATANG` secara otomatis.

### 2.3 Sistem Peristiwa Kependudukan (Event Lifecycle)

Empat jenis peristiwa didukung: **KELAHIRAN**, **KEMATIAN**, **PINDAH**, **DATANG**. Setiap peristiwa mengikuti lifecycle:

```
DRAFT → VERIFIED → (VOID)
```

- **DRAFT**: Diinput operator, menunggu verifikasi
- **VERIFIED**: Disetujui admin; memicu perubahan status penduduk/KK di DB
- **VOID**: Dibatalkan; perubahan status penduduk di-rollback menggunakan soft delete restore

Setiap service peristiwa (`KelahiranService`, `KematianService`, `PindahService`, `DatangService`) mengimplementasikan pola yang sama: validasi domain → eksekusi DB → audit log.

### 2.4 Penerbitan Surat (Letter Management)

`SuratTerbitService` mengelola siklus hidup surat administrasi dari penerbitan hingga kadaluarsa. Fitur utama:
- Nomor surat otomatis via `SequenceGeneratorService` dengan format `[nomor]/[kode_surat]/[bulan_romawi]/[tahun]`
- PDF generation berbasis template Blade via queue job
- Status tracking: `AKTIF` → `KADALUARSA` (via scheduler) atau `DICABUT` (manual)

### 2.5 Penomoran Surat Otomatis

`SequenceGeneratorService` menggunakan `lockForUpdate()` (SELECT FOR UPDATE) untuk mencegah race condition saat nomor urut di-generate secara konkuren. Counter di-reset setiap bulan (monthly sequence). Format nomor mengikuti konvensi administrasi resmi pemerintah desa.

### 2.6 Audit Logging

Setiap perubahan data sensitif (penduduk, KK, surat, events) dicatat ke tabel `audit_logs` melalui trait `Auditable` yang diikat ke `AuditLogObserver`. `AuditLogService` menerapkan **PII masking** — field seperti `nik`, `tgl_lahir`, `nama_ibu_kandung` di-hash/mask sebelum disimpan ke log, sesuai prinsip data minimization.

### 2.7 Territory-Based Access Control (TBAC)

Seluruh query data di-scope otomatis berdasarkan wilayah pengguna melalui:
- **`HasTerritory` trait** + **`TerritoryScope` global scope**: Auto-filter pada Eloquent query berdasarkan `rt_id`, `rw_id`, atau `desa_id` dari user yang login
- **`DashboardService::rtIdsForUser()`**: Resolusi dinamis RT yang accessible per role
- **Policy classes** (10 file): Authorization granular per aksi (viewAny, view, create, update, delete, verify, void, pindah)

### 2.8 Dashboard & Reporting

Dashboard mengaggregasi data real-time dari 4 SQL views:
- `v_penduduk_aktif` — penduduk dengan status aktif
- `v_kk_with_members` — KK beserta jumlah anggota
- `v_data_inconsistency` — deteksi anomali data otomatis
- `v_surat_expiring_soon` — surat mendekati kadaluarsa

`DashboardService` menggabungkan 12 widget data dalam satu call, dengan lazy-evaluation berbasis Gate permission untuk menghindari query yang tidak diperlukan per role.

---

## 3. Architecture Analysis

### 3.1 Request Flow

```
HTTP Request
    │
    ▼
RouteServiceProvider (routes/web.php)
    │ — Auth middleware (session)
    │ — Role middleware (Spatie)
    ▼
Controller (app/Http/Controllers/Web/)
    │ — Form Request validation
    │ — Policy authorization (Gate/authorize())
    ▼
Service Layer (app/Services/)
    │ — Domain logic
    │ — DB transaction wrapping
    ▼
Model / Eloquent (app/Models/)
    │ — Global scope (TerritoryScope)
    │ — Observer (AuditLogObserver)
    ▼
Database (MySQL)
    │ — SQL Views (v_*)
    └── audit_logs table
```

### 3.2 Role-Based Access Control (RBAC)

Implementasi RBAC menggunakan Spatie Permission dikombinasikan dengan Laravel Policy. Setiap aksi sensitif memiliki policy method tersendiri. Contoh hierarki cek:

```php
// KartuKeluargaPolicy::pindah()
public function pindah(User $user, KartuKeluarga $kk): bool
{
    return $user->hasAnyRole(['super_admin', 'admin_desa', 'admin_rw', 'admin_rt'])
        && $this->withinTerritory($user, $kk);
}
```

Dashboard menerapkan `@can`/`@cannot` Blade directives untuk setiap widget berdasarkan permission, memastikan UI tidak menampilkan kontrol yang tidak bisa digunakan.

### 3.3 Event System & Population Lifecycle

Setiap verifikasi event memicu perubahan status penduduk secara cascading:

```
KELAHIRAN verified  → Penduduk baru dibuat dengan status AKTIF, ditambah ke KK
KEMATIAN verified   → Penduduk soft-deleted (status = MENINGGAL)
PINDAH verified     → KK + anggota berpindah RT; status penduduk tetap AKTIF
DATANG verified     → Penduduk restore (jika pindah datang) atau baru
VOID event          → Semua perubahan di atas di-rollback via restore
```

Implementasi menggunakan DB transaction untuk menjamin atomicity. Jika ada step yang gagal, seluruh operasi di-rollback.

### 3.4 Letter Generation Pipeline

```
POST /surat → SuratTerbitController::store()
    │
    ├── SuratTerbitService::create()
    │       ├── SequenceGeneratorService::nextNumber()  ← lockForUpdate()
    │       ├── SuratTerbit::create() [status=AKTIF, pdf_status=PENDING]
    │       └── GenerateSuratPdfJob::dispatch()
    │
    └── Queue Worker
            ├── Render Blade template
            ├── DomPDF::generate()
            ├── Store to storage/app/surat/
            └── Update pdf_status = DONE | FAILED
```

### 3.5 Audit Trail

```php
// Auditable trait (dipasang di model sensitif)
protected static function bootAuditable(): void
{
    static::observe(AuditLogObserver::class);
}

// AuditLogObserver mencatat:
// - event: created/updated/deleted/restored
// - model_type, model_id
// - changed_fields (dengan PII masking untuk field sensitif)
// - user_id, ip_address, user_agent
// - created_at
```

---

## 4. Technical Evaluation

### 4.1 Kekuatan (Strengths)

**Desain Keamanan yang Kuat**
Territory-scoped access control diimplementasikan secara konsisten di tiga layer: global scope Eloquent, service layer (`rtIdsForUser`), dan policy authorization. Risiko data leakage lintas wilayah sangat rendah.

**Service Layer yang Terstruktur**
Pemisahan business logic ke service classes (bukan di controller atau model) membuat kode mudah diuji secara unit. Setiap service memiliki tanggung jawab tunggal yang jelas.

**Atomic Operations untuk Data Kritis**
`SequenceGeneratorService` menggunakan `lockForUpdate()` untuk mencegah duplikasi nomor surat. Event lifecycle menggunakan DB transaction untuk menjamin konsistensi data populasi.

**PII-Aware Audit Logging**
Sistem audit log sudah mempertimbangkan privasi data dengan melakukan masking field sensitif sebelum dicatat, bukan sekadar dump raw data.

**SQL Views untuk Reporting**
Pemisahan logika aggregasi ke SQL views (`v_penduduk_aktif`, `v_data_inconsistency`, dll.) mengurangi kompleksitas query di aplikasi dan memungkinkan optimasi independen di level DB.

**Strict Types dan PHP 8.1+**
Seluruh file PHP menggunakan `declare(strict_types=1)`, mengurangi bug yang disebabkan type coercion.

### 4.2 Kelemahan (Weaknesses)

**~~Queue Job Tanpa Retry Mechanism yang Terdefinisi~~** ✅ *Ditangani — lihat Rekomendasi 1*
~~`GenerateSuratPdfJob` tidak memiliki konfigurasi `$tries` dan `$backoff` yang eksplisit. Jika job gagal (misalnya DomPDF timeout pada template kompleks), `pdf_status` akan tetap `PROCESSING` selamanya. Tidak ada mekanisme notifikasi ke admin bahwa PDF gagal di-generate.~~

**Potensi N+1 Query di List Views**
Eager loading global di model `KartuKeluarga` (misalnya `$with = ['rt', 'rw']`) dikomentari dengan note "disabled due to 12K+ records". Ini menunjukkan ada kasus di mana list view memuat relasi secara lazy per-row. Tanpa query profiling yang sistematis, sulit memastikan semua endpoint sudah bebas N+1.

**~~Ketergantungan Scheduler untuk Status Kadaluarsa Surat~~** ✅ *Ditangani — lihat Rekomendasi 3*
~~Status surat berubah dari `AKTIF` ke `KADALUARSA` sepenuhnya bergantung pada Laravel Scheduler yang berjalan via `cron`. Jika scheduler mati atau gagal berjalan (misalnya konfigurasi cron bermasalah di server), surat yang sudah melewati tanggal kadaluarsa tetap tampil sebagai `AKTIF` di seluruh UI dan laporan. Tidak ada mekanisme fallback maupun monitoring yang mendeteksi kondisi ini.~~

**~~Territory Scope Terimplementasi Berbeda per Model~~** ✅ *Ditangani — lihat Rekomendasi 4*
~~`HasTerritory` trait menggunakan `TerritoryScope` global scope. Namun model `Event` dan `SuratTerbit` mengimplementasikan territory filter secara manual di masing-masing service (tidak menggunakan global scope yang sama). Inkonsistensi ini membuka risiko regresi jika ada model baru yang lupa menerapkan filter.~~

**Validasi Territory-Scope pada Reporting Belum Komprehensif**
`ReportingService` menghasilkan laporan untuk export Excel/PDF. Belum terdapat test suite yang secara eksplisit memverifikasi bahwa setiap tipe laporan (statistik penduduk, surat, events) benar-benar dibatasi per territory untuk role `admin_desa`.

---

## 5. Future Development Recommendations

> **Status Implementasi** (2026-03-08):
> - ✅ **Rekomendasi 1** — Diimplementasikan
> - ⏳ **Rekomendasi 2** — Belum diimplementasikan
> - ✅ **Rekomendasi 3** — Diimplementasikan
> - ✅ **Rekomendasi 4** — Diimplementasikan (test suite)
> - ⏳ **Rekomendasi 5** — Belum diimplementasikan

---

### Rekomendasi 1: Queue Monitoring & Retry untuk PDF Generation `[IMPLEMENTED]`

**Masalah:** `GenerateSuratPdfJob` dapat stuck di status `PROCESSING` tanpa batas waktu jika job gagal — tidak ada retry, tidak ada notifikasi, tidak ada timeout enforcement.

**Solusi yang Direkomendasikan:**

```php
// app/Jobs/GenerateSuratPdfJob.php
class GenerateSuratPdfJob implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 60;       // detik antar retry
    public int $timeout = 120;      // batas waktu eksekusi

    public function failed(\Throwable $exception): void
    {
        $this->suratTerbit->update(['pdf_status' => 'FAILED']);

        // Notifikasi ke admin_desa yang bersangkutan
        Notification::send(
            $this->suratTerbit->createdBy,
            new SuratPdfFailedNotification($this->suratTerbit, $exception)
        );
    }
}
```

Tambahkan juga artisan command `surat:retry-failed-pdf` yang men-dispatch ulang surat dengan `pdf_status = FAILED` untuk keperluan manual recovery. Integrasikan dengan Laravel Horizon atau queue monitoring dashboard sederhana agar admin bisa melihat status queue secara real-time.

**Impact:** Menghilangkan silent failure pada fitur kritis (penerbitan surat). Estimasi effort: 2–3 hari.

**Implementasi:**
- `app/Notifications/SuratPdfFailedNotification.php` — notifikasi mail ke pembuat surat
- `app/Jobs/GenerateSuratPdfJob.php` — dispatch notifikasi di `failed()` setelah semua retry habis
- `app/Console/Commands/SuratRetryFailedPdfCommand.php` — artisan `surat:retry-failed-pdf`

---

### Rekomendasi 2: N+1 Query Optimization dengan Query Profiling Sistematis

**Masalah:** Eager loading KartuKeluarga dinonaktifkan karena dataset besar (12K+ records). Tanpa pendekatan yang terstruktur, sulit memastikan endpoint mana yang masih mengalami N+1 query.

**Solusi yang Direkomendasikan:**

Aktifkan `QueryDetector` (beyondcode/laravel-query-detector) di environment development:

```php
// config/query-detector.php
'threshold' => 5,      // alert jika > 5 query per request
'output'    => [\BeyondCode\QueryDetector\Outputs\Log::class],
```

Untuk list views dengan data besar, implementasikan **cursor-based pagination** kombinasi dengan **chunked eager loading**:

```php
// Ganti paginate() dengan cursor pagination untuk performa
KartuKeluarga::with(['rt:id,nomor_rt,rw_id', 'rt.rw:id,nomor_rw'])
    ->where('status_kk', 'AKTIF')
    ->cursorPaginate(20);
```

Untuk DataTables (Yajra), pastikan menggunakan `Eloquent Builder` bukan `Collection` sebagai sumber data agar query tetap di DB level, bukan di PHP memory.

**Impact:** Mengurangi response time pada halaman list dengan dataset besar. Estimasi effort: 3–5 hari (profiling + fix per endpoint).

---

### Rekomendasi 3: Mitigasi Scheduler Failure untuk Status Kadaluarsa Surat `[IMPLEMENTED]`

**Masalah:** Status surat `AKTIF` → `KADALUARSA` bergantung penuh pada Laravel Scheduler via `cron`. Jika scheduler tidak berjalan dalam beberapa hari (misalnya server restart, misconfigured cron, atau job exception tertangkap diam-diam), data surat kadaluarsa tetap tampil valid di seluruh sistem tanpa ada peringatan apapun ke admin.

**Solusi yang Direkomendasikan:**

Tambahkan **database-level view check** sebagai fallback — query status kadaluarsa secara computed berdasarkan `tanggal_kadaluarsa` aktual, bukan hanya kolom `status`:

```php
// Ganti direct status check dengan computed scope
public function scopeBenarBenarAktif(Builder $query): Builder
{
    return $query->where('status', 'AKTIF')
                 ->where('tanggal_kadaluarsa', '>=', today());
}
```

Tambahkan **scheduler health check** menggunakan artisan command yang dicatat ke tabel `scheduler_heartbeats`:

```php
// app/Console/Commands/SchedulerHeartbeat.php
// Dijadwalkan setiap menit: $schedule->command('scheduler:heartbeat')->everyMinute();
// Dashboard bisa menampilkan "Scheduler aktif: X menit lalu"
```

Aktifkan `schedule:run` monitoring via Laravel Telescope atau integrasi sederhana ke tabel `scheduler_heartbeats`. Jika heartbeat terakhir lebih dari 10 menit, tampilkan banner peringatan di dashboard `super_admin`.

**Impact:** Mencegah data surat yang sudah kadaluarsa tampil sebagai valid, terutama kritis untuk surat domisili dan keterangan yang digunakan warga sebagai dokumen resmi. Estimasi effort: 2–3 hari.

**Implementasi:**
- `app/Models/SuratTerbit.php` — tambah `scopeBenarBenarAktif()`: filter berdasarkan `tanggal_kadaluarsa` aktual sebagai fallback scheduler
- `app/Console/Commands/SchedulerHeartbeatCommand.php` — artisan `scheduler:heartbeat` yang mencatat timestamp ke cache setiap menit
- `app/Console/Kernel.php` — daftarkan `scheduler:heartbeat` ke schedule `everyMinute()`
- Helper statis `SchedulerHeartbeatCommand::isAlive(int $minutes)` untuk dipakai di dashboard monitoring

---

### Rekomendasi 4: Territory Scope — Konsolidasi dan Test Coverage `[IMPLEMENTED]`

**Masalah:** Territory filtering diimplementasikan dengan tiga pendekatan berbeda:
1. `HasTerritory` trait + `TerritoryScope` global scope (untuk Penduduk, KartuKeluarga)
2. Manual `whereIn('rt_id', $rtIds)` di service layer (untuk Event, SuratTerbit)
3. Custom `rtIdsForUser()` di `DashboardService`

Inkonsistensi ini membuat sistem rentan terhadap regresi akses lintas wilayah jika ada fitur baru yang tidak mengikuti pola yang tepat.

**Solusi yang Direkomendasikan:**

Buat `TerritoryFilter` contract yang wajib diimplementasikan:

```php
// app/Contracts/TerritoryFilterable.php
interface TerritoryFilterable
{
    public function applyTerritoryScope(Builder $query, User $user): Builder;
}
```

Implementasikan Feature Test suite khusus untuk territory scoping:

```php
// tests/Feature/TerritoryScope/EventTerritoryScopeTest.php
class EventTerritoryScopeTest extends TestCase
{
    /** @test */
    public function admin_rt_cannot_see_events_from_other_rt(): void
    {
        $user = User::factory()->withRole('admin_rt')->forRt($this->rt)->create();
        $otherRtEvent = Event::factory()->forRt($this->otherRt)->create();

        $this->actingAs($user)
             ->get(route('events.index'))
             ->assertDontSee($otherRtEvent->id);
    }
}
```

Minimal 3 test case per role per modul utama (Event, SuratTerbit, KartuKeluarga, Penduduk). Jalankan sebagai bagian dari CI pipeline.

**Impact:** Mencegah data leakage antar wilayah, meningkatkan kepercayaan terhadap implementasi TBAC. Estimasi effort: 5–7 hari (refactor + test writing).

**Implementasi:**
- `tests/Feature/TerritoryScope/EventTerritoryScopeTest.php` — 5 test case: isolasi per RT, RT dalam RW sama, RW, Desa, dan super_admin lintas desa
- `tests/Feature/TerritoryScope/SuratTerbitTerritoryScopeTest.php` — 6 test case: sama seperti Event + test `scopeBenarBenarAktif()`
- Semua test menggunakan `PolicyTestHelper` trait dan mengikuti pola existing test suite

---

### Rekomendasi 5: Validasi dan Audit Territory Scope pada Fitur Reporting & Export

**Masalah:** `ReportingService` menghasilkan laporan statistik penduduk, surat, dan peristiwa dalam format Excel/PDF. Saat ini belum ada validasi komprehensif bahwa setiap jenis laporan sudah benar-benar dibatasi per territory untuk role non-super_admin. Laporan yang bocor ke data luar territory adalah risiko keamanan dan privasi yang serius.

**Solusi yang Direkomendasikan:**

Lakukan audit manual terhadap semua method di `ReportingService`:

```php
// Checklist audit untuk setiap report method:
// □ Query di-scope dengan whereIn('rt_id', $rtIds)?
// □ Atau whereHas() ke relasi yang territory-scoped?
// □ Raw SQL query melewati TerritoryScope?
// □ Export file name tidak mengandung data dari territory lain?
```

Tambahkan middleware `EnsureReportIsScoped` yang mem-validasi parameter request laporan terhadap territory user:

```php
// app/Http/Middleware/EnsureReportIsScoped.php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    $rtId = $request->input('rt_id');

    if ($rtId && !$this->isRtAccessible($user, (int) $rtId)) {
        abort(403, 'Akses laporan di luar wilayah Anda tidak diizinkan.');
    }

    return $next($request);
}
```

Buat integration test yang men-download laporan sebagai `admin_desa` dari desa A dan memverifikasi bahwa output tidak mengandung data dari desa B.

**Impact:** Menutup potensi celah data leakage pada fitur export — fitur yang sering diabaikan dalam audit keamanan. Estimasi effort: 3–4 hari (audit + fix + test).

---

## Appendix: File Reference

| Area | File Utama |
|---|---|
| Event Lifecycle | `app/Services/KelahiranService.php`, `KematianService.php`, `PindahService.php`, `DatangService.php` |
| Letter System | `app/Services/SuratTerbitService.php`, `SequenceGeneratorService.php` |
| Dashboard | `app/Services/DashboardService.php`, `resources/views/dashboard.blade.php` |
| RBAC | `app/Policies/`, `app/Traits/HasTerritory.php`, `app/Scopes/TerritoryScope.php` |
| Audit | `app/Traits/Auditable.php`, `app/Observers/AuditLogObserver.php`, `app/Services/AuditLogService.php` |
| PDF Job | `app/Jobs/GenerateSuratPdfJob.php` |
| SQL Views | `database/migrations/*_create_v_*_view.php` |
| Reporting | `app/Services/ReportingService.php` |

---

*Dokumen ini dihasilkan berdasarkan analisis statis codebase pada tanggal audit. Temuan bersifat berdasarkan review kode, bukan penetration testing atau load testing aktif.*
