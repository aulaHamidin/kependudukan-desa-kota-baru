<?php

use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Surat\JenisSuratController;
use App\Http\Controllers\Surat\SuratTerbitController;
use App\Http\Controllers\Web\Administrator\AuditLogController;
use App\Http\Controllers\Web\Administrator\ReportingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Event\ApprovalController;
use App\Http\Controllers\Web\Event\DatangController;
use App\Http\Controllers\Web\Event\KelahiranController;
use App\Http\Controllers\Web\Event\KematianController;
use App\Http\Controllers\Web\Event\PindahController;
use App\Http\Controllers\Web\Event\VoidController;
use App\Http\Controllers\Web\Import\PendudukImportController;
use App\Http\Controllers\Web\KartuKeluargaController;
use App\Http\Controllers\Web\KkMemberController;
use App\Http\Controllers\Web\MasterData\AgamaController;
use App\Http\Controllers\Web\MasterData\EventTypeController;
use App\Http\Controllers\Web\MasterData\GolonganDarahController;
use App\Http\Controllers\Web\MasterData\HubunganKeluargaController;
use App\Http\Controllers\Web\MasterData\PekerjaanController;
use App\Http\Controllers\Web\MasterData\PendapatanRangeController;
use App\Http\Controllers\Web\MasterData\PendidikanController;
use App\Http\Controllers\Web\MasterData\StatusKependudukanController;
use App\Http\Controllers\Web\MasterWilayah\DesaController;
use App\Http\Controllers\Web\MasterWilayah\RtController;
use App\Http\Controllers\Web\MasterWilayah\RwController;
use App\Http\Controllers\Web\PendudukController;
use App\Http\Controllers\Web\PindahRtController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Routes
Route::get('/', \App\Http\Controllers\Web\WelcomeController::class)->name('welcome');

// Authenticated Routes
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
    });

    // Territory Role Required Routes
    Route::middleware(['territory_role'])->group(function () {
        
        // Pindah RT
        Route::prefix('pindah-rt')->name('pindah-rt.')->group(function () {
            Route::get('/', [PindahRtController::class, 'index'])->name('index');
            Route::get('/{kk}', [PindahRtController::class, 'show'])->name('show');
            Route::post('/{kk}', [PindahRtController::class, 'store'])->name('store');
        });

        // Events Management
        Route::prefix('events')->name('events.')->group(function () {
            // Shared void endpoint for all events
            Route::post('{event}/void', [VoidController::class, 'store'])->name('void');

            // Event Datang
            Route::resource('datang', DatangController::class)
                ->names('datang')
        ->parameters(['datang' => 'event']);

            // Event Pindah
            Route::get('pindah/kk-members', [PindahController::class, 'getKkMembers'])->name('pindah.kk-members');
            Route::resource('pindah', PindahController::class)
                ->names('pindah')
        ->parameters(['pindah' => 'event']);

            // Event Kelahiran
            Route::resource('kelahiran', KelahiranController::class)
                ->names('kelahiran')
        ->parameters(['kelahiran' => 'event']);

            // Event Kematian
            Route::get('kematian/kk-members', [KematianController::class, 'getKkMembers'])->name('kematian.kk-members');
            Route::resource('kematian', KematianController::class)
                ->names('kematian')
        ->parameters(['kematian' => 'event']);
        });

        // Kartu Keluarga Management
            Route::resource('kartu-keluarga', KartuKeluargaController::class);

        // KK Member Management
        Route::prefix('kk-member')->name('kk-member.')->group(function () {
            Route::post('/', [KkMemberController::class, 'store'])->name('store');
            Route::patch('/{kk_member}', [KkMemberController::class, 'update'])->name('update');
            Route::patch('/{kk_member}/leave', [KkMemberController::class, 'leave'])->name('leave');
            Route::patch('/{kk_member}/set-kepala', [KkMemberController::class, 'setKepala'])->name('set-kepala');
        });
        
        // Import Penduduk (Admin Desa only)
        Route::prefix('penduduk/import')->name('penduduk.import.')->middleware('admin_desa')->group(function () {
            Route::get('/', [PendudukImportController::class, 'index'])->name('index');
            Route::get('/template', [PendudukImportController::class, 'template'])->name('template');
            Route::post('/validate', [PendudukImportController::class, 'validateUpload'])->name('validate');
            Route::post('/execute', [PendudukImportController::class, 'execute'])->name('execute');
        });

        // Penduduk Management
        Route::resource('penduduk', PendudukController::class)->except(['create', 'store', 'destroy']);

        // Approvals (Admin Desa only)
        Route::prefix('approvals')->name('approvals.')->group(function () {
                    Route::get('/', [ApprovalController::class, 'index'])->name('index');
                    Route::post('/{event}/approve', [ApprovalController::class, 'approve'])->name('approve');
                    Route::post('/{event}/reject', [ApprovalController::class, 'reject'])->name('reject');
        });

        // Search API
        Route::prefix('search')->name('search.')->group(function () {
                    Route::get('/jenis-surat', [SearchController::class, 'jenisSurat'])->name('jenis-surat');
                            Route::get('/penduduk', [SearchController::class, 'penduduk'])->name('penduduk');
                            Route::get('/kartu-keluarga', [SearchController::class, 'kartuKeluarga'])->name('kartu-keluarga');
        });

        // Administrator Routes
        Route::prefix('administrator')->name('administrator.')->group(function () {
            // User Management
            Route::resource('kelola-user', \App\Http\Controllers\Web\Administrator\UserController::class)
                ->parameters(['kelola-user' => 'user'])
                ->except(['show', 'create', 'edit']);
            Route::patch('kelola-user/{user}/restore', [\App\Http\Controllers\Web\Administrator\UserController::class, 'restore'])
                ->name('kelola-user.restore');

            // Audit Log (with specific middleware)
            Route::middleware('audit_log_access')->group(function () {
                Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
                Route::get('audit-log/{auditLog}', [AuditLogController::class, 'show'])->name('audit-log.show');
            });

            // Reporting (Admin Desa only)
            Route::prefix('reporting')->name('reporting.')->middleware('admin_desa')->group(function () {
                Route::get('/', [ReportingController::class, 'index'])->name('index');
                Route::get('/export/{type}/{format}', [ReportingController::class, 'export'])
                    ->whereIn('format', ['pdf', 'xlsx'])
                    ->name('export');
            });
        });

        // Master Wilayah
        Route::prefix('master/wilayah')->name('master.wilayah.')->group(function () {
            Route::resource('desas', DesaController::class)->names('desa');
            Route::resource('rws', RwController::class)->names('rw');
            Route::resource('rts', RtController::class)->names('rt');
        });
    });

    // Surat Management
    Route::prefix('surat')->name('surat.')->group(function () {
        Route::resource('terbit', SuratTerbitController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['terbit' => 'suratTerbit']);
        Route::get('jenis-surat/{kode}', [SuratTerbitController::class, 'getJenisSuratDetails'])
            ->name('jenis-surat.details');
        Route::get('terbit/{suratTerbit}/download', [SuratTerbitController::class, 'download'])->name('terbit.download');
        Route::get('terbit/{suratTerbit}/batalkan', [SuratTerbitController::class, 'batalkanForm'])->name('terbit.batalkan.form');
        Route::post('terbit/{suratTerbit}/batalkan', [SuratTerbitController::class, 'batalkan'])->name('terbit.batalkan');
        Route::post('terbit/{suratTerbit}/regenerate-pdf', [SuratTerbitController::class, 'regeneratePdf'])->name('terbit.regenerate-pdf');
    });

    // Master Data (Super Admin only)
    Route::prefix('master')->name('master.')->middleware('super_admin')->group(function () {
        Route::resource('agamas', AgamaController::class)->only(['index'])->names('agama');
        Route::resource('golongan-darahs', GolonganDarahController::class)->only(['index'])->names('golongan_darah');
        Route::resource('event-types', EventTypeController::class)->only(['index'])->names('event_type');
        Route::resource('hubungan-keluarga', HubunganKeluargaController::class)->only(['index'])->names('hubungan_keluarga');
        Route::resource('pendapatan-ranges', PendapatanRangeController::class)->only(['index'])->names('pendapatan_range');
        Route::resource('status', StatusKependudukanController::class)->only(['index'])->names('status');
        Route::resource('pendidikans', PendidikanController::class)->only(['index'])->names('pendidikan');
        Route::resource('pekerjaans', PekerjaanController::class)->only(['index'])->names('pekerjaan');
        Route::resource('jenis-surat', JenisSuratController::class)
            ->only(['index', 'show'])
            ->parameters(['jenis-surat' => 'jenisSurat'])
            ->names('jenis_surat');
    });
});
require __DIR__ . '/auth.php';
