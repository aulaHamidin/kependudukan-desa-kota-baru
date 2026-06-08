<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterWilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterWilayah\Rt\StoreRequest;
use App\Http\Requests\MasterWilayah\Rt\UpdateRequest;
use App\Models\Rt;
use App\Models\Rw;
use App\Services\MasterWilayah\RtService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RtController extends Controller
{
    public function __construct(private RtService $service)
    {
        $this->authorizeResource(Rt::class, 'rt');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $rts = $this->service->list($user);

        // Get RWs for filter dropdown based on user role
        $rws = $this->getRwsForUser($user);

        return view('master_wilayah.rt.index', [
            'rts' => $rts,
            'rws' => $rws,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $rws = $this->getRwsForUser($user);

        return view('master_wilayah.rt.create', [
            'rws' => $rws,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('master.wilayah.rt.index')
            ->with('success', 'RT berhasil ditambahkan.');
    }

    public function show(Rt $rt): View
    {
        return view('master_wilayah.rt.show', [
            'rt' => $rt->load('rw.desa'),
        ]);
    }

    public function edit(Request $request, Rt $rt): View
    {
        $user = $request->user();
        $rws = $this->getRwsForUser($user);

        return view('master_wilayah.rt.edit', [
            'rt' => $rt,
            'rws' => $rws,
        ]);
    }

    public function update(UpdateRequest $request, Rt $rt): RedirectResponse
    {
        $this->service->update($rt, $request->validated());

        return redirect()
            ->route('master.wilayah.rt.index')
            ->with('success', 'RT berhasil diperbarui.');
    }

    public function destroy(Rt $rt): RedirectResponse
    {
        try {
            $this->service->delete($rt);

            return redirect()
                ->route('master.wilayah.rt.index')
                ->with('success', 'RT berhasil dihapus.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->with('error', $exception->getMessage());
        }
    }

    private function getRwsForUser($user): Collection
    {
        if ($user->hasRole('super_admin')) {
            return Rw::with('desa')->orderBy('desa_id')->orderBy('nomor_rw')->get();
        }

        if ($user->hasRole('admin_desa') && $user->desa_id) {
            return Rw::with('desa')->where('desa_id', $user->desa_id)->orderBy('nomor_rw')->get();
        }

        if ($user->hasRole('admin_rw') && $user->rw_id) {
            return Rw::with('desa')->where('id', $user->rw_id)->get();
        }

        return collect();
    }
}
