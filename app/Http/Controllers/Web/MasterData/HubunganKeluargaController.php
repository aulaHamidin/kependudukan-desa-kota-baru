<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Http\Controllers\Controller;
use App\Models\HubunganKeluarga;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HubunganKeluargaController extends Controller
{
    public function index(Request $request): View
    {
        $items = HubunganKeluarga::query()
            ->withCount(['kkMembers', 'eventKematianReports'])
            ->orderBy('nama')
            ->orderBy('kode')
            ->get();

        return view('master_data.hubungan_keluarga.index', [
            'items' => $items,
            'entityName' => 'Hubungan Keluarga',
        ]);
    }
}
