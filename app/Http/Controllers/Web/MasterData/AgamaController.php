<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Services\MasterData\AgamaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgamaController extends MasterReferenceController
{
    public function __construct(AgamaService $service)
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
        return 'master_data.agama';
    }

    protected function getRouteName(): string
    {
        return 'master_data.agama';
    }

    protected function getEntityName(): string
    {
        return 'Agama';
    }
}
