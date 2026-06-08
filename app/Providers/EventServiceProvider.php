<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Audit\DataImported;
use App\Listeners\Audit\LogDataImport;
use App\Listeners\Audit\LogUserLogin;
use App\Listeners\Audit\LogUserLogout;
use App\Listeners\Audit\LogUserPasswordReset;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            LogUserLogin::class,
        ],
        Logout::class => [
            LogUserLogout::class,
        ],
        PasswordReset::class => [
            LogUserPasswordReset::class,
        ],
        DataImported::class => [
            LogDataImport::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
