<?php

namespace Tests\Helpers;

use App\Models\Desa;
use App\Models\Rw;
use App\Models\Rt;
use App\Models\User;

trait PolicyTestHelper
{
    protected function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    protected function adminDesa(Desa $desa): User
    {
        return User::factory()->create([
            'role'    => 'admin_desa',
            'desa_id' => $desa->id,
        ]);
    }

    protected function adminRw(Rw $rw): User
    {
        return User::factory()->create([
            'role'  => 'admin_rw',
            'rw_id' => $rw->id,
        ]);
    }

    protected function adminRt(Rt $rt): User
    {
        return User::factory()->create([
            'role'  => 'admin_rt',
            'rt_id' => $rt->id,
        ]);
    }

    protected function viewer(Rt $rt): User
    {
        return User::factory()->create([
            'role'  => 'viewer',
            'rt_id' => $rt->id,
        ]);
    }

    protected function createTerritory(): array
    {
        $desa = Desa::factory()->create();
        $rw   = Rw::factory()->create(['desa_id' => $desa->id]);
        $rt   = Rt::factory()->create(['rw_id' => $rw->id]);

        return compact('desa', 'rw', 'rt');
    }

    protected function createOtherTerritory(): array
    {
        return $this->createTerritory();
    }
}