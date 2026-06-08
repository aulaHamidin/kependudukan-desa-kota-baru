<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Agama;
use App\Models\GolonganDarah;
use App\Models\HubunganKeluarga;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PendudukImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new Sheets\TemplateDataSheet(),
            new Sheets\ReferensiKodeSheet(),
        ];
    }
}
