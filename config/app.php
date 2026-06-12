<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'SIAK-DESA'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => env('APP_LOCALE', 'id'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'id_ID',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\RepositoryServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Desa Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi data desa untuk header surat, tanda tangan, dan keperluan
    | administrasi lainnya. Set nilai ini via .env atau langsung di sini.
    |
    */

    'desa' => [
        // Identitas Desa
        'nama'      => env('DESA_NAMA', 'DESA KOTA BARU'),
        'kode'      => env('DESA_KODE', '3201010001'),
        'kode_surat' => env('DESA_KODE_SURAT', '01.2009'),
        'kecamatan' => env('DESA_KECAMATAN', 'MARTAPURA'),
        'kabupaten' => env('DESA_KABUPATEN', 'OGAN KOMERING ULU TIMUR'),
        'provinsi'  => env('DESA_PROVINSI', 'SUMATERA SELATAN'),
        'alamat'    => env('DESA_ALAMAT', 'Jalan Pertanian No.958 Desa Kota Baru Kecamatan Martapura Kabupaten OKU Timur'),
        'kode_pos'  => env('DESA_KODE_POS', '32311'),
        'telepon'   => env('DESA_TELEPON', '(021) 123-4567'),
        'email'     => env('DESA_EMAIL', 'kotabaru1608012009@gmail.com'),

        // Pejabat - Kepala Desa
        'kepala_desa' => [
            'nama' => env('DESA_KEPALA_NAMA', 'HENDRI SUSANTO'),
            'nip'  => env('DESA_KEPALA_NIP', null),
            'nik'  => env('DESA_KEPALA_NIK', '1608011703770001'),
            'jabatan' => env('DESA_KEPALA_JABATAN', 'Kepala Desa'),
            'alamat' => env('DESA_KEPALA_ALAMAT', 'Jl Sinar Hijau RT. 001 Dusun 001 Desa Kotabaru Kec. Martapura Kab. OKU Timur Sumatera Selatan'),
        ],

        // Pejabat - Sekretaris Desa
        'sekdes' => [
            'nama' => env('DESA_SEKDES_NAMA', 'Nama Sekretaris Desa'),
            'nip'  => env('DESA_SEKDES_NIP', null),
        ],

        // Pejabat - Kepala Seksi (defaultnya Kasi Pemerintahan)
        'kasi' => [
            'nama'    => env('DESA_KASI_NAMA', 'Nama Kepala Seksi'),
            'jabatan' => env('DESA_KASI_JABATAN', 'Kasi Pemerintahan'),
            'nip'     => env('DESA_KASI_NIP', null),
        ],

        // TTD Digital (opsional)
        'ttd_digital' => [
            'enabled'         => env('DESA_TTD_DIGITAL_ENABLED', false),
            'kepala_desa_path' => env('DESA_TTD_KEPALA_PATH', null),
            'sekdes_path'      => env('DESA_TTD_SEKDES_PATH', null),
            'stempel_path'     => env('DESA_STEMPEL_PATH', null),
        ],

        // Logo Desa
        'logo_path' => env('DESA_LOGO_PATH', null),
    ],

];
