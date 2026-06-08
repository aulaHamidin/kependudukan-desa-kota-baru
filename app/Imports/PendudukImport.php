<?php

declare(strict_types=1);

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PendudukImport implements ToCollection, WithHeadingRow, WithStartRow
{
    private Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    /**
     * Header row is on row 5 in the template (rows 1-3 = instructions, row 4 = empty, row 5 = header).
     * Data starts from row 7 (row 6 = column descriptions).
     * WithHeadingRow uses headingRow() to know where headers are.
     */
    public function headingRow(): int
    {
        return 5;
    }

    /**
     * Start reading data from row 7 (skip the description row 6).
     */
    public function startRow(): int
    {
        return 7;
    }

    public function collection(Collection $rows): void
    {
        // Normalize each row: clean NIK/no_kk leading zeros, handle Excel date serials
        $this->rows = $rows->map(function ($row) {
            $data = $row->toArray();

            // Ensure NIK and no_kk are strings with leading zeros preserved
            if (isset($data['nik'])) {
                $data['nik'] = $this->normalizeNumericString($data['nik'], 16);
            }
            if (isset($data['no_kk'])) {
                $data['no_kk'] = $this->normalizeNumericString($data['no_kk'], 16);
            }

            // Handle Excel date serial for tgl_lahir
            if (isset($data['tgl_lahir']) && is_numeric($data['tgl_lahir'])) {
                try {
                    $dateTime = ExcelDate::excelToDateTimeObject((int) $data['tgl_lahir']);
                    $data['tgl_lahir'] = $dateTime->format('Y-m-d');
                } catch (\Exception $e) {
                    // Leave as-is, validation will catch it
                }
            }

            // Trim all string values
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = trim($value);
                }
            }

            return $data;
        });
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    /**
     * Normalize numeric string (NIK/no_kk) — pad with leading zeros if needed.
     */
    private function normalizeNumericString(mixed $value, int $length): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // If it's a float from Excel (e.g., 3.20123E+15), convert to int string first
        if (is_float($value)) {
            $value = number_format($value, 0, '', '');
        }

        $str = (string) $value;

        // Pad with leading zeros if shorter than expected length
        if (strlen($str) < $length && ctype_digit($str)) {
            $str = str_pad($str, $length, '0', STR_PAD_LEFT);
        }

        return $str;
    }
}
