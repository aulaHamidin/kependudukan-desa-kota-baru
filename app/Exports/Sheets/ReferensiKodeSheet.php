<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Models\Agama;
use App\Models\GolonganDarah;
use App\Models\HubunganKeluarga;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReferensiKodeSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'Kode Referensi';
    }

    public function array(): array
    {
        $rows = [];

        // Section: Agama
        $rows[] = ['AGAMA (kolom agama)', '', ''];
        $rows[] = ['Kode', 'Nama', ''];
        foreach (Agama::orderBy('kode')->get() as $item) {
            $rows[] = [$item->kode, $item->nama, ''];
        }
        $rows[] = ['', '', ''];

        // Section: Pendidikan
        $rows[] = ['PENDIDIKAN (kolom pendidikan)', '', ''];
        $rows[] = ['Kode', 'Nama', ''];
        foreach (Pendidikan::orderBy('kode')->get() as $item) {
            $rows[] = [$item->kode, $item->nama, ''];
        }
        $rows[] = ['', '', ''];

        // Section: Pekerjaan
        $rows[] = ['PEKERJAAN (kolom pekerjaan)', '', ''];
        $rows[] = ['Kode', 'Nama', ''];
        foreach (Pekerjaan::orderBy('kode')->get() as $item) {
            $rows[] = [$item->kode, $item->nama, ''];
        }
        $rows[] = ['', '', ''];

        // Section: Golongan Darah
        $rows[] = ['GOLONGAN DARAH (kolom golongan_darah)', '', ''];
        $rows[] = ['Kode', 'Nama', ''];
        foreach (GolonganDarah::orderBy('kode')->get() as $item) {
            $rows[] = [$item->kode, $item->nama, ''];
        }
        $rows[] = ['', '', ''];

        // Section: Hubungan Keluarga
        $rows[] = ['HUBUNGAN KELUARGA (kolom hubungan_keluarga)', '', ''];
        $rows[] = ['Kode', 'Nama', ''];
        foreach (HubunganKeluarga::orderBy('kode')->get() as $item) {
            $rows[] = [$item->kode, $item->nama, ''];
        }
        $rows[] = ['', '', ''];

        // Section: Status Perkawinan
        $rows[] = ['STATUS PERKAWINAN (kolom status_perkawinan)', '', ''];
        $rows[] = ['Kode', 'Keterangan', ''];
        $rows[] = ['BELUM_KAWIN', 'Belum/Tidak Kawin', ''];
        $rows[] = ['KAWIN', 'Kawin', ''];
        $rows[] = ['CERAI_HIDUP', 'Cerai Hidup', ''];
        $rows[] = ['CERAI_MATI', 'Cerai Mati', ''];
        $rows[] = ['', '', ''];

        // Section: Jenis Kelamin
        $rows[] = ['JENIS KELAMIN (kolom jenis_kelamin)', '', ''];
        $rows[] = ['Kode', 'Keterangan', ''];
        $rows[] = ['L', 'Laki-laki', ''];
        $rows[] = ['P', 'Perempuan', ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Style section headers (rows that have text in column A and empty column B)
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellA = (string) $sheet->getCell("A{$row}")->getValue();
                    $cellB = (string) $sheet->getCell("B{$row}")->getValue();

                    // Section header (contains "kolom")
                    if (str_contains($cellA, 'kolom')) {
                        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003580']],
                        ]);
                    }

                    // Sub-header (Kode | Nama)
                    if ($cellA === 'Kode' && ($cellB === 'Nama' || $cellB === 'Keterangan')) {
                        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        ]);
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(35);
            },
        ];
    }
}
