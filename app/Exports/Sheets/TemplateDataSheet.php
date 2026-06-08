<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateDataSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'Data Penduduk';
    }

    public function array(): array
    {
        return [
            // Row 1-3: Instructions (will be styled in AfterSheet)
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT DATA PENDUDUK'],
            ['Isi data mulai dari baris 6 ke bawah. Baris 5 adalah header kolom. Hapus baris contoh (7-8) sebelum upload.'],
            ['Baris dengan no_kk sama = 1 Kartu Keluarga. Setiap KK baru WAJIB punya tepat 1 anggota KEPALA_KELUARGA. Lihat sheet "Kode Referensi" untuk kode valid.'],
            // Row 4: Empty separator
            [],
            // Row 5: Header row (this is what WithHeadingRow reads)
            [
                'no_kk',
                'alamat_kk',
                'rt_id',
                'hubungan_keluarga',
                'nik',
                'nama_lengkap',
                'jenis_kelamin',
                'tempat_lahir',
                'tgl_lahir',
                'agama',
                'status_perkawinan',
                'pendidikan',
                'pekerjaan',
                'golongan_darah',
                'nama_ayah',
                'nama_ibu',
                'no_hp',
                'email',
                'alamat_asal',
            ],
            // Row 6: Column descriptions
            [
                '16 digit (WAJIB)',
                'Wajib jika KK baru',
                'ID RT (WAJIB)',
                'KEPALA_KELUARGA / ISTRI / ANAK / dll (WAJIB)',
                '16 digit (WAJIB)',
                'Nama lengkap (WAJIB)',
                'L / P (WAJIB)',
                'Kota lahir (WAJIB)',
                'YYYY-MM-DD (WAJIB)',
                'Kode agama (WAJIB)',
                'BELUM_KAWIN / KAWIN / dll (WAJIB)',
                'Kode (opsional)',
                'Kode (opsional)',
                'Kode (opsional)',
                'Opsional',
                'Opsional',
                'Unik (opsional)',
                'Unik (opsional)',
                'Alamat sebelumnya (WAJIB)',
            ],
            // Row 7: Example row 1 (Kepala Keluarga)
            [
                '3201234567890001',
                'Jl. Contoh No. 1 RT 001/RW 001',
                '1',
                'KEPALA_KELUARGA',
                '3201234567890001',
                'BUDI SANTOSO',
                'L',
                'JAKARTA',
                '1985-03-15',
                'ISLAM',
                'KAWIN',
                'S1',
                'SWASTA',
                'O+',
                'AHMAD SANTOSO',
                'SITI AMINAH',
                '081234567890',
                'budi@example.com',
                'Jl. Lama No. 5, Jakarta Selatan',
            ],
            // Row 8: Example row 2 (Istri, same KK)
            [
                '3201234567890001',
                '',
                '1',
                'ISTRI',
                '3201234567890002',
                'DEWI LESTARI',
                'P',
                'BANDUNG',
                '1988-07-22',
                'ISLAM',
                'KAWIN',
                'S1',
                'GURU',
                'A+',
                'JOKO WIDODO',
                'KARTINI',
                '081234567891',
                '',
                'Jl. Lama No. 5, Jakarta Selatan',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = 'S';

                // Format NIK and no_kk columns as TEXT
                $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode('@');

                // --- Instruction rows (1-3) ---
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A2:{$highestColumn}2");
                $sheet->mergeCells("A3:{$highestColumn}3");

                // Style instruction rows — yellow background
                $sheet->getStyle("A1:{$highestColumn}3")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0C000']]],
                ]);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('B45309');
                $sheet->getStyle('A2:A3')->getFont()->setSize(10)->getColor()->setRGB('92400E');

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);

                // --- Header row (5) ---
                $sheet->getStyle("A5:{$highestColumn}5")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003580']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(30);

                // --- Description row (6) — light blue ---
                $sheet->getStyle("A6:{$highestColumn}6")->applyFromArray([
                    'font' => ['size' => 8, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(6)->setRowHeight(30);

                // --- Example rows (7-8) — gray italic ---
                $sheet->getStyle("A7:{$highestColumn}8")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'font' => ['color' => ['rgb' => '666666'], 'italic' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                ]);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(8);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(14);
                $sheet->getColumnDimension('J')->setWidth(12);
                $sheet->getColumnDimension('K')->setWidth(18);
                $sheet->getColumnDimension('S')->setWidth(35);
            },
        ];
    }
}
