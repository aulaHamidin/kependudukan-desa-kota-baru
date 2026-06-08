<?php

declare(strict_types=1);

namespace App\Exports\Reports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EventsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithEvents
{
    public function __construct(private Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function title(): string
    {
        return 'Data Peristiwa';
    }

    public function headings(): array
    {
        return [
            'Jenis Event',
            'Tanggal Event',
            'Status Data',
            'NIK',
            'Nama',
            'RT',
            'RW',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->event_type_code ?? '-',
            optional($row->event_date)->format('Y-m-d') ?? '-',
            $row->status_data ?? '-',
            $row->penduduk?->nik ?? '-',
            $row->penduduk?->nama_lengkap ?? '-',
            $row->rt?->nomor_rt ?? '-',
            $row->rt?->rw?->nomor_rw ?? '-',
            $row->keterangan ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Insert header rows
                $sheet->insertNewRowBefore(1, 5);
                
                // Merge cells for header
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->mergeCells('A3:' . $highestColumn . '3');
                $sheet->mergeCells('A4:' . $highestColumn . '4');
                
                // Set header content
                $sheet->setCellValue('A1', 'PEMERINTAH ' . strtoupper(config('app.desa.kabupaten')));
                $sheet->setCellValue('A2', 'KECAMATAN ' . strtoupper(config('app.desa.kecamatan')));
                $sheet->setCellValue('A3', strtoupper(config('app.desa.nama')));
                $sheet->setCellValue('A4', 'LAPORAN DATA PERISTIWA');
                
                // Style header
                $sheet->getStyle('A1:A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);
                
                // Style table headers (row 6)
                $sheet->getStyle('A6:' . $highestColumn . '6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003580']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                
                // Style data rows
                $sheet->getStyle('A7:' . $highestColumn . ($highestRow + 5))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(25);
            },
        ];
    }
}
