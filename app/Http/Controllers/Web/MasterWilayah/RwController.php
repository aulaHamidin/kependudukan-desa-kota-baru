<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterWilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterWilayah\Rw\StoreRequest;
use App\Http\Requests\MasterWilayah\Rw\UpdateRequest;
use App\Models\Desa;
use App\Models\Rw;
use App\Services\MasterWilayah\RwService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RwController extends Controller
{
    public function __construct(private RwService $service)
    {
        $this->authorizeResource(Rw::class, 'rw');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $rws = $this->service->list($user);

        // Get desas for filter dropdown based on user role
        $desas = $this->getDesasForUser($user);

        return view('master_wilayah.rw.index', [
            'rws' => $rws,
            'desas' => $desas,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $desas = $this->getDesasForUser($user);

        return view('master_wilayah.rw.create', [
            'desas' => $desas,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('master.wilayah.rw.index')
            ->with('success', 'RW berhasil ditambahkan.');
    }

    public function show(Rw $rw): View
    {
        return view('master_wilayah.rw.show', [
            'rw' => $rw->load('desa'),
        ]);
    }

    public function edit(Request $request, Rw $rw): View
    {
        $user = $request->user();
        $desas = $this->getDesasForUser($user);

        return view('master_wilayah.rw.edit', [
            'rw' => $rw,
            'desas' => $desas,
        ]);
    }

    public function update(UpdateRequest $request, Rw $rw): RedirectResponse
    {
        $this->service->update($rw, $request->validated());

        return redirect()
            ->route('master.wilayah.rw.index')
            ->with('success', 'RW berhasil diperbarui.');
    }

    public function destroy(Rw $rw): RedirectResponse
    {
        try {
            $this->service->delete($rw);

            return redirect()
                ->route('master.wilayah.rw.index')
                ->with('success', 'RW berhasil dihapus.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->with('error', $exception->getMessage());
        }
    }

    private function getDesasForUser($user): Collection
    {
        if ($user->hasRole('super_admin')) {
            return Desa::orderBy('nama')->get();
        }

        if ($user->hasRole('admin_desa') && $user->desa_id) {
            return Desa::where('id', $user->desa_id)->get();
        }

        return collect();
    }
}
