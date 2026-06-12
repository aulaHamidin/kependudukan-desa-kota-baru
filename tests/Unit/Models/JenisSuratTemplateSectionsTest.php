<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\JenisSurat;
use Tests\TestCase;

class JenisSuratTemplateSectionsTest extends TestCase
{
    public function test_get_sections_reads_array_template_sections(): void
    {
        $jenisSurat = new JenisSurat([
            'kode' => 'TEST',
            'nama' => 'Surat Test',
            'template_category' => 'keterangan',
            'template_sections' => [
                'data_fields' => ['nama_lengkap', 'nik', 'nama_usaha'],
                'additional_fields' => ['alamat_usaha'],
                'body' => 'Isi khusus template.',
                'purpose_label' => 'Dipakai untuk',
            ],
        ]);

        $sections = $jenisSurat->getSections();

        $this->assertSame(['nama_lengkap', 'nik', 'nama_usaha'], $sections['data_fields']);
        $this->assertSame(['alamat_usaha'], $sections['additional_fields']);
        $this->assertSame('Isi khusus template.', $sections['body']);
        $this->assertSame('Dipakai untuk', $sections['purpose_label']);
    }

    public function test_get_sections_reads_normal_json_template_sections(): void
    {
        $sections = [
            'data_fields' => ['nama_lengkap', 'nik', 'jenis_kegiatan'],
            'additional_fields' => ['tanggal_mulai'],
            'body' => 'Izin kegiatan.',
            'purpose_label' => 'Izin ini untuk',
        ];

        $jenisSurat = new JenisSurat();
        $jenisSurat->setRawAttributes([
            'kode' => 'SIK',
            'nama' => 'Surat Izin Keramaian',
            'template_category' => 'izin',
            'template_sections' => json_encode($sections),
            'masa_berlaku_hari' => null,
        ], true);

        $resolved = $jenisSurat->getSections();

        $this->assertSame($sections['data_fields'], $resolved['data_fields']);
        $this->assertSame($sections['additional_fields'], $resolved['additional_fields']);
        $this->assertSame($sections['body'], $resolved['body']);
        $this->assertSame($sections['purpose_label'], $resolved['purpose_label']);
    }

    public function test_get_sections_reads_legacy_double_encoded_json_template_sections(): void
    {
        $sections = [
            'data_fields' => ['nama_lengkap', 'nik', 'nama_calon_pasangan'],
            'additional_fields' => ['nik_calon_pasangan'],
            'body' => 'Keterangan nikah.',
            'purpose_label' => 'Surat ini dibuat untuk',
        ];

        $jenisSurat = new JenisSurat();
        $jenisSurat->setRawAttributes([
            'kode' => 'SKN',
            'nama' => 'Surat Keterangan Nikah',
            'template_category' => 'pengantar',
            'template_sections' => json_encode(json_encode($sections)),
            'masa_berlaku_hari' => 30,
        ], true);

        $resolved = $jenisSurat->getSections();

        $this->assertSame($sections['data_fields'], $resolved['data_fields']);
        $this->assertSame($sections['additional_fields'], $resolved['additional_fields']);
        $this->assertSame($sections['body'], $resolved['body']);
        $this->assertSame($sections['purpose_label'], $resolved['purpose_label']);
    }
}
