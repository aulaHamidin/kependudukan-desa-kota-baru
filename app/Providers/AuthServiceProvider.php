<?php

declare(strict_types=1);

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Penduduk;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\User;
use App\Models\Desa;
use App\Models\Event;
use App\Models\Rw;
use App\Models\Rt;
use App\Policies\PendudukPolicy;
use App\Policies\KartuKeluargaPolicy;
use App\Policies\KkMemberPolicy;
use App\Policies\UserPolicy;
use App\Policies\DesaPolicy;
use App\Policies\EventPolicy;
use App\Policies\RwPolicy;
use App\Policies\RtPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Penduduk::class     => PendudukPolicy::class,
        KartuKeluarga::class => KartuKeluargaPolicy::class,
        KkMember::class     => KkMemberPolicy::class,
        User::class         => UserPolicy::class,
        Desa::class         => DesaPolicy::class,
        Rw::class           => RwPolicy::class,
        Rt::class           => RtPolicy::class,
        Event::class        => EventPolicy::class,
        // PindahRt tidak punya model sendiri; policy di-bind manual via Gate
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}