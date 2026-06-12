<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SuratSequence;
use App\Services\SequenceGeneratorService;
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

    public function test_format_nomor_surat_mengikuti_contoh_word_desa(): void
    {
        config(['app.desa.kode_surat' => '01.2009']);

        $formatted = app(SequenceGeneratorService::class)
            ->formatSuratNumber('SKD', '3201012001', 1, 2026, 6);

        $this->assertSame('145 / 001 / 01.2009 / 2026', $formatted);
    }

    public function test_generate_nomor_surat_word_memakai_sequence_global_antar_jenis(): void
    {
        config(['app.desa.kode_surat' => '01.2009']);

        $service = app(SequenceGeneratorService::class);

        $first = $service->generateSuratNumber('SKD', '3201012001', 2026, 6);
        $second = $service->generateSuratNumber('SKLHR', '3201012001', 2026, 6);

        $this->assertSame('145 / 001 / 01.2009 / 2026', $first['formatted']);
        $this->assertSame('145 / 002 / 01.2009 / 2026', $second['formatted']);
        $this->assertDatabaseHas('surat_nomor_sequences', [
            'kode_surat' => '01.2009',
            'tahun' => 2026,
            'sequence_number' => 2,
        ]);
    }
}
