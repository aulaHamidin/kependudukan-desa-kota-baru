<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventKelahiranRequest;
use App\Http\Requests\Event\UpdateEventKelahiranRequest;
use App\Models\Agama;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Services\Event\KelahiranService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelahiranController extends Controller
{
    public function __construct(
        private KelahiranService $service
    ) {}

    /**
     * FIXED: Gunakan service untuk query (territory scope auto-applied)
     * Sebelumnya: Event::where(...) langsung → bypass HasTerritory scope
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $filters = $request->only(['status_data', 'start_date', 'end_date', 'rt_id', 'search']);

        // Territory scope auto-applied via HasTerritory global scope
        $events = $this->service->paginateWithFilters(
            auth()->user(),
            $filters,
            $request->input('per_page', 15)
        );

        // Stats territory-aware
        $stats = $this->service->getStats(auth()->user());

        return view('data_peristiwa.kelahiran.index', compact('events', 'stats', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $user = auth()->user();

        return view('data_peristiwa.kelahiran.create', [
            'rtOptions' => $this->getRtOptions($user),
            'kkOptions' => $this->getKkOptions($user),
            'pendudukOptions' => $this->getPendudukOptions($user),
            'agamaOptions' => $this->getAgamaOptions(),
        ]);
    }

    public function store(StoreEventKelahiranRequest $request): RedirectResponse
    {
        try {
            $event = $this->service->createEventKelahiran(
                auth()->user(),
                $request->validated()
            );

            return redirect()
                ->route('events.kelahiran.show', $event->id)
                ->with('success', 'Event kelahiran berhasil dicatat.');
        } catch (AuthorizationException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Kelahiran store failed', [
                'user_id' => auth()->id(),
        'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
    ]);
    return back()->withInput()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
    ]);
        }
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'penduduk',
            'rt.rw.desa',
            'eventKelahiran.ayah',
            'eventKelahiran.ibu',
            'eventKelahiran.kkTujuan',
            'createdBy',
            'verifiedBy',
            'voidedBy',
        ]);

        return view('data_peristiwa.kelahiran.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $event->load(['penduduk', 'eventKelahiran', 'rt.rw']);
        $user = auth()->user();

        return view('data_peristiwa.kelahiran.edit', [
            'event'          => $event,
            'rtOptions'      => $this->getRtOptions($user),
            'kkOptions'      => $this->getKkOptions($user),
            'pendudukOptions' => $this->getPendudukOptions($user),
            'agamaOptions'   => $this->getAgamaOptions(),
        ]);
    }

    /**
     * FIXED: Implement update via service
     */
    public function update(UpdateEventKelahiranRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->service->updateEventKelahiran(
                auth()->user(),
                $event,
                $request->validated()
            );

            return redirect()
                ->route('events.kelahiran.show', $event->id)
                ->with('success', 'Event kelahiran berhasil diperbarui.');
        } catch (AuthorizationException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Kelahiran update failed', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.']);
        }
    }

    /**
     * FIXED: destroy = delete DRAFT only
     * Sebelumnya: destroy() memanggil voidEvent() → semantik salah
     * void = untuk VERIFIED (via VoidController)
     * destroy = untuk DRAFT
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        try {
            $this->service->deleteEventKelahiran(auth()->user(), $event);

            return redirect()
                ->route('events.kelahiran.index')
                ->with('success', 'Event kelahiran berhasil dihapus.');
        } catch (AuthorizationException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event Kelahiran destroy failed', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.']);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Options untuk dropdown agama (create/edit event kelahiran).
     * Format sama dengan DatangController (pluck nama by kode).
     */
    private function getAgamaOptions(): \Illuminate\Support\Collection
    {
        return Agama::where('is_active', true)->orderBy('urutan')->get()->pluck('nama', 'kode');
    }

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

    private function getKkOptions($user): array
    {
        $query = KartuKeluarga::query()->where('status_kk', 'AKTIF');

        if ($user->hasRole('admin_rt')) {
            $query->where('rt_id', $user->rt_id ?? 0);
        } elseif ($user->hasRole('admin_rw')) {
            $query->whereHas('rt', fn($q) => $q->where('rw_id', $user->rw_id ?? 0));
        } elseif ($user->hasRole('admin_desa')) {
            $query->whereHas('rt.rw', fn($q) => $q->where('desa_id', $user->desa_id ?? 0));
        }

        return $query->get()
            ->mapWithKeys(fn($kk) => [$kk->id => $kk->no_kk . ' - ' . $kk->alamat])
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
}
