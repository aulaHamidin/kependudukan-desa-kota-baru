<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SuratSequence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Generate formatted surat number
     * 
     * Format: {sequence}/{jenisSuratKode}/{desaKode}/{month}/{year}  
     * Example: 001/KK/12345/02/2026
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

        return sprintf(
            '%03d/%s/%s/%02d/%d',
            $sequence,
            $jenisSuratKode,
            $desaKode,
            $bulan,
            $tahun
        );
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
        $sequence = $this->generateNextSequence($jenisSuratKode, $tahun, $bulan);

        $formatted = $this->formatSuratNumber(
            $jenisSuratKode,
            $desaKode,
            $sequence,
            $tahun,
            $bulan
        );

        return [
            'sequence' => $sequence,
            'formatted' => $formatted,
        ];
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
