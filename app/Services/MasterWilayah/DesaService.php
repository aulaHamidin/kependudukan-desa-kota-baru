<?php

namespace App\Services\MasterWilayah;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use DomainException;

class DesaService
{
    public function list(User $user): \Illuminate\Support\Collection
    {
        return $this->scopedQuery($user)
            ->withCount(['rws', 'rts', 'users'])
            ->orderBy('nama')
            ->get();
    }

    public function paginate(User $user, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->scopedQuery($user)->latest('id');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($inner) use ($search) {
                $inner->where('kode_desa', 'like', '%' . $search . '%')
                    ->orWhere('nama', 'like', '%' . $search . '%')
                    ->orWhere('kecamatan', 'like', '%' . $search . '%')
                    ->orWhere('kabupaten', 'like', '%' . $search . '%')
                    ->orWhere('provinsi', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    private function scopedQuery(User $user): Builder
    {
        $query = Desa::query();

        if ($user->hasRole('admin_desa')) {
            if ($user->desa_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id', $user->desa_id);
        } elseif ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;
            if ($viewerDesaId === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id', $viewerDesaId);
        }

        return $query;
    }

    public function create(array $payload): Desa
    {
        return Desa::create($payload);
    }

    public function update(Desa $desa, array $payload): Desa
    {
        $desa->fill($payload);
        $desa->save();

        return $desa;
    }

    public function delete(Desa $desa): void
    {
        DB::transaction(function () use ($desa) {
            if ($desa->rws()->exists()) {
                throw new DomainException('Tidak dapat menghapus desa yang masih memiliki RW.');
            }

            $hasPenduduk = DB::table('penduduks')
                ->join('rts', 'penduduks.rt_id', '=', 'rts.id')
                ->join('rws', 'rts.rw_id', '=', 'rws.id')
                ->where('rws.desa_id', $desa->id)
                ->exists();

            if ($hasPenduduk) {
                throw new DomainException('Tidak dapat menghapus desa yang masih memiliki penduduk.');
            }

            $desa->delete();
        });
    }
}
