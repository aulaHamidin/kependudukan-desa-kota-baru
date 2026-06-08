<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventKematianRequest;
use App\Http\Requests\Event\UpdateEventKematianRequest;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Services\Event\KematianService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KematianController extends Controller
{
    public function __construct(
        private KematianService $service
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $filters = $request->only(['status_data', 'start_date', 'end_date', 'rt_id', 'search']);
        $events  = $this->service->paginateWithFilters(auth()->user(), $filters, $request->input('per_page', 15));
        $stats   = $this->service->getStats(auth()->user());

        return view('data_peristiwa.kematian.index', compact('events', 'stats', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $user = auth()->user();
        $hubunganOptions = \App\Models\HubunganKeluarga::orderBy('nama')->pluck('nama', 'kode')->all();
        return view('data_peristiwa.kematian.create', [
            'rtOptions'       => $this->getRtOptions($user),
            'pendudukOptions' => $this->getPendudukOptions($user),
            'hubunganOptions' => $hubunganOptions,
        ]);
    }

    public function store(StoreEventKematianRequest $request): RedirectResponse
    {
        try {
            $event = $this->service->createEventKematian(auth()->user(), $request->validated());
            return redirect()->route('events.kematian.show', $event->id)
                ->with('success', 'Event kematian berhasil dicatat.');
        } catch (AuthorizationException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Kematian store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'penduduk',
            'rt.rw.desa',
            'eventKematian.pelapor',
            'eventKematian.hubunganPelapor',
            'eventKematian.pengganti',      // relasi baru — pengganti kepala yang ditunjuk
            'kartuKeluarga.kepalaKeluarga.penduduk',
            'createdBy',
            'verifiedBy',
            'voidedBy',
        ]);

        return view('data_peristiwa.kematian.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $event->load(['penduduk', 'eventKematian', 'rt.rw']);
        $user = auth()->user();

        $hubunganOptions = \App\Models\HubunganKeluarga::orderBy('nama')->pluck('nama', 'kode')->all();
        return view('data_peristiwa.kematian.edit', [
            'event'           => $event,
            'pendudukOptions' => $this->getPendudukOptions($user),
            'hubunganOptions' => $hubunganOptions,
        ]);
    }

    public function update(UpdateEventKematianRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->service->updateEventKematian(auth()->user(), $event, $request->validated());
            return redirect()->route('events.kematian.show', $event->id)
                ->with('success', 'Event kematian berhasil diperbarui.');
        } catch (AuthorizationException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Kematian update failed', ['event_id' => $event->id, 'error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data.']);
        }
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        try {
            $this->service->deleteEventKematian(auth()->user(), $event);
            return redirect()->route('events.kematian.index')
                ->with('success', 'Event kematian berhasil dihapus.');
        } catch (AuthorizationException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Kematian destroy failed', ['event_id' => $event->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data.']);
        }
    }

    /**
     * AJAX: Get anggota KK aktif (selain almarhum) untuk selector pengganti kepala.
     * Juga return apakah penduduk tersebut adalah kepala keluarga.
     *
     * Response format konsisten dengan events.pindah.kk-members:
     * { is_kepala_keluarga: bool, members: [{id, text}] }
     *
     * Territory check: rt_id KK harus berada di wilayah user yang sedang login.
     * Tanpa ini, admin_rt A bisa fetch anggota KK milik admin_rt B.
     */
    public function getKkMembers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Event::class);

        $kkId       = $request->input('kk_id');
        $pendudukId = $request->input('penduduk_id');

        if (!$kkId) return response()->json(['is_kepala_keluarga' => false, 'members' => []]);

        $kk = KartuKeluarga::with('rt.rw')->find($kkId);
        if (!$kk) return response()->json(['is_kepala_keluarga' => false, 'members' => []]);

        // Territory check: pastikan KK berada di wilayah user
        if (!$this->kkInUserTerritory($kk)) {
            return response()->json([], 403);
        }

        // Cek apakah penduduk adalah kepala keluarga di KK ini
        $isKepala = $pendudukId
            ? KkMember::where('kartu_keluarga_id', $kkId)
                ->where('penduduk_id', $pendudukId)
                ->where('is_kepala_keluarga', true)
                ->where('status', 'AKTIF')
                ->exists()
            : false;

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
            'members'            => $members,
        ]);
    }

    /**
     * Cek apakah KK berada di wilayah user yang sedang login.
     * Super admin / role tanpa territory restriction selalu lolos.
     */
    private function kkInUserTerritory(KartuKeluarga $kk): bool
    {
        $user = auth()->user();

        if ($user->hasRole('admin_rt')) {
            return (int) $kk->rt_id === (int) $user->rt_id;
        }

        if ($user->hasRole('admin_rw')) {
            return (int) $kk->rt?->rw_id === (int) $user->rw_id;
        }

        if ($user->hasRole('admin_desa')) {
            return (int) $kk->rt?->rw?->desa_id === (int) $user->desa_id;
        }

        // super_admin, viewer, dll: tidak dibatasi
        return true;
    }

    private function getRtOptions($user): array
    {
        $query = Rt::query()->with('rw.desa');

        if ($user->hasRole('admin_rt'))        $query->where('id', $user->rt_id ?? 0);
        elseif ($user->hasRole('admin_rw'))    $query->where('rw_id', $user->rw_id ?? 0);
        elseif ($user->hasRole('admin_desa'))  $query->whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));

        return $query->orderBy('nomor_rt')->get()
            ->mapWithKeys(fn($rt) => [$rt->id => 'RT ' . $rt->nomor_rt . ' / RW ' . $rt->rw?->nomor_rw])
            ->all();
    }

    private function getPendudukOptions($user): array
    {
        $query = Penduduk::query()
            ->whereNull('deleted_at')
            ->where('status_kependudukan_code', 'AKTIF')
            ->orderBy('nama_lengkap');

        if ($user->hasRole('admin_rt'))        $query->where('rt_id', $user->rt_id ?? 0);
        elseif ($user->hasRole('admin_rw'))    $query->whereHas('rt', fn($q) => $q->where('rw_id', $user->rw_id ?? 0));
        elseif ($user->hasRole('admin_desa'))  $query->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));

        return $query->get()
            ->mapWithKeys(fn($p) => [$p->id => $p->nik . ' - ' . $p->nama_lengkap])
            ->all();
    }
}
