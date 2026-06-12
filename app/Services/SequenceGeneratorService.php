<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JenisSurat;
use App\Models\SuratNomorSequence;
use App\Models\SuratSequence;
use App\Models\SuratTerbit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Atomic sequence generation service
 * 
 * Handles race-condition safe sequence generation for surat numbering.
 * Uses database-level locking to ensure unique sequences across
 * concurrent requests.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class SequenceGeneratorService
{
    /**
     * Generate next sequence number atomically
     * 
     * This method is thread-safe and handles concurrent requests
     * by using database row-level locking (lockForUpdate).
     * 
     * @param string $jenisSuratKode
     * @param int|null $tahun Default to current year
     * @param int|null $bulan Default to current month
     * @return int The next sequence number
     * @throws Exception When sequence generation fails
     */
    public function generateNextSequence(
        string $jenisSuratKode,
        ?int $tahun = null,
        ?int $bulan = null
    ): int {
        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');

        return DB::transaction(function () use ($jenisSuratKode, $tahun, $bulan) {
            // Find existing sequence with row-level lock
            $sequence = SuratSequence::where('jenis_surat_kode', $jenisSuratKode)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->lockForUpdate()
                ->first();

            if ($sequence) {
                // Increment existing sequence atomically
                $sequence->increment('sequence_number');
                $sequence->update(['last_generated_at' => now()]);
                return $sequence->sequence_number;
            }

            // Create first sequence for this period
            $newSequence = SuratSequence::create([
                'jenis_surat_kode' => $jenisSuratKode,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'sequence_number' => 1,
                'last_generated_at' => now(),
            ]);

            return $newSequence->sequence_number;
        });
    }

    /**
     * Get current sequence number without incrementing
     * 
     * @param string $jenisSuratKode
     * @param int|null $tahun
     * @param int|null $bulan  
     * @return int Current sequence or 0 if none exists
     */
    public function getCurrentSequence(
        string $jenisSuratKode,
        ?int $tahun = null,
        ?int $bulan = null
    ): int {
        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');

        $sequence = SuratSequence::where('jenis_surat_kode', $jenisSuratKode)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        return $sequence?->sequence_number ?? 0;
    }

    /**
     * Generate formatted surat number.
     *
     * Default format follows the Desa Kota Baru Word examples:
     * 145 / 001 / 01.2009 / 2026
     * 
     * @param string $jenisSuratKode
     * @param string $desaKode
     * @param int $sequence
     * @param int|null $tahun
     * @param int|null $bulan
     * @return string Formatted surat number
     */
    public function formatSuratNumber(
        string $jenisSuratKode,
        string $desaKode,
        int $sequence,
        ?int $tahun = null,
        ?int $bulan = null
    ): string {
        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');
        $jenisSurat = JenisSurat::find($jenisSuratKode);
        $prefix = $jenisSurat?->prefix_nomor ?: $jenisSuratKode;
        $format = $jenisSurat?->format_nomor ?: '145 / {sequence3} / {kode_surat} / {year}';
        $kodeSurat = (string) config('app.desa.kode_surat', '01.2009');

        $tokens = [
            '{sequence}' => (string) $sequence,
            '{seq}' => (string) $sequence,
            '{sequence3}' => sprintf('%03d', $sequence),
            '{seq3}' => sprintf('%03d', $sequence),
            '{jenisSuratKode}' => $jenisSuratKode,
            '{jenis_surat_kode}' => $jenisSuratKode,
            '{prefix}' => $prefix,
            '{desaKode}' => $desaKode,
            '{desa_kode}' => $desaKode,
            '{kode_surat}' => $kodeSurat,
            '{month}' => sprintf('%02d', $bulan),
            '{bulan}' => sprintf('%02d', $bulan),
            '{month_roman}' => $this->monthToRoman($bulan),
            '{bulan_romawi}' => $this->monthToRoman($bulan),
            '{year}' => (string) $tahun,
            '{tahun}' => (string) $tahun,
        ];

        return strtr($format, $tokens);
    }

    /**
     * Generate complete surat number atomically
     * 
     * Combines sequence generation and formatting in one operation.
     * This is the main method that controllers should call.
     * 
     * @param string $jenisSuratKode
     * @param string $desaKode Used for formatting only
     * @param int|null $tahun
     * @param int|null $bulan
     * @return array{sequence: int, formatted: string}
     * @throws Exception When generation fails
     */
    public function generateSuratNumber(
        string $jenisSuratKode,
        string $desaKode,
        ?int $tahun = null,
        ?int $bulan = null
    ): array {
        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');
        $kodeSurat = (string) config('app.desa.kode_surat', '01.2009');

        return DB::transaction(function () use ($jenisSuratKode, $desaKode, $tahun, $bulan, $kodeSurat) {
            $this->ensureGlobalNumberSequenceRow($kodeSurat, $tahun);

            $sequenceRow = SuratNomorSequence::forPeriod($kodeSurat, $tahun)
                ->lockForUpdate()
                ->firstOrFail();

            $existingMax = $this->resolveMaxExistingWordSequence($kodeSurat, $tahun);
            if ($sequenceRow->sequence_number < $existingMax) {
                $sequenceRow->sequence_number = $existingMax;
            }

            $attempts = 0;
            do {
                $attempts++;
                if ($attempts > 500) {
                    throw new Exception('Tidak dapat menemukan nomor surat unik setelah 500 percobaan.');
                }

                $sequenceRow->sequence_number++;
                $formatted = $this->formatSuratNumber(
                    $jenisSuratKode,
                    $desaKode,
                    $sequenceRow->sequence_number,
                    $tahun,
                    $bulan
                );
            } while ($this->nomorSuratAlreadyExists($formatted));

            $sequenceRow->last_generated_at = now();
            $sequenceRow->save();

            return [
                'sequence' => $sequenceRow->sequence_number,
                'formatted' => $formatted,
            ];
        });
    }

    private function ensureGlobalNumberSequenceRow(string $kodeSurat, int $tahun): void
    {
        if (SuratNomorSequence::forPeriod($kodeSurat, $tahun)->exists()) {
            return;
        }

        try {
            SuratNomorSequence::create([
                'kode_surat' => $kodeSurat,
                'tahun' => $tahun,
                'sequence_number' => $this->resolveMaxExistingWordSequence($kodeSurat, $tahun),
                'last_generated_at' => null,
            ]);
        } catch (QueryException $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }
        }
    }

    private function resolveMaxExistingWordSequence(string $kodeSurat, int $tahun): int
    {
        return SuratTerbit::withoutGlobalScopes()
            ->withTrashed()
            ->whereYear('tanggal_terbit', $tahun)
            ->where('nomor_surat', 'like', '%' . $kodeSurat . '%' . $tahun . '%')
            ->pluck('nomor_surat')
            ->map(fn(string $nomorSurat): ?int => $this->extractWordSequence($nomorSurat, $kodeSurat, $tahun))
            ->filter(fn(?int $sequence): bool => $sequence !== null)
            ->max() ?? 0;
    }

    private function extractWordSequence(string $nomorSurat, string $kodeSurat, int $tahun): ?int
    {
        $pattern = '/\b(\d{1,9})\s*\/\s*' . preg_quote($kodeSurat, '/') . '\s*\/\s*' . preg_quote((string) $tahun, '/') . '\b/u';

        if (!preg_match($pattern, $nomorSurat, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function nomorSuratAlreadyExists(string $nomorSurat): bool
    {
        return SuratTerbit::withoutGlobalScopes()
            ->withTrashed()
            ->where('nomor_surat', $nomorSurat)
            ->exists();
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return (string) $e->getCode() === '23000'
            || in_array($e->errorInfo[1] ?? null, [1062, 19, 2067], true);
    }

    private function monthToRoman(int $month): string
    {
        $romans = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romans[$month] ?? (string) $month;
    }

    /**
     * Reset sequence for testing purposes (TEST ONLY)
     * 
     * @param string $jenisSuratKode
     * @param int|null $tahun
     * @param int|null $bulan
     * @return bool Success status
     */
    public function resetSequence(
        string $jenisSuratKode,
        ?int $tahun = null,
        ?int $bulan = null
    ): bool {
        if (app()->environment('production')) {
            throw new Exception('Sequence reset not allowed in production');
        }

        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');

        return SuratSequence::where('jenis_surat_kode', $jenisSuratKode)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->delete() > 0;
    }

    /**
     * Get sequence statistics for monitoring
     * 
     * @param int|null $tahun
     * @param int|null $bulan
     * @return array Statistics array
     */
    public function getSequenceStats(
        ?int $tahun = null,
        ?int $bulan = null
    ): array {
        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');

        $stats = SuratSequence::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->selectRaw('jenis_surat_kode, MAX(sequence_number) as max_sequence, COUNT(*) as total_sequences')
            ->groupBy('jenis_surat_kode')
            ->get();

        return [
            'period' => sprintf('%d-%02d', $tahun, $bulan),
            'jenis_surat' => $stats->map(function ($stat) {
                return [
                    'kode' => $stat->jenis_surat_kode,
                    'current_sequence' => $stat->getAttribute('max_sequence'),
                    'total_documents' => $stat->getAttribute('total_sequences'),
                ];
            })->toArray(),
        ];
    }
}
