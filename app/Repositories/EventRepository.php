<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    public function all(): Collection
    {
        return Event::with(['eventType', 'penduduk', 'rt.rw', 'createdBy'])
            ->orderBy('event_date', 'desc')
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Event::with(['eventType', 'penduduk', 'rt.rw', 'createdBy'])
            ->orderBy('event_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Event
    {
        return Event::with([
            'eventType',
            'penduduk',
            'rt.rw',
            'kk',
            'createdBy',
            'verifiedBy',
            'voidedBy',
            'eventDatang'
        ])->find($id);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): bool
    {
        return $event->update($data);
    }

    public function updateStatus(Event $event, string $status, ?int $verifiedBy = null): bool
    {
        $data = ['status_data' => $status];

        if ($status === 'VERIFIED' && $verifiedBy) {
            $data['verified_by'] = $verifiedBy;
            $data['verified_at'] = now();
        }

        return $event->update($data);
    }

    public function voidEvent(Event $event, string $reason): bool
    {
        return $event->update([
            'status_data' => 'VOID',
            'void_reason' => $reason,
            'void_at' => now(),
            'voided_by' => auth()->id(),
        ]);
    }

    public function findPendingByUser(User $user): Collection
    {
        return Event::where('created_by', $user->id)
            ->where('status_data', 'DRAFT')
            ->with(['eventType', 'penduduk'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findEditableByUser(User $user): Collection
    {
        return Event::where('created_by', $user->id)
            ->where('status_data', 'DRAFT')
            ->where('created_at', '>=', now()->subDays(7))
            ->with(['eventType', 'penduduk'])
            ->get();
    }

    public function getByType(string $eventTypeCode): Collection
    {
        return Event::where('event_type_code', $eventTypeCode)
            ->with(['penduduk', 'rt.rw'])
            ->orderBy('event_date', 'desc')
            ->get();
    }

    /**
     * Paginate Event Datang with filters
     * 
     * Territory scope auto-applied via HasTerritory trait
     * 
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateDatangWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Event::with([
            'penduduk',
            'eventDatang',
            'rt.rw.desa',
            'kartuKeluarga',
            'createdBy',
            'verifiedBy',
        ])->where('event_type_code', 'DATANG');

        // Filter by status_data
        if (!empty($filters['status_data'])) {
            $query->where('status_data', $filters['status_data']);
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $query->whereDate('event_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('event_date', '<=', $filters['end_date']);
        }

        // Filter by RT (useful for Admin RW/Desa viewing specific RT)
        if (!empty($filters['rt_id'])) {
            $query->where('rt_id', $filters['rt_id']);
        }

        // Search by penduduk name / NIK
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereHas('penduduk', function ($pendudukQuery) use ($search) {
                    $pendudukQuery->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            });
        }

        // Order by newest first
        $query->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }
}
