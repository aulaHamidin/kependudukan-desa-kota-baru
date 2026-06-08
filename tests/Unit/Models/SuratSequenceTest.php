<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SuratSequence;
use Database\Seeders\JenisSuratSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(JenisSuratSeeder::class);
    }

    public function test_sequence_dapat_dibuat_dan_tersimpan(): void
    {
        $seq = SuratSequence::create([
            'jenis_surat_kode' => 'SKD',
            'tahun'            => 2026,
            'bulan'            => 2,
            'sequence_number'  => 1,
        ]);

        $this->assertDatabaseHas('surat_sequence', [
            'jenis_surat_kode' => 'SKD',
            'tahun'            => 2026,
            'bulan'            => 2,
            'sequence_number'  => 1,
        ]);
        $this->assertEquals(1, $seq->sequence_number);
    }

    public function test_exists_for_period_mendeteksi_record_yang_ada(): void
    {
        SuratSequence::create([
            'jenis_surat_kode' => 'SKD',
            'tahun'            => 2026,
            'bulan'            => 2,
            'sequence_number'  => 5,
        ]);

        $this->assertTrue(SuratSequence::existsForPeriod('SKD', 2026, 2));
        $this->assertFalse(SuratSequence::existsForPeriod('SKD', 2026, 3));
        $this->assertFalse(SuratSequence::existsForPeriod('SKD', 2025, 2));
        $this->assertFalse(SuratSequence::existsForPeriod('SKPD', 2026, 2));
    }

    public function test_get_sequence_key_menghasilkan_format_yang_benar(): void
    {
        $this->assertEquals('SKD_2026_02', SuratSequence::getSequenceKey('SKD', 2026, 2));
        $this->assertEquals('SKPD_2025_12', SuratSequence::getSequenceKey('SKPD', 2025, 12));
    }

    public function test_scope_for_period_memfilter_dengan_benar(): void
    {
        SuratSequence::create([
            'jenis_surat_kode' => 'SKD',
            'tahun'            => 2026,
            'bulan'            => 2,
            'sequence_number'  => 1,
        ]);
        SuratSequence::create([
            'jenis_surat_kode' => 'SKPD',
            'tahun'            => 2026,
            'bulan'            => 2,
            'sequence_number'  => 1,
        ]);

        $results = SuratSequence::forPeriod('SKD', 2026, 2)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('SKD', $results->first()->jenis_surat_kode);
    }
}
