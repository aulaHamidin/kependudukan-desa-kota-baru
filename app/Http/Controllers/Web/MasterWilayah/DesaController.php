<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterWilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterWilayah\Desa\StoreRequest;
use App\Http\Requests\MasterWilayah\Desa\UpdateRequest;
use App\Models\Desa;
use App\Services\MasterWilayah\DesaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use DomainException;

class DesaController extends Controller
{
    public function __construct(private DesaService $service)
    {
        $this->authorizeResource(Desa::class, 'desa');
    }

    public function index(Request $request): View
    {
        $desa = $this->service->list($request->user());

        return view('master_wilayah.desa.index', [
            'desa' => $desa,
        ]);
    }

    public function create(): View
    {
        return view('master_wilayah.desa.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('master.wilayah.desa.index')
            ->with('success', 'Desa berhasil ditambahkan.');
    }

    public function show(Desa $desa): View
    {
        return view('master_wilayah.desa.show', [
            'desa' => $desa,
        ]);
    }

    public function edit(Desa $desa): View
    {
        return view('master_wilayah.desa.edit', [
            'desa' => $desa,
        ]);
    }

    public function update(UpdateRequest $request, Desa $desa): RedirectResponse
    {
        $this->service->update($desa, $request->validated());

        return redirect()
            ->route('master.wilayah.desa.index')
            ->with('success', 'Desa berhasil diperbarui.');
    }

    public function destroy(Desa $desa): RedirectResponse
    {
        try {
            $this->service->delete($desa);

            return redirect()
                ->route('master.wilayah.desa.index')
                ->with('success', 'Desa berhasil dihapus.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->with('error', $exception->getMessage());
        }
    }
}
