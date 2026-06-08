<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Services\MasterData\PekerjaanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PekerjaanController extends MasterReferenceController
{
    public function __construct(PekerjaanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): View
    {
        $items = $this->service->getAll();

        return view($this->getViewPrefix() . '.index', [
            'items' => $items,
            'entityName' => $this->getEntityName(),
        ]);
    }

    protected function getViewPrefix(): string
    {
        return 'master_data.pekerjaan';
    }

    protected function getRouteName(): string
    {
        return 'master_data.pekerjaan';
    }

    protected function getEntityName(): string
    {
        return 'Pekerjaan';
    }
}
