<?php

namespace App\Services\MasterWilayah;

use App\Models\Rt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use DomainException;

class RtService
{
    public function list(User $user): \Illuminate\Support\Collection
    {
        return $this->scopedQuery($user)
            ->with(['rw.desa'])
            ->withCount(['penduduks', 'kartuKeluargas', 'users'])
            ->orderBy('rw_id')
            ->orderBy('nomor_rt')
            ->get();
    }

    public function paginate(User $user, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->scopedQuery($user)->latest('id');

        if (!empty($filters['rw_id'])) {
            $query->where('rw_id', (int) $filters['rw_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($inner) use ($search) {
                $inner->where('nomor_rt', 'like', '%' . $search . '%')
                    ->orWhere('nama_ketua', 'like', '%' . $search . '%')
                    ->orWhere('no_hp_ketua', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    private function scopedQuery(User $user): Builder
    {
        $query = Rt::query();

        if ($user->hasRole('admin_desa')) {
            if ($user->desa_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereHas('rw', function ($rwQuery) use ($user) {
                $rwQuery->where('desa_id', $user->desa_id);
            });
        } elseif ($user->hasRole('admin_rw')) {
            if ($user->rw_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('rw_id', $user->rw_id);
        } elseif ($user->hasRole('admin_rt')) {
            if ($user->rt_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id', $user->rt_id);
        } elseif ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;
            if ($viewerDesaId === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereHas('rw', function ($rwQuery) use ($viewerDesaId) {
                $rwQuery->where('desa_id', $viewerDesaId);
            });
        }

        return $query;
    }

    public function create(array $payload): Rt
    {
        return Rt::create($payload);
    }

    public function update(Rt $rt, array $payload): Rt
    {
        $rt->fill($payload);
        $rt->save();

        return $rt;
    }

    public function delete(Rt $rt): void
    {
        DB::transaction(function () use ($rt) {
            if ($rt->penduduks()->exists()) {
                throw new DomainException('Tidak dapat menghapus RT yang masih memiliki penduduk.');
            }

            if ($rt->kartuKeluargas()->exists()) {
                throw new DomainException('Tidak dapat menghapus RT yang masih memiliki kartu keluarga.');
            }

            $rt->delete();
        });
    }
}
