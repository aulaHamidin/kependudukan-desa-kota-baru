<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoidEventRequest;
use App\Models\Event;
use App\Services\Event\EventVoidService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class VoidController extends Controller
{
    public function __construct(
        private EventVoidService $voidService
    ) {}

    /**
     * Void a verified event
     *
     * Centralized endpoint untuk semua event type:
     * DATANG, KELAHIRAN, KEMATIAN, PINDAH (future)
     *
     * Authorization: EventPolicy::void() via $this->authorize()
     */
    public function store(VoidEventRequest $request, Event $event): RedirectResponse
    {
        // Explicit authorization check via EventPolicy::void()
        // Policy sudah implemented di Phase 0 - tidak perlu ubah
        $this->authorize('void', $event);

        try {
            $this->voidService->voidEvent(
                auth()->user(),
                $event,
                $request->validated('void_reason')
            );

            return redirect()
                ->route($this->resolveShowRoute($event), $event)
                ->with('success', 'Event berhasil di-void.');
        } catch (DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            \Log::error('Event void failed', [
                'event_id'   => $event->id,
                'event_type' => $event->event_type_code,
                'trace' => $e->getTraceAsString(), // tambah ini
            ]);

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolve show route berdasarkan event type
     */
    private function resolveShowRoute(Event $event): string
    {
        return match ($event->event_type_code) {
            'KELAHIRAN' => 'events.kelahiran.show',
            'KEMATIAN'  => 'events.kematian.show',
            'PINDAH'    => 'events.pindah.show',
            'DATANG'    => 'events.datang.show',
            default     => 'dashboard',
        };
    }
}
