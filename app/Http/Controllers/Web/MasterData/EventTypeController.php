<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventTypeController extends Controller
{
    public function index(Request $request): View
    {
        $items = EventType::query()
            ->withCount('events')
            ->orderBy('nama')
            ->orderBy('kode')
            ->get();

        return view('master_data.event_type.index', [
            'items' => $items,
            'entityName' => 'Event Type',
        ]);
    }
}
