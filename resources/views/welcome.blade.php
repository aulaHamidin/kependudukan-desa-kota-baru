<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kependudukan Desa') }} - Desa Kota Baru</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <style>
        /* Scroll Reveal Animations */
        [x-cloak] {
            display: none !important;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-40px);
            transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .reveal-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(40px);
            transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .reveal-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .reveal-scale.revealed {
            opacity: 1;
            transform: scale(1);
        }

        .delay-100 {
            transition-delay: 0.1s;
        }

        .delay-200 {
            transition-delay: 0.2s;
        }

        .delay-300 {
            transition-delay: 0.3s;
        }

        .delay-400 {
            transition-delay: 0.4s;
        }

        .delay-500 {
            transition-delay: 0.5s;
        }

        .stat-glow:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(30, 64, 175, 0.2);
        }

        .service-card:hover {
            transform: translateY(-6px);
        }

        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(-3deg);
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .pulse-ring {
            animation: pulse-ring 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {

            0%,
            100% {
                opacity: 0.15;
                transform: scale(1);
            }

            50% {
                opacity: 0.05;
                transform: scale(1.15);
            }
        }
    </style>
</head>

<body class="antialiased text-gray-900" x-data="{
    mobileMenu: false,
    scrolled: false,
    activeSection: 'beranda',
    init() {
        // Scroll listener for navbar
        window.addEventListener('scroll', () => {
            this.scrolled = window.scrollY > 50;
            this.updateActiveSection();
        });

        // Scroll reveal observer
        const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        revealEls.forEach(el => observer.observe(el));
    },
    updateActiveSection() {
        const sections = ['beranda', 'statistik', 'layanan', 'kontak'];
        for (let i = sections.length - 1; i >= 0; i--) {
            const el = document.getElementById(sections[i]);
            if (el && el.getBoundingClientRect().top <= 120) {
                this.activeSection = sections[i];
                break;
            }
        }
    },
    scrollTo(id) {
        this.mobileMenu = false;
        const el = document.getElementById(id);
        if (el) {
            const offset = 80;
            const top = el.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    }
}">

    {{-- ========================================== --}}
    {{-- NAVBAR - CMS Style --}}
    {{-- ========================================== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        style="background: rgba(30, 58, 95, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
        :style="scrolled ? 'background: rgba(23, 37, 84, 0.97); box-shadow: 0 4px 20px rgba(0,0,0,0.15);' : ''">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between lg:h-20">
                {{-- Logo & Brand --}}
                <a href="/" class="flex items-center gap-3 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg transition"
                        style="background: rgba(255,255,255,0.15); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);">
                        <img src="{{ asset('images/logo-desa.png') }}" alt="Logo Desa" class="h-7 w-7 object-contain">
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-[10px] font-bold uppercase" style="letter-spacing: 0.2em; color: #bfdbfe;">
                            SIAK-DESA
                        </p>
                        <p class="text-sm font-bold leading-tight" style="color: #ffffff;">
                            Desa Kota Baru
                        </p>
                    </div>
                </a>

                {{-- Desktop Nav Links --}}
                <div class="hidden items-center gap-1 lg:flex">
                    <a @click.prevent="scrollTo('beranda')" href="#beranda"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 cursor-pointer"
                        :style="activeSection === 'beranda' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.7);'">
                        Beranda
                    </a>
                    <a @click.prevent="scrollTo('statistik')" href="#statistik"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 cursor-pointer"
                        :style="activeSection === 'statistik' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.7);'">
                        Statistik
                    </a>
                    <a @click.prevent="scrollTo('layanan')" href="#layanan"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 cursor-pointer"
                        :style="activeSection === 'layanan' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.7);'">
                        Layanan
                    </a>
                    <a @click.prevent="scrollTo('kontak')" href="#kontak"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 cursor-pointer"
                        :style="activeSection === 'kontak' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.7);'">
                        Kontak
                    </a>
                </div>

                {{-- Auth Buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold"
                            style="background: #ffffff; color: #1e3a8a; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold"
                            style="background: #ffffff; color: #1e3a8a; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Masuk
                        </a>
                    @endauth

                    {{-- Mobile Menu Toggle --}}
                    <button @click="mobileMenu = !mobileMenu"
                        class="flex h-10 w-10 items-center justify-center rounded-lg transition lg:hidden"
                        style="color: rgba(255,255,255,0.8);">
                        <svg x-show="!mobileMenu" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileMenu" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileMenu" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                class="pb-4 lg:hidden" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <div class="mt-2 space-y-1">
                    <a @click.prevent="scrollTo('beranda')" href="#beranda"
                        class="block rounded-lg px-4 py-2.5 text-sm font-semibold cursor-pointer"
                        :style="activeSection === 'beranda' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.8);'">Beranda</a>
                    <a @click.prevent="scrollTo('statistik')" href="#statistik"
                        class="block rounded-lg px-4 py-2.5 text-sm font-semibold cursor-pointer"
                        :style="activeSection === 'statistik' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.8);'">Statistik</a>
                    <a @click.prevent="scrollTo('layanan')" href="#layanan"
                        class="block rounded-lg px-4 py-2.5 text-sm font-semibold cursor-pointer"
                        :style="activeSection === 'layanan' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.8);'">Layanan</a>
                    <a @click.prevent="scrollTo('kontak')" href="#kontak"
                        class="block rounded-lg px-4 py-2.5 text-sm font-semibold cursor-pointer"
                        :style="activeSection === 'kontak' ? 'color: #ffffff; background: rgba(255,255,255,0.15);' :
                            'color: rgba(255,255,255,0.8);'">Kontak</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{-- ========================================== --}}
        {{-- HERO SECTION - Government Blue --}}
        {{-- ========================================== --}}
        <section id="beranda" class="relative flex items-center overflow-hidden"
            style="min-height: 90vh; background: linear-gradient(135deg, #1e3a5f 0%, #1a365d 25%, #1e40af 50%, #1e3a8a 75%, #172554 100%);">
            {{-- Decorative Circles --}}
            <div class="absolute top-20 right-10 h-72 w-72 rounded-full pulse-ring"
                style="background: rgba(96,165,250,0.1); filter: blur(48px);"></div>
            <div class="absolute bottom-10 left-10 h-96 w-96 rounded-full pulse-ring"
                style="background: rgba(129,140,248,0.1); filter: blur(48px); animation-delay: 1.5s;"></div>
            <div class="absolute rounded-full"
                style="top:50%; left:50%; transform:translate(-50%,-50%); height:500px; width:500px; background: rgba(59,130,246,0.05); filter: blur(48px);">
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 pt-32 lg:pt-24">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    {{-- Hero Content --}}
                    <div class="space-y-8 text-center lg:text-left">
                        {{-- Government Badge --}}
                        <div class="reveal inline-flex items-center gap-2 rounded-full px-4 py-2"
                            style="border: 1px solid rgba(147,197,253,0.3); background: rgba(255,255,255,0.1); backdrop-filter: blur(4px);">
                            <svg class="h-4 w-4" style="color: #fde047;" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider" style="color: #dbeafe;">
                                Sistem Resmi Pemerintahan Desa
                            </span>
                        </div>

                        {{-- Village Name - DOMINANT --}}
                        <div class="reveal delay-100">
                            <p class="text-sm font-semibold uppercase lg:text-base"
                                style="letter-spacing: 0.3em; color: #bfdbfe;">
                                Pemerintah Desa
                            </p>
                            <h1 class="mt-2 font-extrabold leading-none"
                                style="color: #ffffff; font-size: clamp(3rem, 8vw, 7rem); letter-spacing: -0.02em;">
                                KOTA BARU
                            </h1>
                            <div class="mx-auto mt-4 rounded-full lg:mx-0"
                                style="height: 6px; width: 128px; background: linear-gradient(to right, #facc15, #fde047, transparent);">
                            </div>
                        </div>

                        {{-- Subtitle --}}
                        <div class="reveal delay-200">
                            <h2 class="text-xl font-semibold sm:text-2xl" style="color: #dbeafe;">
                                Sistem Informasi Administrasi Kependudukan
                            </h2>
                            <p class="mt-3 max-w-lg text-base leading-relaxed mx-auto lg:mx-0"
                                style="color: rgba(191,219,254,0.8);">
                                Layanan administrasi kependudukan yang terstruktur, akurat, dan dapat
                                dipertanggungjawabkan sesuai standar pelayanan publik.
                            </p>
                        </div>

                        {{-- CTA Buttons --}}
                        <div
                            class="reveal delay-300 flex flex-col items-center gap-4 pt-2 sm:flex-row lg:justify-start">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="btn group inline-flex items-center gap-2 rounded-lg text-base font-semibold"
                                    style="background: #ffffff; color: #1e3a8a; border: 2px solid #ffffff; padding: 12px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                    <span>Buka Dashboard</span>
                                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="btn group inline-flex items-center gap-2 rounded-lg text-base font-semibold"
                                    style="background: #ffffff; color: #1e3a8a; border: 2px solid #ffffff; padding: 12px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                    </svg>
                                    <span>Masuk ke Sistem</span>
                                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            @endauth
                            <a @click.prevent="scrollTo('layanan')" href="#layanan"
                                class="inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold transition cursor-pointer"
                                style="border: 2px solid rgba(255,255,255,0.3); color: white;"
                                onmouseover="this.style.background='rgba(255,255,255,0.1)';this.style.borderColor='rgba(255,255,255,0.5)'"
                                onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,0.3)'">
                                <span>Lihat Layanan</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </a>
                        </div>

                        {{-- Location Hint --}}
                        <div class="reveal delay-400 flex items-center justify-center gap-2 text-sm lg:justify-start"
                            style="color: rgba(191,219,254,0.6);">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>Kec. Martapura, Kab. OKU Timur, Sumatera Selatan</span>
                        </div>
                    </div>

                    {{-- Hero Visual - Logo & Emblem --}}
                    <div class="reveal-right relative hidden lg:flex lg:items-center lg:justify-center">
                        {{-- Glowing Ring --}}
                        <div class="absolute h-80 w-80 rounded-full pulse-ring"
                            style="border: 1px solid rgba(255,255,255,0.1);"></div>
                        <div class="absolute h-96 w-96 rounded-full pulse-ring"
                            style="border: 1px solid rgba(255,255,255,0.05); animation-delay: 1s;"></div>

                        {{-- Central Emblem --}}
                        <div class="float-animation relative flex flex-col items-center">
                            {{-- Logo Container --}}
                            <div class="flex h-48 w-48 items-center justify-center rounded-full"
                                style="background: rgba(255,255,255,0.1); box-shadow: 0 0 0 4px rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                                <img src="{{ asset('images/logo-desa.png') }}" alt="Logo Desa Kota Baru"
                                    class="h-32 w-32 object-contain"
                                    style="filter: drop-shadow(0 8px 16px rgba(0,0,0,0.3));">
                            </div>

                            {{-- Feature Pills --}}
                            <div class="mt-8 grid grid-cols-2 gap-3">
                                <div class="flex items-center gap-2 rounded-lg px-4 py-2.5"
                                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                                    <svg class="h-5 w-5" style="color: #bfdbfe;" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    <span class="text-xs font-semibold"
                                        style="color: rgba(255,255,255,0.8);">Penduduk</span>
                                </div>
                                <div class="flex items-center gap-2 rounded-lg px-4 py-2.5"
                                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                                    <svg class="h-5 w-5" style="color: #bfdbfe;" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>
                                    <span class="text-xs font-semibold"
                                        style="color: rgba(255,255,255,0.8);">Keluarga</span>
                                </div>
                                <div class="flex items-center gap-2 rounded-lg px-4 py-2.5"
                                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                                    <svg class="h-5 w-5" style="color: #bfdbfe;" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span class="text-xs font-semibold"
                                        style="color: rgba(255,255,255,0.8);">Surat</span>
                                </div>
                                <div class="flex items-center gap-2 rounded-lg px-4 py-2.5"
                                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);">
                                    <svg class="h-5 w-5" style="color: #bfdbfe;" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                                    </svg>
                                    <span class="text-xs font-semibold"
                                        style="color: rgba(255,255,255,0.8);">Statistik</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Wave --}}
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg"
                    preserveAspectRatio="none" class="h-12 w-full sm:h-16 lg:h-20">
                    <path
                        d="M0 80L48 74.7C96 69.3 192 58.7 288 53.3C384 48 480 48 576 50.7C672 53.3 768 58.7 864 58.7C960 58.7 1056 53.3 1152 50.7C1248 48 1344 48 1392 48L1440 48V80H1392C1344 80 1248 80 1152 80C1056 80 960 80 864 80C768 80 672 80 576 80C480 80 384 80 288 80C192 80 96 80 48 80H0Z"
                        fill="#f9fafb" />
                </svg>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- STATISTIK DESA --}}
        {{-- ========================================== --}}
        <section id="statistik" class="bg-gray-50 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="reveal mb-10 text-center">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 mb-4">
                        <svg class="h-4 w-4" style="color: #1e40af;" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: #1e40af;">Data
                            Kependudukan</span>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Statistik Desa</h2>
                    <p class="mt-3 text-gray-600 max-w-2xl mx-auto">
                        Data kependudukan Desa Kota Baru yang diperbarui secara real-time dari database
                    </p>
                </div>

                {{-- Summary Numbers --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-10">
                    {{-- Penduduk Aktif --}}
                    <div class="reveal stat-card stat-glow transition-all duration-300 hover:border-blue-300">
                        <div class="stat-icon" style="background-color: rgba(59, 130, 246, 0.1);">
                            <svg class="h-6 w-6" style="color: #1e40af;" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-gray-900">
                                {{ number_format($pendudukStats['aktif'] ?? 0) }}</p>
                            <p class="text-sm font-semibold text-gray-500">Penduduk Aktif</p>
                        </div>
                    </div>

                    {{-- Kartu Keluarga --}}
                    <div
                        class="reveal delay-100 stat-card stat-glow transition-all duration-300 hover:border-emerald-300">
                        <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1);">
                            <svg class="h-6 w-6" style="color: #047857;" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($kkStats['total'] ?? 0) }}
                            </p>
                            <p class="text-sm font-semibold text-gray-500">Kartu Keluarga</p>
                        </div>
                    </div>

                    {{-- Surat Bulan Ini --}}
                    <div
                        class="reveal delay-200 stat-card stat-glow transition-all duration-300 hover:border-blue-300">
                        <div class="stat-icon" style="background-color: rgba(59, 130, 246, 0.1);">
                            <svg class="h-6 w-6" style="color: #1e40af;" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-gray-900">
                                {{ number_format($suratStats['bulan_ini'] ?? 0) }}</p>
                            <p class="text-sm font-semibold text-gray-500">Surat Bulan Ini</p>
                        </div>
                    </div>

                    {{-- Peristiwa Tahun Ini --}}
                    <div
                        class="reveal delay-300 stat-card stat-glow transition-all duration-300 hover:border-amber-300">
                        <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1);">
                            <svg class="h-6 w-6" style="color: #b45309;" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($eventStats['total'] ?? 0) }}
                            </p>
                            <p class="text-sm font-semibold text-gray-500">Peristiwa Tahun Ini</p>
                        </div>
                    </div>
                </div>

                {{-- Charts Row --}}
                <div class="grid gap-6 lg:grid-cols-2 mb-6">
                    {{-- Gender Distribution Doughnut --}}
                    <div class="reveal card p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Distribusi Jenis Kelamin</h3>
                        <p class="text-sm text-gray-500 mb-4">Perbandingan jumlah penduduk laki-laki dan perempuan</p>
                        <div class="flex items-center justify-center" style="height: 280px;">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>

                    {{-- Age Group Bar Chart --}}
                    <div class="reveal delay-100 card p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Kelompok Usia Penduduk</h3>
                        <p class="text-sm text-gray-500 mb-4">Distribusi penduduk aktif berdasarkan rentang usia</p>
                        <div style="height: 280px;">
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Events by Month Chart --}}
                <div class="reveal delay-200 card p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Peristiwa Kependudukan per Bulan</h3>
                    <p class="text-sm text-gray-500 mb-4">Jumlah peristiwa (kelahiran, kematian, pindah, datang) tahun
                        {{ date('Y') }}</p>
                    <div style="height: 280px;">
                        <canvas id="eventsChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- LAYANAN UTAMA --}}
        {{-- ========================================== --}}
        <section id="layanan" class="bg-gray-50 pb-16 pt-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="reveal mb-12 text-center">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 mb-4">
                        <svg class="h-4 w-4" style="color: #1e40af;" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75l-5.571-3m11.142 0l4.179 2.25L12 17.25l-9.75-5.25 4.179-2.25" />
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: #1e40af;">Fitur
                            Sistem</span>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Layanan Utama</h2>
                    <p class="mt-3 text-gray-600 max-w-2xl mx-auto">
                        Akses cepat ke fitur administrasi kependudukan yang terintegrasi dan mudah digunakan
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    {{-- Service 1: Penduduk --}}
                    <div class="reveal group service-card card card-hover p-8 text-center transition-all duration-300">
                        <div class="service-icon mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl transition-all duration-300"
                            style="background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(29,78,216,0.15)); border: 2px solid rgba(59,130,246,0.2);">
                            <svg class="h-10 w-10" style="color: #1d4ed8;" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Data Penduduk</h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">
                            Kelola data identitas, NIK, dan status kependudukan warga secara terstruktur dan akurat.
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #1e40af;">NIK</span>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #1e40af;">KK</span>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #1e40af;">Biodata</span>
                        </div>
                    </div>

                    {{-- Service 2: Peristiwa --}}
                    <div
                        class="reveal delay-100 group service-card card card-hover p-8 text-center transition-all duration-300">
                        <div class="service-icon mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl transition-all duration-300"
                            style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(5,150,105,0.15)); border: 2px solid rgba(16,185,129,0.2);">
                            <svg class="h-10 w-10" style="color: #047857;" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Peristiwa Kependudukan</h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">
                            Catat kelahiran, kematian, perpindahan, dan kedatangan penduduk dengan akurat.
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #047857;">Lahir</span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #047857;">Meninggal</span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #047857;">Pindah</span>
                        </div>
                    </div>

                    {{-- Service 3: Surat --}}
                    <div
                        class="reveal delay-200 group service-card card card-hover p-8 text-center transition-all duration-300">
                        <div class="service-icon mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl transition-all duration-300"
                            style="background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(217,119,6,0.15)); border: 2px solid rgba(245,158,11,0.2);">
                            <svg class="h-10 w-10" style="color: #b45309;" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Penerbitan Surat</h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">
                            Proses penerbitan surat keterangan dengan penomoran yang terkendali dan tersistem.
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #b45309;">SK Domisili</span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #b45309;">SK Usaha</span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold"
                                style="color: #b45309;">SKTM</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- KONTAK & INFORMASI --}}
        {{-- ========================================== --}}
        <section id="kontak" class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="reveal mb-12 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Informasi Pelayanan</h2>
                    <p class="mt-3 text-gray-600">Hubungi kami untuk kebutuhan administrasi kependudukan</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    {{-- Contact --}}
                    <div class="reveal info-box group transition-all duration-300 hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl"
                                style="background: linear-gradient(135deg, #1e40af, #1d4ed8);">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Kontak Pelayanan</p>
                                <p class="mt-1 text-sm text-gray-600">Telepon: (021) 1234-5678</p>
                                <p class="text-sm text-gray-600">Email: admin@desakotabaru.go.id</p>
                            </div>
                        </div>
                    </div>

                    {{-- Operating Hours --}}
                    <div class="reveal delay-100 info-box group transition-all duration-300 hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl"
                                style="background: linear-gradient(135deg, #047857, #059669);">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Jam Pelayanan</p>
                                <p class="mt-1 text-sm text-gray-600">Senin - Jumat</p>
                                <p class="text-sm font-semibold text-gray-700">08:00 - 14:00 WIB</p>
                            </div>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="reveal delay-200 info-box group transition-all duration-300 hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl"
                                style="background: linear-gradient(135deg, #b45309, #d97706);">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Alamat Kantor</p>
                                <p class="mt-1 text-sm text-gray-600">Jl. Pertanian</p>
                                <p class="text-sm text-gray-600">Kota Baru, Martapura, OKU Timur</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ========================================== --}}
    {{-- FOOTER --}}
    {{-- ========================================== --}}
    <footer
        style="background: linear-gradient(135deg, #1e3a5f 0%, #1a365d 25%, #1e40af 50%, #1e3a8a 75%, #172554 100%); color: white;">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid gap-8 lg:grid-cols-3">
                {{-- Brand --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg"
                            style="background: rgba(255,255,255,0.15); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);">
                            <img src="{{ asset('images/logo-desa.png') }}" alt="Logo"
                                class="h-7 w-7 object-contain">
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color: #ffffff;">SIAK-DESA</p>
                            <p class="text-xs" style="color: #bfdbfe;">Desa Kota Baru</p>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed max-w-sm" style="color: rgba(191,219,254,0.7);">
                        Sistem Informasi Administrasi Kependudukan untuk pelayanan publik yang lebih baik.
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider mb-4" style="color: #bfdbfe;">Tautan Cepat
                    </h4>
                    <ul class="space-y-2">
                        <li>
                            <a @click.prevent="scrollTo('beranda')" href="#beranda"
                                class="text-sm transition cursor-pointer"
                                style="color: rgba(191,219,254,0.7);">Beranda</a>
                        </li>
                        <li>
                            <a @click.prevent="scrollTo('statistik')" href="#statistik"
                                class="text-sm transition cursor-pointer"
                                style="color: rgba(191,219,254,0.7);">Statistik</a>
                        </li>
                        <li>
                            <a @click.prevent="scrollTo('layanan')" href="#layanan"
                                class="text-sm transition cursor-pointer"
                                style="color: rgba(191,219,254,0.7);">Layanan</a>
                        </li>
                        <li>
                            <a @click.prevent="scrollTo('kontak')" href="#kontak"
                                class="text-sm transition cursor-pointer"
                                style="color: rgba(191,219,254,0.7);">Kontak</a>
                        </li>
                    </ul>
                </div>

                {{-- Support Badge --}}
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider mb-4" style="color: #bfdbfe;">Dukungan</h4>
                    <a href="https://www.unmaha.ac.id/" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-lg px-4 py-3 transition-all hover:scale-105"
                        style="background: rgba(255,255,255,0.1); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);"
                        onmouseover="this.style.background='rgba(255,255,255,0.15)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                        <svg class="h-5 w-5" style="color: #fde047;" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                        <span class="text-sm font-semibold" style="color: #dbeafe;">Universitas Mahakarya Asia</span>
                    </a>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="mt-10 pt-6" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p class="text-xs" style="color: rgba(191,219,254,0.5);">&copy; {{ date('Y') }} Pemerintah
                        Desa Kota Baru. Hak cipta dilindungi.</p>
                    <div class="flex items-center gap-1 text-xs" style="color: rgba(191,219,254,0.5);">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>SIAK-DESA v1.0</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Chart.js Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gender Distribution Doughnut Chart
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                new Chart(genderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Laki-laki', 'Perempuan'],
                        datasets: [{
                            data: [{{ $pendudukStats['laki_laki'] ?? 0 }},
                                {{ $pendudukStats['perempuan'] ?? 0 }}
                            ],
                            backgroundColor: ['#3b82f6', '#ec4899'],
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 13,
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? ((context.raw / total) * 100).toFixed(
                                            1) : 0;
                                        return context.label + ': ' + context.raw.toLocaleString(
                                            'id-ID') + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }

            // Age Group Bar Chart
            const ageCtx = document.getElementById('ageChart');
            if (ageCtx) {
                new Chart(ageCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($pendudukByAge['labels'] ?? []) !!},
                        datasets: [{
                            label: 'Jumlah Penduduk',
                            data: {!! json_encode($pendudukByAge['data'] ?? []) !!},
                            backgroundColor: [
                                '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8',
                                '#1e40af', '#1e3a8a', '#312e81', '#4c1d95'
                            ],
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Jumlah: ' + context.raw.toLocaleString('id-ID') +
                                            ' jiwa';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.06)'
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Events by Month Bar Chart
            const eventsCtx = document.getElementById('eventsChart');
            if (eventsCtx) {
                new Chart(eventsCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($eventsByMonth['labels'] ?? []) !!},
                        datasets: [{
                            label: 'Jumlah Peristiwa',
                            data: {!! json_encode($eventsByMonth['data'] ?? []) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Peristiwa: ' + context.raw.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.06)'
                                },
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
