<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\ApproveEventRequest;
use App\Http\Requests\Event\RejectEventRequest;
use App\Models\Event;
use App\Services\ApprovalService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalService $service
    ) {}

    /**
     * Approval queue: daftar events DRAFT yang menunggu approval
     *
     * Access:
     * - ADMIN_RW: lihat events dari RT dalam RW-nya
     * - ADMIN_DESA: lihat semua events DRAFT dalam desanya
     * - Others: forbidden
     */
    public function index(): View
    {
        $this->authorize('viewAny', Event::class);

        $user = auth()->user();

        // FIXED: Admin RW juga bisa approve (sesuai matrix)
        if (!$user->hasRole('admin_rw') && !$user->hasRole('admin_desa')) {
            abort(403, 'Hanya Admin RW atau Admin Desa yang dapat mengakses halaman approval.');
        }

        // Get pending events sesuai role via service (territory-aware)
        $pendingEvents = $this->service->getPendingEvents($user);

        // Hitung statistik per jenis event untuk summary cards
        $typeCounts = $this->service->getPendingCountsByType($user);
        $stats = [
            'total'     => $pendingEvents->total(),
            'kelahiran' => $typeCounts['KELAHIRAN'] ?? 0,
            'kematian'  => $typeCounts['KEMATIAN'] ?? 0,
            'pindah'    => $typeCounts['PINDAH'] ?? 0,
            'datang'    => $typeCounts['DATANG'] ?? 0,
        ];

        return view('data_peristiwa.approvals.index', compact('pendingEvents', 'stats'));
    }

    /**
     * Approve event
     *
     * Authorization Matrix:
     * - ADMIN_DESA: approve any event in Desa
     * - ADMIN_RW: approve events created by RT (not self-created by other RW)
     * - Others: forbidden
     */
    public function approve(ApproveEventRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->service->approveEvent(auth()->user(), $event);

            return redirect()
                ->back()
                ->with('success', 'Event berhasil diverifikasi.');
        } catch (AuthorizationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Event approval failed', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menyetujui event.');
        }
    }

    /**
     * Reject event (kembalikan DRAFT dengan catatan)
     *
     * Authorization sama dengan approve.
     */
    public function reject(RejectEventRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->service->rejectEvent(
                auth()->user(),
                $event,
                $request->validated('rejection_reason')
            );

            return redirect()
                ->back()
                ->with('success', 'Event berhasil ditolak. Creator akan mendapat notifikasi.');
        } catch (AuthorizationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Event rejection failed', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menolak event.');
        }
    }
}
