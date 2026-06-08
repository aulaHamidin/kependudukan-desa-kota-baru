<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\Rt;
use App\Models\Views\VDataInconsistency;
use App\Models\Views\VKkWithMembers;
use App\Models\Views\VPendudukAktif;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
        * Get RT ids inside admin_desa territory.
        *
        * @return Collection<int, int>
        */
    public function rtIdsForUser(User $user): Collection
    {
        return Rt::query()
            ->whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id))
            ->pluck('id');
    }

    public function pendudukQuery(array $filters, User $user): Builder
    {
        $rtIds = $this->rtIdsForUser($user);

        $query = VPendudukAktif::query()
            ->whereIn('rt_id', $rtIds);

        if (!empty($filters['rt_id'])) {
            $query->where('rt_id', (int) $filters['rt_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama_lengkap');
    }

    public function kkQuery(array $filters, User $user): Builder
    {
        $rtIds = $this->rtIdsForUser($user);

        $kkIds = KartuKeluarga::whereIn('rt_id', $rtIds)->pluck('id');

        $query = VKkWithMembers::query()
            ->whereIn('kk_id', $kkIds);

        if (!empty($filters['rt_id'])) {
            $query->whereIn('kk_id', KartuKeluarga::where('rt_id', $filters['rt_id'])->pluck('id'));
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('nama_kepala', 'like', "%{$search}%")
                    ->orWhere('nik_kepala', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('no_kk');
    }

    public function inconsistencyQuery(array $filters, User $user): Builder
    {
        $rtIds = $this->rtIdsForUser($user);

        $query = VDataInconsistency::query()
            ->whereExists(function ($sub) use ($rtIds) {
                $sub->select(DB::raw(1))
                    ->from('penduduks as p')
                    ->whereColumn('p.id', 'v_data_inconsistency.penduduk_id')
                    ->whereIn('p.rt_id', $rtIds);
            });

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('issue_type', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('issue_type');
    }

    public function eventsQuery(array $filters, User $user): Builder
    {
        $rtIds = $this->rtIdsForUser($user);

        $query = Event::query()
            ->with(['penduduk', 'rt.rw'])
            ->whereIn('rt_id', $rtIds);

        if (!empty($filters['event_type'])) {
            $query->where('event_type_code', $filters['event_type']);
        }

        if (!empty($filters['status_data'])) {
            $query->where('status_data', $filters['status_data']);
        }

        if (!empty($filters['rt_id'])) {
            $query->where('rt_id', $filters['rt_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('event_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('event_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereHas('penduduk', function ($pendudukQuery) use ($search) {
                    $pendudukQuery->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                })->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('event_date', 'desc')->orderBy('created_at', 'desc');
    }
}
