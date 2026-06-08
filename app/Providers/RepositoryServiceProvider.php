<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\KartuKeluargaRepositoryInterface;
use App\Repositories\Contracts\KkMemberRepositoryInterface;
use App\Repositories\Contracts\PendudukRepositoryInterface;
use App\Repositories\EventRepository;
use App\Repositories\KartuKeluargaRepository;
use App\Repositories\KkMemberRepository;
use App\Repositories\PendudukRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            KartuKeluargaRepositoryInterface::class,
            KartuKeluargaRepository::class
        );

        $this->app->bind(
            EventRepositoryInterface::class,
            EventRepository::class
        );

        $this->app->bind(
            PendudukRepositoryInterface::class,
            PendudukRepository::class
        );

        $this->app->bind(
            KkMemberRepositoryInterface::class,
            KkMemberRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
