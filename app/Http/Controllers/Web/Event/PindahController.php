<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventPindahRequest;
use App\Http\Requests\Event\UpdateEventPindahRequest;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Services\Event\PindahService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PindahController extends Controller
{
    public function __construct(
        private PindahService $service
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $filters = $request->only(['status_data', 'start_date', 'end_date', 'rt_id', 'search']);

        $events = $this->service->paginateWithFilters(
            auth()->user(),
            $filters,
            $request->input('per_page', 15)
        );

        $stats = $this->service->getStats(auth()->user());

        return view('data_peristiwa.pindah.index', compact('events', 'stats', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $user = auth()->user();

        return view('data_peristiwa.pindah.create', [
            'rtOptions'      => $this->getRtOptions($user),
            'pendudukOptions' => $this->getPendudukOptions($user),
            'alasanOptions'  => $this->getAlasanOptions(),
        ]);
    }

    public function store(StoreEventPindahRequest $request): RedirectResponse
    {
        try {
            $event = $this->service->createEventPindah(
                auth()->user(),
                $request->validated()
            );

            return redirect()
                ->route('events.pindah.show', $event->id)
                ->with('success', 'Event kepindahan berhasil dicatat.');
        } catch (AuthorizationException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Pindah store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'penduduk',
            'rt.rw.desa',
            'eventPindah',
            'kartuKeluarga.kepalaKeluarga.penduduk',
            'createdBy',
            'verifiedBy',
            'voidedBy',
        ]);

        return view('data_peristiwa.pindah.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $event->load(['penduduk', 'eventPindah', 'rt.rw.desa']);

        return view('data_peristiwa.pindah.edit', [
            'event'         => $event,
            'rtOptions'     => $this->getRtOptions(auth()->user()),
            'alasanOptions' => $this->getAlasanOptions(),
        ]);
    }

    public function update(UpdateEventPindahRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->service->updateEventPindah(
                auth()->user(),
                $event,
                $request->validated()
            );

            return redirect()
                ->route('events.pindah.show', $event->id)
                ->with('success', 'Event kepindahan berhasil diperbarui.');
        } catch (AuthorizationException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Pindah update failed', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data.']);
        }
    }

    /**
     * Destroy - hanya DRAFT
     * Void VERIFIED → via VoidController
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        try {
            $this->service->deleteEventPindah(auth()->user(), $event);

            return redirect()
                ->route('events.pindah.index')
                ->with('success', 'Event kepindahan berhasil dihapus.');
        } catch (AuthorizationException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Pindah destroy failed', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data.']);
        }
    }

    // =========================================================================
    // AJAX: Get KK members untuk pengganti kepala selector
    // Dipanggil saat user pilih penduduk + KK di form create
    // =========================================================================

    public function getKkMembers(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Event::class);

        $kkId       = $request->input('kk_id');
        $pendudukId = $request->input('penduduk_id');

        if (!$kkId || !$pendudukId) {
            return response()->json(['is_kepala_keluarga' => false, 'members' => []]);
        }

        $kk = \App\Models\KartuKeluarga::find($kkId);
        if (!$kk) {
            return response()->json(['is_kepala_keluarga' => false, 'members' => []]);
        }

        // Cek apakah penduduk adalah kepala keluarga
        $isKepala = KkMember::where('kartu_keluarga_id', $kkId)
            ->where('penduduk_id', $pendudukId)
            ->where('status', 'AKTIF')
            ->where('is_kepala_keluarga', true)
            ->exists();

        $members = KkMember::where('kartu_keluarga_id', $kkId)
            ->where('status', 'AKTIF')
            ->where('penduduk_id', '!=', $pendudukId)
            ->with('penduduk:id,nama_lengkap,nik')
            ->get()
            ->map(fn($m) => [
                'id'   => $m->penduduk_id,
                'text' => $m->penduduk->nik . ' - ' . $m->penduduk->nama_lengkap,
            ]);

        return response()->json([
            'is_kepala_keluarga' => $isKepala,
            'members'            => $isKepala ? $members : [],
        ]);
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
        }

        return $query->orderBy('nomor_rt')->get()
            ->mapWithKeys(fn($rt) => [
                $rt->id => 'RT ' . $rt->nomor_rt . ' / RW ' . $rt->rw?->nomor_rw,
            ])
            ->all();
    }

    private function getPendudukOptions($user): array
    {
        $query = Penduduk::query()
            ->whereNull('deleted_at')
            ->where('status_kependudukan_code', 'AKTIF')
            ->orderBy('nama_lengkap');

        if ($user->hasRole('admin_rt')) {
            $query->where('rt_id', $user->rt_id ?? 0);
        } elseif ($user->hasRole('admin_rw')) {
            $query->whereHas('rt', fn($q) => $q->where('rw_id', $user->rw_id ?? 0));
        } elseif ($user->hasRole('admin_desa')) {
            $query->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));
        }

        return $query->get()
            ->mapWithKeys(fn($p) => [$p->id => $p->nik . ' - ' . $p->nama_lengkap])
            ->all();
    }

    private function getAlasanOptions(): array
    {
        return [
            'PEKERJAAN'   => 'Pekerjaan',
            'PENDIDIKAN'  => 'Pendidikan',
            'KEAMANAN'    => 'Keamanan',
            'KESEHATAN'   => 'Kesehatan',
            'PERKAWINAN'  => 'Perkawinan',
            'LAINNYA'     => 'Lainnya',
        ];
    }
}
