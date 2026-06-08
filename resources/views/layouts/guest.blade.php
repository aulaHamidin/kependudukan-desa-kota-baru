<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kependudukan Desa') }} - Desa Kota Baru</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .auth-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #1a365d 25%, #1e40af 50%, #1e3a8a 75%, #172554 100%);
            min-height: 100vh;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .auth-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            background: #ffffff;
        }

        .auth-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .auth-input.is-error {
            border-color: #ef4444;
        }

        .auth-btn {
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .auth-btn-primary {
            background: linear-gradient(135deg, #1e40af, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }

        .auth-btn-primary:hover {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
            transform: translateY(-1px);
        }

        .auth-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .auth-btn-outline {
            background: transparent;
            color: #1e40af;
            border: 2px solid #dbeafe;
        }

        .auth-btn-outline:hover {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .pulse-circle {
            animation: pulse-c 4s ease-in-out infinite;
        }

        @keyframes pulse-c {

            0%,
            100% {
                opacity: 0.1;
                transform: scale(1);
            }

            50% {
                opacity: 0.05;
                transform: scale(1.1);
            }
        }
    </style>
</head>

<body class="antialiased" x-data="{ pageLoading: false }" @submit.window="pageLoading = true">
    <x-alert />
    <div class="auth-bg flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        {{-- Decorative Circles --}}
        <div class="absolute top-20 right-10 h-72 w-72 rounded-full pulse-circle"
            style="background: rgba(96,165,250,0.1); filter: blur(48px);"></div>
        <div class="absolute bottom-10 left-10 h-96 w-96 rounded-full pulse-circle"
            style="background: rgba(129,140,248,0.1); filter: blur(48px); animation-delay: 1.5s;"></div>

        <div class="w-full max-w-5xl relative z-10">
            <div class="grid w-full gap-10 lg:grid-cols-[1.1fr,0.9fr] items-center">
                {{-- Left Side - Branding --}}
                <div class="space-y-6 hidden lg:block">
                    <a href="/" class="inline-flex items-center gap-3 no-underline">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl"
                            style="background: rgba(255,255,255,0.15); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);">
                            <img src="{{ asset('images/logo-desa.png') }}" alt="Logo Desa"
                                class="h-8 w-8 object-contain">
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase" style="letter-spacing: 0.2em; color: #bfdbfe;">
                                SIAK-DESA
                            </p>
                            <p class="text-xl font-bold" style="color: #ffffff;">
                                Desa Kota Baru
                            </p>
                        </div>
                    </a>

                    <div>
                        <h2 class="text-3xl font-extrabold leading-tight" style="color: #ffffff;">
                            Sistem Informasi<br>Administrasi<br>Kependudukan
                        </h2>
                        <div class="mt-4 rounded-full"
                            style="height: 4px; width: 80px; background: linear-gradient(to right, #facc15, #fde047, transparent);">
                        </div>
                    </div>

                    <p class="text-sm leading-relaxed max-w-md" style="color: rgba(191,219,254,0.8);">
                        Akses layanan administrasi penduduk yang tertib, ringkas, dan terverifikasi untuk kebutuhan
                        desa.
                    </p>

                    <div class="grid gap-3 text-sm" style="color: rgba(191,219,254,0.7);">
                        <div class="flex items-center gap-3 rounded-lg px-4 py-3"
                            style="background: rgba(255,255,255,0.08); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                            <svg class="h-5 w-5 flex-shrink-0" style="color: #facc15;" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Rekap penduduk dan keluarga terstruktur</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg px-4 py-3"
                            style="background: rgba(255,255,255,0.08); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                            <svg class="h-5 w-5 flex-shrink-0" style="color: #facc15;" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Pengelolaan surat dan peristiwa cepat</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg px-4 py-3"
                            style="background: rgba(255,255,255,0.08); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                            <svg class="h-5 w-5 flex-shrink-0" style="color: #facc15;" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Kontrol akses sesuai peran petugas</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs" style="color: rgba(191,219,254,0.4);">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>SIAK-DESA v1.0 &mdash; Pemerintah Desa Kota Baru</span>
                    </div>
                </div>

                {{-- Right Side - Form Card --}}
                <div>
                    {{-- Mobile Logo --}}
                    <div class="lg:hidden text-center mb-6">
                        <a href="/" class="inline-flex items-center gap-3 no-underline">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg"
                                style="background: rgba(255,255,255,0.15); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);">
                                <img src="{{ asset('images/logo-desa.png') }}" alt="Logo"
                                    class="h-7 w-7 object-contain">
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] font-bold uppercase"
                                    style="letter-spacing: 0.2em; color: #bfdbfe;">SIAK-DESA</p>
                                <p class="text-sm font-bold" style="color: #ffffff;">Desa Kota Baru</p>
                            </div>
                        </a>
                    </div>

                    <div class="auth-card p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Full-screen loading overlay --}}
    <div x-show="pageLoading" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="background: linear-gradient(135deg, #1e3a5f, #1e40af, #1e3a8a);">
        <div class="text-center">
            <svg class="animate-spin h-12 w-12 text-white mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="mt-4 text-white/80 text-sm font-medium">Mempersiapkan dashboard...</p>
        </div>
    </div>
</body>

</html>
