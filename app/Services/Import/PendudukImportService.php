<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Events\Audit\DataImported;
use App\Models\Agama;
use App\Models\Event;
use App\Models\EventDatang;
use App\Models\GolonganDarah;
use App\Models\HubunganKeluarga;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Models\User;
use App\Repositories\PendudukRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendudukImportService
{
    public function __construct(
        private PendudukRepository $pendudukRepo,
    ) {}

    /**
     * Phase 1: Validate all rows against ALL business rules.
     *
     * @return array{valid: bool, errors: array, summary: array}
     */
    public function validate(Collection $rows, User $actor): array
    {
        // 1. Pre-load master data caches (avoid N+1)
        $validAgamaCodes = Agama::pluck('kode')->toArray();
        $validPendidikanCodes = Pendidikan::pluck('kode')->toArray();
        $validPekerjaanCodes = Pekerjaan::pluck('kode')->toArray();
        $validGoldarCodes = GolonganDarah::pluck('kode')->toArray();
        $validHubunganCodes = HubunganKeluarga::pluck('kode')->toArray();
        $validPerkawinan = ['BELUM_KAWIN', 'KAWIN', 'CERAI_HIDUP', 'CERAI_MATI'];

        // 2. Resolve admin territory — all RT IDs in this admin's desa
        $allowedRtIds = $this->resolveAllowedRtIds($actor);

        // 3. Batch-load existing data for cross-reference
        $fileNiks = $rows->pluck('nik')->filter()->unique()->toArray();
        $existingActiveNiks = Penduduk::whereIn('nik', $fileNiks)->pluck('nik')->toArray();

        $fileNoKks = $rows->pluck('no_kk')->filter()->unique()->toArray();
        $existingKks = KartuKeluarga::whereIn('no_kk', $fileNoKks)->get()->keyBy('no_kk');

        $filePhones = $rows->pluck('no_hp')->filter()->reject(fn($v) => $v === '' || $v === null)->unique()->toArray();
        $existingPhones = Penduduk::whereIn('no_hp', $filePhones)->pluck('no_hp')->toArray();

        $fileEmails = $rows->pluck('email')->filter()->reject(fn($v) => $v === '' || $v === null)->unique()->toArray();
        $existingEmails = Penduduk::whereIn('email', $fileEmails)->pluck('email')->toArray();

        // 4. Per-row validation
        $errors = [];
        $seenNiks = [];
        $seenPhones = [];
        $seenEmails = [];
        $kkGroups = [];
        $processedRowCount = 0;

        $requiredFields = [
            'no_kk', 'rt_id', 'hubungan_keluarga', 'nik', 'nama_lengkap',
            'jenis_kelamin', 'tempat_lahir', 'tgl_lahir', 'agama',
            'status_perkawinan', 'alamat_asal',
        ];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 7; // Excel row (rows 1-3 instructions, 4 empty, 5 header, 6 description, 7+ data)
            $rowErrors = [];

            // Skip rows without essential identifiers (empty rows, formatting artifacts, or example rows not deleted)
            $nik = trim((string) ($row['nik'] ?? ''));
            $noKkRaw = trim((string) ($row['no_kk'] ?? ''));
            if ($nik === '' && $noKkRaw === '') {
                continue;
            }

            $processedRowCount++;

            // --- Required fields ---
            foreach ($requiredFields as $field) {
                $value = $row[$field] ?? null;
                if ($value === null || $value === '') {
                    $rowErrors[] = ['column' => $field, 'message' => "Kolom {$field} wajib diisi."];
                }
            }

            // --- NIK: 16 digits ---
            $nik = (string) ($row['nik'] ?? '');
            if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
                $rowErrors[] = ['column' => 'nik', 'message' => 'NIK harus 16 digit angka.'];
            }
            if ($nik !== '' && in_array($nik, $existingActiveNiks)) {
                $rowErrors[] = ['column' => 'nik', 'message' => 'NIK sudah terdaftar di database.'];
            }
            if ($nik !== '' && isset($seenNiks[$nik])) {
                $rowErrors[] = ['column' => 'nik', 'message' => "NIK duplikat dalam file (sama dengan baris {$seenNiks[$nik]})."];
            }
            if ($nik !== '') {
                $seenNiks[$nik] = $rowNum;
            }

            // --- No KK: 16 digits ---
            $noKk = (string) ($row['no_kk'] ?? '');
            if ($noKk !== '' && !preg_match('/^\d{16}$/', $noKk)) {
                $rowErrors[] = ['column' => 'no_kk', 'message' => 'No KK harus 16 digit angka.'];
            }

            // --- RT territory ---
            $rtId = $row['rt_id'] ?? null;
            if ($rtId !== null && $rtId !== '') {
                $rtId = (int) $rtId;
                if (!in_array($rtId, $allowedRtIds)) {
                    $rowErrors[] = ['column' => 'rt_id', 'message' => 'RT tidak termasuk dalam wilayah desa Anda.'];
                }
            }

            // --- Master data FK ---
            $agama = $row['agama'] ?? '';
            if ($agama !== '' && !in_array($agama, $validAgamaCodes)) {
                $rowErrors[] = ['column' => 'agama', 'message' => "Kode agama '{$agama}' tidak valid. Lihat sheet Kode Referensi."];
            }

            $pendidikan = $row['pendidikan'] ?? '';
            if ($pendidikan !== '' && !in_array($pendidikan, $validPendidikanCodes)) {
                $rowErrors[] = ['column' => 'pendidikan', 'message' => "Kode pendidikan '{$pendidikan}' tidak valid."];
            }

            $pekerjaan = $row['pekerjaan'] ?? '';
            if ($pekerjaan !== '' && !in_array($pekerjaan, $validPekerjaanCodes)) {
                $rowErrors[] = ['column' => 'pekerjaan', 'message' => "Kode pekerjaan '{$pekerjaan}' tidak valid."];
            }

            $goldar = $row['golongan_darah'] ?? '';
            if ($goldar !== '' && !in_array($goldar, $validGoldarCodes)) {
                $rowErrors[] = ['column' => 'golongan_darah', 'message' => "Kode golongan darah '{$goldar}' tidak valid."];
            }

            $hubungan = strtoupper((string) ($row['hubungan_keluarga'] ?? ''));
            if ($hubungan !== '' && !in_array($hubungan, $validHubunganCodes)) {
                $rowErrors[] = ['column' => 'hubungan_keluarga', 'message' => "Kode hubungan keluarga '{$hubungan}' tidak valid."];
            }

            // --- Jenis kelamin ---
            $jk = $row['jenis_kelamin'] ?? '';
            if ($jk !== '' && !in_array($jk, ['L', 'P'])) {
                $rowErrors[] = ['column' => 'jenis_kelamin', 'message' => 'Jenis kelamin harus L atau P.'];
            }

            // --- Status perkawinan ---
            $perkawinan = $row['status_perkawinan'] ?? '';
            if ($perkawinan !== '' && !in_array($perkawinan, $validPerkawinan)) {
                $rowErrors[] = ['column' => 'status_perkawinan', 'message' => "Status perkawinan '{$perkawinan}' tidak valid."];
            }

            // --- Tanggal lahir ---
            $tglLahir = $row['tgl_lahir'] ?? '';
            if ($tglLahir !== '') {
                try {
                    $parsed = Carbon::parse($tglLahir);
                    if ($parsed->isFuture()) {
                        $rowErrors[] = ['column' => 'tgl_lahir', 'message' => 'Tanggal lahir tidak boleh di masa depan.'];
                    }
                } catch (\Exception $e) {
                    $rowErrors[] = ['column' => 'tgl_lahir', 'message' => 'Format tanggal lahir tidak valid (gunakan YYYY-MM-DD).'];
                }
            }

            // --- No HP uniqueness ---
            $noHp = $row['no_hp'] ?? '';
            if ($noHp !== '') {
                if (in_array($noHp, $existingPhones)) {
                    $rowErrors[] = ['column' => 'no_hp', 'message' => 'No HP sudah terdaftar di database.'];
                }
                if (isset($seenPhones[$noHp])) {
                    $rowErrors[] = ['column' => 'no_hp', 'message' => "No HP duplikat dalam file (sama dengan baris {$seenPhones[$noHp]})."];
                }
                $seenPhones[$noHp] = $rowNum;
            }

            // --- Email uniqueness ---
            $email = $row['email'] ?? '';
            if ($email !== '') {
                if (in_array($email, $existingEmails)) {
                    $rowErrors[] = ['column' => 'email', 'message' => 'Email sudah terdaftar di database.'];
                }
                if (isset($seenEmails[$email])) {
                    $rowErrors[] = ['column' => 'email', 'message' => "Email duplikat dalam file (sama dengan baris {$seenEmails[$email]})."];
                }
                $seenEmails[$email] = $rowNum;
            }

            // Track KK grouping
            if ($noKk !== '') {
                $kkGroups[$noKk][] = [
                    'rowNum' => $rowNum,
                    'hubungan' => $hubungan,
                    'rt_id' => $rtId !== null && $rtId !== '' ? (int) $rtId : null,
                    'alamat_kk' => $row['alamat_kk'] ?? '',
                ];
            }

            if (!empty($rowErrors)) {
                $errors[$rowNum] = $rowErrors;
            }
        }

        // 5. Cross-row KK group validation
        $newKkCount = 0;
        $existingKkCount = 0;

        foreach ($kkGroups as $noKk => $members) {
            $noKk       = (string) $noKk; // Cast to string: PHP auto-converts numeric string keys to int
            $existingKk = $existingKks->get($noKk);

            if ($existingKk) {
                $existingKkCount++;
                $this->validateExistingKkGroup($existingKk, $members, $errors);
            } else {
                $newKkCount++;
                $this->validateNewKkGroup($noKk, $members, $errors);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'summary' => [
                'total_rows' => $processedRowCount,
                'new_kk_count' => $newKkCount,
                'existing_kk_count' => $existingKkCount,
                'error_count' => count($errors),
            ],
        ];
    }

    /**
     * Phase 2: Execute import in a single DB transaction.
     *
     * @return array{imported_count: int, event_ids: array}
     */
    public function execute(Collection $rows, User $actor): array
    {
        return DB::transaction(function () use ($rows, $actor) {
            $createdCount = 0;
            $createdEventIds = [];

            // Filter rows without essential identifiers (same logic as validate)
            $validRows = $rows->filter(function ($row) {
                $nik = trim((string) ($row['nik'] ?? ''));
                $noKk = trim((string) ($row['no_kk'] ?? ''));
                return $nik !== '' && $noKk !== '';
            });

            // Group by no_kk
            $grouped = $validRows->groupBy('no_kk');

            foreach ($grouped as $noKk => $kkRows) {
                $noKk = (string) $noKk; // PHP auto-converts numeric string keys to int
                // Sort: KEPALA_KELUARGA first to ensure they're created before other members
                $sorted = $kkRows->sortBy(function ($r) {
                    return strtoupper((string) ($r['hubungan_keluarga'] ?? '')) === 'KEPALA_KELUARGA' ? 0 : 1;
                });

                $rtId = (int) $sorted->first()['rt_id'];

                // Resolve or create KK
                $kk = KartuKeluarga::where('no_kk', (string) $noKk)->first();
                $alamatKk = $sorted->first(fn($r) => !empty($r['alamat_kk']))['alamat_kk'] ?? '';

                if (!$kk) {
                    // Create new KK
                    $kk = KartuKeluarga::create([
                        'no_kk' => (string) $noKk,
                        'alamat' => $alamatKk,
                        'rt_id' => $rtId,
                        'status_kk' => 'AKTIF',
                        'tanggal_terbentuk' => now()->toDateString(),
                        'created_by' => $actor->id,
                    ]);
                } elseif ($kk->status_kk === 'NON_AKTIF') {
                    // Reactivate NON_AKTIF KK
                    $kk->update([
                        'status_kk' => 'AKTIF',
                        'rt_id' => $rtId,
                        'alamat' => $alamatKk ?: $kk->alamat,
                        'updated_by' => $actor->id,
                    ]);
                }

                // Resolve territory once per KK group
                $rt = Rt::with('rw')->findOrFail($rtId);

                foreach ($sorted as $row) {
                    $hubungan = strtoupper((string) ($row['hubungan_keluarga'] ?? 'LAINNYA'));
                    $isKepala = $hubungan === 'KEPALA_KELUARGA';
                    $nik = (string) $row['nik'];

                    // 1. Create Event (DATANG, DRAFT)
                    $event = Event::create([
                        'event_type_code' => 'DATANG',
                        'penduduk_id' => null,
                        'event_date' => now()->toDateString(),
                        'keterangan' => 'Import bulk penduduk',
                        'rt_id' => $rtId,
                        'rw_id' => $rt->rw_id,
                        'desa_id' => $rt->rw->desa_id,
                        'kk_id' => $kk->id,
                        'status_data' => 'DRAFT',
                        'created_by' => $actor->id,
                    ]);

                    // 2. Create EventDatang detail
                    EventDatang::create([
                        'event_id' => $event->id,
                        'alamat_asal' => $row['alamat_asal'] ?? '',
                        'alasan_datang' => 'Import data penduduk',
                        'jenis_kedatangan' => 'PENDATANG_BARU',
                        'kk_tujuan_id' => $kk->id,
                    ]);

                    // 3. Handle Penduduk (create new or restore soft-deleted)
                    $penduduk = $this->handlePenduduk($row, $event, $actor);

                    // 4. Update event with penduduk_id
                    $event->update(['penduduk_id' => $penduduk->id]);

                    // 5. Create KK membership
                    KkMember::create([
                        'kartu_keluarga_id' => $kk->id,
                        'penduduk_id' => $penduduk->id,
                        'hubungan_keluarga_code' => $hubungan,
                        'is_kepala_keluarga' => $isKepala,
                        'tanggal_masuk' => now()->toDateString(),
                        'status' => 'AKTIF',
                        'created_by' => $actor->id,
                    ]);

                    $createdCount++;
                    $createdEventIds[] = $event->id;
                }
            }

            // Fire audit event
            event(new DataImported(Penduduk::class, $createdCount));

            return [
                'imported_count' => $createdCount,
                'event_ids' => $createdEventIds,
            ];
        });
    }

    /**
     * Resolve all RT IDs within the admin's desa territory.
     */
    private function resolveAllowedRtIds(User $actor): array
    {
        $desaId = $actor->desa_id
            ?? $actor->rw?->desa_id
            ?? $actor->rt?->rw?->desa_id;

        if (!$desaId) {
            return [];
        }

        return Rt::whereHas('rw', fn($q) => $q->where('desa_id', $desaId))
            ->pluck('id')
            ->toArray();
    }

    /**
     * Validate KK group where KK already exists in DB.
     * Handles both AKTIF and NON_AKTIF KK states.
     */
    private function validateExistingKkGroup(KartuKeluarga $existingKk, array $members, array &$errors): void
    {
        $activeMemberCount = KkMember::where('kartu_keluarga_id', $existingKk->id)
            ->where('status', 'AKTIF')
            ->count();

        $isNonAktif = $existingKk->status_kk === 'NON_AKTIF' && $activeMemberCount === 0;

        if ($isNonAktif) {
            // KK NON_AKTIF tanpa member → diperlakukan mirip KK baru
            // RT boleh beda (akan diupdate saat execute)
            // Tapi semua member import harus RT sama
            $rtIds = collect($members)->pluck('rt_id')->filter()->unique();
            if ($rtIds->count() > 1) {
                foreach ($members as $member) {
                    $errors[$member['rowNum']][] = [
                        'column' => 'rt_id',
                        'message' => 'Semua anggota untuk KK ini harus memiliki RT yang sama.',
                    ];
                }
            }

            // Wajib ada 1 KEPALA_KELUARGA (KK kosong)
            $kepalaRows = collect($members)->where('hubungan', 'KEPALA_KELUARGA');
            if ($kepalaRows->count() === 0) {
                $errors[$members[0]['rowNum']][] = [
                    'column' => 'hubungan_keluarga',
                    'message' => 'KK ini berstatus NON_AKTIF dan tidak memiliki anggota. Harus ada tepat 1 KEPALA_KELUARGA.',
                ];
            } elseif ($kepalaRows->count() > 1) {
                foreach ($kepalaRows as $k) {
                    $errors[$k['rowNum']][] = [
                        'column' => 'hubungan_keluarga',
                        'message' => 'Hanya boleh ada satu KEPALA_KELUARGA.',
                    ];
                }
            }

            // Wajib ada alamat_kk (untuk update KK)
            $hasAlamat = collect($members)->contains(fn($m) => !empty($m['alamat_kk']));
            if (!$hasAlamat) {
                $errors[$members[0]['rowNum']][] = [
                    'column' => 'alamat_kk',
                    'message' => 'KK NON_AKTIF perlu alamat baru (kolom alamat_kk wajib diisi minimal di satu baris).',
                ];
            }

            return;
        }

        // KK AKTIF: RT consistency — all imported members must match existing KK's RT
        foreach ($members as $member) {
            if ($member['rt_id'] !== null && $member['rt_id'] !== (int) $existingKk->rt_id) {
                $errors[$member['rowNum']][] = [
                    'column' => 'rt_id',
                    'message' => "RT harus sama dengan RT KK yang sudah ada (RT ID: {$existingKk->rt_id}).",
                ];
            }
        }

        // Check existing kepala
        $hasExistingKepala = KkMember::where('kartu_keluarga_id', $existingKk->id)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->exists();

        $fileKepalaRows = collect($members)->where('hubungan', 'KEPALA_KELUARGA');
        $fileKepalaCount = $fileKepalaRows->count();

        if ($hasExistingKepala && $fileKepalaCount > 0) {
            foreach ($fileKepalaRows as $k) {
                $errors[$k['rowNum']][] = [
                    'column' => 'hubungan_keluarga',
                    'message' => 'KK ini sudah memiliki kepala keluarga aktif.',
                ];
            }
        }

        if (!$hasExistingKepala && $fileKepalaCount === 0) {
            $errors[$members[0]['rowNum']][] = [
                'column' => 'hubungan_keluarga',
                'message' => 'KK ini tidak memiliki kepala keluarga. Salah satu anggota harus memiliki hubungan KEPALA_KELUARGA.',
            ];
        }
    }

    /**
     * Validate KK group where KK is new (will be created).
     */
    private function validateNewKkGroup(string $noKk, array $members, array &$errors): void
    {
        // Must have exactly one KEPALA_KELUARGA
        $kepalaRows = collect($members)->where('hubungan', 'KEPALA_KELUARGA');

        if ($kepalaRows->count() === 0) {
            $errors[$members[0]['rowNum']][] = [
                'column' => 'hubungan_keluarga',
                'message' => 'KK baru harus memiliki tepat satu anggota dengan hubungan KEPALA_KELUARGA.',
            ];
        } elseif ($kepalaRows->count() > 1) {
            foreach ($kepalaRows as $k) {
                $errors[$k['rowNum']][] = [
                    'column' => 'hubungan_keluarga',
                    'message' => 'KK baru hanya boleh memiliki satu KEPALA_KELUARGA.',
                ];
            }
        }

        // All members must have same rt_id
        $rtIds = collect($members)->pluck('rt_id')->filter()->unique();
        if ($rtIds->count() > 1) {
            foreach ($members as $member) {
                $errors[$member['rowNum']][] = [
                    'column' => 'rt_id',
                    'message' => 'Semua anggota KK baru harus memiliki RT yang sama.',
                ];
            }
        }

        // At least one row must have alamat_kk
        $hasAlamat = collect($members)->contains(fn($m) => !empty($m['alamat_kk']));
        if (!$hasAlamat) {
            $errors[$members[0]['rowNum']][] = [
                'column' => 'alamat_kk',
                'message' => 'KK baru harus memiliki alamat (kolom alamat_kk wajib diisi minimal di satu baris).',
            ];
        }
    }

    /**
     * Create new penduduk or restore soft-deleted one.
     * Same pattern as CreateEventDatangAction::handlePenduduk()
     */
    private function handlePenduduk(array $row, Event $event, User $actor): Penduduk
    {
        $nik = (string) $row['nik'];

        // Check for soft-deleted record with same NIK (restore pattern)
        $softDeleted = $this->pendudukRepo->findByNikWithTrashed($nik);

        if ($softDeleted && $softDeleted->trashed()) {
            Log::info('Import: restoring soft-deleted penduduk', [
                'nik' => $nik,
                'event_id' => $event->id,
            ]);

            $softDeleted->restore();

            $this->pendudukRepo->update($softDeleted, $this->buildPendudukData($row, $event, $actor));

            return $softDeleted->fresh();
        }

        return $this->pendudukRepo->create(
            array_merge($this->buildPendudukData($row, $event, $actor), ['nik' => $nik])
        );
    }

    /**
     * Build penduduk data array from import row.
     */
    private function buildPendudukData(array $row, Event $event, User $actor): array
    {
        $tglLahir = null;
        if (!empty($row['tgl_lahir'])) {
            try {
                $tglLahir = Carbon::parse($row['tgl_lahir'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Skip invalid date
            }
        }

        return [
            'nama_lengkap' => $row['nama_lengkap'] ?? '',
            'jenis_kelamin' => $row['jenis_kelamin'] ?? 'L',
            'tempat_lahir' => $row['tempat_lahir'] ?? '',
            'tgl_lahir' => $tglLahir,
            'agama_id' => $row['agama'] ?? null,
            'pendidikan_id' => !empty($row['pendidikan']) ? $row['pendidikan'] : null,
            'pekerjaan_id' => !empty($row['pekerjaan']) ? $row['pekerjaan'] : null,
            'golongan_darah_id' => !empty($row['golongan_darah']) ? $row['golongan_darah'] : null,
            'nama_ayah' => !empty($row['nama_ayah']) ? $row['nama_ayah'] : null,
            'nama_ibu' => !empty($row['nama_ibu']) ? $row['nama_ibu'] : null,
            'no_hp' => !empty($row['no_hp']) ? $row['no_hp'] : null,
            'email' => !empty($row['email']) ? $row['email'] : null,
            'status_perkawinan' => $row['status_perkawinan'] ?? null,
            'rt_id' => (int) ($row['rt_id'] ?? 0),
            'status_kependudukan_code' => 'AKTIF',
            'current_event_id' => $event->id,
            'tanggal_status' => now()->toDateString(),
            'created_by' => $actor->id,
        ];
    }
}
