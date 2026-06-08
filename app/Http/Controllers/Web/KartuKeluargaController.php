<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\DTOs\KartuKeluargaDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\KartuKeluarga\StoreKartuKeluargaRequest;
use App\Http\Requests\KartuKeluarga\UpdateKartuKeluargaRequest;
use App\Models\HubunganKeluarga;
use App\Models\KartuKeluarga;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Services\KartuKeluargaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KartuKeluargaController extends Controller
{
    public function __construct(
        private KartuKeluargaService $service
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', KartuKeluarga::class);

        $filters          = request()->only(['rt_id', 'status_kk', 'search', 'no_kepala']);
        $kartuKeluargas   = $this->service->getAllPaginated(15, $filters);
        // Eager loading already done in repository paginate() method
        $rtOptions        = $this->getRtOptions(auth()->user());
        $statusKkOptions  = ['AKTIF' => 'Aktif', 'NON_AKTIF' => 'Non Aktif'];
        $pendudukOptions  = $this->getPendudukOptions(auth()->user());
        $hubunganOptions  = $this->getHubunganOptions();

        // FIXED: Stats via service → territory-aware, tidak bypass HasTerritory scope
        // Query langsung KartuKeluarga::count() akan bypass scope dan expose data lintas wilayah
        $stats = $this->service->getStats();

        return view('data_inti.kartu_keluarga.index', compact(
            'kartuKeluargas',
            'rtOptions',
            'statusKkOptions',
            'pendudukOptions',
            'hubunganOptions',
            'stats'
        ));
    }

    public function store(StoreKartuKeluargaRequest $request): RedirectResponse
    {
        // Explicit authorization (double protection - FormRequest juga authorize)
        $this->authorize('create', KartuKeluarga::class);

        try {
            $dto = KartuKeluargaDTO::fromRequest($request->validated());
            $kk  = $this->service->createKartuKeluarga($dto);

            return redirect()
                ->route('kartu-keluarga.show', $kk)
                ->with('success', 'Kartu Keluarga berhasil ditambahkan.');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(KartuKeluarga $kartuKeluarga): View
    {
        // Eager load relations BEFORE authorization check
        $kartuKeluarga->load(['rt.rw.desa']);
        
        $this->authorize('view', $kartuKeluarga);

        // Load only AKTIF members with proper ordering and their relations
        $kartuKeluarga->load([
            'kkMembers' => function ($query) {
                $query->where('status', 'AKTIF')
                      ->orderBy('is_kepala_keluarga', 'desc')
                      ->orderBy('tanggal_masuk', 'asc')
                      ->with(['kartuKeluarga.rt.rw.desa']); // For KkMemberPolicy territory check
            },
            'kkMembers.penduduk',
            'kkMembers.hubunganKeluarga',
        ]);

        // For modals in show page
        $rtOptions = $this->getRtOptions(auth()->user());
        $statusKkOptions = ['AKTIF' => 'Aktif', 'NON_AKTIF' => 'Non Aktif'];
        $hubunganOptions = $this->getHubunganOptions();

        return view('data_inti.kartu_keluarga.show', compact(
            'kartuKeluarga',
            'rtOptions',
            'statusKkOptions',
            'hubunganOptions'
        ));
    }

    public function edit(KartuKeluarga $kartuKeluarga): View
    {
        // Eager load relations BEFORE authorization check
        $kartuKeluarga->load(['rt.rw.desa']);
        
        $this->authorize('update', $kartuKeluarga);

        $rtOptions = $this->getRtOptions(auth()->user());
        $statusKkOptions = ['AKTIF' => 'Aktif', 'NON_AKTIF' => 'Non Aktif'];

        return view('data_inti.kartu_keluarga.edit', compact(
            'kartuKeluarga',
            'rtOptions',
            'statusKkOptions'
        ));
    }

    public function update(UpdateKartuKeluargaRequest $request, KartuKeluarga $kartuKeluarga): RedirectResponse
    {
        // Eager load relations BEFORE authorization
        $kartuKeluarga->load(['rt.rw.desa']);
        
        // Explicit authorization (double protection - FormRequest juga authorize)
        $this->authorize('update', $kartuKeluarga);

        try {
            $dto = KartuKeluargaDTO::fromRequest($request->validated());
            $this->service->updateKartuKeluarga($kartuKeluarga->id, $dto);

            return redirect()
                ->route('kartu-keluarga.show', $kartuKeluarga)
                ->with('success', 'Kartu Keluarga berhasil diperbarui.');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(KartuKeluarga $kartuKeluarga): RedirectResponse
    {
        // Eager load relations BEFORE authorization
        $kartuKeluarga->load(['rt.rw.desa']);
        
        $this->authorize('delete', $kartuKeluarga);

        try {
            $this->service->deleteKartuKeluarga($kartuKeluarga->id);

            return redirect()
                ->route('kartu-keluarga.index')
                ->with('success', 'Kartu Keluarga berhasil dihapus.');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function getRtOptions($user): array
    {
        $query = Rt::query()->with('rw.desa');

        if ($user->hasRole('admin_rt')) {
            $query->where('id', $user->rt_id ?? 0);
        } elseif ($user->hasRole('admin_rw')) {
            $query->where('rw_id', $user->rw_id ?? 0);
        } elseif ($user->hasRole('admin_desa')) {
            $query->whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));
        } elseif ($user->hasRole('viewer')) {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->orderBy('nomor_rt')
            ->get()
            ->mapWithKeys(function ($rt) {
                $rw    = $rt->rw;
                $desa  = $rw ? $rw->desa : null;
                $label = 'RT ' . $rt->nomor_rt;

                if ($rw) {
                    $label .= ' / RW ' . $rw->nomor_rw;
                }

                if ($desa) {
                    $label .= ' - ' . $desa->nama;
                }

                return [$rt->id => $label];
            })
            ->all();
    }

    private function getPendudukOptions($user): array
    {
        $query = Penduduk::query()
            ->whereNull('deleted_at')
            ->orderBy('nama_lengkap');

        if ($user->hasRole('admin_rt')) {
            $query->where('rt_id', $user->rt_id ?? 0);
        } elseif ($user->hasRole('admin_rw')) {
            $query->whereHas('rt', fn($q) => $q->where('rw_id', $user->rw_id ?? 0));
        } elseif ($user->hasRole('admin_desa')) {
            $query->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));
        } elseif ($user->hasRole('viewer')) {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->get()
            ->mapWithKeys(fn($p) => [$p->id => $p->nik . ' - ' . $p->nama_lengkap])
            ->all();
    }

    private function getHubunganOptions(): array
    {
        return HubunganKeluarga::where('is_active', true)
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn($h) => [$h->kode => $h->nama])
            ->all();
    }
}
