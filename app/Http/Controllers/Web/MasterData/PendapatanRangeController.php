<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Http\Controllers\Controller;
use App\Models\PendapatanRange;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendapatanRangeController extends Controller
{
    public function index(Request $request): View
    {
        $items = PendapatanRange::query()
            ->withCount('penduduks')
            ->orderBy('urutan')
            ->get();

        return view('master_data.range_pendapatan.index', [
            'items' => $items,
            'entityName' => 'Pendapatan Range',
        ]);
    }
}
