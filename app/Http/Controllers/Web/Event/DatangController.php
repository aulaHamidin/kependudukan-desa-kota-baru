<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventDatangRequest;
use App\Http\Requests\Event\UpdateEventDatangRequest;
use App\Models\Agama;
use App\Models\Event;
use App\Models\GolonganDarah;
use App\Models\KartuKeluarga;
use App\Models\Pekerjaan;
use App\Models\PendapatanRange;
use App\Models\Pendidikan;
use App\Models\Rt;
use App\Services\Event\DatangService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DatangController extends Controller
{
    public function __construct(
        private DatangService $service
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Event::class);

        $user = auth()->user();
        $filters = request()->only(['status_data', 'search', 'start_date', 'end_date', 'rt_id']);
        $events = $this->service->paginateWithFilters($user, $filters, 15);
        $stats = $this->service->getStats($user);

        return view('data_peristiwa.datang.index', compact('events', 'stats', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $data = $this->getFormData();

        return view('data_peristiwa.datang.create', $data);
    }

    public function store(StoreEventDatangRequest $request): RedirectResponse
    {
        $user = auth()->user();
        try {
            $event = $this->service->createEventDatang($user, $request->validated());

            return redirect()
                ->route('events.datang.show', $event->id)
                ->with('success', 'Event Penduduk Datang berhasil dicatat');
        } catch (AuthorizationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'penduduk.agama',
            'penduduk.pendidikan',
            'penduduk.pekerjaan',
            'penduduk.golonganDarah',
            'rt.rw',
            'kartuKeluarga',
            'eventDatang',
            'createdBy',
            'verifiedBy',
        ]);

        return view('data_peristiwa.datang.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $data = $this->getFormData();
        $data['event'] = $event->load(['penduduk', 'eventDatang']);

        return view('data_peristiwa.datang.edit', $data);
    }

    public function update(UpdateEventDatangRequest $request, Event $event): RedirectResponse
    {
        $user = auth()->user();
        try {
            $this->service->updateEventDatang($user, $event, $request->validated());

            return redirect()
                ->route('events.datang.show', $event->id)
                ->with('success', 'Event berhasil diperbarui');
        } catch (AuthorizationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);
        $user = auth()->user();
        try {
            $this->service->deleteEventDatang($user, $event);

            return redirect()
                ->route('events.datang.index')
                ->with('success', 'Event datang berhasil dihapus');
        } catch (AuthorizationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus event');
        }
    }

    private function getFormData(): array
    {
        $user = auth()->user();

        // Get RTs based on user role
        if ($user->hasRole('admin_rt')) {
            $rts = Rt::where('id', $user->rt_id)->get();
        } elseif ($user->hasRole('admin_rw')) {
            $rts = Rt::where('rw_id', $user->rw_id)->get();
        } elseif ($user->hasRole('admin_desa')) {
            $rts = Rt::whereHas('rw', function ($query) use ($user) {
                $query->where('desa_id', $user->desa_id);
            })->get();
        } else {
            $rts = collect();
        }

        // Get KK based on user scope (via HasTerritory trait on KartuKeluarga model)
        $kartuKeluargas = KartuKeluarga::where('status_kk', 'AKTIF')
            ->with('rt.rw')
            ->get();

        return [
            'rts' => $rts,
            'rtOptions' => $this->getRtOptions($user),
            'kartuKeluargas' => $kartuKeluargas,
            'agamas' => Agama::where('is_active', true)->orderBy('urutan')->get()->pluck('nama', 'kode'),
            'pendidikans' => Pendidikan::where('is_active', true)->orderBy('urutan')->get()->pluck('nama', 'kode'),
            'pekerjaans' => Pekerjaan::where('is_active', true)->orderBy('urutan')->get()->pluck('nama', 'kode'),
            'pendapatanRanges' => PendapatanRange::where('is_active', true)->orderBy('urutan')->get()->pluck('label', 'id'),
            'golonganDarahs' => GolonganDarah::where('is_active', true)->get()->pluck('kode', 'kode'),
        ];
    }

    private function getRtOptions($user): array
    {
        $query = Rt::query()->with('rw');

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
}
