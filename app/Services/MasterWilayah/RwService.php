<?php

namespace App\Services\MasterWilayah;

use App\Models\Rw;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use DomainException;

class RwService
{
    public function list(User $user): \Illuminate\Support\Collection
    {
        return $this->scopedQuery($user)
            ->with(['desa'])
            ->withCount(['rts', 'users'])
            ->orderBy('desa_id')
            ->orderBy('nomor_rw')
            ->get();
    }

    public function paginate(User $user, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->scopedQuery($user)->latest('id');

        if (!empty($filters['desa_id'])) {
            $query->where('desa_id', (int) $filters['desa_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($inner) use ($search) {
                $inner->where('nomor_rw', 'like', '%' . $search . '%')
                    ->orWhere('nama_ketua', 'like', '%' . $search . '%')
                    ->orWhere('no_hp_ketua', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    private function scopedQuery(User $user): Builder
    {
        $query = Rw::query();

        if ($user->hasRole('admin_desa')) {
            if ($user->desa_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('desa_id', $user->desa_id);
        } elseif ($user->hasRole('admin_rw')) {
            if ($user->rw_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id', $user->rw_id);
        } elseif ($user->hasRole('admin_rt')) {
            if ($user->rt_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $rt = Rt::query()->select('rw_id')->find($user->rt_id);
            if (!$rt) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id', $rt->rw_id);
        } elseif ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;
            if ($viewerDesaId === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('desa_id', $viewerDesaId);
        }

        return $query;
    }

    public function create(array $payload): Rw
    {
        return Rw::create($payload);
    }

    public function update(Rw $rw, array $payload): Rw
    {
        $rw->fill($payload);
        $rw->save();

        return $rw;
    }

    public function delete(Rw $rw): void
    {
        DB::transaction(function () use ($rw) {
            if ($rw->rts()->exists()) {
                throw new DomainException('Tidak dapat menghapus RW yang masih memiliki RT.');
            }

            $hasPenduduk = DB::table('penduduks')
                ->join('rts', 'penduduks.rt_id', '=', 'rts.id')
                ->where('rts.rw_id', $rw->id)
                ->exists();

            if ($hasPenduduk) {
                throw new DomainException('Tidak dapat menghapus RW yang masih memiliki penduduk.');
            }

            $hasKartuKeluarga = DB::table('kartu_keluargas')
                ->join('rts', 'kartu_keluargas.rt_id', '=', 'rts.id')
                ->where('rts.rw_id', $rw->id)
                ->exists();

            if ($hasKartuKeluarga) {
                throw new DomainException('Tidak dapat menghapus RW yang masih memiliki kartu keluarga.');
            }

            $rw->delete();
        });
    }
}
