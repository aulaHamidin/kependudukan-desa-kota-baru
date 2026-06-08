<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Http\Controllers\Controller;
use App\Models\GolonganDarah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GolonganDarahController extends Controller
{

    public function index(Request $request): View
    {
        $items = GolonganDarah::query()
            ->withCount('penduduks')
            ->orderBy('nama')
            ->orderBy('rhesus')
            ->get();

        return view('master_data.golongan_darah.index', [
            'items' => $items,
            'entityName' => 'Golongan Darah',
        ]);
    }
}
