<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\View;

/**
 * JenisSurat Model - Hybrid Template System
 *
 * Menggunakan pendekatan hybrid:
 * - template_category: memilih file Blade mana (keterangan, pengantar, izin, dll)
 * - template_sections: JSON konfigurasi untuk fields, intro, body, signature
 *
 * @property string $kode
 * @property string $nama
 * @property string|null $deskripsi
 * @property string|null $template_category
 * @property array|null $template_sections
 * @property array|null $required_fields
 * @property string|null $signature_type
 * @property string $prefix_nomor
 * @property string|null $format_nomor
 * @property int|null $masa_berlaku_hari
 * @property array|null $persyaratan
 * @property float|null $biaya_admin
 * @property bool $is_active
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string $template_category_label
 */
class JenisSurat extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'jenis_surat';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Template categories yang tersedia
     */
    public const TEMPLATE_CATEGORIES = [
        'keterangan'  => 'Surat Keterangan (umum)',
        'pengantar'   => 'Surat Pengantar',
        'izin'        => 'Surat Izin',
        'internal'    => 'Surat Internal Desa',
    ];

    /**
     * Signature types yang tersedia
     */
    public const SIGNATURE_TYPES = [
        'kepala_desa' => 'Kepala Desa',
        'sekdes'      => 'Sekretaris Desa',
        'kasi'        => 'Kepala Seksi',
        'dual'        => 'Kepala Desa + Pemohon',
    ];

    /**
     * Field labels untuk template
     */
    public const FIELD_LABELS = [
        'nama_lengkap'    => 'Nama Lengkap',
        'nik'             => 'NIK',
        'tempat_lahir'    => 'Tempat Lahir',
        'tanggal_lahir'   => 'Tanggal Lahir',
        'tempat_tanggal_lahir' => 'Tempat, Tanggal Lahir',
        'jenis_kelamin'   => 'Jenis Kelamin',
        'agama'           => 'Agama',
        'pekerjaan'       => 'Pekerjaan',
        'alamat'          => 'Alamat',
        'alamat_kk'       => 'Alamat Sesuai KK',
        'alamat_domisili' => 'Alamat Domisili',
        'bin_binti'       => 'Bin/Binti',
        'rt'              => 'RT',
        'rw'              => 'RW',
        'desa'            => 'Desa/Kelurahan',
        'kecamatan'       => 'Kecamatan',
        'kabupaten'       => 'Kabupaten/Kota',
        'provinsi'        => 'Provinsi',
        'status_kawin'    => 'Status Perkawinan',
        'status_perkawinan' => 'Status Perkawinan',
        'pendidikan'      => 'Pendidikan Terakhir',
        'kewarganegaraan' => 'Kewarganegaraan',
        'no_kk'           => 'Nomor Kartu Keluarga',
        'tujuan'          => 'Keperluan/Tujuan',
        'instansi_tujuan' => 'Instansi Tujuan',
        'alamat_tujuan'   => 'Alamat Tujuan',
        'alasan_pindah'   => 'Alasan Pindah',
        'nama_bayi'       => 'Nama Bayi',
        'jenis_kelamin_bayi' => 'Jenis Kelamin Bayi',
        'tempat_lahir_bayi' => 'Tempat Lahir Bayi',
        'tanggal_lahir_bayi' => 'Tanggal Lahir Bayi',
        'anak_ke'         => 'Anak Ke',
        'nama_ibu'        => 'Nama Ibu',
        'nik_ibu'         => 'NIK Ibu',
        'nama_ayah'       => 'Nama Ayah',
        'nik_ayah'        => 'NIK Ayah',
        'tanggal_meninggal' => 'Tanggal Meninggal',
        'tempat_meninggal' => 'Tempat Meninggal',
        'sebab_kematian'  => 'Sebab Kematian',
        'nama_calon_pasangan' => 'Nama Calon Pasangan',
        'nik_calon_pasangan' => 'NIK Calon Pasangan',
        'alamat_calon_pasangan' => 'Alamat Calon Pasangan',
        'nama_anak'       => 'Nama Anak',
        'bin_binti_anak'  => 'Bin/Binti Anak',
        'tempat_lahir_anak' => 'Tempat Lahir Anak',
        'tanggal_lahir_anak' => 'Tanggal Lahir Anak',
        'tempat_tanggal_lahir_anak' => 'Tempat, Tanggal Lahir Anak',
        'nik_anak'        => 'NIK Anak',
        'no_kk_anak'      => 'Nomor KK Anak',
        'kewarganegaraan_anak' => 'Kewarganegaraan Anak',
        'agama_anak'      => 'Agama Anak',
        'jenis_kelamin_anak' => 'Jenis Kelamin Anak',
        'pekerjaan_anak'  => 'Pekerjaan Anak',
        'alamat_anak'     => 'Alamat Anak',
        'alamat_domisili_anak' => 'Alamat Domisili Anak',
        'keperluan_program' => 'Keperluan Program',
        'nama_usaha'      => 'Nama Usaha',
        'jenis_usaha'     => 'Jenis Usaha',
        'alamat_usaha'    => 'Alamat Usaha',
        'tahun_berdiri'   => 'Tahun Berdiri',
        'ukuran_tempat_usaha' => 'Ukuran Tempat Usaha',
        'jumlah_tenaga_pembantu' => 'Jumlah Tenaga Pembantu',
        'catatan_usaha'   => 'Catatan Usaha',
        'jenis_kegiatan'  => 'Jenis Kegiatan',
        'tanggal_mulai'   => 'Tanggal Mulai',
        'tanggal_selesai' => 'Tanggal Selesai',
        'waktu'           => 'Waktu',
        'tempat'          => 'Tempat',
        'jumlah_undangan' => 'Perkiraan Jumlah Undangan',
        'kepada'          => 'Kepada Yth.',
        'perihal'         => 'Perihal',
        'lampiran'        => 'Lampiran',
        'nomor_rujukan'   => 'Nomor Surat Rujukan',
        'nomor_surat_masuk' => 'Nomor Surat Masuk',
        'tanggal_surat_masuk' => 'Tanggal Surat Masuk',
        'isi_balasan'     => 'Isi Balasan',
    ];

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'template_category',
        'template_sections',
        'prefix_nomor',
        'format_nomor',
        'masa_berlaku_hari',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'masa_berlaku_hari' => 'integer',
        'is_active'         => 'boolean',
        'template_sections' => 'array',
    ];

    /**
     * ✅ NO global $with - performance compliance (audit requirement)
     */

    /**
     * Relationship: Jenis surat has many surat terbit
     */
    public function suratTerbit(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'jenis_surat_kode', 'kode');
    }

    /**
     * Relationship: Jenis surat has many sequences
     */
    public function suratSequences(): HasMany
    {
        return $this->hasMany(SuratSequence::class, 'jenis_surat_kode', 'kode');
    }

    // =========================================================================
    // HYBRID TEMPLATE SYSTEM METHODS
    // =========================================================================

    /**
     * Validate template category against whitelist
     * 
     * @throws \InvalidArgumentException When category is not in whitelist
     */
    public function validateTemplateCategory(): void
    {
        if (!array_key_exists($this->template_category, self::TEMPLATE_CATEGORIES)) {
            throw new \InvalidArgumentException(
                "Template category '{$this->template_category}' tidak valid. " .
                    "Kategori yang diizinkan: " . implode(', ', array_keys(self::TEMPLATE_CATEGORIES))
            );
        }
    }

    /**
     * Validate required fields are present in data
     * 
     * @param array $data Data yang akan divalidasi
     * @throws \InvalidArgumentException When required fields are missing
     */
    public function validateRequiredFields(array $data): void
    {
        $requiredFields = $this->getSection('required_fields', []);

        if (empty($requiredFields)) {
            // Default required fields for all surat
            $requiredFields = ['nama_lengkap', 'nik'];
        }

        $missing = array_diff($requiredFields, array_keys(array_filter($data, fn($v) => $v !== null && $v !== '')));

        if (!empty($missing)) {
            $labels = array_map(
                fn($field) => self::FIELD_LABELS[$field] ?? $field,
                $missing
            );
            throw new \InvalidArgumentException(
                'Field wajib belum diisi: ' . implode(', ', $labels)
            );
        }
    }

    /**
     * Check if template category Blade file exists
     */
    public function hasTemplate(): bool
    {
        try {
            $this->validateTemplateCategory();
            return View::exists($this->getTemplatePath());
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get full template path for this jenis surat
     * 
     * @throws \InvalidArgumentException When category is not in whitelist
     */
    public function getTemplatePath(): string
    {
        $this->validateTemplateCategory();
        return 'surat.templates._' . $this->template_category;
    }

    /**
     * Get template sections with defaults
     */
    public function getSections(): array
    {
        $templateSections = $this->normalizeTemplateSections($this->template_sections);
        
        return array_merge($this->getDefaultSections(), $templateSections);
    }

    /**
     * Normalize template_sections from current array cast or legacy double-encoded JSON.
     */
    private function normalizeTemplateSections(mixed $templateSections): array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            if (!is_string($templateSections)) {
                break;
            }

            $decoded = json_decode($templateSections, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $templateSections = $decoded;
        }

        return is_array($templateSections) ? $templateSections : [];
    }

    /**
     * Get default sections based on template category
     */
    protected function getDefaultSections(): array
    {
        return [
            'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
            'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
            'body'          => '',
            'purpose_label' => 'Surat ini dibuat untuk keperluan',
            'show_purpose'  => true,
            'masa_berlaku'  => (bool) $this->masa_berlaku_hari,
            'signature'     => 'kepala_desa',
        ];
    }

    /**
     * Get a specific section value
     */
    public function getSection(string $key, mixed $default = null): mixed
    {
        $sections = $this->getSections();

        return $sections[$key] ?? $default;
    }

    /**
     * Get field labels for template rendering
     */
    public function getFieldLabels(): array
    {
        return self::FIELD_LABELS;
    }

    /**
     * Get data fields that should be displayed
     */
    public function getDataFields(): array
    {
        return $this->getSection('data_fields', []);
    }

    /**
     * Get signature type
     */
    public function getSignatureType(): string
    {
        return $this->getSection('signature', 'kepala_desa');
    }

    /**
     * Get signature partial path
     */
    public function getSignaturePartialPath(): string
    {
        return 'surat.templates.partials._signature_' . $this->getSignatureType();
    }

    /**
     * Render the surat template with data
     * 
     * Security: Validates template_category against whitelist before rendering.
     * Validates required fields are present in data.
     *
     * @param array $data Data penduduk dan tambahan
     * @param bool $skipValidation Skip required field validation (for preview)
     * @return \Illuminate\Contracts\View\View
     * @throws \InvalidArgumentException When category invalid or required fields missing
     */
    public function renderTemplate(array $data, bool $skipValidation = false): \Illuminate\Contracts\View\View
    {
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));

        // Security: Validate category whitelist
        $this->validateTemplateCategory();

        // Validate required fields (unless skipped for preview)
        if (!$skipValidation) {
            $this->validateRequiredFields($data);
        }

        // Sanitize data - remove any potential script injection
        $sanitizedData = $this->sanitizeTemplateData($data);

        return view($this->getTemplatePath(), [
            'jenisSurat'  => $this,
            'sections'    => $this->getSections(),
            'fieldLabels' => $this->getFieldLabels(),
            'data'        => $sanitizedData,
            'suratTerbit' => $data['suratTerbit'] ?? null, // Pass suratTerbit for signature templates
        ]);
    }

    /**
     * Sanitize template data to prevent XSS
     */
    protected function sanitizeTemplateData(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                // Strip tags but preserve basic formatting
                return strip_tags($value);
            }
            if (is_array($value)) {
                return $this->sanitizeTemplateData($value);
            }
            return $value;
        }, $data);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Calculate expiry date from now (for new surat)
     */
    public function hitungExpiry(): ?string
    {
        return $this->masa_berlaku_hari
            ? now()->addDays($this->masa_berlaku_hari)->toDateString()
            : null;
    }

    /**
     * Check if ready for surat generation
     */
    public function isReadyForGeneration(): bool
    {
        return $this->is_active && $this->hasTemplate();
    }

    /**
     * Get human-readable template category name
     */
    public function getTemplateCategoryName(): string
    {
        return self::TEMPLATE_CATEGORIES[$this->template_category] ?? $this->template_category;
    }

    /**
     * Scope: Only active jenis surat
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by template category
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('template_category', $category);
    }
}
