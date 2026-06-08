<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penduduk\UpdatePendudukRequest;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Models\StatusKependudukan;
use App\Services\PendudukService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendudukController extends Controller
{
    public function __construct(
        private PendudukService $service
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Penduduk::class);

        $filters = $request->only(['search', 'status_kependudukan_code', 'jenis_kelamin', 'rt_id']);
        $penduduks = $this->service->paginateWithFilters($filters, 10);
        $stats = $this->service->getStats();

        // Filter options for dropdown
        $statuses = StatusKependudukan::where('is_active', true)->get();
        $rts = $this->getRtsForUser();

        // Transform to arrays for blade select options
        $statusOptions = $statuses->pluck('nama', 'kode')->toArray();
        $rtOptions = $rts->mapWithKeys(function ($rt) {
            $label = 'RT ' . $rt->nomor_rt;
            if ($rt->rw) {
                $label .= ' / RW ' . $rt->rw->nomor_rw;
            }
            return [$rt->id => $label];
        })->toArray();

        return view('data_inti.penduduk.index', compact('penduduks', 'stats', 'statuses', 'rts', 'statusOptions', 'rtOptions'));
    }

    private function getRtsForUser(): \Illuminate\Support\Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->hasRole('admin_rt')) {
            return Rt::where('id', $user->rt_id)->get();
        }

        if ($user->hasRole('admin_rw')) {
            return Rt::where('rw_id', $user->rw_id)->with('rw')->get();
        }

        if ($user->hasRole('admin_desa')) {
            return Rt::whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id))
                ->with('rw')
                ->get();
        }

        return collect();
    }

    public function show(Penduduk $penduduk): View
    {
        // Eager load relations BEFORE authorization check (required by ValidatesTerritory trait)
        $penduduk->load(['rt.rw.desa']);

        $this->authorize('view', $penduduk);

        $penduduk->load([
            'agama',
            'pendidikan',
            'pekerjaan',
            'golonganDarah',
            'pendapatanRange',
            'statusKependudukan',
            'currentEvent.eventType',
            'kkMembers' => function ($query) {
                $query->where('status', 'AKTIF')->with('kartuKeluarga');
            },
            'events' => function ($query) {
                $query->with('eventType')->orderBy('event_date', 'desc')->limit(10);
            },
        ]);

        $dataCompleteness = $this->service->calculateDataCompleteness($penduduk);

        return view('data_inti.penduduk.show', compact('penduduk', 'dataCompleteness'));
    }

    public function edit(Penduduk $penduduk): View
    {
        // Eager load relations BEFORE authorization check
        $penduduk->load(['rt.rw.desa']);

        $this->authorize('update', $penduduk);

        $penduduk->load(['agama', 'pendidikan', 'pekerjaan', 'golonganDarah']);

        // Load master data for dropdowns
        $agamas = \App\Models\Agama::where('is_active', true)->orderBy('urutan')->get();
        $pendidikans = \App\Models\Pendidikan::where('is_active', true)->orderBy('urutan')->get();
        $pekerjaans = \App\Models\Pekerjaan::where('is_active', true)->orderBy('urutan')->get();
        $pendapatanRanges = \App\Models\PendapatanRange::where('is_active', true)->orderBy('urutan')->get();
        $golonganDarahs = \App\Models\GolonganDarah::where('is_active', true)->get();

        return view('data_inti.penduduk.edit', compact(
            'penduduk',
            'agamas',
            'pendidikans',
            'pekerjaans',
            'pendapatanRanges',
            'golonganDarahs'
        ));
    }

    public function update(UpdatePendudukRequest $request, Penduduk $penduduk): RedirectResponse
    {
        // Eager load relations BEFORE authorization check (required by ValidatesTerritory trait)
        $penduduk->load(['rt.rw.desa']);

        $this->authorize('update', $penduduk);

        try {
            $this->service->updatePenduduk($penduduk->id, $request->validated());

            return redirect()
                ->route('penduduk.show', $penduduk->id)
                ->with('success', 'Data penduduk berhasil diperbarui');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data');
        }
    
    }
}