<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PindahRtRequest;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Rt;
use App\Services\PindahRtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PindahRtController extends Controller
{
    public function __construct(
        private PindahRtService $service
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', KartuKeluarga::class);

        $user = auth()->user();

        $kartuKeluargas = KartuKeluarga::with(['rt.rw', 'kkMembers' => fn($q) => $q->where('status', 'AKTIF'), 'kepalaKeluarga.penduduk'])
            ->where('status_kk', 'AKTIF')
            ->when($user->hasRole('admin_desa'), fn($q) =>
                $q->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0))
            )
            ->when(
                request('search'),
                fn($q, $search) =>
                $q->where(function ($query) use ($search) {
                    $query->where('no_kk', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%")
                        ->orWhereHas('kepalaKeluarga.penduduk', function ($q) use ($search) {
                            $q->where('nama_lengkap', 'like', "%{$search}%");
                        });
                })
            )
            ->when(request('rt_id'), fn($q, $rtId) => $q->where('rt_id', $rtId))
            ->orderBy('no_kk')
            ->paginate(20)
            ->withQueryString();

        $rtOptions = $this->getRtOptions($user);

        // Statistik untuk summary cards — scoped ke territory user
        $totalKK = KartuKeluarga::query()
            ->where('status_kk', 'AKTIF')
            ->when($user->hasRole('admin_desa'), fn($q) =>
                $q->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0))
            )
            ->count();

        $totalRT = Rt::query()
            ->whereNull('deleted_at')
            ->when($user->hasRole('admin_desa'), fn($q) =>
                $q->whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0))
            )
            ->count();

        // Total anggota dari seluruh KK aktif di territory (bukan hanya halaman saat ini)
        $totalAnggota = KkMember::query()
            ->where('status', 'AKTIF')
            ->whereHas('kartuKeluarga', fn($q) =>
                $q->where('status_kk', 'AKTIF')
                  ->when($user->hasRole('admin_desa'), fn($q) =>
                      $q->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0))
                  )
            )
            ->count();

        return view('data_peristiwa.pindah-rt.index', compact('kartuKeluargas', 'rtOptions', 'totalKK', 'totalRT', 'totalAnggota'));
    }

    public function show(KartuKeluarga $kk): View
    {
        $kk->load('rt.rw');
        $this->authorize('view', $kk);

        $user = auth()->user();
        $preview = $this->service->getPreviewData($kk);
        $rtOptions = $this->getRtOptions($user, excludeRtId: $kk->rt_id);

        return view('data_peristiwa.pindah-rt.show', array_merge($preview, compact('rtOptions')));
    }

    public function store(PindahRtRequest $request, KartuKeluarga $kk): RedirectResponse
    {
        $kk->load('rt.rw');
        $this->authorize('pindah', $kk);

        try {
            $this->service->execute(
                $kk,
                (int) $request->validated('rt_id_tujuan'),
                $request->validated('keterangan')
            );

            return redirect()
                ->route('pindah-rt.index')
                ->with('success', "KK {$kk->no_kk} berhasil dipindahkan ke RT baru.");
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getRtOptions($user, ?int $excludeRtId = null): array
    {
        $query = Rt::query()->with('rw')->whereNull('deleted_at');

        if ($user->hasRole('admin_desa')) {
            $query->whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));
        }

        return $query->orderBy('nomor_rt')->get()
            ->when($excludeRtId, fn($col) => $col->where('id', '!=', $excludeRtId))
            ->mapWithKeys(fn($rt) => [
                $rt->id => 'RT ' . $rt->nomor_rt . ' / RW ' . $rt->rw?->nomor_rw,
            ])
            ->all();
    }
}
