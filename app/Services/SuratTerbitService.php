<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SuratTerbit;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\User;
use App\DTOs\SuratTerbitDTO;
use App\Traits\ValidatesTerritory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

/**
 * SuratTerbit business logic service
 * 
 * Handles all CRUD operations and business rules for SuratTerbit entity.
 * Integrates with SequenceGeneratorService for automatic numbering.
 * Enforces territory-aware operations and audit compliance.
 * 
 * @author System Generator  
 * @since 2026-02-20
 */
class SuratTerbitService
{
    use ValidatesTerritory;

    public function __construct(
        private readonly SequenceGeneratorService $sequenceGenerator
    ) {}

    /**
     * Create new surat with automatic sequence generation
     * 
     * @param array $data Surat data
     * @return SuratTerbit Created surat instance
     * @throws Exception When creation fails
     */
    public function createSurat(array $data): SuratTerbit
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return $this->createSuratWithinTransaction($data);
            } catch (QueryException $e) {
                if (!$this->isDuplicateNomorSurat($e) || $attempt === 3) {
                    throw $e;
                }
            }
        }

        throw new Exception('Surat gagal diterbitkan setelah mencoba membuat nomor surat unik.');
    }

    private function createSuratWithinTransaction(array $data): SuratTerbit
    {
        return DB::transaction(function () use ($data) {
            // Validate required fields
            $this->validateSuratData($data);

            // Get jenis surat for business rules
            $jenisSurat = JenisSurat::findOrFail($data['jenis_surat_kode']);

            // Extract data_surat payload (template dynamic fields + internal fields)
            $dataSuratPayload = $this->extractDataSuratPayload($data, $jenisSurat);

            // Validate penduduk exists and is in same territory
            $penduduk = Penduduk::with('kkMembers')->findOrFail($data['penduduk_id']);

            // Guard: surat hanya untuk penduduk aktif
            if ($penduduk->status_kependudukan_code !== 'AKTIF') {
                throw new \DomainException('Surat hanya dapat diterbitkan untuk penduduk dengan status AKTIF.');
            }

            // Guard: penduduk harus memiliki KK aktif
            $activeKkMember = $penduduk->kkMembers()->where('status', 'AKTIF')->first();
            if (!$activeKkMember) {
                throw new \DomainException('Surat hanya dapat diterbitkan untuk penduduk yang terdaftar dalam Kartu Keluarga aktif.');
            }

            $this->validateTerritoryAccess($penduduk);

            // ✅ CRITICAL: Check CREATE permissions (admin RT/RW are read-only)
            $this->validateCreatePermissions(Auth::user(), $penduduk);

            // Generate sequence and format nomor (use penduduk's desa for consistency)
            $desaKode = $penduduk->rt?->rw?->desa?->kode_desa;
            if (!$desaKode) {
                throw new Exception('Penduduk does not have valid territory assignment');
            }

            $sequenceData = $this->sequenceGenerator->generateSuratNumber(
                $data['jenis_surat_kode'],
                $desaKode,
                $data['tahun'] ?? null,
                $data['bulan'] ?? null
            );

            $masaBerlakuHari = $this->resolveMasaBerlakuHari($data, $jenisSurat);
            $tanggalTerbit = Carbon::parse($data['tanggal_terbit'] ?? now());
            $tanggalExpiry = $masaBerlakuHari !== null && $masaBerlakuHari > 0
                ? $tanggalTerbit->copy()->addDays($masaBerlakuHari)
                : null;

            unset($data['masa_berlaku_khusus']);

            // Create surat with generated data
            $suratData = array_merge($data, [
                'nomor_surat' => $sequenceData['formatted'],
                'masa_berlaku_hari' => $masaBerlakuHari,
                'tanggal_kadaluarsa' => $tanggalExpiry,
                'status' => 'AKTIF',
                'pdf_status' => 'PROCESSING',
                'data_surat' => $dataSuratPayload,
                'kk_id' => $activeKkMember->kartu_keluarga_id,  // KK from active membership
                'rt_id' => $penduduk->rt_id,  // Territory from penduduk
                'rw_id' => $penduduk->rt?->rw_id,
                'desa_id' => $penduduk->rt?->rw?->desa_id,
                'created_by' => Auth::id(),
            ]);

            $surat = SuratTerbit::create($suratData);

            // Load relationships for response
            $surat->load(['jenisSurat', 'penduduk', 'desa']);

            return $surat;
        });
    }

    private function isDuplicateNomorSurat(QueryException $e): bool
    {
        $message = $e->getMessage();
        $isIntegrityError = (string) $e->getCode() === '23000'
            || in_array($e->errorInfo[1] ?? null, [1062, 19, 2067], true);

        return $isIntegrityError
            && (str_contains($message, 'surat_terbit_nomor_surat_unique')
            || str_contains($message, 'Duplicate entry')
            || str_contains($message, 'nomor_surat'));
    }

    /**
     * Update existing surat
     * 
     * @param int $id Surat ID
     * @param array $data Update data
     * @return SuratTerbit Updated surat
     * @throws Exception When update fails
     */
    public function updateSurat(int $id, array $data): SuratTerbit
    {
        throw new AuthorizationException(
            'Surat yang sudah diterbitkan tidak dapat diubah. Batalkan surat lalu terbitkan ulang.'
        );

        return DB::transaction(function () use ($id, $data) {
            $surat = SuratTerbit::findOrFail($id);

            // Territory check
            $this->validateTerritoryAccess($surat);

            // ✅ CRITICAL: Check UPDATE permissions (admin RT/RW are read-only)
            $this->validateUpdatePermissions(Auth::user(), $surat);

            // Validate update data
            $this->validateSuratUpdateData($data, $surat);

            // Resolve jenis surat for data_surat extraction (may be unchanged)
            $jenisSuratForData = isset($data['jenis_surat_kode'])
                ? JenisSurat::findOrFail($data['jenis_surat_kode'])
                : $surat->jenisSurat;

            $dataSuratPayload = $this->extractDataSuratPayload($data, $jenisSuratForData, $surat->data_surat ?? []);

            // Handle jenis surat change (requires new sequence)
            if (isset($data['jenis_surat_kode']) && $data['jenis_surat_kode'] !== $surat->jenis_surat_kode) {
                // Get year/month from existing tanggal_terbit
                $tanggalTerbit = Carbon::parse($surat->tanggal_terbit);
                $sequenceData = $this->sequenceGenerator->generateSuratNumber(
                    $data['jenis_surat_kode'],
                    $surat->desa->kode_desa,  // Use existing desa kode
                    $tanggalTerbit->year,
                    $tanggalTerbit->month
                );

                $data['nomor_surat'] = $sequenceData['formatted'];

                // Recalculate expiry if needed
                $jenisSurat = JenisSurat::findOrFail($data['jenis_surat_kode']);
                if ($jenisSurat->masa_berlaku_hari > 0) {
                    $tanggalTerbit = Carbon::parse($data['tanggal_terbit'] ?? $surat->tanggal_terbit);
                    $data['tanggal_kadaluarsa'] = $tanggalTerbit->addDays($jenisSurat->masa_berlaku_hari);
                }
            }

            // Handle tanggal_terbit change (requires expiry recalculation)
            if (isset($data['tanggal_terbit']) && $data['tanggal_terbit'] !== $surat->tanggal_terbit->toDateString()) {
                $jenisSurat = $surat->jenisSurat;
                if ($jenisSurat->masa_berlaku_hari > 0) {
                    $tanggalTerbit = Carbon::parse($data['tanggal_terbit']);
                    $data['tanggal_kadaluarsa'] = $tanggalTerbit->addDays($jenisSurat->masa_berlaku_hari);
                }
            }

            if ($dataSuratPayload !== null) {
                $data['data_surat'] = $dataSuratPayload;
            }

            // Reset PDF status if content changes
            $contentFields = ['jenis_surat_kode', 'penduduk_id', 'keperluan', 'keterangan', 'tanggal_terbit', 'data_surat'];
            if (array_intersect_key($data, array_flip($contentFields))) {
                $data['pdf_status'] = 'PROCESSING';
                $data['file_path'] = null;
            }

            $data['updated_by'] = Auth::id();

            $surat->update($data);
            $surat->load(['jenisSurat', 'penduduk', 'desa']);

            return $surat;
        });
    }

    /**
     * Get surat by ID with territory check
     * 
     * @param int $id Surat ID
     * @return SuratTerbit
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When not found or no access
     */
    public function getSuratById(int $id): SuratTerbit
    {
        $surat = SuratTerbit::with(['jenisSurat', 'penduduk', 'desa'])->findOrFail($id);

        $this->validateTerritoryAccess($surat);

        return $surat;
    }

    /**
     * Get paginated surat list with filters and territory scope
     * 
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @param User|null $user User for territory filtering (defaults to Auth::user())
     * @return LengthAwarePaginator
     */
    public function getPaginatedSuratList(array $filters = [], int $perPage = 15, ?User $user = null): LengthAwarePaginator
    {
        $user = $user ?? Auth::user();

        $query = SuratTerbit::with(['jenisSurat', 'penduduk', 'desa'])
            ->forTerritory($user)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['jenis_surat_kode'])) {
            $query->where('jenis_surat_kode', $filters['jenis_surat_kode']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['pdf_status'])) {
            $query->where('pdf_status', $filters['pdf_status']);
        }

        if (!empty($filters['penduduk_id'])) {
            $query->where('penduduk_id', $filters['penduduk_id']);
        }

        if (!empty($filters['tahun'])) {
            $query->whereYear('tanggal_terbit', $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $query->whereMonth('tanggal_terbit', $filters['bulan']);
        }

        // Date range filters
        if (!empty($filters['tanggal_dari'])) {
            $query->whereDate('tanggal_terbit', '>=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_terbit', '<=', $filters['tanggal_sampai']);
        }

        // Search by nomor surat or penduduk name
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nomor_surat', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('penduduk', function ($pq) use ($filters) {
                        $pq->where('nama_lengkap', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('nik', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get surat expiring soon
     * 
     * @param int $daysThreshold Days before expiry to consider "soon"
     * @param int $limit Maximum results
     * @return Collection
     */
    public function getSuratExpiringSoon(int $daysThreshold = 30, int $limit = 10): Collection
    {
        $user = Auth::user();
        $thresholdDate = Carbon::now()->addDays($daysThreshold);

        $query = SuratTerbit::with(['jenisSurat', 'penduduk'])
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereBetween('tanggal_kadaluarsa', [today(), $thresholdDate])
            ->where('status', 'AKTIF')
            ->orderBy('tanggal_kadaluarsa', 'asc')
            ->limit($limit);

        if ($user !== null) {
            $query->forTerritory($user);
        }

        return $query->get();
    }

    /**
     * Soft delete surat
     * 
     * @param int $id Surat ID
     * @return bool Success status
     */
    public function softDeleteSurat(int $id): bool
    {
        throw new AuthorizationException(
            'Surat yang sudah diterbitkan tidak dapat dihapus. Gunakan pembatalan untuk audit.'
        );

        return DB::transaction(function () use ($id) {
            $surat = SuratTerbit::findOrFail($id);

            $this->validateTerritoryAccess($surat);

            // ✅ CRITICAL: Check DELETE permissions (admin RT/RW are read-only)
            $this->validateDeletePermissions(Auth::user(), $surat);

            $surat->update([
                'status' => 'nonaktif',
                'updated_by' => Auth::id(),
            ]);

            return true;
        });
    }

    /**
     * Validate surat data for creation
     * 
     * @param array $data Input data
     * @throws InvalidArgumentException When validation fails
     */
    private function validateSuratData(array $data): void
    {
        $required = ['jenis_surat_kode', 'penduduk_id', 'keperluan'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Field {$field} is required");
            }
        }

        // Validate jenis surat exists
        if (!JenisSurat::where('kode', $data['jenis_surat_kode'])->exists()) {
            throw new InvalidArgumentException('Jenis surat tidak ditemukan');
        }

        // Validate penduduk exists  
        if (!Penduduk::where('id', $data['penduduk_id'])->exists()) {
            throw new InvalidArgumentException('Penduduk tidak ditemukan');
        }
    }

    /**
     * Validate surat update data
     * 
     * @param array $data Update data
     * @param SuratTerbit $surat Existing surat
     * @throws InvalidArgumentException When validation fails
     */
    private function validateSuratUpdateData(array $data, SuratTerbit $surat): void
    {
        // Prevent updating immutable fields
        $immutable = ['nomor_surat', 'sequence_number', 'rt_id', 'rw_id', 'desa_id'];
        foreach ($immutable as $field) {
            if (array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Field {$field} cannot be updated");
            }
        }

        // Validate new jenis surat if provided
        if (isset($data['jenis_surat_kode']) && !JenisSurat::where('kode', $data['jenis_surat_kode'])->exists()) {
            throw new InvalidArgumentException('Jenis surat tidak ditemukan');
        }

        // Validate new penduduk if provided
        if (isset($data['penduduk_id']) && !Penduduk::where('id', $data['penduduk_id'])->exists()) {
            throw new InvalidArgumentException('Penduduk tidak ditemukan');
        }
    }

    /**
     * Validate territory access for penduduk/surat
     * 
     * @param mixed $entity Penduduk or SuratTerbit model
     * @param User|null $user User to validate access for (defaults to Auth::user())
     * @throws Exception When access denied
     */
    private function validateTerritoryAccess($entity, ?User $user = null): void
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new Exception('User authentication required');
        }

        // Super admin has access to everything
        if ($this->isSuperAdmin($user)) {
            return;
        }

        // For SuratTerbit models, check via territory relationships
        if ($entity instanceof SuratTerbit) {
            if (!$this->canAccessSuratTerritory($user, $entity)) {
                throw new Exception('Access denied: Territory restriction - Surat not in your jurisdiction');
            }
            return;
        }

        // For Penduduk models, check via rt relationship
        if ($entity instanceof Penduduk) {
            if (!$this->canAccessPenduduk($user, $entity)) {
                throw new Exception('Access denied: Territory restriction - Penduduk not in your jurisdiction');
            }
            return;
        }

        throw new Exception('Unknown entity type for territory validation');
    }

    /**
     * Check if user can access penduduk based on territory
     * 
     * @param User $user
     * @param Penduduk $penduduk
     * @return bool
     */
    private function canAccessPenduduk(User $user, Penduduk $penduduk): bool
    {
        if ($this->isAdminRt($user)) {
            return $user->rt_id === $penduduk->rt_id;
        }

        if ($this->isAdminRw($user)) {
            return $user->rw_id === $penduduk->rt?->rw_id;
        }

        if ($this->isAdminDesa($user) || $this->isViewer($user)) {
            $userDesaId = $user->desa_id ?? $user->rt?->rw?->desa_id;
            $pendudukDesaId = $penduduk->rt?->rw?->desa_id;
            return $userDesaId === $pendudukDesaId;
        }

        return false;
    }

    private function resolveMasaBerlakuHari(array $data, JenisSurat $jenisSurat): ?int
    {
        if (array_key_exists('masa_berlaku_khusus', $data) && $data['masa_berlaku_khusus'] !== null && $data['masa_berlaku_khusus'] !== '') {
            return (int) $data['masa_berlaku_khusus'];
        }

        return $jenisSurat->masa_berlaku_hari !== null
            ? (int) $jenisSurat->masa_berlaku_hari
            : null;
    }

    /**
     * Extract additional payload for data_surat.
     *
     * @param array $data Request data (will not be mutated)
     * @param JenisSurat $jenisSurat Jenis surat for template category
     * @param array|null $existing Existing data_surat to merge (for update)
     * @return array|null
     */
    private function extractDataSuratPayload(array &$data, JenisSurat $jenisSurat, ?array $existing = null): ?array
    {
        $payload = $existing ?? [];

        // Inline data_surat provided directly overrides/extends existing
        if (isset($data['data_surat']) && is_array($data['data_surat'])) {
            $payload = array_merge($payload, $data['data_surat']);
            unset($data['data_surat']);
        }

        foreach ($this->resolveDynamicFieldNames($jenisSurat) as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        $payload = array_filter(
            $payload,
            fn($value) => !($value === null || $value === '')
        );

        return $payload !== [] ? $payload : null;
    }

    /**
     * @return array<int, string>
     */
    private function resolveDynamicFieldNames(JenisSurat $jenisSurat): array
    {
        $sections = $jenisSurat->getSections();
        $sectionKeys = [
            'data_fields',
            'additional_fields',
            'detail_fields',
            'activity_fields',
            'related_fields',
        ];

        $fields = [];
        foreach ($sectionKeys as $sectionKey) {
            $sectionFields = $sections[$sectionKey] ?? [];
            if (is_array($sectionFields)) {
                $fields = array_merge($fields, $sectionFields);
            }
        }

        if (strtolower($jenisSurat->template_category ?? '') === 'internal') {
            $fields = array_merge($fields, [
                'kepada',
                'alamat_tujuan',
                'perihal',
                'lampiran',
                'nomor_rujukan',
                'nomor_surat_masuk',
                'tanggal_surat_masuk',
                'isi_balasan',
            ]);
        }

        return collect($fields)
            ->filter(fn($field) => is_string($field) && $field !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if user can access surat based on territory
     * 
     * @param User $user
     * @param SuratTerbit $surat
     * @return bool
     */
    private function canAccessSuratTerritory(User $user, SuratTerbit $surat): bool
    {
        if ($this->isAdminRt($user)) {
            return $user->rt_id === $surat->rt_id;
        }

        if ($this->isAdminRw($user)) {
            return $user->rw_id === $surat->rw_id;
        }

        if ($this->isAdminDesa($user) || $this->isViewer($user)) {
            $userDesaId = $user->desa_id ?? $user->rt?->rw?->desa_id;
            return $userDesaId === $surat->desa_id;
        }

        return false;
    }

    /**
     * ✅ FIXED: Validate CREATE permissions - Admin RT/RW are read-only monitoring
     * 
     * @param User $user
     * @param Penduduk $penduduk
     * @throws Exception When create not allowed
     */
    private function validateCreatePermissions(User $user, Penduduk $penduduk): void
    {
        // ❌ Super admin is monitoring-only — cannot create surat
        // Matches SuratTerbitPolicy::create() which returns false for super_admin
        if ($this->isSuperAdmin($user)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Super admin tidak dapat menerbitkan surat (monitoring-only).'
            );
        }

        // ❌ Admin RT cannot create surat (read-only monitoring)
        if ($this->isAdminRt($user)) {
            throw new Exception('Access denied: Admin RT role is read-only monitoring, cannot create surat');
        }

        // ❌ Admin RW cannot create surat (read-only monitoring) 
        if ($this->isAdminRw($user)) {
            throw new Exception('Access denied: Admin RW role is read-only monitoring, cannot create surat');
        }

        // ✅ Only Admin Desa can create surat in their territory
        if ($this->isAdminDesa($user)) {
            $userDesaId = $user->desa_id;
            $pendudukDesaId = $penduduk->rt?->rw?->desa_id;

            if ($userDesaId !== $pendudukDesaId) {
                throw new Exception('Access denied: Cannot create surat for penduduk outside your desa');
            }
            return;
        }

        // ❌ Viewer cannot create
        throw new Exception('Access denied: Insufficient permissions to create surat');
    }

    /**
     * ✅ FIXED: Validate UPDATE permissions - Admin RT/RW are read-only monitoring
     * ✅ SECURITY: super_admin is monitoring-only (consistent with createSurat)
     * 
     * @param User $user
     * @param SuratTerbit $surat
     * @throws Exception When update not allowed
     */
    private function validateUpdatePermissions(User $user, SuratTerbit $surat): void
    {
        // ❌ Super admin is monitoring-only — cannot update surat
        // Consistent with validateCreatePermissions() and SuratTerbitPolicy
        if ($this->isSuperAdmin($user)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Super admin tidak dapat mengubah surat (monitoring-only).'
            );
        }

        // ❌ Admin RT cannot update surat (read-only monitoring)
        if ($this->isAdminRt($user)) {
            throw new Exception('Access denied: Admin RT role is read-only monitoring, cannot update surat');
        }

        // ❌ Admin RW cannot update surat (read-only monitoring)
        if ($this->isAdminRw($user)) {
            throw new Exception('Access denied: Admin RW role is read-only monitoring, cannot update surat');
        }

        // ✅ Only Admin Desa can update surat in their territory
        if ($this->isAdminDesa($user)) {
            if ($user->desa_id !== $surat->desa_id) {
                throw new Exception('Access denied: Cannot update surat outside your desa');
            }
            return;
        }

        // ❌ Viewer cannot update
        throw new Exception('Access denied: Insufficient permissions to update surat');
    }

    /**
     * ✅ FIXED: Validate DELETE permissions - Admin RT/RW are read-only monitoring
     * ✅ SECURITY: super_admin is monitoring-only (consistent with createSurat)
     * 
     * @param User $user
     * @param SuratTerbit $surat
     * @throws Exception When delete not allowed
     */
    private function validateDeletePermissions(User $user, SuratTerbit $surat): void
    {
        // ❌ Super admin is monitoring-only — cannot delete surat
        // Consistent with validateCreatePermissions() and validateUpdatePermissions()
        if ($this->isSuperAdmin($user)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Super admin tidak dapat menghapus surat (monitoring-only).'
            );
        }

        // ❌ Admin RT cannot delete surat (read-only monitoring)
        if ($this->isAdminRt($user)) {
            throw new Exception('Access denied: Admin RT role is read-only monitoring, cannot delete surat');
        }

        // ❌ Admin RW cannot delete surat (read-only monitoring)
        if ($this->isAdminRw($user)) {
            throw new Exception('Access denied: Admin RW role is read-only monitoring, cannot delete surat');
        }

        // ✅ Only Admin Desa can delete surat in their territory
        if ($this->isAdminDesa($user)) {
            if ($user->desa_id !== $surat->desa_id) {
                throw new Exception('Access denied: Cannot delete surat outside your desa');
            }
            return;
        }

        // ❌ Viewer cannot delete
        throw new Exception('Access denied: Insufficient permissions to delete surat');
    }

    // ─────────────────────────────────────────────────────────
    // Query helpers (moved from controller to keep controllers thin)
    // ─────────────────────────────────────────────────────────

    /**
     * Get active jenis surat options for filter dropdowns
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JenisSurat>
     */
    public function getActiveJenisSuratOptions(): \Illuminate\Database\Eloquent\Collection
    {
        return JenisSurat::where('is_active', true)
            ->orderBy('nama')
            ->get(['kode', 'nama']);
    }

    /**
     * Get active jenis surat that have templates (for surat creation form)
     * Uses hybrid template system - filters by template_category (not old template_filename)
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JenisSurat>
     */
    public function getActiveJenisSuratWithTemplates(): \Illuminate\Database\Eloquent\Collection
    {
        return JenisSurat::where('is_active', true)
            ->whereNotNull('template_category')
            ->orderBy('nama')
            ->get(['kode', 'nama', 'masa_berlaku_hari', 'deskripsi', 'template_category']);
    }
}
